<?php

namespace App\Repositories;

use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FamilyMemberRepository
{
    public function getAllActive(): Collection
    {
        return FamilyMember::with(['user.level', 'relationships.relatedMember'])
            ->whereHas('user', function ($query) {
                $query->where('levelid', 2)->whereNull('deleted_at');
            })
            ->get();
    }

    public function usersQuery()
    {
        return DB::table('user as u')
            ->leftJoin('level as l', 'l.levelid', '=', 'u.levelid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->select(
                'u.userid',
                'u.username',
                'u.deleted_at',
                'u.levelid',
                'l.levelname',
                'r.roleid',
                'r.rolename',
                'fm.memberid',
                'fm.life_status',
                'fm.gender',
                'fm.birthdate',
                'fm.picture',
                DB::raw("COALESCE(e.name, fm.name, u.username) as fullname"),
                DB::raw("COALESCE(e.email, fm.email, '-') as email"),
                DB::raw("COALESCE(e.phonenumber, fm.phonenumber, '-') as phone"),
                DB::raw("CASE WHEN e.employerid IS NOT NULL THEN 'Employer' WHEN fm.memberid IS NOT NULL THEN 'Family Member' ELSE 'User' END as source")
            );
    }

    public function find(int $memberId): ?FamilyMember
    {
        return FamilyMember::find($memberId);
    }

    public function updateLifeStatus(int $memberId, string $status): bool
    {
        return FamilyMember::where('memberid', $memberId)
            ->update(['life_status' => ucfirst(strtolower($status))]);
    }
}
