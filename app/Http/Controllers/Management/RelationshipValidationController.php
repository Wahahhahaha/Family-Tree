<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Services\RelationshipValidationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RelationshipValidationController extends Controller
{
    public function __construct(
        protected RelationshipValidationService $relationshipValidationService
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'You do not have permission to view relationship validations.');
        }

        $familyId = (int) ($this->resolveCurrentFamilyId((int) session('authenticated_user.userid')) ?? 1);
        $statusFilter = strtolower(trim((string) $request->query('status', 'pending')));
        if ($statusFilter !== '' && !in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'], true)) {
            $statusFilter = 'pending';
        }

        $search = trim((string) $request->query('search', ''));
        $validations = $this->loadValidationPage($request, $familyId, $search, $statusFilter);
        $selectedValidationId = (int) $request->query('selected', 0);
        $selectedValidation = $selectedValidationId > 0
            ? $this->loadValidationRecord($familyId, $selectedValidationId)
            : $validations->first();

        $counts = $this->getValidationStatusCounts($familyId);
        $selectedValidation = $selectedValidation ? $this->decorateValidationRecord($selectedValidation) : null;

        return view('admin.relationship-validation', [
            'pageClass' => 'page-family-tree page-management-validation',
            'systemSettings' => $this->getSystemSettings(),
            'validations' => $validations,
            'selectedValidation' => $selectedValidation,
            'statusFilter' => $statusFilter,
            'searchKeyword' => $search,
            'counts' => $counts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentMember = $this->resolveCurrentMember($currentUserId);

        if (!$currentMember) {
            return redirect('/')->with('error', 'Your family profile could not be found.');
        }

        $familyId = (int) ($this->resolveCurrentFamilyId($currentUserId) ?? 1);

        $validator = Validator::make($request->all(), [
            'action_type' => ['required', 'in:divorce,delete_child,delete_partner'],
            'memberid' => ['required', 'integer', 'exists:family_member,memberid'],
            'reason' => ['required', 'string', 'max:5000'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ], [
            'action_type.required' => 'Please choose a validation action.',
            'action_type.in' => 'The selected validation action is invalid.',
            'memberid.required' => 'Target member is required.',
            'memberid.exists' => 'Selected target member is not found.',
            'reason.required' => 'Reason is required.',
            'document.required' => 'Proof document is required.',
            'document.file' => 'Uploaded proof document is invalid.',
            'document.mimes' => 'Proof document must be PDF, JPG, JPEG, PNG, or WebP.',
            'document.max' => 'Proof document must not exceed 8MB.',
        ]);

        if ($validator->fails()) {
            return redirect('/')
                ->withErrors($validator)
                ->withInput()
                ->with('openRelationshipValidationModal', true);
        }

        $validated = $validator->validated();

        $actionType = strtolower((string) $validated['action_type']);
        $targetMemberId = (int) $validated['memberid'];
        $targetMember = DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->select('memberid', 'userid', 'name')
            ->first();

        if (!$targetMember) {
            return redirect('/')->with('error', 'Selected target member is not found.');
        }

        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        if ($currentMemberId <= 0) {
            return redirect('/')->with('error', 'Your family profile could not be found.');
        }

        $isAdminOrSuperadmin = in_array($currentRoleId, [1, 2], true);
        if (!$isAdminOrSuperadmin) {
            if ($actionType === 'delete_child') {
                $hasChildRelation = DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('memberid', $currentMemberId)
                    ->where('relatedmemberid', $targetMemberId)
                    ->exists();

                if (!$hasChildRelation) {
                    return redirect('/')->with('error', 'You can only request delete child for your own child.');
                }
            } else {
                $hasPartnerRelation = DB::table('relationship')
                    ->where('relationtype', 'partner')
                    ->where(function ($query) use ($currentMemberId, $targetMemberId) {
                        $query->where(function ($subQuery) use ($currentMemberId, $targetMemberId) {
                            $subQuery->where('memberid', $currentMemberId)
                                ->where('relatedmemberid', $targetMemberId);
                        })->orWhere(function ($subQuery) use ($currentMemberId, $targetMemberId) {
                            $subQuery->where('memberid', $targetMemberId)
                                ->where('relatedmemberid', $currentMemberId);
                        });
                    })
                    ->exists();

                if (!$hasPartnerRelation) {
                    return redirect('/')->with('error', 'You can only request divorce or delete partner for your own partner.');
                }
            }
        }

        $relativeDir = 'relationship-validations/family-' . max(1, $familyId) . '/' . now()->format('Y/m');
        $uploadedFile = $request->file('document');
        $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());
        $fileName = Str::slug($actionType . '-' . (string) $targetMember->name . '-' . now()->format('YmdHis')) . '-' . Str::random(12) . '.' . $extension;
        $documentPath = Storage::disk('local')->putFileAs($relativeDir, $uploadedFile, $fileName);

        if (!is_string($documentPath) || trim($documentPath) === '') {
            return redirect('/')->with('error', 'Unable to store the proof document.');
        }

        DB::table('relationship_validations')->insert([
            'family_id' => $familyId > 0 ? $familyId : 1,
            'requested_by' => $currentUserId,
            'requested_by_member_id' => $currentMemberId,
            'action_type' => $actionType,
            'target_member_id' => $targetMemberId,
            'target_user_id' => (int) ($targetMember->userid ?? 0),
            'partner_id' => in_array($actionType, ['divorce', 'delete_partner'], true) ? $targetMemberId : null,
            'child_id' => $actionType === 'delete_child' ? $targetMemberId : null,
            'document_path' => $documentPath,
            'reason' => trim((string) $validated['reason']),
            'status' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->logActivity($request, 'relationship_validation.requested', [
            'family_id' => $familyId,
            'requested_by' => $currentUserId,
            'requested_by_memberid' => $currentMemberId,
            'action_type' => $actionType,
            'target_memberid' => $targetMemberId,
            'target_name' => (string) ($targetMember->name ?? ''),
        ]);

        return redirect('/')
            ->with('success', 'Your relationship validation request has been submitted for verification.');
    }

    public function document(Request $request, int $validationId)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'You do not have permission to view validation evidence.');
        }

        $familyId = (int) ($this->resolveCurrentFamilyId((int) session('authenticated_user.userid')) ?? 1);
        $validation = $this->loadValidationRecord($familyId, $validationId);

        if (!$validation) {
            abort(404);
        }

        $path = trim((string) ($validation->document_path ?? ''));
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($path);
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);
    }

    public function approve(Request $request, int $validationId): RedirectResponse
    {
        return $this->verifyValidationRequest($request, $validationId, 'approved');
    }

    public function reject(Request $request, int $validationId): RedirectResponse
    {
        return $this->verifyValidationRequest($request, $validationId, 'rejected');
    }

    protected function verifyValidationRequest(Request $request, int $validationId, string $status): RedirectResponse
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/management/users')->with('error', 'You do not have permission to verify relationship validations.');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $familyId = (int) ($this->resolveCurrentFamilyId($currentUserId) ?? 1);
        $validation = $this->loadValidationRecord($familyId, $validationId);
        if (!$validation) {
            return redirect('/management/validation')->with('error', 'Validation request was not found.');
        }

        if (($validation->status ?? 'pending') !== 'pending') {
            return redirect('/management/validation?selected=' . $validationId)
                ->with('error', 'This validation request has already been processed.');
        }

        $adminNotes = trim((string) $request->input('admin_notes', ''));
        if ($status === 'rejected' && $adminNotes === '') {
            return redirect('/management/validation?selected=' . $validationId)
                ->withErrors(['admin_notes' => 'Admin notes are required when rejecting a request.']);
        }

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            DB::transaction(function () use ($status, $currentUserId, $adminNotes, $validation, $validationId) {
                if ($status === 'approved') {
                    $this->relationshipValidationService->approve($validation);
                }

                $payload = [
                    'status' => $status,
                    'verified_by' => $currentUserId,
                    'verified_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'admin_notes' => $adminNotes !== '' ? $adminNotes : null,
                ];

                if ($status === 'approved') {
                    $payload['approved_at'] = Carbon::now();
                }

                if ($status === 'rejected') {
                    $payload['rejected_at'] = Carbon::now();
                }

                DB::table('relationship_validations')
                    ->where('id', $validationId)
                    ->update($payload);
            });

            $this->logActivity($request, 'relationship_validation.' . $status, [
                'validation_id' => $validationId,
                'action_type' => (string) ($validation->action_type ?? ''),
                'target_memberid' => (int) ($validation->target_member_id ?? 0),
                'requested_by' => (int) ($validation->requested_by ?? 0),
                'verified_by' => $currentUserId,
            ]);

            $message = $status === 'approved'
                ? 'Relationship validation request has been approved.'
                : 'Relationship validation request has been rejected.';

            return redirect('/management/validation?selected=' . $validationId)->with('success', $message);
        } catch (\Throwable $e) {
            return redirect('/management/validation?selected=' . $validationId)
                ->with('error', 'Unable to process the validation request.');
        }
    }

    protected function loadValidationPage(Request $request, int $familyId, string $search, string $statusFilter): LengthAwarePaginator
    {
        $query = $this->buildValidationQuery($familyId);

        if ($statusFilter !== '' && $statusFilter !== 'all') {
            $query->where('rv.status', $statusFilter);
        }

        if ($search !== '') {
            $needle = '%' . $search . '%';
            $query->where(function ($subQuery) use ($needle) {
                $subQuery->where('rv.reason', 'like', $needle)
                    ->orWhere('rv.action_type', 'like', $needle)
                    ->orWhere('rv.status', 'like', $needle)
                    ->orWhere('requester_user.username', 'like', $needle)
                    ->orWhere('requester_member.name', 'like', $needle)
                    ->orWhere('target_member.name', 'like', $needle);
            });
        }

        $paginator = $query
            ->orderByDesc('rv.created_at')
            ->paginate(15)
            ->withQueryString();

        $decoratedItems = collect($paginator->items())
            ->map(function ($row) {
                return $this->decorateValidationRecord((object) $row);
            })
            ->all();

        return new LengthAwarePaginator(
            $decoratedItems,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    protected function loadValidationRecord(int $familyId, int $validationId): ?object
    {
        if ($validationId <= 0) {
            return null;
        }

        $row = $this->buildValidationQuery($familyId)
            ->where('rv.id', $validationId)
            ->first();

        return $row ? $this->decorateValidationRecord($row) : null;
    }

    protected function buildValidationQuery(int $familyId)
    {
        return DB::table('relationship_validations as rv')
            ->leftJoin('user as requester_user', 'requester_user.userid', '=', 'rv.requested_by')
            ->leftJoin('family_member as requester_member', 'requester_member.memberid', '=', 'rv.requested_by_member_id')
            ->leftJoin('family_member as target_member', 'target_member.memberid', '=', 'rv.target_member_id')
            ->leftJoin('user as verifier_user', 'verifier_user.userid', '=', 'rv.verified_by')
            ->where('rv.family_id', $familyId > 0 ? $familyId : 1)
            ->select(
                'rv.*',
                'requester_user.username as requester_username',
                'requester_member.name as requester_member_name',
                'target_member.name as target_member_name',
                'verifier_user.username as verifier_username'
            );
    }

    protected function decorateValidationRecord(object $row): object
    {
        $actionType = strtolower(trim((string) ($row->action_type ?? '')));
        $status = strtolower(trim((string) ($row->status ?? 'pending')));
        $row->action_label = match ($actionType) {
            'divorce' => 'Divorce',
            'delete_child' => 'Delete Child',
            'delete_partner' => 'Delete Partner',
            default => Str::headline($actionType),
        };
        $row->status_label = match ($status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending Verification',
        };
        $row->status_class = match ($status) {
            'approved' => 'is-approved',
            'rejected' => 'is-rejected',
            default => 'is-pending',
        };
        $row->requester_label = trim((string) ($row->requester_member_name ?? '')) !== ''
            ? (string) $row->requester_member_name
            : (string) ($row->requester_username ?? 'Unknown');
        $row->target_label = trim((string) ($row->target_member_name ?? '')) !== ''
            ? (string) $row->target_member_name
            : ('Member #' . (int) ($row->target_member_id ?? 0));
        $row->verified_label = trim((string) ($row->verifier_username ?? '')) !== ''
            ? (string) $row->verifier_username
            : '-';
        $row->submitted_at = $this->formatDateTimeLabel($row->created_at ?? null);
        $row->verified_at_label = $this->formatDateTimeLabel($row->verified_at ?? null);
        $row->approved_at_label = $this->formatDateTimeLabel($row->approved_at ?? null);
        $row->rejected_at_label = $this->formatDateTimeLabel($row->rejected_at ?? null);
        $row->last_updated_label = $this->formatDateTimeLabel($row->updated_at ?? null);
        $row->document_url = url('/management/validation/' . (int) ($row->id ?? 0) . '/document');

        return $row;
    }

    protected function getValidationStatusCounts(int $familyId): array
    {
        $rows = DB::table('relationship_validations')
            ->where('family_id', $familyId > 0 ? $familyId : 1)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'pending' => (int) ($rows['pending'] ?? 0),
            'approved' => (int) ($rows['approved'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
        ];
    }

    protected function formatDateTimeLabel($value): string
    {
        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return '-';
        }

        try {
            return Carbon::parse($stringValue)->toDateTimeString();
        } catch (\Throwable) {
            return $stringValue;
        }
    }

    protected function resolveCurrentMember(int $userId): ?object
    {
        if ($userId <= 0 || !Schema::hasTable('family_member')) {
            return null;
        }

        return DB::table('family_member')
            ->where('userid', $userId)
            ->select('memberid', 'userid', 'name')
            ->first();
    }
}
