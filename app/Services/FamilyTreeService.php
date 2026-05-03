<?php

namespace App\Services;

use App\Repositories\FamilyMemberRepository;
use App\Models\FamilyMember;
use App\Models\Relationship;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FamilyTreeService
{
    protected $memberRepo;

    public function __construct(FamilyMemberRepository $memberRepo)
    {
        $this->memberRepo = $memberRepo;
    }

    /**
     * Data lengkap untuk halaman Home
     */
    public function getHomeData($currentUserId)
    {
        // 1. Get Members
        $familyMembers = $this->memberRepo->usersQuery()
            ->whereNull('u.deleted_at')
            ->orderBy('fm.memberid')
            ->get()
            ->map(fn($m) => $this->formatMember($m));

        $membersById = $familyMembers->keyBy('memberid');
        $memberIds = $familyMembers->pluck('memberid')->all();

        // 2. Map Relationships
        $relationships = DB::table('relationship')->get();
        $maps = $this->buildRelationshipMaps($relationships, $membersById);

        // 3. Resolve Current Member
        $currentMember = $familyMembers->firstWhere('userid', $currentUserId) ?: $familyMembers->first();
        $currentMemberId = (int) ($currentMember->memberid ?? 0);

        // 4. Resolve Permissions
        $permissions = $this->resolvePermissions($currentMemberId, $familyMembers, $maps['parent'], $maps['partner']);

        // 5. Build Tree
        $familyHeadId = $this->resolveFamilyHeadId($memberIds, $maps['parent']);
        $treeRoots = $this->buildTree($memberIds, $membersById, $maps['children'], $maps['partner'], $familyHeadId);

        return [
            'familyMembers' => $familyMembers,
            'treeRoots' => $treeRoots,
            'relationLabels' => $this->generateRelationLabels($currentMemberId, $membersById, $maps),
            'childParentingModeMap' => $maps['childParentingMode'],
            'canDeletePartnerMap' => $permissions['canDeletePartner'],
            'canDeleteChildMap' => $permissions['canDeleteChild'],
            'canUpdateLifeStatusMap' => $permissions['canUpdateLifeStatus'],
            'canEditProfileMap' => $permissions['canEditProfile'],
        ];
    }

    private function formatMember($member)
    {
        // Ensure fullname exists even if coming from old cache or incomplete query
        if (!isset($member->fullname)) {
            $member->fullname = $member->name ?? $member->username ?? 'Member';
        }
        
        $member->age = !empty($member->birthdate) ? Carbon::parse($member->birthdate)->age : null;
        if (empty($member->picture)) {
            $member->picture = 'https://api.dicebear.com/9.x/personas/svg?seed=' . urlencode((string) ($member->fullname ?? 'Member'));
        }
        $member->bloodtype = strtoupper(trim((string) ($member->bloodtype ?? ''))) ?: '-';
        return $member;
    }

    private function buildRelationshipMaps($relationships, $membersById)
    {
        $children = []; $parent = []; $partner = []; $modes = [];
        foreach ($relationships as $r) {
            $from = (int) $r->memberid; $to = (int) $r->relatedmemberid;
            if (!isset($membersById[$from]) || !isset($membersById[$to])) continue;

            if ($r->relationtype === 'child') {
                $children[$from][] = $to;
                $parent[$to][] = $from;
                $modes[$from][$to] = $r->child_parenting_mode;
            } elseif ($r->relationtype === 'partner') {
                $partner[$from][] = $to;
                $partner[$to][] = $from;
            }
        }
        return ['children' => $children, 'parent' => $parent, 'partner' => $partner, 'childParentingMode' => $modes];
    }

    private function resolveFamilyHeadId($memberIds, $parentMap)
    {
        // Strategy 1: Find anyone who has NO parent recorded
        foreach ($memberIds as $id) {
            if (empty($parentMap[$id])) return $id;
        }
        
        // Strategy 2: If everyone has a parent (loop or data issue), return the first one found
        return !empty($memberIds) ? $memberIds[0] : 0;
    }

    private function buildTree($memberIds, $membersById, $childrenMap, $partnerMap, $headId)
    {
        $processed = [];
        $buildNode = function ($id, $gen = 1) use (&$buildNode, &$processed, $membersById, $childrenMap, $partnerMap) {
            if (isset($processed[$id])) return null;
            $processed[$id] = true;
            $m = $membersById[$id] ?? null;
            if (!$m) return null;

            $node = ['member' => $m, 'generation' => $gen, 'partners' => [], 'children' => []];
            foreach (($partnerMap[$id] ?? []) as $pId) {
                if (!isset($processed[$pId]) && isset($membersById[$pId])) {
                    $node['partners'][] = $membersById[$pId];
                    $processed[$pId] = true;
                }
            }
            foreach (($childrenMap[$id] ?? []) as $cId) {
                $child = $buildNode($cId, $gen + 1);
                if ($child) $node['children'][] = $child;
            }
            return $node;
        };

        return $headId > 0 ? [$buildNode($headId)] : [];
    }

    private function resolvePermissions($currentId, $members, $parentMap, $partnerMap)
    {
        $p = ['canDeletePartner' => [], 'canDeleteChild' => [], 'canUpdateLifeStatus' => [], 'canEditProfile' => []];
        if ($currentId <= 0) return $p;

        $p['canEditProfile'][$currentId] = true;
        $p['canUpdateLifeStatus'][$currentId] = true;

        foreach ($members as $m) {
            $id = (int) $m->memberid;
            if ($id === $currentId) continue;
            
            // Basic logic: parents can edit minor children
            if (($m->age ?? 100) < 17) {
                $parents = $parentMap[$id] ?? [];
                if (in_array($currentId, $parents)) {
                    $p['canEditProfile'][$id] = true;
                    $p['canDeleteChild'][$id] = true;
                }
            }
            // Partners can update status
            if (in_array($id, $partnerMap[$currentId] ?? [])) {
                $p['canUpdateLifeStatus'][$id] = true;
            }
        }
        return $p;
    }

    private function generateRelationLabels($meId, $membersById, $maps)
    {
        $labels = [];
        foreach ($membersById as $id => $m) {
            if ($id === $meId) $labels[$id] = 'Me';
            elseif (in_array($id, $maps['partner'][$meId] ?? [])) $labels[$id] = 'Partner';
            elseif (in_array($id, $maps['parent'][$meId] ?? [])) $labels[$id] = 'Parent';
            elseif (in_array($id, $maps['children'][$meId] ?? [])) $labels[$id] = 'Child';
            else $labels[$id] = 'Relative';
        }
        return $labels;
    }
}
