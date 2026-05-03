<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WikiController extends Controller
{
    private const TIMELINE_CATEGORY_OPTIONS = [
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

    private const TIMELINE_VISIBILITY_OPTIONS = [
        'public_family' => 'Public family',
        'private_shared' => 'Private / shared',
    ];

    private const MEDICAL_CATEGORY_OPTIONS = [
        'allergy' => 'Allergy',
        'disease' => 'Disease',
        'medication' => 'Medication',
        'surgery' => 'Surgery',
        'hospitalization' => 'Hospitalization',
        'vaccination' => 'Vaccination',
        'checkup' => 'Checkup',
    ];

    public function index(Request $request): View
    {
        if (!session('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();
        $search = trim((string) $request->query('q', ''));

        $members = collect();
        if ($search !== '') {
            $query = DB::table('family_member')
                ->select('memberid', 'userid', 'name', 'picture')
                ->orderBy('name', 'asc')
                ->where(function ($memberQuery) use ($search) {
                    $memberQuery->where('name', 'like', '%' . $search . '%');
                    if (Schema::hasColumn('family_member', 'nickname')) {
                        $memberQuery->orWhere('nickname', 'like', '%' . $search . '%');
                    }
                });

            $members = $query->get();
        }

        return view('all.wiki.index', [
            'pageTitle' => 'Wiki',
            'pageClass' => 'page-family-tree page-wiki',
            'systemSettings' => $systemSettings,
            'members' => $members,
            'searchQuery' => $search,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        if (!session('authenticated_user')) {
            abort(403);
        }

        $member = DB::table('family_member')->where('memberid', $id)->first();
        if (!$member) {
            abort(404);
        }

        $article = DB::table('member_articles')->where('member_id', $id)->first();
        $systemSettings = $this->getSystemSettings();

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentUserMember = DB::table('family_member')->where('userid', $currentUserId)->first();
        $currentMemberId = (int) ($currentUserMember->memberid ?? 0);

        $isOwner = $currentMemberId === (int) $id;
        $isPartner = DB::table('relationship')
            ->where(function ($q) use ($id, $currentMemberId) {
                $q->where('memberid', $id)->where('relatedmemberid', $currentMemberId);
            })
            ->orWhere(function ($q) use ($id, $currentMemberId) {
                $q->where('memberid', $currentMemberId)->where('relatedmemberid', $id);
            })
            ->where('relationtype', 'partner')
            ->exists();

        $isChild = DB::table('relationship')
            ->where('memberid', $id)
            ->where('relatedmemberid', $currentMemberId)
            ->where('relationtype', 'child')
            ->exists();

        $canSeeDocs = $isOwner || $isPartner || $isChild || in_array($currentRoleId, [1, 2], true);
        $documents = $canSeeDocs ? DB::table('member_documents')->where('member_id', $id)->get() : [];

        $children = DB::table('relationship')
            ->join('family_member', 'family_member.memberid', '=', 'relationship.relatedmemberid')
            ->where('relationship.memberid', $id)
            ->where('relationtype', 'child')
            ->select('family_member.name', 'family_member.memberid')
            ->get();

        $partners = DB::table('relationship')
            ->join('family_member', 'family_member.memberid', '=', 'relationship.relatedmemberid')
            ->where('relationship.memberid', $id)
            ->where('relationtype', 'partner')
            ->select('family_member.name', 'family_member.memberid')
            ->get();

        $timelineData = $this->loadLifeTimelineData($request, $id, $currentUserId, $currentRoleId, $currentMemberId);
        $medicalData = $this->loadMedicalHistoryData($request, $id, $currentUserId, $currentRoleId, $currentMemberId);

        return view('all.wiki.show', array_merge(compact(
            'member',
            'article',
            'documents',
            'canSeeDocs',
            'isOwner',
            'children',
            'partners',
            'systemSettings',
            'currentUserId',
            'currentRoleId',
            'currentMemberId'
        ), $timelineData, $medicalData));
    }

    public function edit(int $id)
    {
        $currentUserId = (int) session('authenticated_user.userid');
        $member = DB::table('family_member')->where('memberid', $id)->first();
        if (!$member) {
            abort(404);
        }

        if ((int) session('authenticated_user.roleid') > 2 && (int) ($member->userid ?? 0) !== $currentUserId) {
            return abort(403, 'You can only edit your own biography.');
        }

        $article = DB::table('member_articles')->where('member_id', $id)->first();
        $systemSettings = $this->getSystemSettings();

        return view('all.wiki.edit', compact('member', 'article', 'systemSettings'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate(['biography' => 'required|string']);

        DB::table('member_articles')->updateOrInsert(
            ['member_id' => $id],
            [
                'biography' => $validated['biography'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect("/member/{$id}/wiki")->with('success', 'Biography updated!');
    }

    public function uploadDoc(Request $request, int $id)
    {
        $currentUserId = (int) session('authenticated_user.userid');
        $member = DB::table('family_member')->where('memberid', $id)->first();

        if (!$member || (int) ($member->userid ?? 0) !== $currentUserId) {
            return abort(403, 'Only the owner can upload documents to this vault.');
        }

        $request->validate([
            'doc_type' => 'required|string',
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/docs'), $filename);
            $path = '/uploads/docs/' . $filename;

            DB::table('member_documents')->insert([
                'member_id' => $id,
                'doc_type' => $request->doc_type,
                'file_path' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Document uploaded successfully!');
    }

    public function storeMedicalHistory(Request $request)
    {
        if (!session('authenticated_user')) {
            abort(403);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentUserMember = DB::table('family_member')->where('userid', $currentUserId)->first();
        $currentMemberId = (int) ($currentUserMember->memberid ?? 0);
        $familyMemberId = (int) $request->input('family_member_id', 0);
        $member = $familyMemberId > 0 ? DB::table('family_member')->where('memberid', $familyMemberId)->first() : null;

        if (!$member) {
            return back()->withInput()->withErrors(['medical' => 'Medical history target member was not found.']);
        }

        if (!in_array($currentRoleId, [1, 2], true) && $currentMemberId !== $familyMemberId) {
            return back()->withInput()->withErrors(['medical' => 'You can only manage your own medical history.']);
        }

        $validated = $request->validate([
            'medical_history_id' => ['nullable', 'integer'],
            'family_member_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'allergy_name' => ['nullable', 'string', 'max:255'],
            'medical_date' => ['required', 'date'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(self::MEDICAL_CATEGORY_OPTIONS))],
            'notes' => ['nullable', 'string', 'max:3000'],
            'redirect_to' => ['nullable', 'string', 'max:2000'],
        ], [
            'title.required' => 'Medical history title is required.',
            'medical_date.required' => 'Medical date is required.',
            'medical_date.date' => 'Medical date is invalid.',
            'category.in' => 'Selected category is invalid.',
        ]);

        $isEditing = (int) ($validated['medical_history_id'] ?? 0) > 0;
        $existing = null;
        if ($isEditing) {
            $existing = DB::table('family_medical_histories')->where('id', (int) $validated['medical_history_id'])->first();
            if (!$existing) {
                return back()->withInput()->withErrors(['medical' => 'Medical history entry was not found.']);
            }

            if (!in_array($currentRoleId, [1, 2], true) && (int) ($existing->user_id ?? 0) !== $currentUserId) {
                return back()->withInput()->withErrors(['medical' => 'You can only edit your own medical history.']);
            }
        }

        $category = strtolower(trim((string) $validated['category']));
        $allergyName = trim((string) $request->input('allergy_name', ''));
        if ($category !== 'allergy') {
            $allergyName = '';
        }

        $payload = [
            'family_id' => 1,
            'family_member_id' => $familyMemberId,
            'user_id' => (int) ($member->userid ?? 0),
            'title' => trim((string) $validated['title']),
            'allergy_name' => $allergyName !== '' ? $allergyName : null,
            'medical_date' => $validated['medical_date'],
            'category' => $category,
            'notes' => trim((string) ($validated['notes'] ?? '')) !== '' ? trim((string) $validated['notes']) : null,
            'created_by_userid' => $isEditing ? (int) ($existing->created_by_userid ?? $currentUserId) : $currentUserId,
            'updated_by_userid' => $currentUserId,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if ($isEditing) {
            unset($payload['created_at']);
            DB::table('family_medical_histories')->where('id', (int) $validated['medical_history_id'])->update($payload);
            $message = 'Medical history updated successfully!';
        } else {
            DB::table('family_medical_histories')->insert($payload);
            $message = 'Medical history added successfully!';
        }

        $redirectTo = trim((string) ($validated['redirect_to'] ?? ''));
        if ($redirectTo === '') {
            $redirectTo = '/member/' . $familyMemberId . '/wiki#medical-history';
        }

        return redirect($redirectTo)->with('success', $message);
    }

    public function updateMedicalHistory(Request $request, int $historyId)
    {
        $request->merge(['medical_history_id' => $historyId]);
        return $this->storeMedicalHistory($request);
    }

    public function deleteMedicalHistory(Request $request, int $historyId)
    {
        if (!session('authenticated_user')) {
            abort(403);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!Schema::hasTable('family_medical_histories')) {
            return back()->withErrors(['medical' => 'Medical history table is not available.']);
        }

        $entry = DB::table('family_medical_histories')->where('id', $historyId)->first();
        if (!$entry) {
            return back()->withErrors(['medical' => 'Medical history entry was not found.']);
        }

        if (!in_array($currentRoleId, [1, 2], true) && (int) ($entry->user_id ?? 0) !== $currentUserId) {
            return back()->withErrors(['medical' => 'You can only delete your own medical history.']);
        }

        DB::table('family_medical_histories')->where('id', $historyId)->delete();

        $redirectTo = trim((string) $request->input('redirect_to', ''));
        if ($redirectTo === '') {
            $redirectTo = '/member/' . (int) ($entry->family_member_id ?? 0) . '/wiki#medical-history';
        }

        return redirect($redirectTo)->with('success', 'Medical history deleted successfully!');
    }

    private function loadLifeTimelineData(Request $request, int $memberId, int $currentUserId, int $currentRoleId, int $currentMemberId): array
    {
        $familyId = (int) ($this->resolveCurrentFamilyId($currentUserId) ?? 1);
        if ($familyId <= 0) {
            $familyId = 1;
        }

        $filterCategory = strtolower(trim((string) $request->query('timeline_category', '')));
        if ($filterCategory !== '' && !array_key_exists($filterCategory, self::TIMELINE_CATEGORY_OPTIONS)) {
            $filterCategory = '';
        }

        $filterYear = (int) $request->query('timeline_year', 0);
        if ($filterYear > 0 && ($filterYear < 1900 || $filterYear > (int) now()->format('Y'))) {
            $filterYear = 0;
        }

        $timelineEntries = $this->queryLifeTimelineEntries(
            $memberId,
            $familyId,
            $currentUserId,
            $currentRoleId,
            $currentMemberId,
            $filterCategory,
            $filterYear
        );

        $shareMembers = $this->getLifeTimelineShareMembers();
        $timelineActiveTab = trim((string) $request->query('tab', ''));
        if ($timelineActiveTab === '') {
            $timelineActiveTab = ($filterCategory !== '' || $filterYear > 0) ? 'timeline' : 'biography';
        }

        return [
            'timelineEntries' => $timelineEntries,
            'timelineCategories' => self::TIMELINE_CATEGORY_OPTIONS,
            'timelineVisibilityOptions' => self::TIMELINE_VISIBILITY_OPTIONS,
            'timelineFilters' => [
                'category' => $filterCategory,
                'year' => $filterYear,
            ],
            'timelineShareMembers' => $shareMembers,
            'timelineCanManage' => in_array($currentRoleId, [1, 2], true) || $currentMemberId === $memberId,
            'timelineTargetMemberId' => $memberId,
            'timelineMemberName' => (string) ($this->memberDisplayName((object) DB::table('family_member')->where('memberid', $memberId)->first()) ?? ''),
            'timelineFormAction' => '/timeline/store',
            'timelineReturnUrl' => $this->buildWikiTimelineReturnUrl($memberId, $filterCategory, $filterYear),
            'timelineActiveTab' => $timelineActiveTab,
            'timelineEditId' => (int) old('timeline_id', 0),
            'timelineFormValues' => [
                'title' => old('title', ''),
                'description' => old('description', ''),
                'event_date' => old('event_date', ''),
                'event_year' => old('event_year', ''),
                'category' => old('category', ''),
                'location' => old('location', ''),
                'visibility' => old('visibility', 'public_family'),
                'shared_with' => array_map('intval', (array) old('shared_with', [])),
            ],
        ];
    }

    private function loadMedicalHistoryData(Request $request, int $memberId, int $currentUserId, int $currentRoleId, int $currentMemberId): array
    {
        $familyId = (int) ($this->resolveCurrentFamilyId($currentUserId) ?? 1);
        if ($familyId <= 0) {
            $familyId = 1;
        }

        $filterCategory = strtolower(trim((string) $request->query('medical_category', '')));
        if ($filterCategory !== '' && !array_key_exists($filterCategory, self::MEDICAL_CATEGORY_OPTIONS)) {
            $filterCategory = '';
        }

        $filterYear = (int) $request->query('medical_year', 0);
        if ($filterYear > 0 && ($filterYear < 1900 || $filterYear > (int) now()->format('Y'))) {
            $filterYear = 0;
        }

        $filterDate = trim((string) $request->query('medical_date', ''));
        if ($filterDate !== '') {
            try {
                Carbon::parse($filterDate);
            } catch (\Throwable $e) {
                $filterDate = '';
            }
        }

        $medicalEntries = $this->queryMedicalHistoryEntries(
            $memberId,
            $familyId,
            $currentUserId,
            $currentRoleId,
            $currentMemberId,
            $filterCategory,
            $filterYear,
            $filterDate
        );

        $medicalActiveTab = trim((string) $request->query('medical_tab', ''));
        if ($medicalActiveTab === '') {
            $medicalActiveTab = ($filterCategory !== '' || $filterYear > 0 || $filterDate !== '') ? 'medical-history' : 'biography';
        }

        return [
            'medicalEntries' => $medicalEntries,
            'medicalCategories' => self::MEDICAL_CATEGORY_OPTIONS,
            'medicalFilters' => [
                'category' => $filterCategory,
                'year' => $filterYear,
                'date' => $filterDate,
            ],
            'medicalCanManage' => in_array($currentRoleId, [1, 2], true) || $currentMemberId === $memberId,
            'medicalTargetMemberId' => $memberId,
            'medicalMemberName' => (string) ($this->memberDisplayName((object) DB::table('family_member')->where('memberid', $memberId)->first()) ?? ''),
            'medicalFormAction' => '/medical-history/store',
            'medicalReturnUrl' => $this->buildWikiMedicalHistoryReturnUrl($memberId, $filterCategory, $filterYear, $filterDate),
            'medicalActiveTab' => $medicalActiveTab,
            'medicalEditId' => (int) old('medical_history_id', 0),
            'medicalFormValues' => [
                'title' => old('medical_title', ''),
                'allergy_name' => old('allergy_name', ''),
                'medical_date' => old('medical_date', ''),
                'category' => old('medical_category', ''),
                'notes' => old('medical_notes', ''),
            ],
        ];
    }

    private function buildWikiMedicalHistoryReturnUrl(int $memberId, string $filterCategory, int $filterYear, string $filterDate): string
    {
        $query = ['medical_tab' => 'medical-history'];
        if ($filterCategory !== '') {
            $query['medical_category'] = $filterCategory;
        }
        if ($filterYear > 0) {
            $query['medical_year'] = $filterYear;
        }
        if ($filterDate !== '') {
            $query['medical_date'] = $filterDate;
        }

        return '/member/' . $memberId . '/wiki?' . http_build_query($query);
    }

    private function queryMedicalHistoryEntries(
        int $memberId,
        int $familyId,
        int $currentUserId,
        int $currentRoleId,
        int $currentMemberId,
        string $filterCategory,
        int $filterYear,
        string $filterDate
    ): LengthAwarePaginator {
        if (!Schema::hasTable('family_medical_histories')) {
            return new LengthAwarePaginator(collect(), 0, 10, 1);
        }

        $query = DB::table('family_medical_histories as mh')
            ->leftJoin('user as creator_user', 'creator_user.userid', '=', 'mh.created_by_userid')
            ->leftJoin('user as updater_user', 'updater_user.userid', '=', 'mh.updated_by_userid')
            ->select([
                'mh.*',
                'creator_user.username as creator_username',
                'updater_user.username as updater_username',
            ])
            ->where('mh.family_member_id', $memberId);

        if (Schema::hasColumn('family_medical_histories', 'family_id')) {
            $query->where('mh.family_id', $familyId > 0 ? $familyId : 1);
        }

        if ($filterCategory !== '') {
            $query->whereRaw('LOWER(mh.category) = ?', [$filterCategory]);
        }

        if ($filterDate !== '') {
            $query->whereDate('mh.medical_date', $filterDate);
        } elseif ($filterYear > 0) {
            $query->whereYear('mh.medical_date', $filterYear);
        }

        $entries = $query
            ->orderByRaw('COALESCE(mh.medical_date, mh.created_at) DESC')
            ->orderBy('mh.id', 'DESC')
            ->paginate(10)
            ->withQueryString()
            ->through(function (object $entry) {
                $entry->display_date = $this->formatMedicalDateLabel($entry->medical_date ?? null);
                $entry->category_label = self::MEDICAL_CATEGORY_OPTIONS[strtolower(trim((string) ($entry->category ?? '')))] ?? ucfirst((string) ($entry->category ?? 'Other'));
                $entry->can_manage = false;
                return $entry;
            });

        $entries->getCollection()->transform(function (object $entry) use ($currentUserId, $currentRoleId, $currentMemberId) {
            $entry->can_manage = in_array($currentRoleId, [1, 2], true)
                || ((int) ($entry->user_id ?? 0) === $currentUserId && (int) ($entry->family_member_id ?? 0) === $currentMemberId);

            return $entry;
        });

        return $entries;
    }

    private function buildWikiTimelineReturnUrl(int $memberId, string $filterCategory, int $filterYear): string
    {
        $query = ['tab' => 'timeline'];
        if ($filterCategory !== '') {
            $query['timeline_category'] = $filterCategory;
        }
        if ($filterYear > 0) {
            $query['timeline_year'] = $filterYear;
        }

        return '/member/' . $memberId . '/wiki?' . http_build_query($query);
    }

    private function queryLifeTimelineEntries(
        int $memberId,
        int $familyId,
        int $currentUserId,
        int $currentRoleId,
        int $currentMemberId,
        string $filterCategory,
        int $filterYear
    ): LengthAwarePaginator {
        if (!Schema::hasTable('family_timelines')) {
            return new LengthAwarePaginator(collect(), 0, 10, 1);
        }

        $query = DB::table('family_timelines as ft')
            ->leftJoin('user as creator_user', 'creator_user.userid', '=', 'ft.created_by_userid')
            ->leftJoin('user as updater_user', 'updater_user.userid', '=', 'ft.updated_by_userid')
            ->select([
                'ft.*',
                'creator_user.username as creator_username',
                'updater_user.username as updater_username',
            ])
            ->where('ft.family_member_id', $memberId);

        if (Schema::hasColumn('family_timelines', 'family_id')) {
            $query->where('ft.family_id', $familyId > 0 ? $familyId : 1);
        }

        if ($filterCategory !== '') {
            $query->whereRaw('LOWER(ft.category) = ?', [$filterCategory]);
        }

        if ($filterYear > 0) {
            $query->where(function ($subQuery) use ($filterYear) {
                $subQuery->where('ft.event_year', $filterYear)
                    ->orWhereRaw('YEAR(ft.event_date) = ?', [$filterYear]);
            });
        }

        $hasTimelineVisibilityColumn = Schema::hasColumn('family_timelines', 'visibility');

        if (!in_array($currentRoleId, [1, 2], true) && $hasTimelineVisibilityColumn) {
            $query->where(function ($visibilityQuery) use ($currentUserId) {
                $visibilityQuery->whereNull('ft.visibility')
                    ->orWhere('ft.visibility', 'public_family')
                    ->orWhere('ft.created_by_userid', $currentUserId)
                    ->orWhere('ft.user_id', $currentUserId);

                if (Schema::hasTable('family_timeline_viewers')) {
                    $visibilityQuery->orWhereExists(function ($subQuery) use ($currentUserId) {
                        $subQuery->select(DB::raw(1))
                            ->from('family_timeline_viewers as ftv')
                            ->whereColumn('ftv.timeline_id', 'ft.id')
                            ->where('ftv.userid', $currentUserId);
                    });
                }
            });
        }

        $entries = $query
            ->orderByRaw('COALESCE(ft.event_date, STR_TO_DATE(CONCAT(ft.event_year, "-01-01"), "%Y-%m-%d"), ft.created_at) ASC')
            ->orderBy('ft.id', 'ASC')
            ->paginate(10)
            ->withQueryString()
            ->through(function (object $entry) {
                $entry->display_date = $this->formatTimelineDateLabel($entry->event_date ?? null, $entry->event_year ?? null);
                $entry->category_label = self::TIMELINE_CATEGORY_OPTIONS[strtolower(trim((string) ($entry->category ?? '')))] ?? ucfirst((string) ($entry->category ?? 'Other'));
                $entry->visibility = (string) ($entry->visibility ?? 'public_family');
                $entry->visibility_label = $this->timelineVisibilityLabel($entry->visibility);
                $entry->attachment_url = $this->resolvePublicFileUrl((string) ($entry->attachment_path ?? ''));
                $entry->shared_with_names = [];
                $entry->shared_with_ids = [];
                $entry->can_manage = false;

                return $entry;
            });

        $timelineIds = $entries->getCollection()->pluck('id')->map(fn ($value) => (int) $value)->filter(fn ($value) => $value > 0)->values()->all();
        $viewerMap = $this->getLifeTimelineViewerMap($timelineIds);

        $entries->getCollection()->transform(function (object $entry) use ($viewerMap, $currentUserId, $currentRoleId, $currentMemberId) {
            $entryId = (int) ($entry->id ?? 0);
            $viewers = $viewerMap[$entryId] ?? [];
            $entry->shared_with_names = array_values(array_map(fn ($viewer) => (string) ($viewer->display_name ?? $viewer->username ?? 'User'), $viewers));
            $entry->shared_with_ids = array_values(array_map(fn ($viewer) => (int) ($viewer->userid ?? 0), $viewers));
            $entry->can_manage = in_array($currentRoleId, [1, 2], true)
                || ((int) ($entry->user_id ?? 0) === $currentUserId && (int) ($entry->family_member_id ?? 0) === $currentMemberId);

            return $entry;
        });

        return $entries;
    }

    private function getLifeTimelineViewerMap(array $timelineIds): array
    {
        if (empty($timelineIds) || !Schema::hasTable('family_timeline_viewers')) {
            return [];
        }

        $rows = DB::table('family_timeline_viewers as ftv')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'ftv.userid')
            ->leftJoin('user as u', 'u.userid', '=', 'ftv.userid')
            ->whereIn('ftv.timeline_id', $timelineIds)
            ->select([
                'ftv.timeline_id',
                'ftv.userid',
                'u.username',
                'fm.name as member_name',
            ])
            ->get();

        $viewerMap = [];
        foreach ($rows as $row) {
            $timelineId = (int) ($row->timeline_id ?? 0);
            if ($timelineId <= 0) {
                continue;
            }

            $displayName = trim((string) ($row->member_name ?? $row->username ?? ''));
            $row->display_name = $displayName !== '' ? $displayName : (string) ($row->username ?? 'User');
            $viewerMap[$timelineId] = $viewerMap[$timelineId] ?? [];
            $viewerMap[$timelineId][] = $row;
        }

        return $viewerMap;
    }

    private function getLifeTimelineShareMembers(): Collection
    {
        if (!Schema::hasTable('family_member') || !Schema::hasTable('user')) {
            return collect();
        }

        $query = DB::table('family_member as fm')
            ->join('user as u', 'u.userid', '=', 'fm.userid')
            ->whereNull('u.deleted_at');

        $members = $query
            ->select('u.userid', 'u.username', 'fm.memberid', 'fm.name as member_name')
            ->orderBy('fm.name')
            ->get()
            ->map(function (object $member) {
                $member->display_name = trim((string) ($member->member_name ?? $member->username ?? 'Member'));
                return $member;
            });

        return $members;
    }

    private function timelineVisibilityLabel(string $visibility): string
    {
        return strtolower(trim($visibility)) === 'private_shared' ? 'Private / shared' : 'Public family';
    }

    private function formatMedicalDateLabel($medicalDate): string
    {
        $medicalDate = trim((string) ($medicalDate ?? ''));
        if ($medicalDate !== '') {
            try {
                return Carbon::parse($medicalDate)->format('F j, Y');
            } catch (\Throwable $e) {
                return $medicalDate;
            }
        }

        return 'Undated';
    }

    private function memberDisplayName(?object $member): string
    {
        if (!$member) {
            return '';
        }

        return trim((string) ($member->name ?? $member->member_name ?? $member->display_name ?? ''));
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

    protected function resolvePublicFileUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
