<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class Ctrl extends Controller
{


    public function home(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();
        $familyMembers = DB::table('user as u')
            ->join('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->where('u.levelid', 2)
            ->whereNull('u.deleted_at')
            ->select(
                'u.userid',
                'u.username',
                'fm.memberid',
                'fm.name',
                'fm.gender',
                'fm.birthdate',
                'fm.birthplace',
                'fm.life_status',
                'fm.picture',
                'fm.email',
                'fm.phonenumber',
                'fm.job',
                'fm.address',
                'fm.education_status'
            )
            ->orderBy('fm.memberid')
            ->get()
            ->map(function ($member) {
                $age = null;
                if (!empty($member->birthdate)) {
                    try {
                        $age = Carbon::parse($member->birthdate)->age;
                    } catch (\Throwable $e) {
                        $age = null;
                    }
                }

                $member->age = $age;
                if (empty($member->picture)) {
                    $member->picture = 'https://api.dicebear.com/9.x/personas/svg?seed='
                        . urlencode((string) $member->name)
                        . '&backgroundColor=93c5fd';
                }

                return $member;
            });

        $membersById = $familyMembers->keyBy('memberid');
        $relationships = DB::table('relationship')
            ->select('memberid', 'relatedmemberid', 'relationtype', 'child_parenting_mode')
            ->get();

        $childrenMap = [];
        $childParentingModeMap = [];
        $partnerMap = [];
        $parentMap = [];
        $parentCount = [];

        foreach ($relationships as $relation) {
            $from = (int) $relation->memberid;
            $to = (int) $relation->relatedmemberid;
            $type = strtolower((string) $relation->relationtype);
            $parentingMode = (string) ($relation->child_parenting_mode ?? 'with_current_partner');

            if (!isset($membersById[$from]) || !isset($membersById[$to])) {
                continue;
            }

            if ($type === 'child') {
                $childrenMap[$from] = $childrenMap[$from] ?? [];
                if (!in_array($to, $childrenMap[$from], true)) {
                    $childrenMap[$from][] = $to;
                    $childParentingModeMap[$from][$to] = $parentingMode;
                    $parentMap[$to] = $parentMap[$to] ?? [];
                    if (!in_array($from, $parentMap[$to], true)) {
                        $parentMap[$to][] = $from;
                    }
                    $parentCount[$to] = ($parentCount[$to] ?? 0) + 1;
                }
            }

            if ($type === 'partner') {
                $partnerMap[$from] = $partnerMap[$from] ?? [];
                if (!in_array($to, $partnerMap[$from], true)) {
                    $partnerMap[$from][] = $to;
                }

                $partnerMap[$to] = $partnerMap[$to] ?? [];
                if (!in_array($from, $partnerMap[$to], true)) {
                    $partnerMap[$to][] = $from;
                }
            }
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = $familyMembers->firstWhere('userid', $currentUserId);
        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        $currentMemberHasPartner = !empty($partnerMap[$currentMemberId] ?? []);
        $canDeletePartnerMap = [];
        $canDeleteChildMap = [];
        $canUpdateLifeStatusMap = [];

        if ($currentMemberId !== 0) {
            $canUpdateLifeStatusMap[$currentMemberId] = true;

            foreach ($relationships as $relation) {
                $from = (int) $relation->memberid;
                $to = (int) $relation->relatedmemberid;
                $type = strtolower((string) $relation->relationtype);

                if (!isset($membersById[$from]) || !isset($membersById[$to])) {
                    continue;
                }

                if ($type === 'partner' && ($from === $currentMemberId || $to === $currentMemberId)) {
                    $partnerId = $from === $currentMemberId ? $to : $from;
                    $canDeletePartnerMap[$partnerId] = true;
                    $canUpdateLifeStatusMap[$partnerId] = true;
                }

                if ($type === 'child' && $from === $currentMemberId) {
                    $canDeleteChildMap[$to] = true;
                    $canUpdateLifeStatusMap[$to] = true;
                }
            }

            $currentParents = $parentMap[$currentMemberId] ?? [];
            foreach ($currentParents as $parentId) {
                $parentId = (int) $parentId;
                if ($parentId !== 0 && isset($membersById[$parentId])) {
                    $canUpdateLifeStatusMap[$parentId] = true;
                }
            }

            foreach ($currentParents as $parentId) {
                $parentId = (int) $parentId;
                foreach ($childrenMap[$parentId] ?? [] as $siblingId) {
                    $siblingId = (int) $siblingId;
                    if ($siblingId !== 0 && $siblingId !== $currentMemberId && isset($membersById[$siblingId])) {
                        $canUpdateLifeStatusMap[$siblingId] = true;
                    }
                }
            }
        }

        $relationLabels = [];
        $genderLabel = function (int $memberId, string $maleLabel, string $femaleLabel, string $fallback) use ($membersById): string {
            $gender = strtolower((string) ($membersById[$memberId]->gender ?? ''));
            if ($gender === 'male') {
                return $maleLabel;
            }
            if ($gender === 'female') {
                return $femaleLabel;
            }
            return $fallback;
        };

        $asSet = function (array $ids): array {
            $set = [];
            foreach ($ids as $id) {
                $set[(int) $id] = true;
            }
            return $set;
        };

        $parentsOf = function (int $memberId) use ($parentMap): array {
            return $parentMap[$memberId] ?? [];
        };

        $childrenOf = function (int $memberId) use ($childrenMap): array {
            return $childrenMap[$memberId] ?? [];
        };

        $getAgeDirection = function (int $targetId) use ($membersById, $currentMemberId): string {
            $currentBirthdateRaw = $membersById[$currentMemberId]->birthdate ?? null;
            $targetBirthdateRaw = $membersById[$targetId]->birthdate ?? null;

            if (empty($currentBirthdateRaw) || empty($targetBirthdateRaw)) {
                return '';
            }

            try {
                $currentBirthdate = Carbon::parse((string) $currentBirthdateRaw)->startOfDay();
                $targetBirthdate = Carbon::parse((string) $targetBirthdateRaw)->startOfDay();
            } catch (\Throwable $e) {
                return '';
            }

            if ($targetBirthdate->lt($currentBirthdate)) {
                return 'older';
            }

            if ($targetBirthdate->gt($currentBirthdate)) {
                return 'younger';
            }

            return '';
        };

        $buildSiblingLabel = function (int $targetId, string $kind) use ($membersById, $getAgeDirection): string {
            $gender = strtolower((string) ($membersById[$targetId]->gender ?? ''));
            $ageDirection = $getAgeDirection($targetId);
            $prefix = $ageDirection === '' ? '' : ucfirst($ageDirection) . ' ';

            if ($kind === 'half') {
                if ($gender === 'male') {
                    return $prefix . 'Half Brother';
                }
                if ($gender === 'female') {
                    return $prefix . 'Half Sister';
                }
                return $prefix . 'Half Sibling';
            }

            if ($kind === 'step') {
                if ($gender === 'male') {
                    return $prefix . 'Step Brother';
                }
                if ($gender === 'female') {
                    return $prefix . 'Step Sister';
                }
                return $prefix . 'Step Sibling';
            }

            if ($gender === 'male') {
                return $prefix . 'Brother';
            }
            if ($gender === 'female') {
                return $prefix . 'Sister';
            }
            return $prefix . 'Sibling';
        };

        $countSharedParents = function (array $firstParents, array $secondParents): int {
            $secondSet = [];
            foreach ($secondParents as $parentId) {
                $secondSet[(int) $parentId] = true;
            }

            $count = 0;
            foreach ($firstParents as $parentId) {
                if (isset($secondSet[(int) $parentId])) {
                    $count++;
                }
            }

            return $count;
        };

        $resolveSiblingKind = function (int $siblingId, array $myParents) use ($parentsOf, $countSharedParents): ?string {
            $siblingParents = $parentsOf($siblingId);
            $sharedParentCount = $countSharedParents($myParents, $siblingParents);
            if ($sharedParentCount >= 2) {
                return 'full';
            }
            if ($sharedParentCount === 1) {
                return 'half';
            }

            return null;
        };

        foreach ($familyMembers as $member) {
            $targetId = (int) $member->memberid;

            if ($targetId === 0) {
                continue;
            }

            if ($currentMemberId === 0) {
                $relationLabels[$targetId] = 'Family Member';
                continue;
            }

            if ($targetId === $currentMemberId) {
                $relationLabels[$targetId] = 'Me';
                continue;
            }

            $myParents = $parentsOf($currentMemberId);
            $myChildren = $childrenOf($currentMemberId);
            $targetParents = $parentsOf($targetId);
            $targetChildren = $childrenOf($targetId);
            $myParentSet = $asSet($myParents);
            $targetParentSet = $asSet($targetParents);
            $mySiblings = [];
            foreach ($myParents as $parentId) {
                foreach ($childrenOf((int) $parentId) as $siblingId) {
                    $siblingId = (int) $siblingId;
                    if ($siblingId !== $currentMemberId) {
                        $mySiblings[$siblingId] = true;
                    }
                }
            }

            $myPartnerSiblings = [];
            foreach ($partnerMap[$currentMemberId] ?? [] as $myPartnerId) {
                foreach ($parentsOf((int) $myPartnerId) as $partnerParentId) {
                    foreach ($childrenOf((int) $partnerParentId) as $partnerSiblingId) {
                        $partnerSiblingId = (int) $partnerSiblingId;
                        if ($partnerSiblingId !== (int) $myPartnerId && $partnerSiblingId !== $currentMemberId) {
                            $myPartnerSiblings[$partnerSiblingId] = true;
                        }
                    }
                }
            }

            if (in_array($targetId, $partnerMap[$currentMemberId] ?? [], true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Husband', 'Wife', 'Partner');
                continue;
            }

            if (in_array($targetId, $myParents, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Father', 'Mother', 'Parent');
                continue;
            }

            if (in_array($targetId, $myChildren, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Son', 'Daughter', 'Child');
                continue;
            }

            $stepParentSet = [];
            foreach ($myParents as $parentId) {
                foreach ($partnerMap[(int) $parentId] ?? [] as $partnerId) {
                    $partnerId = (int) $partnerId;
                    if ($partnerId !== $currentMemberId && !isset($myParentSet[$partnerId])) {
                        $stepParentSet[$partnerId] = true;
                    }
                }
            }

            if (isset($stepParentSet[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Stepfather', 'Stepmother', 'Stepparent');
                continue;
            }

            $sharedParentCount = $countSharedParents($myParents, $targetParents);
            if ($sharedParentCount >= 2) {
                $relationLabels[$targetId] = $buildSiblingLabel($targetId, 'full');
                continue;
            }
            if ($sharedParentCount === 1) {
                $relationLabels[$targetId] = $buildSiblingLabel($targetId, 'half');
                continue;
            }

            $isStepSibling = false;
            foreach (array_keys($stepParentSet) as $stepParentId) {
                if (in_array($targetId, $childrenOf((int) $stepParentId), true)) {
                    $isStepSibling = true;
                    break;
                }
            }
            if ($isStepSibling) {
                $relationLabels[$targetId] = $buildSiblingLabel($targetId, 'step');
                continue;
            }

            $isSiblingInLaw = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                if (in_array($targetId, $partnerMap[(int) $siblingId] ?? [], true)) {
                    $isSiblingInLaw = true;
                    break;
                }
            }
            if (!$isSiblingInLaw && isset($myPartnerSiblings[$targetId])) {
                $isSiblingInLaw = true;
            }
            if ($isSiblingInLaw) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Brother in law', 'Sister in law', 'Sibling in law');
                continue;
            }

            $isChildSpouse = false;
            foreach ($myChildren as $childId) {
                if (in_array($targetId, $partnerMap[(int) $childId] ?? [], true)) {
                    $isChildSpouse = true;
                    break;
                }
            }
            if ($isChildSpouse) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Son in law', 'Daughter in law', 'Child in law');
                continue;
            }

            $inLawParents = [];
            foreach ($partnerMap[$currentMemberId] ?? [] as $myPartnerId) {
                foreach ($parentsOf((int) $myPartnerId) as $partnerParentId) {
                    $inLawParents[(int) $partnerParentId] = true;
                }
            }
            if (isset($inLawParents[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Father in law', 'Mother in law', 'Parent in law');
                continue;
            }

            $inLawParentSiblings = [];
            foreach (array_keys($inLawParents) as $inLawParentId) {
                foreach ($parentsOf((int) $inLawParentId) as $grandInLawParentId) {
                    foreach ($childrenOf((int) $grandInLawParentId) as $inLawParentSiblingId) {
                        $inLawParentSiblingId = (int) $inLawParentSiblingId;
                        if ($inLawParentSiblingId !== (int) $inLawParentId) {
                            $inLawParentSiblings[$inLawParentSiblingId] = true;
                        }
                    }
                }
            }
            if (isset($inLawParentSiblings[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Uncle in law', 'Aunt in law', 'Relative in law');
                continue;
            }

            $myGrandParents = [];
            foreach ($myParents as $parentId) {
                foreach ($parentsOf((int) $parentId) as $grandParentId) {
                    $myGrandParents[] = (int) $grandParentId;
                }
            }
            if (in_array($targetId, $myGrandParents, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandfather', 'Grandmother', 'Grandparent');
                continue;
            }

            $myGreatGrandParents = [];
            foreach ($myGrandParents as $grandParentId) {
                foreach ($parentsOf((int) $grandParentId) as $greatGrandParentId) {
                    $myGreatGrandParents[(int) $greatGrandParentId] = true;
                }
            }
            if (isset($myGreatGrandParents[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Great Grandfather', 'Great Grandmother', 'Great Grandparent');
                continue;
            }

            $myGrandParentSiblings = [];
            foreach ($myGrandParents as $grandParentId) {
                $greatGrandParents = $parentsOf((int) $grandParentId);
                foreach ($greatGrandParents as $greatGrandParentId) {
                    foreach ($childrenOf((int) $greatGrandParentId) as $grandParentSiblingId) {
                        $grandParentSiblingId = (int) $grandParentSiblingId;
                        if ($grandParentSiblingId !== (int) $grandParentId) {
                            $myGrandParentSiblings[$grandParentSiblingId] = true;
                        }
                    }
                }
            }
            if (isset($myGrandParentSiblings[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grand Uncle', 'Grand Aunt', 'Grand Relative');
                continue;
            }

            $myParentsCousins = [];
            foreach (array_keys($myGrandParentSiblings) as $grandParentSiblingId) {
                foreach ($childrenOf((int) $grandParentSiblingId) as $parentsCousinId) {
                    $myParentsCousins[(int) $parentsCousinId] = true;
                }
            }
            if (isset($myParentsCousins[$targetId])) {
                $relationLabels[$targetId] = 'First cousin once removed';
                continue;
            }

            $myGrandChildren = [];
            foreach ($myChildren as $childId) {
                foreach ($childrenOf((int) $childId) as $grandChildId) {
                    $myGrandChildren[] = (int) $grandChildId;
                }
            }
            if (in_array($targetId, $myGrandChildren, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandson', 'Granddaughter', 'Grandchild');
                continue;
            }

            $isGrandChildSpouse = false;
            foreach ($myGrandChildren as $grandChildId) {
                if (in_array($targetId, $partnerMap[(int) $grandChildId] ?? [], true)) {
                    $isGrandChildSpouse = true;
                    break;
                }
            }
            if ($isGrandChildSpouse) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandson-in-law', 'Granddaughter-in-law', 'Grandchild-in-law');
                continue;
            }

            $myGreatGrandChildren = [];
            foreach ($myGrandChildren as $grandChildId) {
                foreach ($childrenOf((int) $grandChildId) as $greatGrandChildId) {
                    $myGreatGrandChildren[] = (int) $greatGrandChildId;
                }
            }
            if (in_array($targetId, $myGreatGrandChildren, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Great Grandson', 'Great Granddaughter', 'Great Grandchild');
                continue;
            }

            $myParentSiblings = [];
            foreach ($myParents as $parentId) {
                $grandParents = $parentsOf((int) $parentId);
                $parentSiblingCandidates = [];
                foreach ($grandParents as $grandParentId) {
                    foreach ($childrenOf((int) $grandParentId) as $childOfGrand) {
                        $parentSiblingCandidates[] = (int) $childOfGrand;
                    }
                }

                foreach ($parentSiblingCandidates as $candidateId) {
                    if ($candidateId !== (int) $parentId) {
                        $myParentSiblings[$candidateId] = true;
                    }
                }
            }

            if (isset($myParentSiblings[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Uncle', 'Aunt', 'Relative');
                continue;
            }

            $isUncleAuntyByMarriage = false;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                if (in_array($targetId, $partnerMap[(int) $parentSiblingId] ?? [], true)) {
                    $isUncleAuntyByMarriage = true;
                    break;
                }
            }

            if ($isUncleAuntyByMarriage) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Uncle', 'Aunt', 'Relative');
                continue;
            }

            $myCousins = [];
            $myFirstCousinParentSiblingId = null;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                if (in_array($targetId, $childrenOf((int) $parentSiblingId), true)) {
                    $myCousins[$targetId] = true;
                    $myFirstCousinParentSiblingId = (int) $parentSiblingId;
                    break;
                }
            }

            if (isset($myCousins[$targetId])) {
                // Check if cousin is from a half-sibling of parent for more accurate labeling
                $isFirstCousinFromHalfSibling = false;
                if ($myFirstCousinParentSiblingId !== null) {
                    $cousinParentSiblingParents = $parentsOf($myFirstCousinParentSiblingId);
                    $siblingKind = $resolveSiblingKind($myFirstCousinParentSiblingId, $myParents);
                    $isFirstCousinFromHalfSibling = ($siblingKind === 'half');
                }
                
                $relationLabels[$targetId] = 'First Cousin';
                continue;
            }

            $isCousinInLaw = false;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                foreach ($childrenOf((int) $parentSiblingId) as $cousinId) {
                    if (in_array($targetId, $partnerMap[(int) $cousinId] ?? [], true)) {
                        $isCousinInLaw = true;
                        break 2;
                    }
                }
            }
            if ($isCousinInLaw) {
                $relationLabels[$targetId] = 'First Cousin In Law';
                continue;
            }

            $isNephewNiece = false;
            $isHalfNephewNiece = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                if (in_array($targetId, $childrenOf((int) $siblingId), true)) {
                    $isNephewNiece = true;
                    $isHalfNephewNiece = $resolveSiblingKind((int) $siblingId, $myParents) === 'half';
                    break;
                }
            }
            if (!$isNephewNiece) {
                foreach (array_keys($myPartnerSiblings) as $partnerSiblingId) {
                    if (in_array($targetId, $childrenOf((int) $partnerSiblingId), true)) {
                        $isNephewNiece = true;
                        break;
                    }
                }
            }
            if ($isNephewNiece) {
                if ($isHalfNephewNiece) {
                    $relationLabels[$targetId] = $genderLabel($targetId, 'Half Nephew', 'Half Niece', 'Half Relative');
                } else {
                    $relationLabels[$targetId] = $genderLabel($targetId, 'Nephew', 'Niece', 'Relative');
                }
                continue;
            }

            $isNephewNieceInLaw = false;
            $isHalfNephewNieceInLaw = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                foreach ($childrenOf((int) $siblingId) as $siblingChildId) {
                    if (in_array($targetId, $partnerMap[(int) $siblingChildId] ?? [], true)) {
                        $isNephewNieceInLaw = true;
                        $isHalfNephewNieceInLaw = $resolveSiblingKind((int) $siblingId, $myParents) === 'half';
                        break 2;
                    }
                }
            }
            if ($isNephewNieceInLaw) {
                if ($isHalfNephewNieceInLaw) {
                    $relationLabels[$targetId] = $genderLabel($targetId, 'Half Nephew in law', 'Half Niece in law', 'Half Relative in law');
                } else {
                    $relationLabels[$targetId] = $genderLabel($targetId, 'Nephew in law', 'Niece in law', 'Relative in law');
                }
                continue;
            }

            $isGrandNephewNiece = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                if ($resolveSiblingKind((int) $siblingId, $myParents) !== 'half') {
                    continue;
                }

                foreach ($childrenOf((int) $siblingId) as $siblingChildId) {
                    if (in_array($targetId, $childrenOf((int) $siblingChildId), true)) {
                        $isGrandNephewNiece = true;
                        break 2;
                    }
                }
            }
            if ($isGrandNephewNiece) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandnephew', 'Grandniece', 'Grand Relative');
                continue;
            }

            $isGrandNephewNieceInLaw = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                if ($resolveSiblingKind((int) $siblingId, $myParents) !== 'half') {
                    continue;
                }

                foreach ($childrenOf((int) $siblingId) as $siblingChildId) {
                    foreach ($childrenOf((int) $siblingChildId) as $grandSiblingChildId) {
                        if (in_array($targetId, $partnerMap[(int) $grandSiblingChildId] ?? [], true)) {
                            $isGrandNephewNieceInLaw = true;
                            break 3;
                        }
                    }
                }
            }
            if ($isGrandNephewNieceInLaw) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandnephew-in-law', 'Grandniece-in-law', 'Grand Relative in law');
                continue;
            }

            $relationLabels[$targetId] = 'Relative';
        }

        $generationMap = [];
        if ($currentMemberId !== 0 && isset($membersById[$currentMemberId])) {
            $queue = [[$currentMemberId, 0]];
            $generationMap[$currentMemberId] = 0;

            while (!empty($queue)) {
                [$memberId, $generation] = array_shift($queue);

                foreach ($partnerMap[$memberId] ?? [] as $partnerId) {
                    $partnerId = (int) $partnerId;
                    if (!isset($membersById[$partnerId]) || array_key_exists($partnerId, $generationMap)) {
                        continue;
                    }

                    $generationMap[$partnerId] = $generation;
                    $queue[] = [$partnerId, $generation];
                }

                foreach ($childrenMap[$memberId] ?? [] as $childId) {
                    $childId = (int) $childId;
                    if (!isset($membersById[$childId]) || array_key_exists($childId, $generationMap)) {
                        continue;
                    }

                    $generationMap[$childId] = $generation + 1;
                    $queue[] = [$childId, $generation + 1];
                }

                foreach ($parentMap[$memberId] ?? [] as $parentId) {
                    $parentId = (int) $parentId;
                    if (!isset($membersById[$parentId]) || array_key_exists($parentId, $generationMap)) {
                        continue;
                    }

                    $generationMap[$parentId] = $generation - 1;
                    $queue[] = [$parentId, $generation - 1];
                }
            }
        }

        $buildTreeRoots = function (?array $allowedMemberIds = null) use ($familyMembers, $parentCount, $membersById, $childrenMap, $partnerMap) {
            $allowedSet = null;
            if (is_array($allowedMemberIds)) {
                $allowedSet = [];
                foreach ($allowedMemberIds as $allowedMemberId) {
                    $allowedSet[(int) $allowedMemberId] = true;
                }
            }

            $candidateRoots = $familyMembers
                ->pluck('memberid')
                ->filter(function ($memberId) use ($parentCount, $allowedSet) {
                    $memberId = (int) $memberId;
                    if ($allowedSet !== null && !isset($allowedSet[$memberId])) {
                        return false;
                    }

                    return !isset($parentCount[$memberId]);
                })
                ->values()
                ->all();

            if (empty($candidateRoots)) {
                $candidateRoots = $familyMembers
                    ->pluck('memberid')
                    ->filter(function ($memberId) use ($allowedSet) {
                        return $allowedSet === null || isset($allowedSet[(int) $memberId]);
                    })
                    ->values()
                    ->all();
            }

            $usedMemberIds = [];
            $buildNode = function (int $memberId, array $ancestorIds = []) use (&$buildNode, &$usedMemberIds, $membersById, $childrenMap, $partnerMap, $allowedSet) {
                if (isset($ancestorIds[$memberId]) || !isset($membersById[$memberId])) {
                    return null;
                }

                if ($allowedSet !== null && !isset($allowedSet[$memberId])) {
                    return null;
                }

                if (isset($usedMemberIds[$memberId])) {
                    return null;
                }

                $usedMemberIds[$memberId] = true;
                $ancestorIds[$memberId] = true;

                $partnerMembers = collect();
                foreach ($partnerMap[$memberId] ?? [] as $partnerId) {
                    $partnerId = (int) $partnerId;
                    if (!isset($membersById[$partnerId])) {
                        continue;
                    }

                    if ($allowedSet !== null && !isset($allowedSet[$partnerId])) {
                        continue;
                    }

                    if (!isset($usedMemberIds[$partnerId])) {
                        $usedMemberIds[$partnerId] = true;
                        $partnerMembers->push($membersById[$partnerId]);
                    }
                }

                $children = [];
                foreach ($childrenMap[$memberId] ?? [] as $childId) {
                    $childNode = $buildNode((int) $childId, $ancestorIds);
                    if ($childNode !== null) {
                        $children[] = $childNode;
                    }
                }

                return [
                    'member' => $membersById[$memberId],
                    'partners' => $partnerMembers->values()->all(),
                    'children' => $children,
                ];
            };

            $treeRoots = [];
            foreach ($candidateRoots as $rootId) {
                $rootNode = $buildNode((int) $rootId);
                if ($rootNode !== null) {
                    $treeRoots[] = $rootNode;
                }
            }

            foreach ($familyMembers as $member) {
                $memberId = (int) $member->memberid;
                if ($allowedSet !== null && !isset($allowedSet[$memberId])) {
                    continue;
                }

                if (isset($usedMemberIds[$memberId])) {
                    continue;
                }

                $node = $buildNode($memberId);
                if ($node !== null) {
                    $treeRoots[] = $node;
                }
            }

            return $treeRoots;
        };

        $fullTreeRoots = $buildTreeRoots();
        $showUpperTree = $request->boolean('show_upper_tree');
        $showLowerTree = $request->boolean('show_lower_tree');
        $visibleMinGeneration = $showUpperTree ? null : -2;
        $visibleMaxGeneration = $showLowerTree ? null : 2;
        $hasHiddenUpperTreeLevels = false;
        $hasHiddenLowerTreeLevels = false;

        $limitedMemberIds = $familyMembers
            ->pluck('memberid')
            ->filter(function ($memberId) use ($generationMap, $currentMemberId, $visibleMinGeneration, $visibleMaxGeneration, &$hasHiddenUpperTreeLevels, &$hasHiddenLowerTreeLevels) {
                $memberId = (int) $memberId;
                if ($currentMemberId === 0 || !array_key_exists($memberId, $generationMap)) {
                    return true;
                }

                $generation = (int) $generationMap[$memberId];
                if ($visibleMinGeneration !== null && $generation < $visibleMinGeneration) {
                    $hasHiddenUpperTreeLevels = true;
                    return false;
                }

                if ($visibleMaxGeneration !== null && $generation > $visibleMaxGeneration) {
                    $hasHiddenLowerTreeLevels = true;
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
        $treeRoots = $buildTreeRoots($limitedMemberIds);

        if ($showUpperTree) {
            $hasHiddenUpperTreeLevels = false;
        }

        if ($showLowerTree) {
            $hasHiddenLowerTreeLevels = false;
        }

        if ($showUpperTree && $showLowerTree) {
            $treeSummaryText = 'Showing full family tree.';
        } elseif ($showUpperTree) {
            $treeSummaryText = 'Showing full family tree above grandparent level and up to your grandchildren below.';
        } elseif ($showLowerTree) {
            $treeSummaryText = 'Showing from your grandparents down to the full descendant tree below your grandchildren.';
        } else {
            $treeSummaryText = 'Showing members from your grandparents to your grandchildren.';
        }

        if (($request->ajax() || $request->expectsJson()) && $request->boolean('tree_section')) {
            return response()->json([
                'tree_html' => view('all.partials.family-tree-content', [
                    'members' => $familyMembers,
                    'renderTreeRoots' => $treeRoots,
                    'firstMember' => $currentMember ?: $familyMembers->first(),
                    'relationMap' => $relationLabels,
                    'canDeletePartnerMap' => $canDeletePartnerMap,
                    'canDeleteChildMap' => $canDeleteChildMap,
                    'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                    'childParentingModeMap' => $childParentingModeMap,
                ])->render(),
                'show_upper_tree' => $showUpperTree,
                'show_lower_tree' => $showLowerTree,
                'has_hidden_upper_tree_levels' => $hasHiddenUpperTreeLevels,
                'has_hidden_lower_tree_levels' => $hasHiddenLowerTreeLevels,
                'toggle_upper_tree_url' => $showUpperTree
                    ? $request->fullUrlWithQuery(['show_upper_tree' => 0])
                    : $request->fullUrlWithQuery(['show_upper_tree' => 1]),
                'toggle_lower_tree_url' => $showLowerTree
                    ? $request->fullUrlWithQuery(['show_lower_tree' => 0])
                    : $request->fullUrlWithQuery(['show_lower_tree' => 1]),
                'summary_text' => $treeSummaryText,
            ]);
        }

        echo view('all.header', [
            'pageTitle' => $systemSettings['website_name'],
            'pageClass' => 'page-family-tree',
        ]);
        $currentUserId = (int) session('authenticated_user.userid');
        $currentFamilyProfile = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('userid', 'job', 'address', 'education_status')
            ->first();

        echo view('all.home', compact(
            'systemSettings',
            'familyMembers',
            'currentFamilyProfile',
            'treeRoots',
            'showUpperTree',
            'showLowerTree',
            'hasHiddenUpperTreeLevels',
            'hasHiddenLowerTreeLevels',
            'treeSummaryText',
            'relationLabels',
            'currentMemberHasPartner',
            'canDeletePartnerMap',
            'canDeleteChildMap',
            'canUpdateLifeStatusMap',
            'childParentingModeMap'
        ));
        echo view('all.footer');
    }

    public function login(){
        if (session()->has('authenticated_user')) {
            return redirect('/');

        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Login | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.login', compact('systemSettings'));
        echo view('all.footer');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        $username = trim($credentials['username']);

        $user = DB::table('user as u')
            ->where('u.username', $username)
            ->select('u.userid', 'u.username', 'u.password', 'u.levelid')
            ->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username not found.',
            ])->withInput();
        }

        $storedPassword = stripslashes((string) $user->password);
        $validPassword = Hash::check($credentials['password'], $storedPassword)
            || hash_equals((string) $user->password, $credentials['password']);

        if (!$validPassword) {
            return back()->withErrors([
                'password' => 'Invalid password.',
            ])->withInput();
        }

        $level = DB::table('level')
            ->where('levelid', $user->levelid)
            ->first();

        $employer = DB::table('employer as e')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->where('e.userid', $user->userid)
            ->select(
                'e.employerid',
                'e.name',
                'e.email',
                'e.phonenumber',
                'e.roleid',
                'r.rolename'
            )
            ->first();

        $familyMember = DB::table('family_member')
            ->where('userid', $user->userid)
            ->first();
        $displayName = trim((string) ($employer->name ?? $familyMember->name ?? ''));
        if ($displayName === '') {
            $displayName = (string) $user->username;
        }

        $request->session()->regenerate();
        $request->session()->put('authenticated_user', [
            'userid' => $user->userid,
            'username' => $user->username,
            'name' => $displayName,
            'levelid' => $user->levelid,
            'levelname' => $level->levelname ?? null,
            'roleid' => $employer->roleid ?? null,
            'rolename' => $employer->rolename ?? null,
            'employer' => $employer,
            'familyMember' => $familyMember,
        ]);

        $this->logActivity($request, 'login', [
            'target_userid' => (int) $user->userid,
            'target_username' => (string) $user->username,
        ]);

        return redirect('/');
    }

    public function forgotPassword(Request $request)
    {
        if ($request->session()->has('authenticated_user')) {
            return redirect('/');
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Forgot Password | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.forgot-password', compact('systemSettings'));
        echo view('all.footer');
    }

    public function forgotPasswordPhone(Request $request)
    {
        if ($request->session()->has('authenticated_user')) {
            return redirect('/');
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Forgot Password by Phone | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.forgot-password-phone', compact('systemSettings'));
        echo view('all.footer');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $account = DB::table('user as u')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->where(function ($query) use ($email) {
                $query->whereRaw('LOWER(fm.email) = ?', [$email])
                    ->orWhereRaw('LOWER(e.email) = ?', [$email]);
            })
            ->select('u.userid', 'u.reset_password_token', 'u.reset_password_token_expired')
            ->first();

        if (!$account) {
            return back()->withErrors([
                'email' => 'Email is not registered in the system.',
            ])->withInput();
        }

        $plainToken = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes(60);

        DB::table('user')
            ->where('userid', (int) $account->userid)
            ->update([
                'reset_password_token' => Hash::make($plainToken),
                'reset_password_token_expired' => $expiresAt->toDateTimeString(),
            ]);

        $resetUrl = url('/reset-password/' . $plainToken) . '?email=' . urlencode($email);
        $appName = config('app.name', 'Family Tree System');

        try {
            Mail::raw(
                "You requested a password reset.\n\n"
                . "Click the link below to reset your password:\n"
                . $resetUrl . "\n\n"
                . "This link will expire in 60 minutes.\n\n"
                . "If you did not request this, you can ignore this email.",
                function ($message) use ($email, $appName) {
                    $message->to($email)->subject('Password Reset Request - ' . $appName);
                }
            );
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'We could not send the reset email. Please try again later.',
            ])->withInput();
        }

        return redirect('/forgot-password')->with(
            'status',
            'A password reset link has been sent to your email.'
        );
    }

    public function sendPhoneResetOtp(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:30'],
        ], [
            'phone_number.required' => 'Phone number is required.',
        ]);

        $account = $this->findAccountByPhoneNumber((string) $validated['phone_number']);
        if (!$account) {
            return back()->withErrors([
                'phone_number' => 'Phone number is not registered in the system.',
            ])->withInput();
        }

        $otp = (string) random_int(100000, 999999);
        $otpExpiresAt = Carbon::now()->addMinutes(5);

        if (!$this->sendWhatsappOtpViaFonnte((string) $account->phone_number, $otp, $otpExpiresAt)) {
            return back()->withErrors([
                'phone_number' => 'Failed to send OTP to WhatsApp. Please try again later.',
            ])->withInput();
        }

        $request->session()->put('phone_password_reset', [
            'userid' => (int) $account->userid,
            'phone_number' => (string) $account->phone_number,
            'phone_display' => trim((string) $validated['phone_number']),
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => $otpExpiresAt->toDateTimeString(),
        ]);

        $request->session()->forget('phone_password_reset_verified');

        return redirect('/forgot-password/phone')
            ->with('phone_otp_sent', true)
            ->with('show_phone_otp_form', true)
            ->with('phone_status', 'An OTP code has been sent to your WhatsApp number.');
    }

    public function verifyPhoneResetOtp(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:30'],
            'otp' => ['required', 'digits:6'],
        ], [
            'phone_number.required' => 'Phone number is required.',
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must contain 6 digits.',
        ]);

        $sessionReset = $request->session()->get('phone_password_reset');
        if (!is_array($sessionReset)) {
            return redirect('/forgot-password/phone')->withErrors([
                'otp' => 'OTP session not found. Please request a new OTP.',
            ]);
        }

        $inputPhone = $this->normalizePhoneNumber((string) $validated['phone_number']);
        $storedPhone = $this->normalizePhoneNumber((string) ($sessionReset['phone_number'] ?? ''));
        if ($inputPhone === '' || $storedPhone === '' || $inputPhone !== $storedPhone) {
            return back()->withErrors([
                'phone_number' => 'The phone number does not match the OTP request.',
            ])->with('show_phone_otp_form', true)->withInput();
        }

        $otpExpiresAtRaw = (string) ($sessionReset['otp_expires_at'] ?? '');
        $otpExpiresAt = $otpExpiresAtRaw !== '' ? Carbon::parse($otpExpiresAtRaw) : null;
        if (!$otpExpiresAt || $otpExpiresAt->isPast()) {
            $request->session()->forget('phone_password_reset');

            return redirect('/forgot-password/phone')->withErrors([
                'otp' => 'OTP has expired. Please request a new OTP.',
            ]);
        }

        $storedOtpHash = (string) ($sessionReset['otp_hash'] ?? '');
        if ($storedOtpHash === '' || !Hash::check((string) $validated['otp'], $storedOtpHash)) {
            return back()->withErrors([
                'otp' => 'Invalid OTP code.',
            ])->with('show_phone_otp_form', true)->withInput();
        }

        $request->session()->put('phone_password_reset_verified', [
            'userid' => (int) ($sessionReset['userid'] ?? 0),
            'phone_number' => (string) ($sessionReset['phone_number'] ?? ''),
            'verified_expires_at' => Carbon::now()->addMinutes(15)->toDateTimeString(),
        ]);

        $request->session()->forget('phone_password_reset');

        return redirect('/reset-password/phone');
    }

    public function resetPasswordPhoneForm(Request $request)
    {
        if ($request->session()->has('authenticated_user')) {
            return redirect('/');
        }

        $verified = $request->session()->get('phone_password_reset_verified');
        if (!is_array($verified)) {
            return redirect('/forgot-password/phone')->withErrors([
                'phone_number' => 'Phone verification is required before resetting your password.',
            ]);
        }

        $expiresAtRaw = (string) ($verified['verified_expires_at'] ?? '');
        $expiresAt = $expiresAtRaw !== '' ? Carbon::parse($expiresAtRaw) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            $request->session()->forget('phone_password_reset_verified');

            return redirect('/forgot-password/phone')->withErrors([
                'phone_number' => 'Your verification session has expired. Please restart from phone OTP.',
            ]);
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Reset Password by Phone | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.reset-password-phone', compact('systemSettings'));
        echo view('all.footer');
    }

    public function updatePasswordByPhone(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $verified = $request->session()->get('phone_password_reset_verified');
        if (!is_array($verified)) {
            return redirect('/forgot-password/phone')->withErrors([
                'phone_number' => 'Phone verification is required before resetting your password.',
            ]);
        }

        $expiresAtRaw = (string) ($verified['verified_expires_at'] ?? '');
        $expiresAt = $expiresAtRaw !== '' ? Carbon::parse($expiresAtRaw) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            $request->session()->forget('phone_password_reset_verified');

            return redirect('/forgot-password/phone')->withErrors([
                'phone_number' => 'Your verification session has expired. Please restart from phone OTP.',
            ]);
        }

        $userId = (int) ($verified['userid'] ?? 0);
        $accountExists = DB::table('user')->where('userid', $userId)->exists();
        if (!$accountExists) {
            $request->session()->forget('phone_password_reset_verified');

            return redirect('/forgot-password/phone')->withErrors([
                'phone_number' => 'User account is not found. Please contact administrator.',
            ]);
        }

        DB::table('user')
            ->where('userid', $userId)
            ->update([
                'password' => Hash::make((string) $validated['password']),
                'reset_password_token' => null,
                'reset_password_token_expired' => null,
            ]);

        $request->session()->forget('phone_password_reset_verified');

        return redirect('/password-reset/success')->with('password_reset_success', true);
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        if ($request->session()->has('authenticated_user')) {
            return redirect('/');
        }

        $email = strtolower(trim((string) $request->query('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect('/forgot-password')->withErrors([
                'email' => 'Invalid reset link. Please request a new one.',
            ]);
        }

        $account = DB::table('user as u')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->where(function ($query) use ($email) {
                $query->whereRaw('LOWER(fm.email) = ?', [$email])
                    ->orWhereRaw('LOWER(e.email) = ?', [$email]);
            })
            ->select('u.userid', 'u.reset_password_token', 'u.reset_password_token_expired')
            ->first();

        $storedToken = (string) ($account->reset_password_token ?? '');
        if (!$account || $storedToken === '' || !Hash::check($token, $storedToken)) {
            return redirect('/forgot-password')->withErrors([
                'email' => 'This reset link is invalid or has expired.',
            ]);
        }

        $expiresAt = !empty($account->reset_password_token_expired)
            ? Carbon::parse((string) $account->reset_password_token_expired)
            : null;

        if (!$expiresAt || $expiresAt->isPast()) {
            DB::table('user')
                ->where('userid', (int) $account->userid)
                ->update([
                    'reset_password_token' => null,
                    'reset_password_token_expired' => null,
                ]);

            return redirect('/forgot-password')->withErrors([
                'email' => 'This reset link has expired. Please request a new one.',
            ]);
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Reset Password | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.reset-password', compact('systemSettings', 'email', 'token'));
        echo view('all.footer');
    }

    public function updatePassword(Request $request, string $token)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $account = DB::table('user as u')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->where(function ($query) use ($email) {
                $query->whereRaw('LOWER(fm.email) = ?', [$email])
                    ->orWhereRaw('LOWER(e.email) = ?', [$email]);
            })
            ->select('u.userid', 'u.reset_password_token', 'u.reset_password_token_expired')
            ->first();

        $storedToken = (string) ($account->reset_password_token ?? '');
        if (!$account || $storedToken === '' || !Hash::check($token, $storedToken)) {
            return redirect('/forgot-password')->withErrors([
                'email' => 'This reset link is invalid or has expired.',
            ]);
        }

        $expiresAt = !empty($account->reset_password_token_expired)
            ? Carbon::parse((string) $account->reset_password_token_expired)
            : null;

        if (!$expiresAt || $expiresAt->isPast()) {
            DB::table('user')
                ->where('userid', (int) $account->userid)
                ->update([
                    'reset_password_token' => null,
                    'reset_password_token_expired' => null,
                ]);

            return redirect('/forgot-password')->withErrors([
                'email' => 'This reset link has expired. Please request a new one.',
            ]);
        }

        if (!$account) {
            return redirect('/forgot-password')->withErrors([
                'email' => 'Email is not registered in the system.',
            ]);
        }

        DB::table('user')
            ->where('userid', (int) $account->userid)
            ->update([
                'password' => Hash::make($validated['password']),
                'reset_password_token' => null,
                'reset_password_token_expired' => null,
            ]);

        return redirect('/password-reset/success')->with('password_reset_success', true);
    }

    public function passwordResetSuccess(Request $request)
    {
        if (!$request->session()->pull('password_reset_success', false)) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Password Reset Success | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.password-reset-success', compact('systemSettings'));
        echo view('all.footer');
    }

    public function logout(Request $request)
    {
        $this->logActivity($request, 'logout');

        $request->session()->forget('authenticated_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }


    public function account(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();
        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentFamilyProfile = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();
        $currentEmployerProfile = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();
        $canEditOwnProfile = $currentLevelId === 2 && !empty($currentFamilyProfile);
        $canEditAdminProfile = in_array($currentRoleId, [1, 2], true) && !empty($currentEmployerProfile);


        echo view('all.header', [
            'pageTitle' => 'Account | ' . $systemSettings['website_name'],
            'pageClass' => 'page-family-tree',
        ]);
        echo view('all.account', compact(
            'systemSettings',
            'currentFamilyProfile',
            'currentEmployerProfile',
            'canEditOwnProfile',
            'canEditAdminProfile'
        ));
        echo view('all.footer');
    }

    public function chatbot(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Chatbot | ' . $systemSettings['website_name'],
            'pageClass' => 'page-family-tree',
        ]);
        echo view('all.chatbot', compact('systemSettings'));
        echo view('all.footer');
    }

    public function updateEmployerProfile(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');

        if (!in_array($currentRoleId, [1, 2], true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Only admin can update this profile.'], 403);
            }

            return redirect('/account')->with('error', 'Only admin can update this profile.');
        }

        $employer = DB::table('employer')
            ->where('userid', $currentUserId)
            ->first();

        if (!$employer) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Admin profile not found.'], 404);
            }

            return redirect('/account')->with('error', 'Admin profile not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phonenumber' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email format is invalid.',
            'phonenumber.required' => 'Phone number is required.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect('/account')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $oldName = trim((string) ($employer->name ?? ''));
        $oldEmail = strtolower(trim((string) ($employer->email ?? '')));
        $oldPhoneNumber = trim((string) ($employer->phonenumber ?? ''));
        $newName = trim((string) $validated['name']);
        $newEmail = strtolower(trim((string) $validated['email']));
        $newPhoneNumber = trim((string) $validated['phonenumber']);

        DB::table('employer')
            ->where('userid', $currentUserId)
            ->update([
                'name' => $newName,
                'email' => $newEmail,
                'phonenumber' => $newPhoneNumber,
            ]);

        $activityChanges = [];

        if ($oldName !== $newName) {
            $activityChanges[] = [
                'field' => 'name',
                'old' => $oldName,
                'new' => $newName,
            ];
        }

        if ($oldEmail !== $newEmail) {
            $activityChanges[] = [
                'field' => 'email',
                'old' => $oldEmail,
                'new' => $newEmail,
            ];
        }

        if ($oldPhoneNumber !== $newPhoneNumber) {
            $activityChanges[] = [
                'field' => 'phone number',
                'old' => $oldPhoneNumber,
                'new' => $newPhoneNumber,
            ];
        }

        if (count($activityChanges) > 0) {
            $this->logActivity($request, 'account.update_admin_profile', [
                'userid' => $currentUserId,
                'changes' => $activityChanges,
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'profile' => [
                    'name' => $newName,
                    'email' => $newEmail,
                    'phonenumber' => $newPhoneNumber,
                ],
            ]);
        }

        return redirect('/account')->with('success', 'Profile updated successfully.');
    }

    public function userManagement(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to access user management.');
        }

        $usersQuery = $this->usersQuery()->whereNull('u.deleted_at');
        if ($isFamilyHead) {
            $usersQuery->where('u.levelid', 2);
        }

        $perPage = 20;
        $users = $usersQuery->paginate($perPage)->withQueryString();

        $levels = DB::table('level')
            ->selectRaw('MIN(levelid) as levelid, levelname')
            ->groupBy('levelname')
            ->orderBy('levelname')
            ->get();

        $roles = DB::table('role')
            ->orderBy('roleid')
            ->get();

        if ($isFamilyHead) {
            $levels = $levels->filter(function ($level) {
                return in_array((int) $level->levelid, [2, 4], true);
            })->values();

            $roles = $roles->filter(function ($role) {
                return in_array((int) $role->roleid, [3, 4], true);
            })->values();
        }

        $systemSettings = $this->getSystemSettings();

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('admin.partials.user-table-rows', ['users' => $users])->render(),
                'pagination_html' => view('admin.partials.user-pagination', ['users' => $users])->render(),
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]);
        }

        echo view('all.header', [
            'pageTitle' => 'User Management',
            'pageClass' => 'page-family-tree',
        ]);
        echo view('admin.user-management', compact('users', 'levels', 'roles', 'systemSettings'));
        echo view('all.footer');
    }

    public function exportUsers(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to export users.');
        }

        $fileName = 'users-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new UsersExport($isFamilyHead), $fileName);
    }

    public function importUsers(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to import users.');
        }

        $validator = Validator::make($request->all(), [
            'import_file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ], [
            'import_file.required' => 'Please choose an Excel file first.',
            'import_file.file' => 'Uploaded file is invalid.',
            'import_file.mimes' => 'File must be .xlsx format.',
            'import_file.max' => 'Maximum file size is 5MB.',
        ]);

        if ($validator->fails()) {
            return redirect('/management/users')
                ->withErrors($validator, 'userImport')
                ->with('openImportModal', true);
        }

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('import_file'));

            $message = 'Import completed. Added ' . $import->getImportedCount() . ' user(s)';
            if ($import->getSkippedCount() > 0) {
                $message .= ', skipped ' . $import->getSkippedCount() . ' row(s).';
            } else {
                $message .= '.';
            }

            return redirect('/management/users')->with('success', $message);
        } catch (\Throwable $e) {
            return redirect('/management/users')
                ->with('error', 'Failed to import users. Please check file format and template.');
        }
    }

    public function activityLog(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can access activity log.');
        }

        $systemSettings = $this->getSystemSettings();
        $perPage = 20;
        $allActivityLogs = collect($this->readActivityLogs(null))->values();
        $currentPage = max(1, (int) $request->query('page', 1));
        $currentPageItems = $allActivityLogs->forPage($currentPage, $perPage)->values();

        $activityLogs = new LengthAwarePaginator(
            $currentPageItems,
            $allActivityLogs->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('admin.partials.activity-log-table-rows', ['activityLogs' => $activityLogs])->render(),
                'pagination_html' => view('admin.partials.activity-log-pagination', ['activityLogs' => $activityLogs])->render(),
                'total' => $activityLogs->total(),
                'current_page' => $activityLogs->currentPage(),
                'last_page' => $activityLogs->lastPage(),
            ]);
        }

        echo view('all.header', [
            'pageTitle' => 'Activity Log',
            'pageClass' => 'page-family-tree',
        ]);
        echo view('admin.activity-log', compact('systemSettings', 'activityLogs'));
        echo view('all.footer');
    }

    public function recycleBin(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can access recycle bin.');
        }

        $deletedUsersQuery = $this->usersQuery()->whereNotNull('u.deleted_at');
        $perPage = 20;
        $deletedUsers = $deletedUsersQuery->paginate($perPage)->withQueryString();
        $systemSettings = $this->getSystemSettings();

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('superadmin.partials.recycle-bin-table-rows', ['users' => $deletedUsers])->render(),
                'pagination_html' => view('admin.partials.user-pagination', ['users' => $deletedUsers])->render(),
                'total' => $deletedUsers->total(),
                'current_page' => $deletedUsers->currentPage(),
                'last_page' => $deletedUsers->lastPage(),
            ]);
        }

        echo view('all.header', [
            'pageTitle' => 'Recycle Bin',
            'pageClass' => 'page-family-tree',
        ]);
        echo view('superadmin.recycle-bin', compact('systemSettings', 'deletedUsers'));
        echo view('all.footer');
    }

    public function storeUser(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to add users.',
                ], 403);
            }

            return redirect('/')->with('error', 'You do not have permission to add users.');
        }

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'levelid' => ['required', 'integer', 'exists:level,levelid'],
            'roleid' => ['nullable', 'integer', 'exists:role,roleid'],
            'email' => ['nullable', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'birthplace' => ['nullable', 'string', 'max:255'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'levelid.required' => 'Level is required.',
            'levelid.exists' => 'Selected level is invalid.',
            'roleid.exists' => 'Selected role is invalid.',
            'email.email' => 'Email format is invalid.',
            'gender.in' => 'Gender must be male or female.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $selectedLevel = DB::table('level')
            ->where('levelid', (int) $validated['levelid'])
            ->first();

        $selectedLevelId = (int) ($selectedLevel->levelid ?? 0);
        if ($selectedLevelId === 2) {
            $validated['roleid'] = 4;
        } elseif (empty($validated['roleid'])) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role is required.']],
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors(['roleid' => 'Role is required.'])
                ->withInput();
        }

        $isFamilyLevel = $selectedLevel && in_array($selectedLevelId, [2, 4], true);
        $allowedRoleIds = $isFamilyLevel ? [3, 4] : [1, 2];
        $isEmployerLevel = !$isFamilyLevel;

        if ($isFamilyHead) {
            if (!$isFamilyLevel) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Family head can only add family users.',
                    ], 422);
                }

                return redirect('/management/users')->with('error', 'Family head can only add family users.');
            }

            if (!in_array((int) $validated['roleid'], [3, 4], true)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Family head can only assign family roles.',
                    ], 422);
                }

                return redirect('/management/users')->with('error', 'Family head can only assign family roles.');
            }
        }

        if (!in_array((int) $validated['roleid'], $allowedRoleIds, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role does not match the selected level.']],
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Role does not match the selected level.');
        }

        if ($isEmployerLevel && (empty($validated['email']) || empty($validated['phonenumber']))) {
            $errors = [];
            if (empty($validated['name'])) {
                $errors['name'] = 'Name is required for Employer level.';
            }
            if (empty($validated['email'])) {
                $errors['email'] = 'Email is required for Employer level.';
            }
            if (empty($validated['phonenumber'])) {
                $errors['phonenumber'] = 'Phone number is required for Employer level.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $errors,
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors($errors)
                ->withInput();
        }

        if ($isFamilyLevel) {
            $errors = [];
            $requiredFields = [
                'name' => 'Name is required for Family level.',
                'email' => 'Email is required for Family level.',
                'phonenumber' => 'Phone number is required for Family level.',
                'gender' => 'Gender is required for Family level.',
                'address' => 'Address is required for Family level.',
                'marital_status' => 'Marital status is required for Family level.',
                'birthdate' => 'Birthdate is required for Family level.',
                'birthplace' => 'Birthplace is required for Family level.',
            ];

            foreach ($requiredFields as $field => $message) {
                if (empty($validated[$field])) {
                    $errors[$field] = $message;
                }
            }

            if (!empty($errors)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $errors,
                    ], 422);
                }

                return redirect('/management/users')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $isFamilyLevel) {
            $userId = DB::table('user')->insertGetId([
                'username' => $validated['username'],
                'password' => Hash::make($validated['username']),
                'levelid' => (int) $validated['levelid'],
            ]);

            if ($isFamilyLevel) {
                $birthdate = Carbon::parse($validated['birthdate']);
                $picture = $validated['gender'] === 'male'
                    ? '/images/avatar-male.svg'
                    : '/images/avatar-female.svg';

                DB::table('family_member')->insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phonenumber' => $validated['phonenumber'],
                    'gender' => $validated['gender'],
                    'birthdate' => $birthdate->toDateString(),
                    'birthplace' => $validated['birthplace'],
                    'address' => $validated['address'],
                    'job' => null,
                    'education_status' => null,
                    'life_status' => 'alive',
                    'marital_status' => $validated['marital_status'],
                    'deaddate' => null,
                    'picture' => $picture,
                    'userid' => $userId,
                ]);
            } else {
                DB::table('employer')->insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? '',
                    'phonenumber' => $validated['phonenumber'] ?? '',
                    'roleid' => (int) $validated['roleid'],
                    'userid' => $userId,
                ]);
            }
        });

        $this->logActivity($request, 'management.create_user', [
            'username' => (string) $validated['username'],
            'levelid' => (int) $validated['levelid'],
            'roleid' => isset($validated['roleid']) ? (int) $validated['roleid'] : null,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'New user has been added.',
            ]);
        }

        return redirect('/management/users')->with('success', 'New user has been added.');
    }

    public function resetUserPassword(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $user = DB::table('user')
            ->where('userid', (int) $userid)
            ->first();

        if (!$user) {
            return redirect('/management/users')->with('error', 'User not found.');
        }

        DB::table('user')
            ->where('userid', (int) $userid)
            ->update([
                'password' => Hash::make($user->username),
            ]);

        $this->logActivity($request, 'management.reset_password', [
            'target_userid' => (int) $userid,
            'target_username' => (string) $user->username,
        ]);

        return redirect('/management/users')->with(
            'success',
            'Password has been reset to the default value (username).'
        );
    }

    public function deleteUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $targetUserId = (int) $userid;
        $currentUserId = (int) session('authenticated_user.userid');

        if ($targetUserId === $currentUserId) {
            return redirect('/management/users')->with('error', 'You cannot delete your own account.');
        }

        $user = DB::table('user')
            ->where('userid', $targetUserId)
            ->first();

        if (!$user) {
            return redirect('/management/users')->with('error', 'User not found.');
        }

        if ($user->deleted_at !== null) {
            return redirect('/management/recycle-bin')->with('error', 'User is already in the recycle bin.');
        }

        DB::table('user')
            ->where('userid', $targetUserId)
            ->update(['deleted_at' => Carbon::now()]);

        $this->logActivity($request, 'management.delete_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($user->username ?? ''),
        ]);

        return redirect('/management/users')->with('success', 'User has been moved to Recycle Bin.');
    }

    public function restoreUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can restore users.');
        }

        $targetUserId = (int) $userid;
        $user = DB::table('user')
            ->where('userid', $targetUserId)
            ->first();

        if (!$user || $user->deleted_at === null) {
            return redirect('/management/recycle-bin')->with('error', 'User not found in recycle bin.');
        }

        DB::table('user')
            ->where('userid', $targetUserId)
            ->update(['deleted_at' => null]);

        $this->logActivity($request, 'management.restore_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($user->username ?? ''),
        ]);

        return redirect('/management/recycle-bin')->with('success', 'User has been restored from Recycle Bin.');
    }

    public function forceDeleteUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can permanently delete users.');
        }

        $targetUserId = (int) $userid;
        $user = DB::table('user')
            ->where('userid', $targetUserId)
            ->first();

        if (!$user || $user->deleted_at === null) {
            return redirect('/management/recycle-bin')->with('error', 'User not found in recycle bin.');
        }

        DB::transaction(function () use ($targetUserId) {
            $familyMemberIds = DB::table('family_member')
                ->where('userid', $targetUserId)
                ->pluck('memberid')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (!empty($familyMemberIds)) {
                DB::table('relationship')
                    ->whereIn('memberid', $familyMemberIds)
                    ->orWhereIn('relatedmemberid', $familyMemberIds)
                    ->delete();
            }

            DB::table('employer')->where('userid', $targetUserId)->delete();
            DB::table('family_member')->where('userid', $targetUserId)->delete();
            DB::table('user')->where('userid', $targetUserId)->delete();
        });

        $this->logActivity($request, 'management.force_delete_user', [
            'target_userid' => $targetUserId,
            'target_username' => (string) ($user->username ?? ''),
        ]);

        return redirect('/management/recycle-bin')->with('success', 'User has been permanently deleted.');
    }

    public function updateFamilyProfile(Request $request)
    {
        $redirectTo = (string) $request->input('redirect_to', '/');
        if (!in_array($redirectTo, ['/', '/account'], true)) {
            $redirectTo = '/';
        }

        if (!$request->session()->has('authenticated_user')) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Only family members can update this profile.'], 403);
            }

            return redirect($redirectTo)->with('error', 'Only family members can update this profile.');
        }

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Family profile not found.'], 404);
            }

            return redirect($redirectTo)->with('error', 'Family profile not found.');
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'job' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'education_status' => ['nullable', 'string', 'max:255'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [
            'name.max' => 'Name max length is 255 characters.',
            'email.email' => 'Email format is invalid.',
            'email.max' => 'Email max length is 255 characters.',
            'phonenumber.max' => 'Phone number max length is 255 characters.',
            'job.max' => 'Job max length is 255 characters.',
            'address.max' => 'Address max length is 255 characters.',
            'education_status.max' => 'Education max length is 255 characters.',
            'picture.image' => 'Profile picture must be an image file.',
            'picture.mimes' => 'Profile picture must be jpg, jpeg, png, webp, or gif.',
            'picture.max' => 'Profile picture max size is 2MB.',
        ]);

        $oldName = trim((string) ($familyMember->name ?? ''));
        $oldEmail = strtolower(trim((string) ($familyMember->email ?? '')));
        $oldPhoneNumber = trim((string) ($familyMember->phonenumber ?? ''));
        $oldJob = trim((string) ($familyMember->job ?? ''));
        $oldAddress = trim((string) ($familyMember->address ?? ''));
        $oldEducationStatus = trim((string) ($familyMember->education_status ?? ''));
        $oldPicture = trim((string) ($familyMember->picture ?? ''));
        $newName = trim((string) ($validated['name'] ?? ''));
        $newEmail = strtolower(trim((string) ($validated['email'] ?? '')));
        $newPhoneNumber = trim((string) ($validated['phonenumber'] ?? ''));
        $newJob = trim((string) ($validated['job'] ?? ''));
        $newAddress = trim((string) ($validated['address'] ?? ''));
        $newEducationStatus = trim((string) ($validated['education_status'] ?? ''));

        $updatePayload = [
            'name' => $newName !== '' ? $newName : null,
            'email' => $newEmail !== '' ? $newEmail : null,
            'phonenumber' => $newPhoneNumber !== '' ? $newPhoneNumber : null,
            'job' => $newJob !== '' ? $newJob : null,
            'address' => $newAddress !== '' ? $newAddress : null,
            'education_status' => $newEducationStatus !== '' ? $newEducationStatus : null,
        ];

        if ($request->hasFile('picture')) {
            $uploadDir = public_path('uploads/family');
            File::ensureDirectoryExists($uploadDir);

            if (!empty($familyMember->picture) && str_starts_with((string) $familyMember->picture, '/uploads/family/')) {
                $oldFile = public_path(ltrim((string) $familyMember->picture, '/'));
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
            }

            $ext = $request->file('picture')->getClientOriginalExtension();
            $fileName = 'family_member_' . $currentUserId . '_' . time() . '.' . $ext;
            $request->file('picture')->move($uploadDir, $fileName);
            $updatePayload['picture'] = '/uploads/family/' . $fileName;
        }

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update($updatePayload);

        $updatedFamilyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('name', 'email', 'phonenumber', 'job', 'address', 'education_status', 'picture')
            ->first();

        $activityChanges = [];

        if ($oldName !== $newName) {
            $activityChanges[] = [
                'field' => 'name',
                'old' => $oldName,
                'new' => $newName,
            ];
        }

        if ($oldEmail !== $newEmail) {
            $activityChanges[] = [
                'field' => 'email',
                'old' => $oldEmail,
                'new' => $newEmail,
            ];
        }

        if ($oldPhoneNumber !== $newPhoneNumber) {
            $activityChanges[] = [
                'field' => 'phone number',
                'old' => $oldPhoneNumber,
                'new' => $newPhoneNumber,
            ];
        }

        if ($oldJob !== $newJob) {
            $activityChanges[] = [
                'field' => 'job',
                'old' => $oldJob,
                'new' => $newJob,
            ];
        }

        if ($oldAddress !== $newAddress) {
            $activityChanges[] = [
                'field' => 'address',
                'old' => $oldAddress,
                'new' => $newAddress,
            ];
        }

        if ($oldEducationStatus !== $newEducationStatus) {
            $activityChanges[] = [
                'field' => 'education status',
                'old' => $oldEducationStatus,
                'new' => $newEducationStatus,
            ];
        }

        $newPicture = trim((string) ($updatedFamilyMember->picture ?? ''));
        if ($oldPicture !== $newPicture) {
            $activityChanges[] = [
                'field' => 'profile picture',
                'old' => $oldPicture,
                'new' => $newPicture,
            ];
        }

        if (count($activityChanges) > 0) {
            $this->logActivity($request, 'family.edit_profile', [
                'userid' => $currentUserId,
                'has_picture_upload' => $request->hasFile('picture'),
                'redirect_to' => $redirectTo,
                'changes' => $activityChanges,
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Profile details updated successfully.',
                'family_member' => $updatedFamilyMember,
            ]);
        }

        return redirect($redirectTo)->with('success', 'Profile details updated successfully.');
    }

    public function storeFamilyMemberFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        if ($currentRoleId !== 3 && $currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family users can add members from this page.');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('memberid')
            ->first();

        if (!$currentMember) {
            return redirect('/')->with('error', 'Only registered family members can add family relations.');
        }

        $targetMemberId = (int) $currentMember->memberid;

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'relation_type' => ['required', 'string', 'in:child,partner'],
            'child_parenting_mode' => ['nullable', 'string', 'in:with_current_partner,single_parent'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phonenumber' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'address' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'birthplace' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'relation_type.required' => 'Please choose Add Child or Add Partner.',
            'relation_type.in' => 'Invalid relation type selected.',
            'child_parenting_mode.in' => 'Invalid child parent mode selected.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
        ]);

        $relationType = (string) $validated['relation_type'];
        $childParentingMode = (string) ($validated['child_parenting_mode'] ?? 'with_current_partner');
        $newMemberMaritalStatus = $relationType === 'partner' ? 'married' : 'single';
        $partnerMemberId = null;
        $partnerIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($targetMemberId) {
                $query->where('memberid', $targetMemberId)
                    ->orWhere('relatedmemberid', $targetMemberId);
            })
            ->get()
            ->map(function ($row) use ($targetMemberId) {
                return (int) ((int) $row->memberid === $targetMemberId
                    ? $row->relatedmemberid
                    : $row->memberid);
            })
            ->unique()
            ->values();

        $partnerCount = $partnerIds->count();

        if ($partnerCount > 1) {
            throw ValidationException::withMessages([
                'relation_type' => ['Selected member has more than one partner in current data. Please fix data consistency first.'],
            ]);
        }

        if ($relationType === 'partner' && $partnerCount > 0) {
            throw ValidationException::withMessages([
                'relation_type' => ['You already have a partner and cannot add another partner.'],
            ]);
        }

        if ($relationType === 'child' && $childParentingMode === 'with_current_partner' && $partnerCount === 0) {
            throw ValidationException::withMessages([
                'child_parenting_mode' => ['Current partner not found for selected member. Choose Single parent instead.'],
            ]);
        }

        if ($relationType === 'child' && $childParentingMode === 'with_current_partner') {
            $partnerMemberId = (int) $partnerIds->first();
        }

        $createdUserId = 0;
        $createdMemberId = 0;

        DB::transaction(function () use ($validated, $targetMemberId, $relationType, $childParentingMode, $partnerMemberId, $newMemberMaritalStatus, &$createdUserId, &$createdMemberId) {
            $userId = DB::table('user')->insertGetId([
                'username' => $validated['username'],
                'password' => Hash::make($validated['username']),
                'levelid' => 2,
            ]);
            $createdUserId = (int) $userId;

            $picture = $validated['gender'] === 'male'
                ? '/images/avatar-male.svg'
                : '/images/avatar-female.svg';

            $newMemberId = DB::table('family_member')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phonenumber' => $validated['phonenumber'],
                'gender' => $validated['gender'],
                'birthdate' => Carbon::parse($validated['birthdate'])->toDateString(),
                'birthplace' => $validated['birthplace'],
                'address' => $validated['address'],
                'job' => null,
                'education_status' => null,
                'life_status' => 'alive',
                'marital_status' => $newMemberMaritalStatus,
                'deaddate' => null,
                'picture' => $picture,
                'userid' => $userId,
            ]);
            $createdMemberId = (int) $newMemberId;

            $relationsPersisted = true;
            if ($relationType === 'child') {
                DB::table('relationship')->insert([
                    'memberid' => $targetMemberId,
                    'relatedmemberid' => $newMemberId,
                    'relationtype' => 'child',
                ]);

                if ($childParentingMode === 'with_current_partner' && $partnerMemberId) {
                    DB::table('relationship')->insert([
                        'memberid' => $partnerMemberId,
                        'relatedmemberid' => $newMemberId,
                        'relationtype' => 'child',
                    ]);
                }
            }

            if ($relationType === 'partner') {
                DB::table('relationship')->insert([
                    'memberid' => $targetMemberId,
                    'relatedmemberid' => $newMemberId,
                    'relationtype' => 'partner',
                ]);

                DB::table('relationship')->insert([
                    'memberid' => $newMemberId,
                    'relatedmemberid' => $targetMemberId,
                    'relationtype' => 'partner',
                ]);

                DB::table('family_member')
                    ->whereIn('memberid', [$targetMemberId, $newMemberId])
                    ->update(['marital_status' => 'married']);
            }

            $userExists = DB::table('user')
                ->where('userid', $userId)
                ->exists();

            $familyMemberExists = DB::table('family_member')
                ->where('memberid', $newMemberId)
                ->where('userid', $userId)
                ->exists();

            if ($relationType === 'child') {
                $parentIds = [$targetMemberId];
                if ($childParentingMode === 'with_current_partner' && $partnerMemberId) {
                    $parentIds[] = (int) $partnerMemberId;
                }

                $persistedParentIds = DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $newMemberId)
                    ->whereIn('memberid', $parentIds)
                    ->pluck('memberid')
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->unique()
                    ->values()
                    ->all();

                $relationsPersisted = count($persistedParentIds) === count(array_unique($parentIds));
            }

            if ($relationType === 'partner') {
                $hasForwardPartnerRelation = DB::table('relationship')
                    ->where('memberid', $targetMemberId)
                    ->where('relatedmemberid', $newMemberId)
                    ->where('relationtype', 'partner')
                    ->exists();

                $hasBackwardPartnerRelation = DB::table('relationship')
                    ->where('memberid', $newMemberId)
                    ->where('relatedmemberid', $targetMemberId)
                    ->where('relationtype', 'partner')
                    ->exists();

                $relationsPersisted = $hasForwardPartnerRelation && $hasBackwardPartnerRelation;
            }

            if (!$userExists || !$familyMemberExists || !$relationsPersisted) {
                throw new \RuntimeException('Failed to persist new member data consistently.');
            }
        });

        $this->logActivity($request, 'family.add_relationship', [
            'target_memberid' => $targetMemberId,
            'new_memberid' => $createdMemberId,
            'new_userid' => $createdUserId,
            'new_username' => (string) $validated['username'],
            'new_member_name' => (string) $validated['name'],
            'new_member_gender' => (string) $validated['gender'],
            'relation_type' => $relationType,
            'child_parenting_mode' => $relationType === 'child' ? $childParentingMode : null,
            'partner_memberid' => $partnerMemberId,
        ]);

        return redirect('/')->with('success', 'New family member has been added.');
    }

    public function deleteFamilyMemberFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentLevelId = (int) session('authenticated_user.levelid');
        if ($currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family members can delete partner or child data.');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('memberid')
            ->first();

        if (!$currentMember) {
            return redirect('/')->with('error', 'Current family profile was not found.');
        }

        $validated = $request->validate([
            'memberid' => ['required', 'integer', 'exists:family_member,memberid'],
        ], [
            'memberid.required' => 'Member target is required.',
            'memberid.exists' => 'Selected member is not found.',
        ]);

        $currentMemberId = (int) $currentMember->memberid;
        $targetMemberId = (int) $validated['memberid'];

        if ($targetMemberId === $currentMemberId) {
            return redirect('/')->with('error', 'You cannot delete your own account from this action.');
        }

        $targetMember = DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->select('memberid', 'userid', 'name', 'gender', 'picture')
            ->first();

        if (!$targetMember) {
            return redirect('/')->with('error', 'Selected member is not found.');
        }

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

        $hasChildRelation = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $currentMemberId)
            ->where('relatedmemberid', $targetMemberId)
            ->exists();

        if (!$hasPartnerRelation && !$hasChildRelation) {
            return redirect('/')->with('error', 'You can only delete your own partner or child.');
        }

        DB::transaction(function () use ($targetMember, $targetMemberId, $hasPartnerRelation, $currentMemberId) {
            DB::table('relationship')
                ->where('memberid', $targetMemberId)
                ->orWhere('relatedmemberid', $targetMemberId)
                ->delete();

            DB::table('family_member')
                ->where('memberid', $targetMemberId)
                ->delete();

            DB::table('employer')
                ->where('userid', (int) $targetMember->userid)
                ->delete();

            DB::table('user')
                ->where('userid', (int) $targetMember->userid)
                ->delete();

            if ($hasPartnerRelation) {
                $remainingPartnerCount = DB::table('relationship')
                    ->where('relationtype', 'partner')
                    ->where(function ($query) use ($currentMemberId) {
                        $query->where('memberid', $currentMemberId)
                            ->orWhere('relatedmemberid', $currentMemberId);
                    })
                    ->count();

                if ($remainingPartnerCount === 0) {
                    DB::table('family_member')
                        ->where('memberid', $currentMemberId)
                        ->update(['marital_status' => 'single']);
                }
            }
        });

        if (!empty($targetMember->picture) && str_starts_with((string) $targetMember->picture, '/uploads/family/')) {
            $picturePath = public_path(ltrim((string) $targetMember->picture, '/'));
            if (File::exists($picturePath)) {
                File::delete($picturePath);
            }
        }

        $this->logActivity($request, 'family.delete_relationship', [
            'target_memberid' => $targetMemberId,
            'target_userid' => (int) ($targetMember->userid ?? 0),
            'target_name' => (string) ($targetMember->name ?? ''),
            'target_gender' => (string) ($targetMember->gender ?? ''),
            'relation_deleted' => $hasPartnerRelation ? 'partner' : 'child',
        ]);

        return redirect('/')->with('success', 'Family member has been deleted.');
    }

    public function updateFamilyMemberLifeStatus(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentLevelId = (int) session('authenticated_user.levelid');
        if ($currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family members can update life status.');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('memberid')
            ->first();

        if (!$currentMember) {
            return redirect('/')->with('error', 'Current family profile was not found.');
        }

        $validated = $request->validate([
            'memberid' => ['required', 'integer', 'exists:family_member,memberid'],
            'life_status' => ['required', 'string', 'in:alive,deceased'],
        ], [
            'memberid.required' => 'Member target is required.',
            'memberid.exists' => 'Selected member is not found.',
            'life_status.required' => 'Life status is required.',
            'life_status.in' => 'Life status must be alive or deceased.',
        ]);

        $currentMemberId = (int) $currentMember->memberid;
        $targetMemberId = (int) $validated['memberid'];

        $partnerIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($currentMemberId) {
                $query->where('memberid', $currentMemberId)
                    ->orWhere('relatedmemberid', $currentMemberId);
            })
            ->get()
            ->map(function ($row) use ($currentMemberId) {
                return (int) ((int) $row->memberid === $currentMemberId
                    ? $row->relatedmemberid
                    : $row->memberid);
            })
            ->filter(function ($id) {
                return (int) $id !== 0;
            })
            ->unique()
            ->values()
            ->all();

        $childIds = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $currentMemberId)
            ->pluck('relatedmemberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id !== 0;
            })
            ->unique()
            ->values()
            ->all();

        $parentIds = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('relatedmemberid', $currentMemberId)
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id !== 0;
            })
            ->unique()
            ->values()
            ->all();

        $siblingIds = [];
        if (!empty($parentIds)) {
            $siblingIds = DB::table('relationship')
                ->where('relationtype', 'child')
                ->whereIn('memberid', $parentIds)
                ->where('relatedmemberid', '!=', $currentMemberId)
                ->pluck('relatedmemberid')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter(function ($id) {
                    return $id !== 0;
                })
                ->unique()
                ->values()
                ->all();
        }

        $allowedMemberIds = [];
        $allowedMemberIds[$currentMemberId] = true;
        foreach ($partnerIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }
        foreach ($childIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }
        foreach ($parentIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }
        foreach ($siblingIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }

        if (empty($allowedMemberIds[$targetMemberId])) {
            return redirect('/')->with('error', 'You can only update life status for yourself, partner, child, parent, or sibling.');
        }

        $targetMember = DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->select('name', 'gender', 'life_status')
            ->first();

        $nextLifeStatus = (string) $validated['life_status'];
        DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->update([
                'life_status' => $nextLifeStatus,
                'deaddate' => $nextLifeStatus === 'deceased' ? now()->toDateString() : null,
            ]);

        $previousLifeStatus = (string) ($targetMember->life_status ?? '');
        if ($previousLifeStatus !== $nextLifeStatus) {
            $this->logActivity($request, 'family.update_life_status', [
                'target_memberid' => $targetMemberId,
                'target_name' => (string) ($targetMember->name ?? ''),
                'target_gender' => (string) ($targetMember->gender ?? ''),
                'target_relation_label' => $this->resolveActivityRelationLabel($currentMemberId, $targetMemberId),
                'life_status_old' => $previousLifeStatus,
                'life_status_new' => $nextLifeStatus,
            ]);
        }

        return redirect('/')->with('success', 'Life status has been updated.');
    }

    public function systemSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can access settings.');
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'System Settings',
            'pageClass' => 'page-family-tree',
        ]);
        echo view('superadmin.settings', compact('systemSettings'));
        echo view('all.footer');
    }

    public function updateSystemSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Only superadmin can update settings.'], 403);
            }

            return redirect('/management/users')->with('error', 'Only superadmin can update settings.');
        }

        $validated = $request->validate([
            'website_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ], [
            'website_name.required' => 'Website name is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be jpg, jpeg, png, webp, or svg.',
            'logo.max' => 'Logo max size is 2MB.',
        ]);

        $settings = $this->getSystemSettings();
        $oldWebsiteName = (string) ($settings['website_name'] ?? '');
        $oldLogoPath = (string) ($settings['logo_path'] ?? '');
        $settings['website_name'] = $validated['website_name'];

        if ($request->hasFile('logo')) {
            $uploadDir = public_path('uploads/system');
            File::ensureDirectoryExists($uploadDir);

            if (!empty($settings['logo_path']) && str_starts_with($settings['logo_path'], '/uploads/system/')) {
                $oldFile = public_path(ltrim($settings['logo_path'], '/'));
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
            }

            $ext = $request->file('logo')->getClientOriginalExtension();
            $fileName = 'system_logo_' . time() . '.' . $ext;
            $request->file('logo')->move($uploadDir, $fileName);
            $settings['logo_path'] = '/uploads/system/' . $fileName;
        }

        $this->saveSystemSettings($settings);

        $activityChanges = [];

        if ($oldWebsiteName !== (string) $settings['website_name']) {
            $activityChanges[] = 'Changed website name from "' . ($oldWebsiteName !== '' ? $oldWebsiteName : 'empty') . '" to "' . ((string) $settings['website_name'] !== '' ? (string) $settings['website_name'] : 'empty') . '"';
        }

        if ($oldLogoPath !== (string) ($settings['logo_path'] ?? '')) {
            $activityChanges[] = empty($oldLogoPath)
                ? 'Added website logo'
                : 'Changed website logo';
        }

        if (count($activityChanges) > 0) {
            $this->logActivity($request, 'superadmin.update_setting', [
                'website_name_old' => $oldWebsiteName,
                'website_name_new' => (string) $settings['website_name'],
                'logo_path_old' => $oldLogoPath,
                'logo_path_new' => (string) ($settings['logo_path'] ?? ''),
                'changes' => $activityChanges,
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'System settings updated successfully.',
                'settings' => $settings,
            ]);
        }

        return redirect('/setting')->with('success', 'System settings updated successfully.');
    }

    private function findAccountByPhoneNumber(string $inputPhone): ?object
    {
        $normalizedInput = $this->normalizePhoneNumber($inputPhone);
        if ($normalizedInput === '') {
            return null;
        }

        $accounts = DB::table('user as u')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->select('u.userid', 'fm.phonenumber as family_phone', 'e.phonenumber as employer_phone')
            ->get();

        foreach ($accounts as $account) {
            $familyPhone = $this->normalizePhoneNumber((string) ($account->family_phone ?? ''));
            if ($familyPhone !== '' && $familyPhone === $normalizedInput) {
                return (object) [
                    'userid' => (int) $account->userid,
                    'phone_number' => $familyPhone,
                ];
            }

            $employerPhone = $this->normalizePhoneNumber((string) ($account->employer_phone ?? ''));
            if ($employerPhone !== '' && $employerPhone === $normalizedInput) {
                return (object) [
                    'userid' => (int) $account->userid,
                    'phone_number' => $employerPhone,
                ];
            }
        }

        return null;
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', trim($phoneNumber));
        if (!is_string($digits) || $digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    private function sendWhatsappOtpViaFonnte(string $phoneNumber, string $otp, Carbon $expiresAt): bool
    {
        $token = (string) config('services.fonnte.token', '');
        if ($token === '') {
            return false;
        }

        $url = (string) config('services.fonnte.url', 'https://api.fonnte.com/send');
        $message = 'Your password reset OTP is: ' . $otp . '. This code expires at '
            . $expiresAt->format('Y-m-d H:i:s') . ' (server time).';

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->asForm()->post($url, [
                'target' => $phoneNumber,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                return false;
            }

            $payload = $response->json();
            if (is_array($payload) && array_key_exists('status', $payload)) {
                return (bool) $payload['status'];
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function usersQuery()
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
                'l.levelname',
                'r.rolename',
                DB::raw("COALESCE(e.name, fm.name, '-') as fullname"),
                DB::raw("COALESCE(e.email, fm.email, '-') as email"),
                DB::raw("COALESCE(e.phonenumber, fm.phonenumber, '-') as phone"),
                DB::raw("CASE
                    WHEN e.employerid IS NOT NULL THEN 'Employer'
                    WHEN fm.memberid IS NOT NULL THEN 'Family Member'
                    ELSE 'User'
                END as source")
            )
            ->orderBy('u.userid');
    }

    private function resolveActivityRelationLabel(int $sourceMemberId, int $targetMemberId): string
    {
        if ($sourceMemberId === 0 || $targetMemberId === 0) {
            return 'Member';
        }

        if ($sourceMemberId === $targetMemberId) {
            return 'Self';
        }

        $targetGender = strtolower((string) DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->value('gender'));

        $partnerExists = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($sourceMemberId, $targetMemberId) {
                $query->where(function ($subQuery) use ($sourceMemberId, $targetMemberId) {
                    $subQuery->where('memberid', $sourceMemberId)
                        ->where('relatedmemberid', $targetMemberId);
                })->orWhere(function ($subQuery) use ($sourceMemberId, $targetMemberId) {
                    $subQuery->where('memberid', $targetMemberId)
                        ->where('relatedmemberid', $sourceMemberId);
                });
            })
            ->exists();

        if ($partnerExists) {
            if ($targetGender === 'female') {
                return 'Wife';
            }
            if ($targetGender === 'male') {
                return 'Husband';
            }
            return 'Partner';
        }

        $isChild = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $sourceMemberId)
            ->where('relatedmemberid', $targetMemberId)
            ->exists();

        if ($isChild) {
            if ($targetGender === 'female') {
                return 'Daughter';
            }
            if ($targetGender === 'male') {
                return 'Son';
            }
            return 'Child';
        }

        $isParent = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $targetMemberId)
            ->where('relatedmemberid', $sourceMemberId)
            ->exists();

        if ($isParent) {
            if ($targetGender === 'female') {
                return 'Mother';
            }
            if ($targetGender === 'male') {
                return 'Father';
            }
            return 'Parent';
        }

        $parentIds = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('relatedmemberid', $sourceMemberId)
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id !== 0;
            })
            ->unique()
            ->values()
            ->all();

        if (!empty($parentIds)) {
            $isSibling = DB::table('relationship')
                ->where('relationtype', 'child')
                ->whereIn('memberid', $parentIds)
                ->where('relatedmemberid', $targetMemberId)
                ->exists();

            if ($isSibling) {
                if ($targetGender === 'female') {
                    return 'Sister';
                }
                if ($targetGender === 'male') {
                    return 'Brother';
                }
                return 'Sibling';
            }
        }

        return 'Member';
    }

    private function logActivity(Request $request, string $action, array $context = []): void
    {
        try {
            $actor = (array) ($request->session()->get('authenticated_user', []) ?: []);
            $rawLatitude = $request->input('activity_latitude', $request->input('latitude'));
            $rawLongitude = $request->input('activity_longitude', $request->input('longitude'));
            $latitude = is_numeric($rawLatitude) ? (float) $rawLatitude : null;
            $longitude = is_numeric($rawLongitude) ? (float) $rawLongitude : null;
            $entry = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'action' => $action,
                'actor' => [
                    'userid' => isset($actor['userid']) ? (int) $actor['userid'] : null,
                    'username' => isset($actor['username']) ? (string) $actor['username'] : 'guest',
                    'roleid' => isset($actor['roleid']) ? (int) $actor['roleid'] : null,
                    'levelid' => isset($actor['levelid']) ? (int) $actor['levelid'] : null,
                ],
                'ip' => (string) $request->ip(),
                'longitude' => $longitude,
                'latitude' => $latitude,
                'user_agent' => (string) ($request->userAgent() ?? ''),
                'context' => $context,
            ];

            $path = storage_path('app/activity_log.jsonl');
            File::append($path, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL);
        } catch (\Throwable $e) {
            // Keep app flow safe even when logging fails.
        }
    }

    private function readActivityLogs(?int $limit = 200): array
    {
        $path = storage_path('app/activity_log.jsonl');
        if (!File::exists($path)) {
            return [];
        }

        $raw = (string) File::get($path);
        if ($raw === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $logs = [];

        foreach (array_reverse($lines) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);
            if (!is_array($decoded)) {
                continue;
            }

            $logs[] = $decoded;
            if (is_int($limit) && $limit > 0 && count($logs) >= $limit) {
                break;
            }
        }

        return $logs;
    }

    private function getSystemSettings(): array
    {
        $defaults = [
            'website_name' => 'Family Tree System',
            'logo_path' => '',
        ];

        if (Schema::hasTable('system')) {
            $system = DB::table('system')
                ->orderBy('systemid')
                ->first();

            if ($system) {
                return array_merge($defaults, [
                    'website_name' => (string) ($system->systemname ?? $defaults['website_name']),
                    'logo_path' => (string) ($system->systemlogo ?? $defaults['logo_path']),
                ]);
            }

            $legacySettings = $this->getLegacySystemSettings();
            $this->saveSystemSettings($legacySettings);

            return array_merge($defaults, $legacySettings);
        }

        return $this->getLegacySystemSettings();
    }

    private function getLegacySystemSettings(): array
    {
        $defaults = [
            'website_name' => 'Family Tree System',
            'logo_path' => '',
        ];

        $path = storage_path('app/system_settings.json');
        if (!File::exists($path)) {
            return $defaults;
        }

        $data = json_decode((string) File::get($path), true);
        if (!is_array($data)) {
            return $defaults;
        }

        return array_merge($defaults, $data);
    }

    private function saveSystemSettings(array $settings): void
    {
        $normalizedSettings = [
            'website_name' => (string) ($settings['website_name'] ?? 'Family Tree System'),
            'logo_path' => (string) ($settings['logo_path'] ?? ''),
        ];

        if (Schema::hasTable('system')) {
            $currentSystem = DB::table('system')
                ->orderBy('systemid')
                ->first();

            $payload = [
                'systemname' => $normalizedSettings['website_name'],
                'systemlogo' => $normalizedSettings['logo_path'],
            ];

            if ($currentSystem) {
                DB::table('system')
                    ->where('systemid', $currentSystem->systemid)
                    ->update($payload);
            } else {
                DB::table('system')->insert([
                    'systemname' => $payload['systemname'],
                    'systemlogo' => $payload['systemlogo'],
                    'systemcontact' => '',
                    'systemmanager' => '',
                    'systemaddress' => '',
                ]);
            }
        }

        $path = storage_path('app/system_settings.json');
        File::put($path, json_encode($normalizedSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
