<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class Controller
{
    protected function getSupportedLocales(): array
    {
        return [
            'en' => 'English',
            'id' => 'Bahasa Indonesia',
        ];
    }

    protected function normalizeLocale(?string $locale, string $default = 'en'): string
    {
        $locale = strtolower(trim((string) $locale));
        $supportedLocales = $this->getSupportedLocales();

        return array_key_exists($locale, $supportedLocales) ? $locale : $default;
    }

    protected function getCurrentLocale(?string $fallback = null): string
    {
        $sessionLocale = session('locale');
        if (is_string($sessionLocale) && trim($sessionLocale) !== '') {
            $locale = $this->normalizeLocale($sessionLocale, 'en');
        } else {
            $locale = $this->normalizeLocale(app()->getLocale() ?: $fallback, 'en');
        }

        app()->setLocale($locale);

        return $locale;
    }

    protected function setCurrentLocale(string $locale): string
    {
        $locale = $this->normalizeLocale($locale, 'en');
        session(['locale' => $locale]);
        app()->setLocale($locale);

        return $locale;
    }

    protected function getSystemSettings(): array
    {
        $setting = Schema::hasTable('system') ? DB::table('system')->first() : null;

        if (!$setting) {
            return [
                'website_name' => 'Family Tree System',
                'logo_path' => '',
                'systemlogo' => '',
                'logo_url' => '',
                'systemcontact' => '',
                'systemmanager' => '',
                'systemaddress' => '',
            ];
        }

        $logoPath = trim((string) ($setting->systemlogo ?? ''));
        $logoUrl = '';

        if ($logoPath !== '') {
            $logoUrl = (preg_match('#^https?://#i', $logoPath) || str_starts_with($logoPath, 'data:'))
                ? $logoPath
                : asset(ltrim($logoPath, '/'));

            if ($logoUrl !== '' && str_starts_with($logoUrl, '/') && !str_contains($logoUrl, '?')) {
                $localLogoPath = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR
                    . ltrim($logoPath, '/');
                if (is_file($localLogoPath)) {
                    $logoUrl .= '?v=' . @filemtime($localLogoPath);
                }
            }
        }

        return [
            'website_name' => $setting->systemname ?? 'Family Tree System',
            'logo_path' => $logoPath,
            'systemlogo' => $logoPath,
            'logo_url' => $logoUrl,
            'systemcontact' => $setting->systemcontact ?? '',
            'systemmanager' => $setting->systemmanager ?? '',
            'systemaddress' => $setting->systemaddress ?? '',
        ];
    }

    protected function getLandingPageSettings(): array
    {
        $defaults = [
            'family_name' => 'Family Tree',
            'description' => 'A private family space for stories, memories, and important moments.',
            'head_of_family_name' => 'Family Head',
            'head_of_family_message' => 'Welcome to our family story.',
            'head_of_family_photo' => '',
            'created_by_name' => 'Created by',
            'created_by_photo' => '',
            'designed_by_name' => 'Designed by',
            'designed_by_photo' => '',
            'approved_by_name' => 'Approved by',
            'approved_by_photo' => '',
            'acknowledged_by_name' => 'Acknowledged by',
            'acknowledged_by_photo' => '',
        ];

        $settings = $defaults;

        if (Schema::hasTable('landing_page_settings')) {
            $row = DB::table('landing_page_settings')->orderBy('id')->first();
            if ($row) {
                foreach (array_keys($defaults) as $field) {
                    $value = trim((string) ($row->{$field} ?? ''));
                    if ($value !== '') {
                        $settings[$field] = $value;
                    }
                }
            }
        }

        foreach ([
            'head_of_family_photo',
            'created_by_photo',
            'designed_by_photo',
            'approved_by_photo',
            'acknowledged_by_photo',
        ] as $photoField) {
            $photoPath = trim((string) ($settings[$photoField] ?? ''));
            $settings[$photoField . '_url'] = $photoPath !== '' ? $this->resolvePublicFileUrl($photoPath) : '';
        }

        return $settings;
    }

    protected function saveLandingPageSettings(array $settings): void
    {
        if (!Schema::hasTable('landing_page_settings')) {
            return;
        }

        DB::table('landing_page_settings')->updateOrInsert(
            ['id' => 1],
            [
                'id' => 1,
                'family_name' => trim((string) ($settings['family_name'] ?? '')),
                'description' => trim((string) ($settings['description'] ?? '')),
                'head_of_family_name' => trim((string) ($settings['head_of_family_name'] ?? '')),
                'head_of_family_message' => trim((string) ($settings['head_of_family_message'] ?? '')),
                'head_of_family_photo' => trim((string) ($settings['head_of_family_photo'] ?? '')),
                'created_by_name' => trim((string) ($settings['created_by_name'] ?? '')),
                'created_by_photo' => trim((string) ($settings['created_by_photo'] ?? '')),
                'designed_by_name' => trim((string) ($settings['designed_by_name'] ?? '')),
                'designed_by_photo' => trim((string) ($settings['designed_by_photo'] ?? '')),
                'approved_by_name' => trim((string) ($settings['approved_by_name'] ?? '')),
                'approved_by_photo' => trim((string) ($settings['approved_by_photo'] ?? '')),
                'acknowledged_by_name' => trim((string) ($settings['acknowledged_by_name'] ?? '')),
                'acknowledged_by_photo' => trim((string) ($settings['acknowledged_by_photo'] ?? '')),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }

    protected function resolvePublicFileUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:https?:|data:|blob:)#i', $path)) {
            return $path;
        }

        $url = asset(ltrim($path, '/'));
        $localPath = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . ltrim($path, '/');

        if ($localPath !== DIRECTORY_SEPARATOR && is_file($localPath)) {
            $url .= '?v=' . @filemtime($localPath);
        }

        return $url;
    }

    protected function translateLandingSettings(array $settings, string $targetLocale, string $sourceLocale = 'en'): array
    {
        $targetLocale = $this->normalizeLocale($targetLocale, $sourceLocale);
        if ($targetLocale === $sourceLocale) {
            return $settings;
        }

        foreach ($settings as $field => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }

            if (str_ends_with((string) $field, '_photo') || str_ends_with((string) $field, '_url')) {
                continue;
            }

            $originalValue = trim((string) $value);
            if ($originalValue === '') {
                continue;
            }

            $settings[$field] = $this->translateLandingField(
                $originalValue,
                $targetLocale,
                $sourceLocale,
                'landing.' . $field,
                $this->translateLandingFallback((string) $field, $targetLocale, $originalValue)
            );
        }

        return $settings;
    }

    protected function translateLandingField(
        string $value,
        string $targetLocale,
        string $sourceLocale,
        string $cacheKey,
        ?string $fallback = null
    ): string {
        $value = trim($value);
        if ($value === '') {
            return $fallback ?? '';
        }

        $manualTranslation = $this->translateLandingManually($value, $targetLocale, $sourceLocale, $cacheKey);
        if ($manualTranslation !== null && $manualTranslation !== '') {
            return $manualTranslation;
        }

        $translated = $this->translateTextWithCache($value, $targetLocale, $sourceLocale, $cacheKey);
        $translated = trim($translated);

        if ($translated === '') {
            return $fallback ?? $value;
        }

        return $translated;
    }

    protected function translateLandingFallback(string $field, string $targetLocale, string $originalValue): ?string
    {
        $field = trim($field);
        $targetLocale = $this->normalizeLocale($targetLocale, 'en');

        $fallbackMap = [
            'description' => [
                'id' => [
                    'A private family space for stories, memories, and important moments.' => 'Ruang keluarga privat untuk cerita, kenangan, dan momen penting.',
                    'A private family portal for history, memory, and togetherness.' => 'Portal keluarga privat untuk sejarah, kenangan, dan kebersamaan.',
                ],
            ],
            'head_of_family_message' => [
                'id' => [
                    'Welcome to our family story.' => 'Selamat datang di cerita keluarga kami.',
                ],
            ],
        ];

        return $fallbackMap[$field][$targetLocale][$originalValue] ?? null;
    }

    protected function translateLandingManually(
        string $value,
        string $targetLocale,
        string $sourceLocale,
        string $cacheKey
    ): ?string {
        $value = trim($value);
        $targetLocale = $this->normalizeLocale($targetLocale, $sourceLocale);
        $sourceLocale = $this->normalizeLocale($sourceLocale, 'en');

        if ($value === '' || $targetLocale === $sourceLocale) {
            return $value;
        }

        $manualMap = [
            'landing.description' => [
                'en' => [
                    'A private family space for stories, memories, and important moments.' => 'A private family space for stories, memories, and important moments.',
                    'A private family portal for history, memory, and togetherness.' => 'A private family portal for history, memory, and togetherness.',
                    'Everything is about hahahehehuhuhihihoho' => 'Everything is about hahahehehuhuhihihoho',
                ],
                'id' => [
                    'A private family space for stories, memories, and important moments.' => 'Ruang keluarga privat untuk cerita, kenangan, dan momen penting.',
                    'A private family portal for history, memory, and togetherness.' => 'Portal keluarga privat untuk sejarah, kenangan, dan kebersamaan.',
                    'Everything is about hahahehehuhuhihihoho' => 'Semuanya tentang hahahehehuhuhihihoho',
                ],
            ],
            'landing.head_of_family_message' => [
                'en' => [
                    'Welcome to our family story.' => 'Welcome to our family story.',
                ],
                'id' => [
                    'Welcome to our family story.' => 'Selamat datang di cerita keluarga kami.',
                ],
            ],
        ];

        $cacheKey = trim($cacheKey);
        if (!isset($manualMap[$cacheKey][$targetLocale])) {
            return null;
        }

        if (isset($manualMap[$cacheKey][$targetLocale][$value])) {
            return $manualMap[$cacheKey][$targetLocale][$value];
        }

        if ($cacheKey === 'landing.description' && $targetLocale === 'id' && str_starts_with($value, 'Everything is about ')) {
            return 'Semuanya tentang ' . substr($value, strlen('Everything is about '));
        }

        return null;
    }

    protected function translateTextWithCache(
        string $text,
        string $targetLocale,
        string $sourceLocale = 'en',
        string $cacheKey = 'generic'
    ): string {
        $text = trim($text);
        $targetLocale = $this->normalizeLocale($targetLocale, $sourceLocale);
        $sourceLocale = $this->normalizeLocale($sourceLocale, 'en');

        if ($text === '' || $targetLocale === $sourceLocale) {
            return $text;
        }

        $hash = hash('sha256', $text);
        $cacheKey = trim($cacheKey) !== '' ? trim($cacheKey) : 'generic';
        $memoryKey = sprintf('translation:%s:%s:%s:%s', $cacheKey, $sourceLocale, $targetLocale, $hash);

        return Cache::rememberForever($memoryKey, function () use ($text, $targetLocale, $sourceLocale, $cacheKey, $hash) {
            $cachedText = $this->getCachedTranslation($cacheKey, $sourceLocale, $targetLocale, $hash);
            if ($cachedText !== null && $cachedText !== '' && trim($cachedText) !== $text) {
                return $cachedText;
            }

            $translated = $this->translateViaGooglePackage($text, $sourceLocale, $targetLocale);
            if ($translated === '' || trim($translated) === $text) {
                if (trim($sourceLocale) !== 'auto') {
                    $translated = $this->translateViaGooglePackage($text, 'auto', $targetLocale);
                }
            }

            if ($translated === '' || trim($translated) === $text) {
                return $text;
            }

            $this->storeTranslationCache($cacheKey, $sourceLocale, $targetLocale, $hash, $text, $translated);

            return $translated;
        });
    }

    protected function getCachedTranslation(
        string $cacheKey,
        string $sourceLocale,
        string $targetLocale,
        string $sourceHash
    ): ?string {
        if (!Schema::hasTable('translation_cache')) {
            return null;
        }

        $row = DB::table('translation_cache')
            ->where('cache_key', $cacheKey)
            ->where('source_locale', $sourceLocale)
            ->where('target_locale', $targetLocale)
            ->where('source_hash', $sourceHash)
            ->orderByDesc('id')
            ->first();

        $translated = trim((string) ($row->translated_text ?? ''));

        return $translated !== '' ? $translated : null;
    }

    protected function storeTranslationCache(
        string $cacheKey,
        string $sourceLocale,
        string $targetLocale,
        string $sourceHash,
        string $sourceText,
        string $translatedText
    ): void {
        if (!Schema::hasTable('translation_cache')) {
            return;
        }

        DB::table('translation_cache')->updateOrInsert(
            [
                'cache_key' => $cacheKey,
                'source_locale' => $sourceLocale,
                'target_locale' => $targetLocale,
                'source_hash' => $sourceHash,
            ],
            [
                'source_text' => $sourceText,
                'translated_text' => $translatedText,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        );
    }

    protected function translateViaGooglePackage(string $text, string $sourceLocale, string $targetLocale): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        try {
            if (class_exists(\Stichoza\GoogleTranslate\GoogleTranslate::class)) {
                foreach (array_values(array_unique([$sourceLocale, 'auto', 'en'])) as $candidateSource) {
                    $translator = new \Stichoza\GoogleTranslate\GoogleTranslate($targetLocale);
                    $translator->setSource($candidateSource);
                    $translated = trim((string) $translator->translate($text));
                    if ($translated !== '' && strcasecmp($translated, $text) !== 0) {
                        return $translated;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fall back to the original text if the translation provider fails.
        }

        foreach (array_values(array_unique([$sourceLocale, 'auto', 'en'])) as $candidateSource) {
            $fallbackTranslated = $this->translateViaGoogleHttpFallback($text, $candidateSource, $targetLocale);
            if ($fallbackTranslated !== '' && strcasecmp($fallbackTranslated, $text) !== 0) {
                return $fallbackTranslated;
            }
        }

        return $text;
    }

    protected function translateViaGoogleHttpFallback(string $text, string $sourceLocale, string $targetLocale): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        try {
            $response = Http::timeout(8)->retry(1, 150)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => $sourceLocale,
                'tl' => $targetLocale,
                'dt' => 't',
                'q' => $text,
            ]);

            if (!$response->successful()) {
                return '';
            }

            $payload = $response->json();
            if (!is_array($payload) || !isset($payload[0]) || !is_array($payload[0])) {
                return '';
            }

            $parts = [];
            foreach ($payload[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $parts[] = $segment[0];
                }
            }

            $translated = trim(implode('', $parts));
            return $translated !== '' ? $translated : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function normalizePhoneNumber(?string $phoneNumber): string
    {
        $normalized = preg_replace('/\D+/', '', trim((string) $phoneNumber));

        return is_string($normalized) ? $normalized : '';
    }

    protected function findAccountByPhoneNumber(?string $phoneNumber): ?object
    {
        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);
        if ($normalizedPhone === '') {
            return null;
        }

        $phoneColumns = [
            'family_member' => ['phonenumber'],
            'employer' => ['phonenumber'],
        ];

        foreach ($phoneColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table)->select('userid');
            $hasPhoneConstraint = false;

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                $phoneExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE($column, ''), ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '')";
                if ($hasPhoneConstraint) {
                    $query->orWhereRaw($phoneExpression . ' = ?', [$normalizedPhone]);
                } else {
                    $query->whereRaw($phoneExpression . ' = ?', [$normalizedPhone]);
                    $hasPhoneConstraint = true;
                }
            }

            if (Schema::hasColumn($table, 'pending_phonenumber')) {
                $phoneExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(pending_phonenumber, ''), ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '')";
                if ($hasPhoneConstraint) {
                    $query->orWhereRaw($phoneExpression . ' = ?', [$normalizedPhone]);
                } else {
                    $query->whereRaw($phoneExpression . ' = ?', [$normalizedPhone]);
                    $hasPhoneConstraint = true;
                }
            }

            $account = $query->first();
            if ($account) {
                return $account;
            }
        }

        return 1;
    }

    protected function storeAuthenticatedSession(
        Request $request,
        object $user,
        ?object $level,
        ?object $employer,
        ?object $familyMember,
        string $loginMethod = 'password'
    ): void {
        $displayName = trim((string) ($employer->name ?? $familyMember->name ?? ''));
        if ($displayName === '') {
            $displayName = (string) $user->username;
        }

        $roleId = $employer->roleid ?? null;
        if ($roleId === null && (int) ($user->levelid ?? 0) === 2) {
            $roleId = 4;
        }

        $roleName = $employer->rolename ?? null;
        if ($roleName === null && (int) $roleId === 4) {
            $roleName = 'Family Member';
        }

        $request->session()->regenerate();
        $request->session()->put('authenticated_user', [
            'userid' => $user->userid,
            'username' => $user->username,
            'name' => $displayName,
            'levelid' => $user->levelid,
            'levelname' => $level->levelname ?? null,
            'roleid' => $roleId,
            'rolename' => $roleName,
            'employer' => $employer,
            'familyMember' => $familyMember,
        ]);

        $authUser = User::query()->find((int) $user->userid);
        if ($authUser) {
            Auth::login($authUser);
        }

        $this->logActivity($request, $loginMethod === 'google' ? 'auth.login_google' : 'auth.login', [
            'login_method' => $loginMethod,
            'userid' => (int) ($user->userid ?? 0),
            'username' => (string) ($user->username ?? ''),
            'levelid' => (int) ($user->levelid ?? 0),
        ]);
    }

    protected function isLoginCaptchaRequired(Request $request): bool
    {
        $attempts = (int) $request->session()->get('login_failed_attempts', 0);

        return $attempts >= 3;
    }

    protected function isOnlineLoginCaptchaConfigured(): bool
    {
        return !empty(config('services.recaptcha.site_key'))
            && !empty(config('services.recaptcha.secret_key'));
    }

    protected function refreshOfflineLoginCaptchaChallenge(Request $request): string
    {
        $a = rand(1, 10);
        $b = rand(1, 10);
        $request->session()->put('login_offline_captcha_answer', $a + $b);

        return "Berapakah $a + $b?";
    }

    protected function incrementLoginFailedAttempts(Request $request): int
    {
        $attempts = (int) $request->session()->get('login_failed_attempts', 0) + 1;
        $request->session()->put('login_failed_attempts', $attempts);

        return $attempts;
    }

    protected function resetLoginFailedAttempts(Request $request): void
    {
        $request->session()->forget('login_failed_attempts');
        $request->session()->forget('login_offline_captcha_answer');
    }

    protected function verifyOfflineLoginCaptchaAnswer(Request $request, string $answer): bool
    {
        $correct = $request->session()->get('login_offline_captcha_answer');

        return (int) $answer === (int) $correct;
    }

    protected function verifyRecaptchaResponse(string $token, string $remoteIp = ''): bool
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => (string) config('services.recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        return $response->json('success') === true;
    }

    protected function logActivity(Request $request, string $action, array $context = []): void
    {
        $path = storage_path('app/activity_log.jsonl');
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        $sessionUser = (array) $request->session()->get('authenticated_user', []);
        $actor = [
            'userid' => (int) ($sessionUser['userid'] ?? 0),
            'username' => (string) ($sessionUser['username'] ?? ''),
            'name' => (string) ($sessionUser['name'] ?? ''),
            'levelid' => isset($sessionUser['levelid']) ? (int) $sessionUser['levelid'] : null,
            'levelname' => $sessionUser['levelname'] ?? null,
            'roleid' => isset($sessionUser['roleid']) ? (int) $sessionUser['roleid'] : null,
            'rolename' => $sessionUser['rolename'] ?? null,
        ];

        $entry = [
            'id' => (string) Str::uuid(),
            'action' => (string) $action,
            'context' => $context,
            'actor' => $actor,
            'ip_address' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        File::append($path, json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

        try {
            if (DB::getSchemaBuilder()->hasTable('activity_log')) {
                $payload = [
                    'action' => (string) $action,
                    'context' => json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'user_agent' => (string) $request->userAgent(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'userid')) {
                    $payload['userid'] = (int) ($actor['userid'] ?? 0);
                }
                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'user_id')) {
                    $payload['user_id'] = (int) ($actor['userid'] ?? 0);
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'ip_address')) {
                    $payload['ip_address'] = (string) $request->ip();
                }
                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'ip_adress')) {
                    $payload['ip_adress'] = (string) $request->ip();
                }

                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'latitude')) {
                    $payload['latitude'] = null;
                }
                if (DB::getSchemaBuilder()->hasColumn('activity_log', 'longitude')) {
                    $payload['longitude'] = null;
                }

                DB::table('activity_log')->insert($payload);
            }
        } catch (\Throwable $e) {
            // Ignore DB logging errors and keep the JSONL fallback.
        }
    }

    protected function resolveCurrentFamilyId(int $userId): ?int
    {
        $sessionUser = (array) session('authenticated_user', []);
        $roleId = isset($sessionUser['roleid']) ? (int) $sessionUser['roleid'] : 0;

        foreach (['user' => 'userid', 'family_member' => 'userid', 'employer' => 'userid'] as $table => $userColumn) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (['family_id', 'familyid'] as $familyColumn) {
                if (!Schema::hasColumn($table, $familyColumn)) {
                    continue;
                }

                $value = DB::table($table)->where($userColumn, $userId)->value($familyColumn);
                if ($value !== null && (int) $value > 0) {
                    return (int) $value;
                }
            }
        }

        foreach (['familyMember.family_id', 'familyMember.familyid', 'employer.family_id', 'employer.familyid', 'family_id', 'familyid'] as $sessionPath) {
            $value = data_get($sessionUser, $sessionPath);
            if ($value !== null && (int) $value > 0) {
                return (int) $value;
            }
        }

        foreach (['familyMember.memberid', 'familyMember.member_id', 'familyMember.userid'] as $sessionPath) {
            $memberId = data_get($sessionUser, $sessionPath);
            if ($memberId === null || (int) $memberId <= 0) {
                continue;
            }

            foreach (['family_member', 'user'] as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $lookupColumn = $table === 'family_member'
                    ? (Schema::hasColumn($table, 'memberid') ? 'memberid' : 'userid')
                    : 'userid';

                if (!Schema::hasColumn($table, $lookupColumn)) {
                    continue;
                }

                foreach (['family_id', 'familyid'] as $familyColumn) {
                    if (!Schema::hasColumn($table, $familyColumn)) {
                        continue;
                    }

                    $value = DB::table($table)
                        ->where($lookupColumn, (int) $memberId)
                        ->value($familyColumn);

                    if ($value !== null && (int) $value > 0) {
                        return (int) $value;
                    }
                }
            }
        }


        foreach (['family_member', 'employer', 'user'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (['family_id', 'familyid'] as $familyColumn) {
                if (!Schema::hasColumn($table, $familyColumn)) {
                    continue;
                }

                $value = DB::table($table)
                    ->where('userid', $userId)
                    ->whereNotNull($familyColumn)
                    ->where($familyColumn, '>', 0)
                    ->value($familyColumn);

                if ($value !== null && (int) $value > 0) {
                    return (int) $value;
                }
            }
        }

        foreach (['families', 'family'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (['id', 'familyid', 'family_id'] as $familyColumn) {
                if (!Schema::hasColumn($table, $familyColumn)) {
                    continue;
                }

                $value = DB::table($table)
                    ->orderBy($familyColumn)
                    ->value($familyColumn);

                if ($value !== null && (int) $value > 0) {
                    return (int) $value;
                }
            }
        }

        if (in_array($roleId, [1, 2], true)) {
            foreach (['family_member', 'employer', 'user'] as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                foreach (['family_id', 'familyid'] as $familyColumn) {
                    if (!Schema::hasColumn($table, $familyColumn)) {
                        continue;
                    }

                    $value = DB::table($table)
                        ->whereNotNull($familyColumn)
                        ->where($familyColumn, '>', 0)
                        ->orderBy($familyColumn)
                        ->value($familyColumn);

                    if ($value !== null && (int) $value > 0) {
                        return (int) $value;
                    }
                }
            }
        }

        return 1;
    }

    protected function formatTimelineDateLabel($eventDate, $eventYear): string
    {
        $eventDate = trim((string) ($eventDate ?? ''));
        if ($eventDate !== '') {
            try {
                return Carbon::parse($eventDate)->format('F j, Y');
            } catch (\Throwable $e) {
                return $eventDate;
            }
        }

        $eventYear = trim((string) ($eventYear ?? ''));
        if ($eventYear !== '') {
            return $eventYear;
        }

        return 'Undated';
    }
}
