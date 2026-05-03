<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LiveLocationService
{
    public function buildPageData(int $currentUserId): array
    {
        $currentMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('memberid', 'name', 'gender', 'picture')
            ->first();

        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        $members = $this->loadTrackedMembers();
        $memberIds = $members
            ->pluck('memberid')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();

        $relationMaps = $this->buildRelationMaps($memberIds);
        $membersById = $members->keyBy(function ($member) {
            return (int) ($member->memberid ?? 0);
        });

        $markers = [];
        foreach ($members as $member) {
            $latitude = $this->normalizeCoordinate($member->latitude ?? null);
            $longitude = $this->normalizeCoordinate($member->longitude ?? null);
            if ($latitude === null || $longitude === null) {
                continue;
            }

            $memberId = (int) ($member->memberid ?? 0);
            if ($memberId <= 0) {
                continue;
            }

            $updatedAt = $member->location_updated_at ?? null;
            $updatedCarbon = null;
            if (!empty($updatedAt)) {
                try {
                    $updatedCarbon = Carbon::parse((string) $updatedAt);
                } catch (\Throwable $e) {
                    $updatedCarbon = null;
                }
            }

            $markers[] = [
                'memberid' => $memberId,
                'userid' => (int) ($member->userid ?? 0),
                'name' => (string) ($member->name ?? ''),
                'relationship' => $this->resolveRelationshipLabel(
                    $currentMemberId,
                    $memberId,
                    $membersById,
                    $relationMaps['parentMap'],
                    $relationMaps['childrenMap']
                ),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'picture_url' => $this->resolvePictureUrl((string) ($member->picture ?? ''), (string) ($member->name ?? '')),
                'updated_at' => $updatedCarbon ? $updatedCarbon->toIso8601String() : null,
            'updated_at_label' => $updatedCarbon ? $updatedCarbon->diffForHumans() : __('live_location.unknown'),
            'updated_at_exact' => $updatedCarbon ? $updatedCarbon->format('F j, Y g:i A') : __('live_location.unknown'),
            ];
        }

        $currentMarker = null;
        foreach ($markers as $marker) {
            if ((int) ($marker['memberid'] ?? 0) === $currentMemberId) {
                $currentMarker = $marker;
                break;
            }
        }

        $center = [0, 0];
        if ($currentMarker !== null) {
            $center = [$currentMarker['latitude'], $currentMarker['longitude']];
        } elseif (!empty($markers)) {
            $center = [$markers[0]['latitude'], $markers[0]['longitude']];
        }

        return [
            'current_user_id' => $currentUserId,
            'current_member_id' => $currentMemberId,
            'current_member_name' => (string) ($currentMember->name ?? ''),
            'markers' => $markers,
            'center' => $center,
            'zoom' => !empty($markers) ? 6 : 2,
        ];
    }

    public function storeLocation(int $userId, array $payload): array
    {
        $user = DB::table('user')
            ->where('userid', $userId)
            ->select('userid', 'levelid', 'deleted_at')
            ->first();

        if (!$user || !empty($user->deleted_at)) {
            return [
                'success' => false,
                'message' => __('live_location.user_account_not_found'),
            ];
        }

        if ((int) ($user->levelid ?? 0) === 1) {
            return [
                'success' => true,
                'message' => __('live_location.location_sharing_disabled'),
            ];
        }

        $member = DB::table('family_member')
            ->where('userid', $userId)
            ->select('memberid', 'name')
            ->first();

        if (!$member) {
            return [
                'success' => false,
                'message' => __('live_location.family_member_profile_not_found'),
            ];
        }

        if (!DB::getSchemaBuilder()->hasTable('live_locations')) {
            return [
                'success' => false,
                'message' => __('live_location.live_location_table_missing'),
            ];
        }

        $latitude = $this->normalizeCoordinate($payload['latitude'] ?? null);
        $longitude = $this->normalizeCoordinate($payload['longitude'] ?? null);
        if ($latitude === null || $longitude === null) {
            return [
                'success' => false,
                'message' => __('live_location.invalid_coordinates'),
            ];
        }

        $accuracy = array_key_exists('accuracy', $payload)
            ? $this->normalizeCoordinate($payload['accuracy'])
            : null;

        $now = Carbon::now();
        $existingLocation = DB::table('live_locations')
            ->where('userid', $userId)
            ->first();

        if ($existingLocation) {
            DB::table('live_locations')
                ->where('userid', $userId)
                ->update([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy' => $accuracy,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('live_locations')->insert([
                'userid' => $userId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return [
            'success' => true,
            'message' => __('live_location.location_updated'),
        ];
    }

    private function loadTrackedMembers(): Collection
    {
        $query = DB::table('user as u')
            ->join('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->where('u.levelid', '<>', 1)
            ->whereNull('u.deleted_at');

        if (DB::getSchemaBuilder()->hasTable('live_locations')) {
            $query->leftJoin('live_locations as ll', 'll.userid', '=', 'u.userid')
                ->select(
                    'u.userid',
                    'u.levelid',
                    'u.username',
                    'fm.memberid',
                    'fm.name',
                    'fm.gender',
                    'fm.picture',
                    'll.latitude',
                    'll.longitude',
                    'll.accuracy',
                    'll.updated_at as location_updated_at'
                );
        } else {
            $query->select(
                'u.userid',
                'u.levelid',
                'u.username',
                'fm.memberid',
                'fm.name',
                'fm.gender',
                'fm.picture',
                DB::raw('NULL as latitude'),
                DB::raw('NULL as longitude'),
                DB::raw('NULL as accuracy'),
                DB::raw('NULL as location_updated_at')
            );
        }

        return $query
            ->orderBy('fm.name')
            ->get()
            ->map(function ($member) {
                if (empty($member->picture)) {
                    $member->picture = 'https://api.dicebear.com/9.x/personas/svg?seed='
                        . urlencode((string) ($member->name ?? ''))
                        . '&backgroundColor=93c5fd';
                }

                return $member;
            });
    }

    private function buildRelationMaps(array $memberIds): array
    {
        $parentMap = [];
        $childrenMap = [];

        if (empty($memberIds)) {
            return [
                'parentMap' => $parentMap,
                'childrenMap' => $childrenMap,
            ];
        }

        $relations = DB::table('relationship')
            ->whereIn('relationtype', ['child', 'partner'])
            ->select('memberid', 'relatedmemberid', 'relationtype')
            ->get();

        $memberSet = array_fill_keys($memberIds, true);

        foreach ($relations as $relation) {
            $from = (int) ($relation->memberid ?? 0);
            $to = (int) ($relation->relatedmemberid ?? 0);
            $type = strtolower(trim((string) ($relation->relationtype ?? '')));

            if ($from <= 0 || $to <= 0) {
                continue;
            }

            if (!isset($memberSet[$from]) || !isset($memberSet[$to])) {
                continue;
            }

            if ($type === 'child') {
                $childrenMap[$from] = $childrenMap[$from] ?? [];
                if (!in_array($to, $childrenMap[$from], true)) {
                    $childrenMap[$from][] = $to;
                }

                $parentMap[$to] = $parentMap[$to] ?? [];
                if (!in_array($from, $parentMap[$to], true)) {
                    $parentMap[$to][] = $from;
                }
            }
        }

        return [
            'parentMap' => $parentMap,
            'childrenMap' => $childrenMap,
        ];
    }

    private function resolveRelationshipLabel(
        int $currentMemberId,
        int $targetMemberId,
        Collection $membersById,
        array $parentMap,
        array $childrenMap
    ): string {
        if ($targetMemberId <= 0) {
            return __('live_location.other_family_member');
        }

        if ($currentMemberId <= 0) {
            return __('live_location.other_family_member');
        }

        if ($currentMemberId === $targetMemberId) {
            return __('live_location.you');
        }

        if (in_array($targetMemberId, $parentMap[$currentMemberId] ?? [], true)) {
            return $this->genderLabel($membersById, $targetMemberId, __('live_location.father'), __('live_location.mother'), __('live_location.parent'));
        }

        if (in_array($targetMemberId, $childrenMap[$currentMemberId] ?? [], true)) {
            return $this->genderLabel($membersById, $targetMemberId, __('live_location.son'), __('live_location.daughter'), __('live_location.child'));
        }

        $grandParentIds = [];
        foreach ($parentMap[$currentMemberId] ?? [] as $parentId) {
            foreach ($parentMap[(int) $parentId] ?? [] as $grandParentId) {
                $grandParentIds[(int) $grandParentId] = true;
            }
        }

        if (isset($grandParentIds[$targetMemberId])) {
            return $this->genderLabel($membersById, $targetMemberId, __('live_location.grandfather'), __('live_location.grandmother'), __('live_location.grandparent'));
        }

        $grandChildIds = [];
        foreach ($childrenMap[$currentMemberId] ?? [] as $childId) {
            foreach ($childrenMap[(int) $childId] ?? [] as $grandChildId) {
                $grandChildIds[(int) $grandChildId] = true;
            }
        }

        if (isset($grandChildIds[$targetMemberId])) {
            return $this->genderLabel($membersById, $targetMemberId, __('live_location.grandson'), __('live_location.granddaughter'), __('live_location.grandchild'));
        }

        return __('live_location.other_family_member');
    }

    private function genderLabel(Collection $membersById, int $memberId, string $maleLabel, string $femaleLabel, string $fallback): string
    {
        $member = $membersById->get($memberId);
        $gender = strtolower(trim((string) ($member->gender ?? '')));
        if ($gender === 'male') {
            return $maleLabel;
        }

        if ($gender === 'female') {
            return $femaleLabel;
        }

        return $fallback;
    }

    private function resolvePictureUrl(string $picture, string $name): string
    {
        $picture = trim($picture);
        if ($picture === '') {
            return 'https://api.dicebear.com/9.x/personas/svg?seed=' . urlencode($name) . '&backgroundColor=93c5fd';
        }

        if (preg_match('#^https?://#i', $picture) || str_starts_with($picture, 'data:')) {
            return $picture;
        }

        return asset(ltrim($picture, '/'));
    }

    private function normalizeCoordinate($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '' || !is_numeric($stringValue)) {
            return null;
        }

        return (float) $stringValue;
    }
}
