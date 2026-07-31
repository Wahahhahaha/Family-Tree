<?php

namespace App\Services;

use App\Models\Relationship;
use Illuminate\Support\Facades\DB;

class FamilyTreeService
{
    /**
     * Get the complete family tree starting from the head.
     */
    public function getTreeData()
    {
        $members = DB::table('family_member')
            ->leftJoin('user', 'family_member.userid', '=', 'user.userid')
            ->whereNull('family_member.deleted_at')
            ->select('family_member.*', 'user.username')
            ->orderBy('memberid', 'asc')
            ->get();

        $membersById = $members->keyBy('memberid');

        // Fetch all social media for these members
        $socials = DB::table('ownsocial')
            ->join('socialmedia', 'ownsocial.socialid', '=', 'socialmedia.socialid')
            ->select('ownsocial.*', 'socialmedia.socialname', 'socialmedia.socialicon', 'socialmedia.prefix')
            ->get()
            ->groupBy('memberid');

        // Build a flat partner map for each member
        $allRelationships = Relationship::where('relationtype', 'partner')
            ->whereNull('deleted_at')
            ->orderBy('relationid', 'asc')
            ->get();
        $partnerMap = [];
        foreach ($allRelationships as $r) {
            if (isset($membersById[$r->memberid]) && isset($membersById[$r->relatedmemberid])) {
                $m1 = $membersById[$r->memberid];
                $m2 = $membersById[$r->relatedmemberid];

                if (! isset($partnerMap[$r->memberid]) || ! collect($partnerMap[$r->memberid])->contains('memberid', $m2->memberid)) {
                    $partnerMap[$r->memberid][] = [
                        'memberid' => $m2->memberid,
                        'name' => $m2->name,
                        'userid' => $m2->userid,
                        'gender' => $m2->gender,
                        'picture' => $m2->picture,
                        'life_status' => $m2->life_status,
                        'birthdate' => $m2->birthdate,
                        'deaddate' => $m2->deaddate,
                    ];
                }

                if (! isset($partnerMap[$r->relatedmemberid]) || ! collect($partnerMap[$r->relatedmemberid])->contains('memberid', $m1->memberid)) {
                    $partnerMap[$r->relatedmemberid][] = [
                        'memberid' => $m1->memberid,
                        'name' => $m1->name,
                        'userid' => $m1->userid,
                        'gender' => $m1->gender,
                        'picture' => $m1->picture,
                        'life_status' => $m1->life_status,
                        'birthdate' => $m1->birthdate,
                        'deaddate' => $m1->deaddate,
                    ];
                }
            }
        }

        $relationships = Relationship::whereNull('deleted_at')
            ->orderBy('relationid', 'asc')
            ->get();
        $maps = $this->buildRelationshipMaps($relationships, $membersById);

        // Identify authorized relatives for DOB visibility
        $user = auth()->user();
        $authorizedMemberIds = [];
        if ($user) {
            $currentUserMember = $members->where('userid', $user->userid)->first();
            if ($currentUserMember) {
                $myId = $currentUserMember->memberid;
                $authorizedMemberIds[] = $myId; // Can see own DOB

                // 1. My Direct Parents
                $myParents = $maps['parent'][$myId] ?? [];
                foreach ($myParents as $pId) {
                    $authorizedMemberIds[] = $pId;
                }

                // 2. My Direct Siblings (Those who share a parent with ME)
                foreach ($myParents as $pId) {
                    $siblings = $maps['children'][$pId] ?? [];
                    foreach ($siblings as $sId) {
                        $authorizedMemberIds[] = $sId;
                    }
                }

                // 3. My Direct Partners (Husband/Wife)
                $myPartners = $maps['partner'][$myId] ?? [];
                foreach ($myPartners as $pId) {
                    $authorizedMemberIds[] = $pId;
                }

                // 4. My Direct Children
                $myChildren = $maps['children'][$myId] ?? [];
                foreach ($myChildren as $cId) {
                    $authorizedMemberIds[] = $cId;
                }

                $authorizedMemberIds = array_unique($authorizedMemberIds);
            }
        }

        $isAdmin = $user && DB::table('user')->where('userid', $user->userid)->value('levelid') == 1;

        $members->each(function ($m) use ($socials, $partnerMap, $relationships, $membersById, $authorizedMemberIds, $isAdmin) {
            $m->social_media = $socials->get($m->memberid) ?? [];
            $m->partners_list = $partnerMap[$m->memberid] ?? [];
            
            // Privacy Logic: Only show DOB if Admin or immediate relative
            $m->can_see_dob = $isAdmin || in_array($m->memberid, $authorizedMemberIds);
            if (!$m->can_see_dob) {
                $m->birthdate = null;
            }

            $m->parent_relationships = $relationships->where('relatedmemberid', $m->memberid)
                ->where('relationtype', 'child')
                ->map(function ($r) use ($membersById, $partnerMap) {
                    $parent = $membersById[$r->memberid] ?? null;

                    return [
                        'relationid' => $r->relationid,
                        'parent_id' => $r->memberid,
                        'parent_name' => $parent ? $parent->name : 'Unknown',
                        'parent_partners' => $parent ? ($partnerMap[$r->memberid] ?? []) : [],
                        'mode' => $r->child_parenting_mode ?? 'biological',
                    ];
                })->values();
        });

        $memberIds = $members->pluck('memberid')->all();
        $rootIds = $this->resolveRoots($memberIds, $maps['parent'], $partnerMap);

        $trees = [];
        $processedGlobal = [];
        foreach ($rootIds as $rootId) {
            if (isset($processedGlobal[$rootId])) {
                continue;
            }
            $tree = $this->buildTree($membersById, $maps['children'], $partnerMap, $relationships, $rootId, $processedGlobal);
            if ($tree) {
                $trees[] = $tree;
            }
        }

        return $trees;
    }

    private function buildRelationshipMaps($relationships, $membersById)
    {
        $children = [];
        $parent = [];
        $partner = [];
        foreach ($relationships as $r) {
            $from = (int) $r->memberid;
            $to = (int) $r->relatedmemberid;
            if (! isset($membersById[$from]) || ! isset($membersById[$to])) {
                continue;
            }

            if ($r->relationtype === 'child') {
                $children[$from][] = $to;
                $parent[$to][] = $from;
            } elseif ($r->relationtype === 'partner') {
                $partner[$from][] = $to;
                $partner[$to][] = $from;
            }
        }

        return ['children' => $children, 'parent' => $parent, 'partner' => $partner];
    }

    private function resolveRoots($memberIds, $parentMap, $partnerMap)
    {
        $potentialRoots = [];
        foreach ($memberIds as $id) {
            if (! isset($parentMap[$id]) || empty($parentMap[$id])) {
                $potentialRoots[] = $id;
            }
        }

        if (empty($potentialRoots)) {
            return [];
        }

        $trueRoots = [];
        foreach ($potentialRoots as $id) {
            $isJoiningMember = false;
            $partners = $partnerMap[$id] ?? [];

            foreach ($partners as $p) {
                $pId = $p['memberid'];
                if (isset($parentMap[$pId]) && ! empty($parentMap[$pId])) {
                    $isJoiningMember = true;
                    break;
                }
            }

            if (! $isJoiningMember) {
                $trueRoots[] = $id;
            }
        }

        return ! empty($trueRoots) ? $trueRoots : [$potentialRoots[0]];
    }

    private function buildTree($membersById, $childrenMap, $partnerMap, $relationships, $headId, &$processedGlobal = [])
    {
        $processedInTree = [];

        $buildNode = function ($id, $gen = 1) use (&$buildNode, &$processedInTree, &$processedGlobal, $membersById, $childrenMap, $partnerMap, $relationships) {
            if (isset($processedInTree[$id])) {
                return null;
            }

            $m = $membersById[$id] ?? null;
            if (! $m) {
                return null;
            }

            $processedInTree[$id] = true;
            $processedGlobal[$id] = true;

            $node = [
                'member' => $m,
                'generation' => $gen,
                'partners' => [],
                'children' => [],
            ];
            $node['member']->is_partner = false;

            $allChildrenIds = $childrenMap[$id] ?? [];

            foreach (($partnerMap[$id] ?? []) as $pData) {
                $pId = $pData['memberid'];
                if (! isset($processedInTree[$pId]) && isset($membersById[$pId])) {
                    $partnerMember = $membersById[$pId];
                    $partnerMember->is_partner = true;
                    $node['partners'][] = $partnerMember;

                    $processedInTree[$pId] = true;
                    $processedGlobal[$pId] = true;

                    if (isset($childrenMap[$pId])) {
                        $allChildrenIds = array_merge($allChildrenIds, $childrenMap[$pId]);
                    }
                }
            }

            $uniqueChildrenIds = array_unique($allChildrenIds);
            sort($uniqueChildrenIds);

            foreach ($uniqueChildrenIds as $cId) {
                $childNode = $buildNode($cId, $gen + 1);
                if ($childNode) {
                    $childNode['parent_ids'] = $relationships->where('relatedmemberid', $cId)
                        ->where('relationtype', 'child')
                        ->pluck('memberid')
                        ->toArray();

                    $node['children'][] = $childNode;
                }
            }

            return $node;
        };

        return $buildNode($headId);
    }
}
