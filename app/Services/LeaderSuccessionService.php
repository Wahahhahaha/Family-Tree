<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LeaderSuccessionService
{
    public function getSettingForOwner(int $ownerUserId): ?object
    {
        if ($ownerUserId <= 0 || !Schema::hasTable('leader_succession_settings')) {
            return null;
        }

        return DB::table('leader_succession_settings')
            ->where('owner_userid', $ownerUserId)
            ->first();
    }

    public function getCandidateMembers(int $ownerUserId)
    {
        if (!Schema::hasTable('family_member') || !Schema::hasTable('user')) {
            return collect();
        }

        $eligibleIds = $this->resolveEligibleCandidateMemberIds($ownerUserId);

        if (empty($eligibleIds)) {
            return collect();
        }

        return DB::table('family_member as fm')
            ->join('user as u', 'u.userid', '=', 'fm.userid')
            ->whereIn('fm.memberid', $eligibleIds)
            ->whereNull('u.deleted_at')
            ->where('u.userid', '!=', $ownerUserId)
            ->where(function ($query) {
                $query->whereNull('fm.life_status')
                    ->orWhereRaw('LOWER(fm.life_status) <> ?', ['deceased']);
            })
            ->select('fm.memberid', 'fm.userid', 'fm.name', 'fm.gender', 'fm.life_status', 'u.username')
            ->orderBy('fm.name')
            ->get();
    }

    public function saveHeir(int $ownerUserId, int $heirMemberId, string $currentPin, ?string $newPin = null): object
    {
        $setting = $this->getSettingForOwner($ownerUserId);
        $isFirstSetup = !$setting || empty($setting->pin_hash);

        if ($setting && !$isFirstSetup) {
            $this->assertPinMatches($currentPin, (string) $setting->pin_hash);
        }

        $heir = $this->resolveValidHeirMember($ownerUserId, $heirMemberId);
        if (!$heir) {
            throw ValidationException::withMessages([
                'heir_memberid' => ['Selected heir is not a valid active family member.'],
            ]);
        }

        $payload = [
            'owner_userid' => $ownerUserId,
            'heir_memberid' => $heir->memberid,
            'updated_at' => now(),
        ];

        if ($isFirstSetup) {
            $this->assertPinFormat($newPin);
            $payload['pin_hash'] = Hash::make($newPin);
            $payload['created_at'] = now();
        }

        DB::table('leader_succession_settings')->updateOrInsert(
            ['owner_userid' => $ownerUserId],
            $payload
        );

        return $this->getSettingForOwner($ownerUserId) ?? (object) $payload;
    }

    public function updatePin(int $ownerUserId, string $currentPin, string $newPin): void
    {
        $setting = $this->requireSetting($ownerUserId);
        $this->assertPinMatches($currentPin, (string) $setting->pin_hash);
        $this->assertPinFormat($newPin);

        DB::table('leader_succession_settings')
            ->where('owner_userid', $ownerUserId)
            ->update([
                'pin_hash' => Hash::make($newPin),
                'updated_at' => now(),
            ]);
    }

    public function promoteHeirForDeceasedMember(int $deceasedMemberId, ?Request $request = null): ?array
    {
        if ($deceasedMemberId <= 0 || !Schema::hasTable('leader_succession_settings')) {
            return null;
        }

        $deceasedUserId = (int) DB::table('family_member')
            ->where('memberid', $deceasedMemberId)
            ->value('userid');

        if ($deceasedUserId <= 0) {
            return null;
        }

        $setting = DB::table('leader_succession_settings')
            ->where('owner_userid', $deceasedUserId)
            ->first();

        if (!$setting || empty($setting->heir_memberid)) {
            return null;
        }

        $heir = DB::table('family_member as fm')
            ->join('user as u', 'u.userid', '=', 'fm.userid')
            ->where('fm.memberid', (int) $setting->heir_memberid)
            ->whereNull('u.deleted_at')
            ->where(function ($query) {
                $query->whereNull('fm.life_status')
                    ->orWhereRaw('LOWER(fm.life_status) <> ?', ['deceased']);
            })
            ->select('fm.memberid', 'fm.userid', 'fm.name', 'fm.email', 'fm.phonenumber', 'u.username')
            ->first();

        if (!$heir || (int) $heir->userid <= 0) {
            return null;
        }

        $heirUserId = (int) $heir->userid;

        DB::transaction(function () use ($heir, $heirUserId, $deceasedUserId) {
            DB::table('user')
                ->where('userid', $heirUserId)
                ->update([
                    'levelid' => 2,
                ]);

            $employerPayload = [
                'name' => (string) ($heir->name ?? ''),
                'email' => (string) ($heir->email ?? ''),
                'phonenumber' => (string) ($heir->phonenumber ?? ''),
                'roleid' => 2,
            ];

            if (Schema::hasTable('employer')) {
                DB::table('employer')->updateOrInsert(
                    ['userid' => $heirUserId],
                    array_merge($employerPayload, ['userid' => $heirUserId])
                );
            }

            DB::table('leader_succession_settings')
                ->where('owner_userid', $deceasedUserId)
                ->update([
                    'updated_at' => now(),
                ]);
        });

        if ($request) {
            $sessionUser = (array) $request->session()->get('authenticated_user', []);
            if ((int) ($sessionUser['userid'] ?? 0) === $heirUserId) {
                $sessionUser['levelid'] = 2;
                $sessionUser['roleid'] = 2;
                $sessionUser['rolename'] = 'Admin';
                $request->session()->put('authenticated_user', $sessionUser);
            }
        }

        return [
            'heir_memberid' => (int) $heir->memberid,
            'heir_userid' => $heirUserId,
            'heir_name' => (string) ($heir->name ?? ''),
        ];
    }

    private function requireSetting(int $ownerUserId): object
    {
        $setting = $this->getSettingForOwner($ownerUserId);
        if (!$setting) {
            throw ValidationException::withMessages([
                'setting' => ['Leader succession settings have not been created yet.'],
            ]);
        }

        return $setting;
    }

    private function resolveValidHeirMember(int $ownerUserId, int $heirMemberId): ?object
    {
        if ($heirMemberId <= 0 || !Schema::hasTable('family_member') || !Schema::hasTable('user')) {
            return null;
        }

        $eligibleIds = $this->resolveEligibleCandidateMemberIds($ownerUserId);
        if (empty($eligibleIds) || !in_array($heirMemberId, $eligibleIds, true)) {
            return null;
        }

        return DB::table('family_member as fm')
            ->join('user as u', 'u.userid', '=', 'fm.userid')
            ->where('fm.memberid', $heirMemberId)
            ->where('u.userid', '!=', $ownerUserId)
            ->whereNull('u.deleted_at')
            ->where(function ($query) {
                $query->whereNull('fm.life_status')
                    ->orWhereRaw('LOWER(fm.life_status) <> ?', ['deceased']);
            })
            ->select('fm.memberid', 'fm.userid', 'fm.name', 'fm.life_status')
            ->first();
    }

    private function resolveOwnerMemberId(int $ownerUserId): int
    {
        if ($ownerUserId <= 0 || !Schema::hasTable('family_member')) {
            return 0;
        }

        return (int) DB::table('family_member')
            ->where('userid', $ownerUserId)
            ->value('memberid');
    }

    private function resolveEligibleCandidateMemberIds(int $ownerUserId): array
    {
        $ownerMemberId = $this->resolveOwnerMemberId($ownerUserId);
        if ($ownerMemberId > 0) {
            $eligibleIds = $this->filterCoreLineageMemberIds(
                $this->resolveDirectLineageMemberIds($ownerMemberId)
            );
            if (!empty($eligibleIds)) {
                return $eligibleIds;
            }
        }

        return $this->resolveAllCoreLineageMemberIds();
    }

    private function resolveAllCoreLineageMemberIds(): array
    {
        if (!Schema::hasTable('family_member') || !Schema::hasTable('relationship')) {
            return [];
        }

        $relations = DB::table('relationship')
            ->where('relationtype', 'child')
            ->select('memberid', 'relatedmemberid')
            ->get();

        $childrenMap = [];
        $childMemberIds = [];

        foreach ($relations as $relation) {
            $parentId = (int) ($relation->memberid ?? 0);
            $childId = (int) ($relation->relatedmemberid ?? 0);

            if ($parentId <= 0 || $childId <= 0) {
                continue;
            }

            $childrenMap[$parentId] = $childrenMap[$parentId] ?? [];
            if (!in_array($childId, $childrenMap[$parentId], true)) {
                $childrenMap[$parentId][] = $childId;
            }

            $childMemberIds[$childId] = true;
        }

        $rootMemberIds = DB::table('family_member')
            ->pluck('memberid')
            ->map(fn ($memberId) => (int) $memberId)
            ->filter(fn ($memberId) => $memberId > 0 && !isset($childMemberIds[$memberId]))
            ->values()
            ->all();

        if (empty($rootMemberIds)) {
            return [];
        }

        $allDescendants = [];
        foreach ($rootMemberIds as $rootMemberId) {
            foreach ($this->resolveDirectLineageMemberIds($rootMemberId) as $descendantId) {
                $allDescendants[] = (int) $descendantId;
            }
        }

        return $this->filterCoreLineageMemberIds(array_values(array_unique($allDescendants)));
    }

    private function resolveDirectLineageMemberIds(int $rootMemberId): array
    {
        if ($rootMemberId <= 0 || !Schema::hasTable('relationship')) {
            return [];
        }

        $relations = DB::table('relationship')
            ->where('relationtype', 'child')
            ->select('memberid', 'relatedmemberid')
            ->get();

        $childrenMap = [];
        foreach ($relations as $relation) {
            $parentId = (int) ($relation->memberid ?? 0);
            $childId = (int) ($relation->relatedmemberid ?? 0);

            if ($parentId <= 0 || $childId <= 0) {
                continue;
            }

            $childrenMap[$parentId] = $childrenMap[$parentId] ?? [];
            if (!in_array($childId, $childrenMap[$parentId], true)) {
                $childrenMap[$parentId][] = $childId;
            }
        }

        $descendants = [];
        $queue = [$rootMemberId];
        $visited = [$rootMemberId => true];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            foreach ($childrenMap[$currentId] ?? [] as $childId) {
                if (isset($visited[$childId])) {
                    continue;
                }

                $visited[$childId] = true;
                $descendants[] = $childId;
                $queue[] = $childId;
            }
        }

        return $descendants;
    }

    private function filterCoreLineageMemberIds(array $descendantIds): array
    {
        if (empty($descendantIds) || !Schema::hasTable('relationship')) {
            return $descendantIds;
        }

        $descendantSet = array_fill_keys(array_map('intval', $descendantIds), true);
        $inLawSet = [];

        $partnerRelations = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->select('memberid', 'relatedmemberid')
            ->get();

        foreach ($partnerRelations as $relation) {
            $leftId = (int) ($relation->memberid ?? 0);
            $rightId = (int) ($relation->relatedmemberid ?? 0);

            if ($leftId <= 0 || $rightId <= 0) {
                continue;
            }

            $leftIsDescendant = isset($descendantSet[$leftId]);
            $rightIsDescendant = isset($descendantSet[$rightId]);

            if ($leftIsDescendant && !$rightIsDescendant) {
                $inLawSet[$rightId] = true;
            } elseif ($rightIsDescendant && !$leftIsDescendant) {
                $inLawSet[$leftId] = true;
            }
        }

        $eligibleIds = [];
        foreach ($descendantIds as $memberId) {
            $memberId = (int) $memberId;
            if ($memberId <= 0 || isset($inLawSet[$memberId])) {
                continue;
            }

            $eligibleIds[] = $memberId;
        }

        return array_values(array_unique($eligibleIds));
    }

    private function assertPinMatches(string $plainPin, string $hashedPin): void
    {
        if (!$this->isValidPinFormat($plainPin) || !Hash::check($plainPin, $hashedPin)) {
            throw ValidationException::withMessages([
                'current_pin' => ['PIN is incorrect.'],
            ]);
        }
    }

    private function assertPinFormat(?string $pin): void
    {
        if (!$this->isValidPinFormat((string) $pin)) {
            throw ValidationException::withMessages([
                'pin' => ['PIN must be exactly 4 digits.'],
            ]);
        }
    }

    private function isValidPinFormat(string $pin): bool
    {
        return (bool) preg_match('/^\d{4}$/', $pin);
    }
}
