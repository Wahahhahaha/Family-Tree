<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    public function askChatbotAi(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return $this->chatbotTextResponse('Unauthenticated.', 401);
        }

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:1500'],
        ], [
            'message.required' => 'Message is required.',
            'message.max' => 'Message is too long.',
            'history.array' => 'History format is invalid.',
            'history.max' => 'History is too long.',
        ]);

        if ($validator->fails()) {
            $firstError = (string) $validator->errors()->first('message');
            if ($firstError === '') {
                $firstError = (string) $validator->errors()->first();
            }

            return $this->chatbotTextResponse(
                $firstError !== '' ? $firstError : 'Validation failed.',
                422
            );
        }

        $validated = $validator->validated();
        $userMessage = trim((string) ($validated['message'] ?? ''));
        $history = $validated['history'] ?? [];
        $preferGroq = $request->boolean('prefer_groq') || $request->boolean('prefer_xai');
        $databaseReply = '';
        $workflowReply = '';
        $localFallbackReply = '';

        if (!$preferGroq) {
            $databaseReply = $this->buildChatbotDatabaseReply($request, $userMessage);
            if ($databaseReply !== '') {
                return $this->chatbotTextResponse($this->sanitizeChatbotReply($databaseReply));
            }

            $workflowReply = $this->buildSystemWorkflowReply($request, $userMessage);
            if ($workflowReply !== '') {
                return $this->chatbotTextResponse($this->sanitizeChatbotReply($workflowReply));
            }

            $localFallbackReply = $this->sanitizeChatbotReply(
                $this->buildLocalChatbotReply($request, $userMessage)
            );
        }
        $apiKey = trim((string) config('services.groq.api_key'));
        $baseUrl = rtrim((string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $model = trim((string) config('services.groq.model', 'llama-3.3-70b-versatile'));
        $fallbackModelsRaw = trim((string) config('services.groq.fallback_models', 'llama-3.1-8b-instant'));
        $timeout = (int) config('services.groq.timeout', 25);
        $timeout = max(5, min(120, $timeout));

        if ($apiKey === '') {
            return $this->chatbotTextResponse(
                $localFallbackReply !== ''
                    ? $localFallbackReply
                    : 'Groq API key is missing. Please set GROQ_API_KEY in the environment.',
                500
            );
        }

        $messages = $this->buildGroqMessagesFromHistory($request, $history, $userMessage);
        $modelCandidates = $this->buildGroqModelCandidates($model, $fallbackModelsRaw);
        $lastError = null;

        try {
            foreach ($modelCandidates as $modelCandidate) {
                $response = Http::acceptJson()
                    ->asJson()
                    ->withToken($apiKey)
                    ->timeout($timeout)
                    ->post($baseUrl . '/chat/completions', [
                        'model' => $modelCandidate,
                        'messages' => $messages,
                        'temperature' => 0.35,
                        'max_completion_tokens' => 260,
                    ]);

                if ($response->successful()) {
                    $responseData = (array) $response->json();
                    $reply = $this->sanitizeChatbotReply(
                        $this->extractGroqReply($responseData)
                    );
                    if ($reply === '') {
                        $lastError = [
                            'status_code' => 502,
                            'provider_error' => 'Groq returned empty response body.',
                            'provider_status' => '',
                            'model' => $modelCandidate,
                        ];
                        break;
                    }

                    return $this->chatbotTextResponse($reply);
                }

                $statusCode = $response->status();
                $providerError = trim((string) data_get($response->json(), 'error.message', ''));
                $providerStatus = strtoupper(trim((string) data_get($response->json(), 'error.status', '')));

                $lastError = [
                    'status_code' => $statusCode,
                    'provider_error' => $providerError,
                    'provider_status' => $providerStatus,
                    'model' => $modelCandidate,
                ];

                if (!$this->shouldTryNextGroqModel($statusCode, $providerStatus, $providerError)) {
                    break;
                }
            }

            $statusCode = (int) data_get($lastError, 'status_code', 0);
            $providerError = (string) data_get($lastError, 'provider_error', '');
            $providerStatus = (string) data_get($lastError, 'provider_status', '');
            $failedModel = (string) data_get($lastError, 'model', $model);
            $message = $this->mapGroqErrorMessage($statusCode, $providerStatus, $providerError, $failedModel);

            if (!$preferGroq && $localFallbackReply !== '') {
                return $this->chatbotTextResponse($localFallbackReply);
            }

            return $this->chatbotTextResponse($message, 502);
        } catch (\Throwable $e) {
            report($e);

            return $this->chatbotTextResponse(
                $localFallbackReply !== ''
                    ? $localFallbackReply
                    : 'Groq is temporarily unavailable. Please try again.',
                500
            );
        }
    }

    private function buildChatbotSystemPrompt(Request $request): string
    {
        $roleName = $this->resolveChatbotRoleName($request);

        return implode("\n", [
            'You are Family Assistant inside a Family Tree web application.',
            'You can answer both app-usage questions and general knowledge questions.',
            'Do not refuse just because a question is outside app scope.',
            'For website workflow rules, member creation supports only Add Child and Add Partner under the logged-in family member account.',
            'If user asks to add grandson, cousin, nephew, uncle, aunt, or other extended relation directly, explain the correct chain: add through the immediate parent/member account.',
            'For app-usage questions, give practical step-by-step guidance.',
            'For family-relation questions, prefer precise kinship terms.',
            'If an app detail is uncertain, say it briefly and provide best-effort guidance without inventing exact menu names.',
            'Keep replies concise, clear, and useful for non-technical users.',
            'Reply in the same language as the user message.',
            'User role context: ' . $roleName . '.',
        ]);
    }

    private function buildSystemWorkflowReply(Request $request, string $userMessage): string
    {
        $message = $this->normalizeChatbotMessage($userMessage);
        if ($message === '') {
            return '';
        }

        $isIndonesian = $this->isLikelyIndonesianMessage($message);
        $roleName = $this->resolveChatbotRoleName($request);
        $isAdmin = in_array($roleName, ['Superadmin', 'Admin'], true);
        $asksAdd = $this->containsAnyKeyword($message, ['add', 'tambah', 'buat', 'create', 'insert']);

        if (
            $asksAdd
            && $this->containsAnyKeyword($message, ['grandson', 'granddaughter', 'grandchild', 'cucu'])
        ) {
            return $isIndonesian
                ? 'Tidak bisa langsung menambah "cucu" sebagai relasi khusus. Di sistem ini, penambahan relasi hanya Add Child atau Add Partner untuk akun member yang sedang login. Jadi cucu harus ditambahkan oleh orang tuanya (anak Anda) melalui akun anak tersebut sebagai Child.'
                : 'No. You cannot add someone directly as "grandson/grandchild". In this system, relation creation only supports Add Child or Add Partner for the logged-in member. So your grandchild should be added by their parent (your child) from that child account as Child.';
        }

        if (
            $asksAdd
            && $this->containsAnyKeyword($message, [
                'cousin',
                'sepupu',
                'nephew',
                'niece',
                'keponakan',
                'uncle',
                'aunt',
                'paman',
                'bibi',
                'grandparent',
                'kakek',
                'nenek',
            ])
        ) {
            return $isIndonesian
                ? 'Untuk alur website: Anda tidak bisa menambah relasi extended secara langsung. Sistem hanya menyediakan Add Child dan Add Partner pada akun member yang login. Relasi seperti sepupu/keponakan/cucu harus dibentuk lewat rantai orang tua yang benar.'
                : 'In this website workflow, you cannot add extended relations directly. The system only provides Add Child and Add Partner on the logged-in member account. Relations like cousin/niece/nephew/grandchild must be built through the correct parent chain.';
        }

        if (
            $this->containsAnyKeyword($message, ['add child', 'add children', 'tambah anak', 'menambah anak'])
            || ($asksAdd && $this->containsAnyKeyword($message, ['child', 'anak']))
        ) {
            return $isIndonesian
                ? 'Bisa. Alurnya: buka Family Tree, pilih panel Add Member (hanya muncul di kartu "Me"), pilih Add Child, isi data, pilih Child Parent Mode (With current partner atau Single parent), lalu simpan.'
                : 'Yes. Workflow: open Family Tree, open Add Member panel (available only when "Me" card is selected), choose Add Child, fill member data, choose Child Parent Mode (With current partner or Single parent), then save.';
        }

        if (
            $this->containsAnyKeyword($message, ['add partner', 'tambah pasangan', 'menambah pasangan'])
            || ($asksAdd && $this->containsAnyKeyword($message, ['partner', 'pasangan', 'spouse']))
        ) {
            return $isIndonesian
                ? 'Bisa jika belum punya pasangan aktif. Kalau pasangan sebelumnya sudah meninggal, Anda bisa menambah pasangan baru. Alurnya: buka Family Tree, panel Add Member pada kartu "Me", pilih Add Partner, isi data pasangan, lalu simpan.'
                : 'Yes, if you do not already have an active partner. If the previous partner has passed away, you can add a new partner. Workflow: open Family Tree, use Add Member panel on the "Me" card, choose Add Partner, complete partner data, then save.';
        }

        if (
            $this->containsAnyKeyword($message, ['user management', 'manage user', 'kelola user', 'manajemen user', 'role user', 'hak akses'])
        ) {
            if ($isAdmin) {
                return $isIndonesian
                    ? 'Untuk alur website, manajemen user tersedia untuk Admin/Superadmin melalui menu User (tambah, edit, nonaktifkan, atur role).'
                    : 'In website workflow, user management is available for Admin/Superadmin via the User menu (add, edit, deactivate, manage roles).';
            }

            return $isIndonesian
                ? 'Untuk alur website, manajemen user dibatasi untuk Admin/Superadmin.'
                : 'In this website workflow, user management is restricted to Admin/Superadmin.';
        }

        return '';
    }

    private function buildLocalChatbotReply(Request $request, string $userMessage): string
    {
        $databaseReply = $this->buildChatbotDatabaseReply($request, $userMessage);
        if ($databaseReply !== '') {
            return $databaseReply;
        }

        $workflowReply = $this->buildSystemWorkflowReply($request, $userMessage);
        if ($workflowReply !== '') {
            return $workflowReply;
        }

        $message = $this->normalizeChatbotMessage($userMessage);
        $isIndonesian = $this->isLikelyIndonesianMessage($message);
        $roleName = $this->resolveChatbotRoleName($request);
        $isAdmin = in_array($roleName, ['Superadmin', 'Admin'], true);

        if (
            $this->containsAnyKeyword($message, ['cousin child', 'cousin s child', 'child of my cousin', 'anak sepupu', 'child of cousin'])
            || ($this->containsAnyKeyword($message, ['cousin', 'sepupu']) && $this->containsAnyKeyword($message, ['child', 'anak']))
        ) {
            return $isIndonesian
                ? 'Anak dari sepupu biasanya disebut first cousin once removed (sepupu beda satu generasi). Dalam percakapan santai, sebagian orang menyebutnya keponakan sepupu.'
                : 'Your cousin\'s child is usually called your first cousin once removed. In casual conversation, some people also say niece/nephew of cousin.';
        }

        if (
            $this->containsAnyKeyword($message, ['uncle child', 'aunt child', 'anak paman', 'anak bibi', 'anak om', 'anak tante'])
            || $this->containsAnyKeyword($message, ['aunt and uncle child', 'aunt and uncle s child', 'my aunt and uncle child'])
            || (
                $this->containsAnyKeyword($message, ['cousin', 'sepupu'])
                && $this->containsAnyKeyword($message, ['aunt', 'uncle', 'paman', 'bibi', 'om', 'tante'])
                && $this->containsAnyKeyword($message, ['child', 'anak'])
            )
        ) {
            return $isIndonesian
                ? 'Ya, benar. Anak paman atau bibi adalah sepupu kamu (cousin).'
                : 'Yes, that is correct. Your aunt or uncle\'s child is your cousin.';
        }

        if (
            $this->containsAnyKeyword($message, ['sibling child', 'brother child', 'sister child', 'anak saudara', 'anak kakak', 'anak adik'])
        ) {
            return $isIndonesian
                ? 'Anak dari kakak atau adik kamu adalah keponakan kamu.'
                : 'Your sibling\'s child is your niece or nephew.';
        }

        if (
            $this->containsAnyKeyword($message, ['add child', 'add children', 'tambah anak', 'menambah anak', 'buat anak', 'child'])
            || (str_contains($message, 'anak') && str_contains($message, 'tambah'))
        ) {
            return $isIndonesian
                ? 'Untuk menambah anak: buka Family Tree, pilih profil orang tua, klik aksi Tambah Anak, isi data wajib lalu simpan. Jika anak dari pasangan aktif, pastikan relasi pasangan sudah dibuat dulu.'
                : 'To add a child: open Family Tree, select the parent profile, click Add Child, fill the required fields, then save. If the child belongs to a partner pair, make sure the partner relationship exists first.';
        }

        if (
            $this->containsAnyKeyword($message, ['add partner', 'tambah pasangan', 'menambah pasangan', 'partner', 'pasangan', 'spouse', 'suami', 'istri'])
            && !$this->containsAnyKeyword($message, ['delete partner', 'hapus pasangan'])
        ) {
            return $isIndonesian
                ? 'Untuk menambah pasangan: pilih profil member, klik Tambah Pasangan, isi biodata pasangan, lalu simpan. Kalau pasangan sebelumnya sudah meninggal, Anda tetap bisa menambah pasangan baru selama tidak ada pasangan aktif.'
                : 'To add a partner: open a member profile, click Add Partner, complete the partner data, then save. If the previous partner has passed away, you can still add a new partner as long as there is no active partner.';
        }

        if (
            $this->containsAnyKeyword($message, ['edit profile', 'ubah profil', 'update profil', 'ganti profil', 'edit akun', 'ubah akun'])
        ) {
            return $isIndonesian
                ? 'Untuk edit profil: buka halaman Account, ubah data yang dibutuhkan (nama, email, nomor HP, dan lainnya), lalu klik simpan. Jika email diganti, sistem bisa meminta verifikasi.'
                : 'To edit profile: open the Account page, update needed fields (name, email, phone, and others), then save. If you change email, verification may be required.';
        }

        if (
            $this->containsAnyKeyword($message, ['forgot password', 'forget password', 'lupa password', 'reset password', 'ganti password'])
        ) {
            return $isIndonesian
                ? 'Untuk lupa password: buka halaman Login lalu pilih Forgot Password. Masukkan email akun, cek email verifikasi, lalu buat password baru.'
                : 'For a forgotten password: go to Login and click Forgot Password. Enter your account email, open the verification email, then set a new password.';
        }

        if (
            $this->containsAnyKeyword($message, ['life status', 'status hidup', 'meninggal', 'wafat', 'deceased', 'alive'])
        ) {
            return $isIndonesian
                ? 'Status hidup bisa diubah dari halaman edit profil member. Pilih status yang sesuai (misalnya hidup/meninggal) lalu simpan agar data di pohon keluarga ikut terbarui.'
                : 'Life status can be updated from the member edit profile page. Select the correct status (for example alive/deceased), then save so the family tree data updates.';
        }

        if (
            $this->containsAnyKeyword($message, ['delete child', 'hapus anak', 'remove child', 'delete partner', 'hapus pasangan', 'remove partner'])
        ) {
            return $isIndonesian
                ? 'Untuk hapus relasi anak/pasangan: buka profil member terkait, pilih data anak atau pasangan yang ingin dihapus, lalu konfirmasi penghapusan. Pastikan data yang dipilih sudah benar karena perubahan ini memengaruhi struktur tree.'
                : 'To delete child/partner relationship: open the related member profile, choose the child or partner entry to remove, then confirm deletion. Verify the target carefully because this changes the tree structure.';
        }

        if (
            $this->containsAnyKeyword($message, ['user management', 'manage user', 'kelola user', 'manajemen user', 'tambah user', 'role user', 'hak akses'])
        ) {
            if ($isAdmin) {
                return $isIndonesian
                    ? 'Akses manajemen user tersedia untuk Admin/Superadmin. Buka menu User, lalu Anda bisa tambah, edit, nonaktifkan, atau atur role user sesuai kebutuhan.'
                    : 'User management is available for Admin/Superadmin. Open the User menu to add, edit, deactivate, or update user roles.';
            }

            return $isIndonesian
                ? 'Manajemen user biasanya hanya bisa diakses Admin/Superadmin. Jika perlu akses, silakan hubungi admin sistem.'
                : 'User management is usually restricted to Admin/Superadmin. Contact your system admin if you need access.';
        }

        if ($this->containsAnyKeyword($message, ['halo', 'hai', 'hello', 'hi'])) {
            return $isIndonesian
                ? 'Halo, saya bisa bantu pertanyaan umum dan juga fitur Family Tree (tambah anak/pasangan, edit profil, reset password, update status hidup, dan kelola user).'
                : 'Hello, I can help with both general questions and Family Tree features (add child/partner, edit profile, password reset, life-status updates, and user management).';
        }

        return $isIndonesian
            ? 'Saya tetap bisa jawab tanpa quick question, termasuk pertanyaan umum. Coba kirim pertanyaanmu lebih spesifik agar jawaban saya lebih tepat.'
            : 'I can still respond without quick questions, including general topics. Ask your question with a bit more detail so I can answer more precisely.';
    }

    private function buildChatbotDatabaseReply(Request $request, string $userMessage): string
    {
        $message = $this->normalizeChatbotMessage($userMessage);
        if ($message === '') {
            return '';
        }

        $query = $this->extractChatbotDatabaseQuery($message);
        $memberName = (string) ($query['member_name'] ?? '');
        $queryType = (string) ($query['type'] ?? '');
        if ($memberName === '' && $queryType !== 'parent') {
            return '';
        }

        if ($queryType === 'parent') {
            $currentMember = $this->resolveCurrentChatbotFamilyMember($request);
            if (!$currentMember) {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Akun ini belum terhubung ke data family member di database.'
                    : 'This account is not linked to a family member record in the database.';
            }

            $parentRows = DB::table('relationship as r')
                ->join('family_member as parent', 'parent.memberid', '=', 'r.memberid')
                ->where('r.relatedmemberid', (int) $currentMember->memberid)
                ->whereRaw('LOWER(r.relationtype) = ?', ['child'])
                ->select('parent.memberid', 'parent.name', 'parent.gender', 'parent.life_status')
                ->orderBy('parent.name')
                ->get();

            if ($parentRows->isEmpty()) {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Orang tua ' . $currentMember->name . ' belum tercatat di database.'
                    : 'No parent is recorded in the database for ' . $currentMember->name . '.';
            }

            $parentNames = $parentRows->pluck('name')->all();
            $parentList = $this->formatChatbotNameList($parentNames);

            if (count($parentNames) === 1) {
                $parent = $parentRows->first();
                $gender = strtolower(trim((string) ($parent->gender ?? '')));

                if ($gender === 'male') {
                    return $this->isLikelyIndonesianMessage($message)
                        ? 'Ayah ' . $currentMember->name . ' adalah ' . $parent->name . '.'
                        : $currentMember->name . '\'s father is ' . $parent->name . '.';
                }

                if ($gender === 'female') {
                    return $this->isLikelyIndonesianMessage($message)
                        ? 'Ibu ' . $currentMember->name . ' adalah ' . $parent->name . '.'
                        : $currentMember->name . '\'s mother is ' . $parent->name . '.';
                }

                return $this->isLikelyIndonesianMessage($message)
                    ? 'Orang tua ' . $currentMember->name . ' adalah ' . $parent->name . '.'
                    : $currentMember->name . '\'s parent is ' . $parent->name . '.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? 'Orang tua ' . $currentMember->name . ' yang tercatat di database adalah ' . $parentList . '.'
                : $currentMember->name . '\'s parents recorded in the database are ' . $parentList . '.';
        }

        $member = $this->findFamilyMemberByName($memberName);
        if (!$member) {
            return '';
        }

        if ($queryType === 'spouse') {
            $partners = DB::table('relationship as r')
                ->join('family_member as partner', 'partner.memberid', '=', 'r.relatedmemberid')
                ->where('r.memberid', (int) $member->memberid)
                ->whereRaw('LOWER(r.relationtype) = ?', ['partner'])
                ->select('partner.memberid', 'partner.name', 'partner.life_status')
                ->orderBy('partner.name')
                ->get();

            if ($partners->isEmpty()) {
                return $this->isLikelyIndonesianMessage($message)
                    ? $member->name . ' belum punya pasangan yang tercatat.'
                    : $member->name . ' has no partner recorded.';
            }

            $activePartners = $partners->filter(function ($partner) {
                return strtolower(trim((string) ($partner->life_status ?? ''))) !== 'deceased';
            })->values();
            $displayPartners = $activePartners->isNotEmpty() ? $activePartners : $partners;
            $partnerNames = $displayPartners->pluck('name')->all();
            $partnerList = $this->formatChatbotNameList($partnerNames);

            if (count($partnerNames) === 1) {
                return $this->isLikelyIndonesianMessage($message)
                    ? $member->name . ' menikah dengan ' . $partnerList . '.'
                    : $member->name . ' is married to ' . $partnerList . '.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? $member->name . ' tercatat memiliki pasangan: ' . $partnerList . '.'
                : $member->name . ' has these partners recorded: ' . $partnerList . '.';
        }

        if ($queryType === 'age') {
            $birthdate = trim((string) ($member->birthdate ?? ''));
            if ($birthdate === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Tanggal lahir ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s birthdate is not recorded.';
            }

            try {
                $age = Carbon::parse($birthdate)->age;
            } catch (\Throwable $e) {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Tanggal lahir ' . $member->name . ' tidak valid di database.'
                    : $member->name . '\'s birthdate is invalid in the database.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? $member->name . ' berumur ' . $age . ' tahun.'
                : $member->name . ' is ' . $age . ' years old.';
        }

        if ($queryType === 'job') {
            $job = trim((string) ($member->job ?? ''));
            if ($job === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Pekerjaan ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s job is not recorded.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? 'Pekerjaan ' . $member->name . ' adalah ' . $job . '.'
                : $member->name . '\'s job is ' . $job . '.';
        }

        if ($queryType === 'birthplace') {
            $birthplace = trim((string) ($member->birthplace ?? ''));
            if ($birthplace === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Tempat lahir ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s birthplace is not recorded.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? $member->name . ' lahir di ' . $birthplace . '.'
                : $member->name . ' was born in ' . $birthplace . '.';
        }

        if ($queryType === 'address') {
            $address = trim((string) ($member->address ?? ''));
            if ($address === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Alamat ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s address is not recorded.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? 'Alamat ' . $member->name . ' adalah ' . $address . '.'
                : $member->name . '\'s address is ' . $address . '.';
        }

        if ($queryType === 'education') {
            $education = trim((string) ($member->education_status ?? ''));
            if ($education === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Pendidikan ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s education is not recorded.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? 'Pendidikan ' . $member->name . ' adalah ' . $education . '.'
                : $member->name . '\'s education is ' . $education . '.';
        }

        if ($queryType === 'life_status') {
            $lifeStatus = trim((string) ($member->life_status ?? ''));
            if ($lifeStatus === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Status hidup ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s life status is not recorded.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? 'Status hidup ' . $member->name . ' adalah ' . $lifeStatus . '.'
                : $member->name . '\'s life status is ' . $lifeStatus . '.';
        }

        if ($queryType === 'marital_status') {
            $maritalStatus = trim((string) ($member->marital_status ?? ''));
            if ($maritalStatus === '') {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Status pernikahan ' . $member->name . ' belum tercatat.'
                    : $member->name . '\'s marital status is not recorded.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? 'Status pernikahan ' . $member->name . ' adalah ' . $maritalStatus . '.'
                : $member->name . '\'s marital status is ' . $maritalStatus . '.';
        }

        if ($queryType === 'profile') {
            $parts = [];

            $age = null;
            $birthdate = trim((string) ($member->birthdate ?? ''));
            if ($birthdate !== '') {
                try {
                    $age = Carbon::parse($birthdate)->age;
                } catch (\Throwable $e) {
                    $age = null;
                }
            }

            if ($age !== null) {
                $parts[] = $this->isLikelyIndonesianMessage($message)
                    ? 'berumur ' . $age . ' tahun'
                    : 'is ' . $age . ' years old';
            }

            $job = trim((string) ($member->job ?? ''));
            if ($job !== '') {
                $parts[] = $this->isLikelyIndonesianMessage($message)
                    ? 'pekerjaannya ' . $job
                    : 'works as ' . $job;
            }

            $maritalStatus = trim((string) ($member->marital_status ?? ''));
            if ($maritalStatus !== '') {
                $parts[] = $this->isLikelyIndonesianMessage($message)
                    ? 'status pernikahan ' . $maritalStatus
                    : 'marital status is ' . $maritalStatus;
            }

            $activePartners = DB::table('relationship as r')
                ->join('family_member as partner', 'partner.memberid', '=', 'r.relatedmemberid')
                ->where('r.memberid', (int) $member->memberid)
                ->whereRaw('LOWER(r.relationtype) = ?', ['partner'])
                ->select('partner.memberid', 'partner.name', 'partner.life_status')
                ->get()
                ->filter(function ($partner) {
                    return strtolower(trim((string) ($partner->life_status ?? ''))) !== 'deceased';
                })
                ->pluck('name')
                ->values()
                ->all();

            if (!empty($activePartners)) {
                $partnerList = $this->formatChatbotNameList($activePartners);
                $parts[] = $this->isLikelyIndonesianMessage($message)
                    ? 'pasangan aktifnya ' . $partnerList
                    : 'current partner is ' . $partnerList;
            }

            $address = trim((string) ($member->address ?? ''));
            if ($address !== '') {
                $parts[] = $this->isLikelyIndonesianMessage($message)
                    ? 'tinggal di ' . $address
                    : 'lives at ' . $address;
            }

            if (empty($parts)) {
                return $this->isLikelyIndonesianMessage($message)
                    ? 'Data ' . $member->name . ' belum lengkap di database.'
                    : 'No additional profile data is recorded for ' . $member->name . '.';
            }

            return $this->isLikelyIndonesianMessage($message)
                ? $member->name . ' ' . implode(', ', $parts) . '.'
                : $member->name . ' ' . implode(', ', $parts) . '.';
        }

        if ($queryType !== 'children') {
            return '';
        }

        $children = DB::table('relationship as r')
            ->join('family_member as child', 'child.memberid', '=', 'r.relatedmemberid')
            ->where('r.memberid', (int) $member->memberid)
            ->whereRaw('LOWER(r.relationtype) = ?', ['child'])
            ->select('child.memberid', 'child.name')
            ->orderBy('child.name')
            ->get();

        if ($children->isEmpty()) {
            return $this->isLikelyIndonesianMessage($message)
                ? $member->name . ' belum punya anak yang tercatat di database.'
                : $member->name . ' has no children recorded in the database.';
        }

        $childNames = $children->pluck('name')->all();
        $childList = $this->formatChatbotNameList($childNames);

        if (count($childNames) === 1) {
            return $this->isLikelyIndonesianMessage($message)
                ? 'Anak dari ' . $member->name . ' adalah ' . $childList . '.'
                : $member->name . '\'s child is ' . $childList . '.';
        }

        return $this->isLikelyIndonesianMessage($message)
            ? 'Anak dari ' . $member->name . ' yang tercatat di database adalah ' . $childList . '.'
            : $member->name . '\'s children are ' . $childList . '.';
    }

    private function extractChatbotDatabaseQuery(string $message): array
    {
        $patterns = [
            'spouse' => [
                '/^(?:who is|who are|who s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:married to|maried to|marry to|partner of|spouse of)$/i',
                '/^(?:who is|who are|who s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:wife|husband|husbanad|partner|spouse)$/i',
                '/^(?:who is|who are|who s)\s+(?:the\s+)?(?:wife|husband|partner|spouse)\s+of\s+(.+)$/i',
                '/^(?:who is|who are|who s)\s+(.+?)\s+married$/i',
                '/^(?:is|are|does|did)\s+(.+?)\s+(?:married|maried|marry)\s*(?:to)?(?:\s+.*)?$/i',
                '/^(?:is|are)\s+(.+?)\s+married$/i',
            ],
            'children' => [
                '/^(?:who is|who are|who s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:child|children|kids|kid|son|daughter)s?$/i',
                '/^(?:who is|who are|who s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(?:child|children|kids|kid|son|daughter)s?\s+of\s+(.+)$/i',
                '/^(?:the\s+)?(?:child|children|kids|kid|son|daughter)s?\s+of\s+(.+)$/i',
            ],
            'parent' => [
                '/^(?:who is|who are|who s|what is|what are|tell me|show me|list)\s+(?:my|our)\s+(?:parent|parents|father|mother)$/i',
                '/^(?:who is|who are|who s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(?:parent|parents|father|mother)\s+of\s+(.+)$/i',
                '/^(?:the\s+)?(?:parent|parents|father|mother)\s+of\s+(.+)$/i',
            ],
        ];

        foreach ($patterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                if (preg_match($pattern, $message, $matches) === 1) {
                    $candidate = trim((string) ($matches[1] ?? ''));
                    $candidate = preg_replace('/\s+/u', ' ', $candidate) ?? $candidate;
                    $candidate = trim($candidate);

                    if ($type === 'parent' && $candidate === '') {
                        $candidate = '__current__';
                    }

                    if ($candidate !== '') {
                        return [
                            'type' => $type,
                            'member_name' => $candidate,
                        ];
                    }
                }
            }
        }

        $attributePatterns = [
            'age' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:age|umur|old)$/i',
                '/^(?:how old is|how old are)\s+(.+)$/i',
            ],
            'job' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:job|work|occupation|pekerjaan)$/i',
            ],
            'birthplace' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:birthplace|born in|lahir di|tempat lahir)$/i',
            ],
            'address' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:address|home|live|lives|tinggal|alamat)$/i',
                '/^(?:where does|where do|where is)\s+(.+?)\s+(?:live|lives|living)$/i',
            ],
            'education' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:education|study|school|pendidikan)$/i',
            ],
            'life_status' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:life status|status hidup|alive|deceased)$/i',
            ],
            'marital_status' => [
                '/^(?:what is|who is|who are|who s|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:s\s+)?(?:marital status|status pernikahan)$/i',
            ],
            'profile' => [
                '/^(?:who is|who are|who\'?s|tell me about|show me|list)\s+(.+)$/i',
            ],
        ];

        foreach ($attributePatterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                if (preg_match($pattern, $message, $matches) === 1) {
                    $candidate = trim((string) ($matches[1] ?? ''));
                    $candidate = preg_replace('/\s+/u', ' ', $candidate) ?? $candidate;
                    $candidate = trim($candidate);

                    if ($candidate !== '') {
                        return [
                            'type' => $type,
                            'member_name' => $candidate,
                        ];
                    }
                }
            }
        }

        return [
            'type' => '',
            'member_name' => '',
        ];
    }

    private function extractChatbotTargetMemberName(string $message): string
    {
        $patterns = [
            '/^(?:who is|who are|who\'?s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:child|children|kids|kid|son|daughter)s?$/i',
            '/^(?:who is|who are|who\'?s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(?:child|children|kids|kid|son|daughter)s?\s+of\s+(.+)$/i',
            '/^(?:the\s+)?(?:child|children|kids|kid|son|daughter)s?\s+of\s+(.+)$/i',
            '/^(?:who is|who are|who\'?s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:married to|maried to|marry to|partner of|spouse of)$/i',
            '/^(?:who is|who are|who\'?s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:wife|husband|husbanad|partner|spouse)$/i',
            '/^(?:who is|who are|who\'?s)\s+(?:the\s+)?(?:wife|husband|partner|spouse)\s+of\s+(.+)$/i',
            '/^(?:is|are|does|did)\s+(.+?)\s+(?:married|maried|marry)\s*(?:to)?(?:\s+.*)?$/i',
            '/^(?:is|are)\s+(.+?)\s+married$/i',
            '/^(?:who is|who are|who\'?s|what is|what are|tell me|show me|list)\s+(?:the\s+)?(.+?)\s+(?:age|umur|old|job|work|occupation|pekerjaan|birthplace|born in|lahir di|address|home|live|lives|tinggal|education|study|school|pendidikan|life status|status hidup|marital status|status pernikahan)$/i',
            '/^(?:how old is|how old are)\s+(.+)$/i',
            '/^(?:where does|where do|where is)\s+(.+?)\s+(?:live|lives|living)$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches) === 1) {
                $candidate = trim((string) ($matches[1] ?? ''));
                $candidate = preg_replace('/\s+/u', ' ', $candidate) ?? $candidate;
                $candidate = trim($candidate);

                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }

        return '';
    }

    private function formatChatbotNameList(array $names): string
    {
        $names = array_values(array_filter(array_map(function ($name) {
            return trim((string) $name);
        }, $names), function ($name) {
            return $name !== '';
        }));

        $count = count($names);
        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $names[0];
        }

        if ($count === 2) {
            return $names[0] . ' and ' . $names[1];
        }

        $lastName = array_pop($names);
        return implode(', ', $names) . ', and ' . $lastName;
    }

    private function normalizeChatbotMessage(string $message): string
    {
        $normalized = Str::lower(trim($message));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function isLikelyIndonesianMessage(string $message): bool
    {
        return $this->containsAnyKeyword($message, [
            'bagaimana',
            'kenapa',
            'tolong',
            'saya',
            'aku',
            'kami',
            'kamu',
            'bisa',
            'tidak',
            'nggak',
            'ga',
            'pakai',
            'cara',
            'tambah',
            'hapus',
            'ubah',
            'login',
            'akun',
            'password',
        ]);
    }

    private function containsAnyKeyword(string $message, array $keywords): bool
    {
        $haystack = ' ' . trim($message) . ' ';
        foreach ($keywords as $keyword) {
            $needle = trim(Str::lower((string) $keyword));
            if ($needle === '') {
                continue;
            }

            if (str_contains($haystack, ' ' . $needle . ' ')) {
                return true;
            }
        }

        return false;
    }

    private function resolveChatbotRoleName(Request $request): string
    {
        $authUser = (array) $request->session()->get('authenticated_user', []);
        $roleId = (int) ($authUser['roleid'] ?? 0);

        if ($roleId === 1) {
            return 'Superadmin';
        }

        if ($roleId === 2) {
            return 'Admin';
        }

        return 'Family Member';
    }

    private function resolveCurrentChatbotFamilyMember(Request $request): ?object
    {
        $authUser = (array) $request->session()->get('authenticated_user', []);
        $userId = (int) ($authUser['userid'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        return DB::table('family_member')
            ->where('userid', $userId)
            ->select('memberid', 'userid', 'name', 'gender', 'life_status')
            ->first();
    }

    private function findFamilyMemberByName(string $memberName): ?object
    {
        $memberName = trim($memberName);
        if ($memberName === '') {
            return null;
        }

        if ($memberName === '__current__') {
            return $this->resolveCurrentChatbotFamilyMember(request());
        }

        $exactMatch = DB::table('family_member')
            ->whereRaw('LOWER(name) = ?', [Str::lower($memberName)])
            ->select('memberid', 'userid', 'name', 'gender', 'birthdate', 'birthplace', 'job', 'address', 'education_status', 'life_status', 'marital_status')
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        return DB::table('family_member')
            ->where('name', 'like', '%' . $memberName . '%')
            ->select('memberid', 'userid', 'name', 'gender', 'birthdate', 'birthplace', 'job', 'address', 'education_status', 'life_status', 'marital_status')
            ->orderByRaw('LENGTH(name) ASC')
            ->first();
    }

    private function buildGroqMessagesFromHistory(Request $request, array $history, string $userMessage): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildChatbotSystemPrompt($request),
            ],
        ];

        foreach ($history as $item) {
            $role = (string) ($item['role'] ?? '');
            $content = trim((string) ($item['content'] ?? ''));

            if ($content === '' || !in_array($role, ['user', 'assistant'], true)) {
                continue;
            }

            $messages[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        return $messages;
    }

    private function extractGroqReply(array $responseData): string
    {
        $content = data_get($responseData, 'choices.0.message.content', '');
        if (is_array($content)) {
            $texts = [];
            foreach ($content as $part) {
                $text = trim((string) data_get($part, 'text', ''));
                if ($text === '') {
                    $text = trim((string) data_get($part, 'content', ''));
                }
                if ($text !== '') {
                    $texts[] = $text;
                }
            }

            return trim(implode("\n", $texts));
        }

        return trim((string) $content);
    }

    private function sanitizeChatbotReply(string $reply): string
    {
        $cleaned = str_replace('**', '', $reply);

        return trim($cleaned);
    }

    private function chatbotTextResponse(string $message, int $status = 200)
    {
        return response($message, $status)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function buildGroqModelCandidates(string $primaryModel, string $fallbackModelsRaw): array
    {
        $models = [];
        $primaryModel = trim($primaryModel);
        if ($primaryModel !== '') {
            $models[] = $primaryModel;
        }

        $fallbackModels = array_map('trim', explode(',', $fallbackModelsRaw));
        foreach ($fallbackModels as $fallbackModel) {
            if ($fallbackModel !== '') {
                $models[] = $fallbackModel;
            }
        }

        $normalized = [];
        foreach ($models as $model) {
            if (!in_array($model, $normalized, true)) {
                $normalized[] = $model;
            }
        }

        return $normalized;
    }

    private function shouldTryNextGroqModel(int $statusCode, string $providerStatus, string $providerError): bool
    {
        if (in_array($statusCode, [403, 429, 500, 502, 503, 504], true)) {
            return true;
        }

        if ($providerStatus !== '' && in_array($providerStatus, ['PERMISSION_DENIED', 'RESOURCE_EXHAUSTED', 'UNAVAILABLE', 'DEADLINE_EXCEEDED'], true)) {
            return true;
        }

        $providerError = strtolower($providerError);
        foreach (['rate limit', 'temporarily unavailable', 'service unavailable', 'timeout', 'retry'] as $needle) {
            if (str_contains($providerError, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function mapGroqErrorMessage(int $statusCode, string $providerStatus, string $providerError, string $failedModel): string
    {
        $modelName = $failedModel !== '' ? $failedModel : 'Groq';

        if ($statusCode === 401 || $providerStatus === 'UNAUTHENTICATED') {
            return 'Groq authentication failed. Check your Groq API key.';
        }

        if ($statusCode === 403 || $providerStatus === 'PERMISSION_DENIED') {
            return 'Groq access is forbidden for the current API key or model. Check GROQ_MODEL and GROQ_FALLBACK_MODELS, or use a model your key can access.';
        }

        if ($statusCode === 429 || $providerStatus === 'RESOURCE_EXHAUSTED') {
            return 'Groq is rate limited right now. Please try again in a moment.';
        }

        if ($statusCode >= 500) {
            return 'Groq is temporarily unavailable. Please try again later.';
        }

        if ($providerError !== '') {
            return 'Groq error on ' . $modelName . ': ' . $providerError;
        }

        return 'Groq request failed on ' . $modelName . '.';
    }


}
