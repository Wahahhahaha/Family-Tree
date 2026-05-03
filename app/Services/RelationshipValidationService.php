<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RelationshipValidationService
{
    public function approve(object $validation): void
    {
        $actionType = strtolower(trim((string) ($validation->action_type ?? '')));
        if ($actionType === '') {
            throw new \RuntimeException('Invalid validation action.');
        }

        if (in_array($actionType, ['divorce', 'delete_partner'], true)) {
            $this->applyPartnerSeparation($validation);
            if ($actionType === 'divorce') {
                $this->moveDivorceTargetAccountToRecycleBin($validation);
            }
            return;
        }

        if ($actionType === 'delete_child') {
            $this->applyCascadeDeleteChild($validation);
            return;
        }

        throw new \RuntimeException('Unsupported validation action.');
    }

    protected function applyPartnerSeparation(object $validation): void
    {
        $memberA = (int) ($validation->requested_by_member_id ?? 0);
        $memberB = (int) ($validation->target_member_id ?? 0);

        if ($memberA <= 0 || $memberB <= 0 || $memberA === $memberB) {
            throw new \RuntimeException('Invalid partner relation.');
        }

        DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($memberA, $memberB) {
                $query->where(function ($subQuery) use ($memberA, $memberB) {
                    $subQuery->where('memberid', $memberA)
                        ->where('relatedmemberid', $memberB);
                })->orWhere(function ($subQuery) use ($memberA, $memberB) {
                    $subQuery->where('memberid', $memberB)
                        ->where('relatedmemberid', $memberA);
                });
            })
            ->delete();

        DB::table('family_member')
            ->whereIn('memberid', [$memberA, $memberB])
            ->update(['marital_status' => 'single']);

        $this->clearFamilyCaches();
    }

    protected function moveDivorceTargetAccountToRecycleBin(object $validation): void
    {
        $targetUserId = (int) ($validation->target_user_id ?? 0);
        if ($targetUserId <= 0) {
            $targetMemberId = (int) ($validation->target_member_id ?? 0);
            if ($targetMemberId > 0) {
                $targetUserId = (int) (DB::table('family_member')
                    ->where('memberid', $targetMemberId)
                    ->value('userid') ?? 0);
            }
        }

        if ($targetUserId <= 0) {
            return;
        }

        $targetUser = DB::table('user')
            ->where('userid', $targetUserId)
            ->select('userid', 'deleted_at')
            ->first();

        if (!$targetUser || $targetUser->deleted_at !== null) {
            return;
        }

        DB::table('user')
            ->where('userid', $targetUserId)
            ->update([
                'deleted_at' => Carbon::now(),
            ]);
    }

    protected function applyCascadeDeleteChild(object $validation): void
    {
        $rootChildMemberId = (int) ($validation->child_id ?? $validation->target_member_id ?? 0);
        if ($rootChildMemberId <= 0) {
            throw new \RuntimeException('Invalid child relation.');
        }

        $memberIdsToDelete = $this->resolveCascadeDeleteMemberIdsFromChild($rootChildMemberId);
        $memberIdsToDelete = array_values(array_unique(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $memberIdsToDelete), function ($id) {
            return $id > 0;
        })));

        if (empty($memberIdsToDelete)) {
            throw new \RuntimeException('No related members were found for deletion.');
        }

        $membersToDelete = DB::table('family_member')
            ->whereIn('memberid', $memberIdsToDelete)
            ->select('memberid', 'userid', 'picture')
            ->get();

        if ($membersToDelete->isEmpty()) {
            throw new \RuntimeException('No related members were found for deletion.');
        }

        $userIdsToDelete = $membersToDelete
            ->pluck('userid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        DB::table('relationship')
            ->whereIn('memberid', $memberIdsToDelete)
            ->orWhereIn('relatedmemberid', $memberIdsToDelete)
            ->delete();

        DB::table('ownsocial')
            ->whereIn('memberid', $memberIdsToDelete)
            ->delete();

        DB::table('family_member')
            ->whereIn('memberid', $memberIdsToDelete)
            ->delete();

        if (!empty($userIdsToDelete)) {
            DB::table('employer')
                ->whereIn('userid', $userIdsToDelete)
                ->delete();

            DB::table('user')
                ->whereIn('userid', $userIdsToDelete)
                ->delete();
        }

        foreach ($membersToDelete as $memberToDelete) {
            $picture = (string) ($memberToDelete->picture ?? '');
            if ($picture !== '' && str_starts_with($picture, '/uploads/family-member/')) {
                $picturePath = public_path(ltrim($picture, '/'));
                if (File::exists($picturePath)) {
                    File::delete($picturePath);
                }
            }
        }

        $this->clearFamilyCaches();
    }

    protected function clearFamilyCaches(): void
    {
        Cache::store('file')->forget('family_tree:relationships:v1');
        Cache::store('file')->forget('family_tree:family_members:v1');
        Cache::store('file')->put('family_tree:render_version:v1', (string) now()->timestamp, now()->addDay());
    }

    protected function resolveCascadeDeleteMemberIdsFromChild(int $rootChildMemberId): array
    {
        if ($rootChildMemberId <= 0) {
            return [];
        }

        $relations = DB::table('relationship')
            ->whereIn('relationtype', ['child', 'partner'])
            ->select('memberid', 'relatedmemberid', 'relationtype')
            ->get();

        $childrenMap = [];
        $partnerMap = [];

        foreach ($relations as $relation) {
            $from = (int) ($relation->memberid ?? 0);
            $to = (int) ($relation->relatedmemberid ?? 0);
            if ($from <= 0 || $to <= 0 || $from === $to) {
                continue;
            }

            $type = strtolower(trim((string) ($relation->relationtype ?? '')));
            if ($type === 'child') {
                $childrenMap[$from] = $childrenMap[$from] ?? [];
                $childrenMap[$from][$to] = true;
                continue;
            }

            if ($type === 'partner') {
                $partnerMap[$from] = $partnerMap[$from] ?? [];
                $partnerMap[$to] = $partnerMap[$to] ?? [];
                $partnerMap[$from][$to] = true;
                $partnerMap[$to][$from] = true;
            }
        }

        $queue = new \SplQueue();
        $queue->enqueue([
            'memberid' => $rootChildMemberId,
            'expand_partners' => true,
        ]);

        $processedWithoutPartners = [];
        $processedWithPartners = [];
        $deleteSet = [];

        while (!$queue->isEmpty()) {
            $current = (array) $queue->dequeue();
            $memberId = (int) ($current['memberid'] ?? 0);
            $expandPartners = (bool) ($current['expand_partners'] ?? false);
            if ($memberId <= 0) {
                continue;
            }

            if ($expandPartners) {
                if (!empty($processedWithPartners[$memberId])) {
                    continue;
                }

                $processedWithPartners[$memberId] = true;
                $processedWithoutPartners[$memberId] = true;
            } else {
                if (!empty($processedWithoutPartners[$memberId])) {
                    continue;
                }

                $processedWithoutPartners[$memberId] = true;
            }

            $deleteSet[$memberId] = true;

            if ($expandPartners) {
                foreach (array_keys($partnerMap[$memberId] ?? []) as $partnerId) {
                    $partnerId = (int) $partnerId;
                    if ($partnerId <= 0) {
                        continue;
                    }

                    $deleteSet[$partnerId] = true;

                    foreach (array_keys($childrenMap[$partnerId] ?? []) as $partnerChildId) {
                        $partnerChildId = (int) $partnerChildId;
                        if ($partnerChildId <= 0 || $partnerChildId === $memberId) {
                            continue;
                        }

                        $queue->enqueue([
                            'memberid' => $partnerChildId,
                            'expand_partners' => false,
                        ]);
                    }
                }
            }

            foreach (array_keys($childrenMap[$memberId] ?? []) as $childId) {
                $childId = (int) $childId;
                if ($childId <= 0) {
                    continue;
                }

                $queue->enqueue([
                    'memberid' => $childId,
                    'expand_partners' => true,
                ]);
            }
        }

        return array_keys($deleteSet);
    }
}
