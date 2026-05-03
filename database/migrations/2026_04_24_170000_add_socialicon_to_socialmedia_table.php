<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('socialmedia')) {
            return;
        }

        if (!Schema::hasColumn('socialmedia', 'socialicon')) {
            Schema::table('socialmedia', function (Blueprint $table) {
                $table->string('socialicon', 100)->nullable()->after('socialname');
            });
        }

        if (!Schema::hasColumn('socialmedia', 'socialicon')) {
            return;
        }

        $socialRows = DB::table('socialmedia')
            ->select('socialid', 'socialname', 'socialicon')
            ->get();

        foreach ($socialRows as $socialRow) {
            $currentIcon = trim((string) ($socialRow->socialicon ?? ''));
            $socialName = trim((string) ($socialRow->socialname ?? ''));
            $resolvedIcon = $this->normalizeSocialIcon('', $socialName);
            if ($resolvedIcon === '') {
                $resolvedIcon = $this->normalizeSocialIcon($currentIcon, $socialName);
            }

            if ($resolvedIcon === '' || $resolvedIcon === $currentIcon) {
                continue;
            }

            DB::table('socialmedia')
                ->where('socialid', (int) ($socialRow->socialid ?? 0))
                ->update([
                    'socialicon' => $resolvedIcon,
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('socialmedia')) {
            return;
        }

        if (!Schema::hasColumn('socialmedia', 'socialicon')) {
            return;
        }

        Schema::table('socialmedia', function (Blueprint $table) {
            $table->dropColumn('socialicon');
        });
    }

    private function normalizeSocialIcon(string $iconValue, string $socialName): string
    {
        $keywordMap = [
            'instagram' => ['instagram', 'insta', 'ig'],
            'facebook' => ['facebook', 'fb', 'meta'],
            'x' => ['x', 'xcom', 'twitter'],
            'tiktok' => ['tiktok'],
            'linkedin' => ['linkedin'],
            'youtube' => ['youtube', 'youtu', 'yt'],
            'github' => ['github'],
            'telegram' => ['telegram'],
            'whatsapp' => ['whatsapp', 'wa'],
            'line' => ['line'],
            'discord' => ['discord'],
            'threads' => ['threads'],
            'reddit' => ['reddit'],
            'pinterest' => ['pinterest'],
        ];

        $supportedKeys = array_keys($keywordMap);
        $normalizeRawValue = function (?string $value) use ($keywordMap): string {
            $raw = trim((string) $value);
            if ($raw === '') {
                return '';
            }

            $normalized = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $raw));
            if ($normalized === '') {
                return '';
            }

            foreach ($keywordMap as $platformKey => $keywords) {
                foreach ($keywords as $keyword) {
                    if ($normalized === $keyword || strpos($normalized, $keyword) !== false) {
                        return $platformKey;
                    }
                }
            }

            return $normalized;
        };

        $normalizedIconKey = $normalizeRawValue($iconValue);
        $normalizedNameKey = $normalizeRawValue($socialName);
        $hasRawIconValue = trim($iconValue) !== '';
        $nameIsSupported = in_array($normalizedNameKey, $supportedKeys, true);
        $iconIsSupported = in_array($normalizedIconKey, $supportedKeys, true);

        if ($nameIsSupported && (!$hasRawIconValue || !$iconIsSupported)) {
            return $normalizedNameKey;
        }

        if ($normalizedIconKey !== '') {
            return $normalizedIconKey;
        }

        return $normalizedNameKey;
    }
};
