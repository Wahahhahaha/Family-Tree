<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ValidationController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();

        // Determine if user is Admin/Superadmin
        $isAdmin = false;
        if ($currentUser->levelid == 1) {
            $isAdmin = true;
        } else {
            $member = DB::table('family_member')->where('userid', $currentUser->userid)->first();
            if ($member && in_array($member->roleid, [1, 2])) {
                $isAdmin = true;
            }
        }

        $member = DB::table('family_member')->where('userid', $currentUser->userid)->first();

        $query = DB::table('relationship_validations')
            ->join('family_member as requesters', 'relationship_validations.requested_by_member_id', '=', 'requesters.memberid')
            ->leftJoin('family_member as targets', 'relationship_validations.target_member_id', '=', 'targets.memberid')
            ->select(
                'relationship_validations.*',
                'requesters.name as requester_name',
                'requesters.picture as requester_picture',
                'targets.name as target_name',
                'targets.picture as target_picture'
            );

        if (! $isAdmin) {
            $query->where('relationship_validations.requested_by_member_id', $member?->memberid ?? 0);
        }

        $validations = $query->latest()->get();

        $statsQuery = DB::table('relationship_validations');
        if (! $isAdmin) {
            $statsQuery->where('requested_by_member_id', $member?->memberid ?? 0);
        }

        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        $targetMember = null;
        if ($request->has('target_id')) {
            $targetMember = DB::table('family_member')->where('memberid', $request->target_id)->first();
        }

        return Inertia::render('Validation/Index', [
            'validations' => $validations,
            'stats' => $stats,
            'targetMember' => $targetMember,
            'requestDeletion' => $request->boolean('request_deletion'),
            'isAdmin' => $isAdmin,
            'translations' => trans('validation_page'),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        DB::table('relationship_validations')->where('id', $id)->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'],
            'verified_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', trans('validation_page.success_update'));
    }
}
