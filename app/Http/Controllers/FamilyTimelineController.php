<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FamilyTimelineController extends Controller
{
    private const CATEGORY_OPTIONS = [
        'education' => 'Education',
        'health' => 'Health',
        'accident' => 'Accident',
        'achievement' => 'Achievement',
        'marriage' => 'Marriage',
        'birth' => 'Birth',
        'death' => 'Death',
        'work' => 'Work',
        'move' => 'Move',
        'other' => 'Other',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        $familyMembers = $this->getFamilyMembersForSelection();
        $familyMemberMap = $familyMembers->keyBy('memberid');
        $currentMember = $familyMembers->firstWhere('userid', $currentUserId);
        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        $isAdminOrSuperadmin = in_array($currentRoleId, [1, 2], true);

        $filterMemberId = (int) $request->query('member_id', 0);
        if ($filterMemberId > 0 && !$familyMemberMap->has($filterMemberId)) {
            $filterMemberId = 0;
        }

        $filterCategory = strtolower(trim((string) $request->query('category', '')));
        if ($filterCategory !== '' && !array_key_exists($filterCategory, self::CATEGORY_OPTIONS)) {
            $filterCategory = '';
        }

        $filterYear = (int) $request->query('year', 0);
        if ($filterYear > 0 && ($filterYear < 1900 || $filterYear > (int) now()->format('Y'))) {
            $filterYear = 0;
        }

        if (Schema::hasTable('family_timelines')) {
            $timelineQuery = $this->buildFamilyTimelineQuery();
            if ($filterMemberId > 0) {
                $timelineQuery->where('ft.family_member_id', $filterMemberId);
            }
            if ($filterCategory !== '') {
                $timelineQuery->whereRaw('LOWER(ft.category) = ?', [$filterCategory]);
            }
            if ($filterYear > 0) {
                $timelineQuery->where(function ($query) use ($filterYear) {
                    $query->where('ft.event_year', $filterYear)
                        ->orWhereRaw('YEAR(ft.event_date) = ?', [$filterYear]);
                });
            }

            $timelineEntries = $timelineQuery
                ->orderByRaw('COALESCE(ft.event_date, STR_TO_DATE(CONCAT(ft.event_year, "-01-01"), "%Y-%m-%d"), ft.created_at) DESC')
                ->orderByDesc('ft.id')
                ->paginate(12)
                ->withQueryString()
                ->through(function (object $entry) {
                    $entry->attachment_url = $this->resolvePublicFileUrl((string) ($entry->attachment_path ?? ''));
                    $entry->display_date = $this->formatTimelineDateLabel($entry->event_date ?? null, $entry->event_year ?? null);
                    $entry->category_label = self::CATEGORY_OPTIONS[strtolower(trim((string) ($entry->category ?? '')))] ?? ucfirst((string) ($entry->category ?? 'Other'));
                    $entry->can_manage = false;

                    return $entry;
                });

            $timelineEntries->getCollection()->transform(function (object $entry) use ($currentRoleId, $currentUserId, $currentMemberId, $isAdminOrSuperadmin) {
                $entry->can_manage = $isAdminOrSuperadmin
                    || (
                        (int) ($entry->user_id ?? 0) === $currentUserId
                        && (int) ($entry->family_member_id ?? 0) === $currentMemberId
                    );

                return $entry;
            });
        } else {
            $timelineEntries = new LengthAwarePaginator(
                collect(),
                0,
                12,
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        $familyMembersForPage = $familyMembers->map(function (object $member) {
            $member->is_current = false;

            return $member;
        });

        $defaultMemberId = $currentMemberId > 0
            ? $currentMemberId
            : (int) ($familyMembersForPage->first()->memberid ?? 0);

        $selectedMemberId = (int) old('family_member_id', $defaultMemberId);
        $selectedMemberForForm = $familyMemberMap->get($selectedMemberId) ?? $familyMemberMap->get($defaultMemberId);

        return view('all.family-timeline', [
            'pageTitle' => 'Family Timeline',
            'pageClass' => 'page-family-tree page-family-timeline',
            'systemSettings' => $this->getSystemSettings(),
            'familyMembers' => $familyMembersForPage,
            'timelineEntries' => $timelineEntries,
            'timelineCategories' => self::CATEGORY_OPTIONS,
            'timelineFilters' => [
                'member_id' => $filterMemberId,
                'category' => $filterCategory,
                'year' => $filterYear,
            ],
            'currentUserId' => $currentUserId,
            'currentRoleId' => $currentRoleId,
            'currentLevelId' => $currentLevelId,
            'currentMemberId' => $currentMemberId,
            'selectedMemberForForm' => $selectedMemberForForm,
            'isAdminOrSuperadmin' => $isAdminOrSuperadmin,
            'canManageAllTimeline' => $isAdminOrSuperadmin,
            'formAction' => old('timeline_id')
                ? '/timeline/' . (int) old('timeline_id') . '/update'
                : '/timeline/store',
            'editTimelineId' => (int) old('timeline_id', 0),
            'formValues' => [
                'title' => old('title', ''),
                'description' => old('description', ''),
                'event_date' => old('event_date', ''),
                'event_year' => old('event_year', ''),
                'category' => old('category', ''),
                'location' => old('location', ''),
            ],
            'activeTimelinePage' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->persistTimeline($request, null);
    }

    public function update(Request $request, int $timelineId): RedirectResponse
    {
        return $this->persistTimeline($request, $timelineId);
    }

    public function destroy(Request $request, int $timelineId): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $record = $this->findTimelineRecord($timelineId);

        if (!$record) {
            return redirect($this->resolveTimelineRedirectTarget($request, 0))->with('error', 'Timeline entry not found.');
        }

        if (!$this->canManageTimelineRecord($record, $currentUserId, $currentLevelId, $currentRoleId)) {
            return redirect($this->resolveTimelineRedirectTarget($request, (int) ($record->family_member_id ?? 0)))->with('error', 'You do not have permission to delete this timeline entry.');
        }

        $attachmentPath = trim((string) ($record->attachment_path ?? ''));
        DB::table('family_timelines')
            ->where('id', (int) $timelineId)
            ->delete();

        if ($attachmentPath !== '') {
            $this->deleteTimelineAttachment($attachmentPath);
        }

        return redirect($this->resolveTimelineRedirectTarget($request, (int) ($record->family_member_id ?? 0)))->with('success', 'Timeline entry deleted.');
    }

    private function persistTimeline(Request $request, ?int $timelineId): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $isUpdate = $timelineId !== null && $timelineId > 0;
        if (!Schema::hasTable('family_timelines')) {
            return redirect('/timeline')->with('error', 'Family timeline feature is not available yet.');
        }

        $familyMembers = $this->getFamilyMembersForSelection();
        $familyMemberMap = $familyMembers->keyBy('memberid');
        $currentMember = $familyMembers->firstWhere('userid', $currentUserId);
        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        $isAdminOrSuperadmin = in_array($currentRoleId, [1, 2], true);

        $validator = Validator::make($request->all(), [
            'family_member_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'event_date' => ['nullable', 'date', 'before_or_equal:today'],
            'event_year' => ['nullable', 'integer', 'min:1900', 'max:' . (int) now()->format('Y')],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(self::CATEGORY_OPTIONS))],
            'location' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'string', 'in:public_family,private_shared'],
            'shared_with' => ['nullable', 'array'],
            'shared_with.*' => ['integer'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'family_member_id.required' => 'Please choose a family member.',
            'family_member_id.integer' => 'Family member selection is invalid.',
            'title.required' => 'Timeline title is required.',
            'title.max' => 'Timeline title may not exceed 255 characters.',
            'description.max' => 'Timeline description may not exceed 3000 characters.',
            'event_date.date' => 'Event date must be a valid date.',
            'event_date.before_or_equal' => 'Event date cannot be in the future.',
            'event_year.integer' => 'Event year must be a valid number.',
            'event_year.min' => 'Event year is invalid.',
            'category.required' => 'Please choose a timeline category.',
            'category.in' => 'Selected timeline category is invalid.',
            'location.max' => 'Location may not exceed 255 characters.',
            'visibility.in' => 'Selected visibility is invalid.',
            'shared_with.array' => 'Shared With must be a list of users.',
            'shared_with.*.integer' => 'Shared With selection is invalid.',
            'attachment.image' => 'Attachment must be an image.',
            'attachment.mimes' => 'Attachment must be a JPG, JPEG, PNG, or WebP file.',
        ]);

        $validator->after(function ($validator) use ($request, $familyMemberMap, $currentMemberId, $isAdminOrSuperadmin) {
            $memberId = (int) $request->input('family_member_id', 0);
            if ($memberId <= 0 || !$familyMemberMap->has($memberId)) {
                $validator->errors()->add('family_member_id', 'Selected family member is invalid.');
            }

            if (!$isAdminOrSuperadmin && $currentMemberId > 0 && $memberId !== $currentMemberId) {
                $validator->errors()->add('family_member_id', 'You can only manage your own timeline entries.');
            }

            $eventDate = trim((string) $request->input('event_date', ''));
            $eventYear = trim((string) $request->input('event_year', ''));
            if ($eventDate === '' && $eventYear === '') {
                $validator->errors()->add('event_date', 'Please provide either an event date or an event year.');
            }

            $visibility = strtolower(trim((string) $request->input('visibility', 'public_family')));
            $sharedWith = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('shared_with', [])), fn ($value) => $value > 0)));
            if ($visibility === 'private_shared' && empty($sharedWith)) {
                $validator->errors()->add('shared_with', 'Please choose at least one shared user for a private timeline.');
            }
        });

        if ($validator->fails()) {
            return redirect($this->resolveTimelineRedirectTarget($request, (int) $request->input('family_member_id', 0)))
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $targetMemberId = (int) $validated['family_member_id'];
        $targetMember = $familyMemberMap->get($targetMemberId);
        if (!$targetMember) {
            return redirect('/timeline')->with('error', 'Selected family member is not available in this family.');
        }

        $existingRecord = null;
        if ($isUpdate) {
            $existingRecord = $this->findTimelineRecord($timelineId ?? 0);
            if (!$existingRecord) {
                return redirect('/timeline')->with('error', 'Timeline entry not found.');
            }

            if (!$this->canManageTimelineRecord($existingRecord, $currentUserId, $currentLevelId, $currentRoleId)) {
                return redirect('/timeline')->with('error', 'You do not have permission to update this timeline entry.');
            }
        }

        $eventDate = trim((string) ($validated['event_date'] ?? ''));
        $eventYear = trim((string) ($validated['event_year'] ?? ''));
        $normalizedEventDate = $eventDate !== '' ? Carbon::parse($eventDate)->toDateString() : null;
        $normalizedEventYear = $normalizedEventDate !== null
            ? (int) Carbon::parse($normalizedEventDate)->format('Y')
            : ($eventYear !== '' ? (int) $eventYear : null);

        $attachmentPath = $existingRecord ? trim((string) ($existingRecord->attachment_path ?? '')) : '';
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $attachmentPath = $this->storeTimelineAttachment($request->file('attachment'));
        }

        $visibility = strtolower(trim((string) ($validated['visibility'] ?? 'public_family')));
        if (!array_key_exists($visibility, ['public_family' => true, 'private_shared' => true])) {
            $visibility = 'public_family';
        }

        $sharedWithIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('shared_with', [])), fn ($value) => $value > 0)));
        if ($visibility !== 'private_shared') {
            $sharedWithIds = [];
        }

        $payload = [
            'family_member_id' => $targetMemberId,
            'user_id' => (int) ($targetMember->userid ?? 0) ?: null,
            'title' => trim((string) $validated['title']),
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'event_date' => $normalizedEventDate,
            'event_year' => $normalizedEventYear,
            'category' => strtolower(trim((string) $validated['category'])),
            'location' => trim((string) ($validated['location'] ?? '')) ?: null,
            'attachment_path' => $attachmentPath !== '' ? $attachmentPath : null,
            'updated_by_userid' => $currentUserId,
            'updated_at' => Carbon::now(),
        ];

        if (Schema::hasColumn('family_timelines', 'family_id')) {
            $payload['family_id'] = (int) ($this->resolveCurrentFamilyId($currentUserId) ?? 1);
        }

        if (Schema::hasColumn('family_timelines', 'visibility')) {
            $payload['visibility'] = $visibility;
        }

        $recordId = 0;
        if ($isUpdate) {
            DB::table('family_timelines')
                ->where('id', (int) $timelineId)
                ->update($payload);
            $recordId = (int) $timelineId;
            $message = 'Timeline entry updated.';
        } else {
            $payload['created_by_userid'] = $currentUserId;
            $payload['created_at'] = Carbon::now();
            $recordId = (int) DB::table('family_timelines')->insertGetId($payload);
            $message = 'Timeline entry created.';
        }

        $this->syncTimelineViewers($recordId, $sharedWithIds);

        if ($isUpdate && $existingRecord && $existingRecord->attachment_path !== null && $existingRecord->attachment_path !== '' && $existingRecord->attachment_path !== $attachmentPath) {
            $this->deleteTimelineAttachment((string) $existingRecord->attachment_path);
        }

        return redirect($this->resolveTimelineRedirectTarget($request, $targetMemberId))->with('success', $message);
    }

    private function buildFamilyTimelineQuery()
    {
        if (!Schema::hasTable('family_timelines')) {
            return DB::table('family_timelines as ft')
                ->whereRaw('1 = 0');
        }

        return DB::table('family_timelines as ft')
            ->leftJoin('family_member as fm', 'fm.memberid', '=', 'ft.family_member_id')
            ->leftJoin('user as member_user', 'member_user.userid', '=', 'ft.user_id')
            ->leftJoin('user as creator_user', 'creator_user.userid', '=', 'ft.created_by_userid')
            ->leftJoin('user as updater_user', 'updater_user.userid', '=', 'ft.updated_by_userid')
            ->select([
                'ft.*',
                'fm.name as family_member_name',
                'member_user.username as member_username',
                'creator_user.username as creator_username',
                'updater_user.username as updater_username',
            ]);
    }

    private function findTimelineRecord(int $timelineId): ?object
    {
        if ($timelineId <= 0 || !Schema::hasTable('family_timelines')) {
            return null;
        }

        return DB::table('family_timelines as ft')
            ->where('ft.id', $timelineId)
            ->first();
    }

    private function canManageTimelineRecord(object $record, int $currentUserId, int $currentLevelId, int $currentRoleId): bool
    {
        if (in_array($currentRoleId, [1, 2], true)) {
            return true;
        }

        return $currentLevelId === 2
            && (int) ($record->user_id ?? 0) === $currentUserId;
    }

    private function getFamilyMembersForSelection()
    {
        if (!Schema::hasTable('family_member') || !Schema::hasTable('user')) {
            return collect();
        }

        $query = DB::table('family_member as fm')
            ->join('user as u', 'u.userid', '=', 'fm.userid')
            ->whereNull('u.deleted_at');

        if (Schema::hasTable('employer')) {
            $query->leftJoin('employer as e', 'e.userid', '=', 'u.userid');
        }

        $select = ['u.userid', 'u.username'];
        if (Schema::hasTable('family_member') && Schema::hasColumn('family_member', 'name')) {
            $select[] = 'fm.name as family_name';
        }
        if (Schema::hasTable('employer') && Schema::hasColumn('employer', 'name')) {
            $select[] = 'e.name as employer_name';
        }
        if (Schema::hasTable('family_member') && Schema::hasColumn('family_member', 'memberid')) {
            $select[] = 'fm.memberid as memberid';
        }

        return $query
            ->select($select)
            ->distinct()
            ->orderBy('u.username')
            ->get()
            ->map(function (object $user) {
                $displayName = trim((string) ($user->family_name ?? $user->employer_name ?? $user->username ?? ''));
                $user->display_name = $displayName !== '' ? $displayName : (string) ($user->username ?? 'Member');
                return $user;
            });
    }

    private function storeTimelineAttachment($file): string
    {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/family-timeline';
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: 'jpg'));
        $fileName = 'timeline-' . Str::uuid()->toString() . '.' . $extension;
        $file->move($uploadDir, $fileName);

        return '/uploads/family-timeline/' . $fileName;
    }

    private function deleteTimelineAttachment(string $attachmentPath): void
    {
        $attachmentPath = trim($attachmentPath);
        if ($attachmentPath === '') {
            return;
        }

        if (preg_match('#^(?:https?:|data:)#i', $attachmentPath)) {
            return;
        }

        $localPath = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . ltrim($attachmentPath, '/');

        if (is_file($localPath)) {
            @File::delete($localPath);
        }
    }

    private function syncTimelineViewers(int $timelineId, array $viewerIds): void
    {
        if ($timelineId <= 0 || !Schema::hasTable('family_timeline_viewers')) {
            return;
        }

        $viewerIds = array_values(array_unique(array_filter(array_map('intval', $viewerIds), fn ($value) => $value > 0)));

        DB::table('family_timeline_viewers')
            ->where('timeline_id', $timelineId)
            ->delete();

        if (empty($viewerIds)) {
            return;
        }

        $now = Carbon::now();
        $rows = [];
        foreach ($viewerIds as $viewerId) {
            $rows[] = [
                'timeline_id' => $timelineId,
                'userid' => $viewerId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('family_timeline_viewers')->insert($rows);
    }

    private function resolveTimelineRedirectTarget(Request $request, int $memberId): string
    {
        $redirectTo = trim((string) $request->input('redirect_to', ''));
        if ($redirectTo !== '' && str_starts_with($redirectTo, '/') && !str_contains($redirectTo, '://')) {
            return $redirectTo;
        }

        if ($memberId > 0) {
            return '/member/' . $memberId . '/wiki';
        }

        return '/wiki';
    }

}
