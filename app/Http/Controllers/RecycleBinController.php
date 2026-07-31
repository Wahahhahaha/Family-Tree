<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RecycleBinController extends Controller
{
    public function index()
    {
        $deletedMembers = DB::table('family_member')
            ->whereNotNull('deleted_at')
            ->select('memberid as id', 'name', 'picture', 'deleted_at', 'userid')    
            ->get()
            ->map(function ($item) {
                $item->type = 'member';
                $item->deleted_by_cascade_from = null;

                return $item;
            });

        $deletedUsers = DB::table('user')
            ->leftJoin('employer', 'user.userid', '=', 'employer.userid')
            ->whereNotNull('user.deleted_at')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('family_member')
                    ->whereColumn('family_member.userid', 'user.userid');
            })
            ->select('user.userid as id', 'employer.name', 'user.deleted_at')
            ->get()
            ->map(function ($item) {
                $item->type = 'user';
                $item->picture = null;
                $item->deleted_by_cascade_from = null;

                return $item;
            });

        $mergedItems = $deletedMembers->concat($deletedUsers)->sortByDesc('deleted_at')->values();

        $deletedSocialMedia = DB::table('socialmedia')
            ->whereNotNull('deleted_at')
            ->select('socialid as id', 'socialname as name', 'deleted_at', 'socialicon')
            ->latest('deleted_at')
            ->get()
            ->map(function ($item) {
                $item->type = 'socialmedia';

                return $item;
            });

        return Inertia::render('Management/RecycleBin', [
            'mergedItems' => $mergedItems,
            'deletedSocialMedia' => $deletedSocialMedia,
            'translations' => trans('recycle'),
        ]);
    }

    public function restore(Request $request, $id)
    {
        $type = $request->input('type', 'member');

        DB::transaction(function () use ($id, $type) {
            if ($type === 'member') {
                // Restore the core member
                DB::table('family_member')->where('memberid', $id)->update([
                    'deleted_at' => null,
                ]);

                $member = DB::table('family_member')->where('memberid', $id)->first();
                if ($member && $member->userid) {
                    DB::table('user')->where('userid', $member->userid)->update(['deleted_at' => null]);        
                }

                // Restore relationships
                DB::table('relationship')
                    ->where(function ($q) use ($id) {
                        $q->where('memberid', $id)->orWhere('relatedmemberid', $id);
                    })
                    ->update(['deleted_at' => null]);

            } elseif ($type === 'user') {
                DB::table('user')->where('userid', $id)->update(['deleted_at' => null]);
            } elseif ($type === 'socialmedia') {
                DB::table('socialmedia')->where('socialid', $id)->update(['deleted_at' => null]);
            }
        });

        return back()->with('success', trans('recycle.success_restore'));
    }

    public function permanentDelete(Request $request, $id)
    {
        $type = $request->input('type', 'member');

        DB::transaction(function () use ($id, $type) {
            if ($type === 'member') {
                $member = DB::table('family_member')->where('memberid', $id)->first();
                if ($member) {
                    DB::table('relationship')->where('memberid', $id)->orWhere('relatedmemberid', $id)->delete();
                    DB::table('ownsocial')->where('memberid', $id)->delete();
                    DB::table('family_member')->where('memberid', $id)->delete();
                    if ($member->userid) {
                        DB::table('user')->where('userid', $member->userid)->delete();
                    }
                }
            } elseif ($type === 'user') {
                DB::table('employer')->where('userid', $id)->delete();
                DB::table('user')->where('userid', $id)->delete();
            } elseif ($type === 'socialmedia') {
                DB::table('socialmedia')->where('socialid', $id)->delete();
            }
        });

        return back()->with('success', trans('recycle.success_permanent', ['type' => ucfirst($type)]));
    }
}
