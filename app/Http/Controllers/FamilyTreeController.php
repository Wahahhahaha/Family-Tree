<?php

namespace App\Http\Controllers;

use App\Services\FamilyTreeService;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangeEmailVerification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FamilyTreeController extends Controller
{
    protected const MAX_SOCIAL_MEDIA_PER_MEMBER = 3;
    protected const TIMELINE_CATEGORY_OPTIONS = [
        'education' => 'Education',
        'health' => 'Health',
        'accident' => 'Accident',
        'achievement' => 'Achievement',
        'marriage' => 'Marriage',
        'birth' => 'Birth',
        'death' => 'Death',
        'work' => 'Work',
        'move' => 'Move',
        'other' => 'Other',
    ];

        protected $treeService;

    public function __construct(FamilyTreeService $treeService)
    {
                $this->treeService = $treeService;
    }


    public function home(Request $request)
    {
        $systemSettings = $this->getSystemSettings();
        $landingPageSettings = $this->getLandingPageSettings();
        $currentLocale = $this->getCurrentLocale($request->session()->get('locale', 'en'));
        $landingPageSettings = $this->translateLandingSettings($landingPageSettings, $currentLocale);

        if (!$request->session()->has('authenticated_user')) {
            return view('all.landing', [
                'pageTitle' => $systemSettings['website_name'] ?? 'Family Tree',
                'pageClass' => 'page-landing',
                'hideNavbar' => true,
                'hideFooter' => true,
                'systemSettings' => $systemSettings,
                'landingPageSettings' => $landingPageSettings,
                'currentLocale' => $currentLocale,
                'supportedLocales' => $this->getSupportedLocales(),
            ]);
        }

        $familyMembers = Cache::store('file')->remember('family_tree:family_members:v1', now()->addSeconds(30), function () {
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
                    'fm.bloodtype',
                    'fm.life_status',
                    'fm.marital_status',
                    'fm.picture',
                    'fm.email',
                    'fm.phonenumber',
                    'fm.job',
                    'fm.address',
                    'fm.education_status',
                    'fm.deaddate',
                    'fm.grave_location_url'
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
                    $bloodType = strtoupper(trim((string) ($member->bloodtype ?? '')));
                    $member->bloodtype = $bloodType !== '' ? $bloodType : '-';
                    $member->social_media = '-';
                    $member->social_media_items = [];

                    return $member;
                });

            $memberIds = $familyMembers
                ->pluck('memberid')
                ->map(function ($value) {
                    return (int) $value;
                })
                ->filter(function ($value) {
                    return $value > 0;
                })
                ->values()
                ->all();

            $socialMediaLabelsByMemberId = [];
            $socialMediaItemsByMemberId = [];
            $hasSocialMediaIconColumn = $this->hasSocialMediaIconColumn();
            if (!empty($memberIds)) {
                $ownSocialRowsQuery = DB::table('ownsocial as os')
                    ->leftJoin('socialmedia as sm', 'sm.socialid', '=', 'os.socialid')
                    ->whereIn('os.memberid', $memberIds);

                if ($hasSocialMediaIconColumn) {
                    $ownSocialRowsQuery->select('os.memberid', 'os.link', 'sm.socialname', 'sm.socialicon');
                } else {
                    $ownSocialRowsQuery->select('os.memberid', 'os.link', 'sm.socialname');
                }

                $ownSocialRows = $ownSocialRowsQuery
                    ->orderBy('sm.socialname')
                    ->get();

                foreach ($ownSocialRows as $ownSocialRow) {
                    $memberId = (int) ($ownSocialRow->memberid ?? 0);
                    if ($memberId <= 0) {
                        continue;
                    }

                    $socialName = trim((string) ($ownSocialRow->socialname ?? ''));
                    if ($socialName === '') {
                        continue;
                    }

                    $socialLink = trim((string) ($ownSocialRow->link ?? ''));
                    $socialLabel = $socialLink !== ''
                        ? $socialName . ' (' . $socialLink . ')'
                        : $socialName;

                    $socialMediaLabelsByMemberId[$memberId] = $socialMediaLabelsByMemberId[$memberId] ?? [];
                    $socialMediaItemsByMemberId[$memberId] = $socialMediaItemsByMemberId[$memberId] ?? [];
                    if (!in_array($socialLabel, $socialMediaLabelsByMemberId[$memberId], true)) {
                        $socialMediaLabelsByMemberId[$memberId][] = $socialLabel;
                    }

                    $socialIcon = $this->normalizeSocialMediaIconKey(
                        (string) ($ownSocialRow->socialicon ?? ''),
                        $socialName
                    );
                    $socialItem = [
                        'name' => $socialName,
                        'link' => $socialLink,
                        'icon' => $socialIcon,
                    ];
                    $hasSameSocialItem = false;
                    foreach ($socialMediaItemsByMemberId[$memberId] as $existingSocialItem) {
                        $sameName = Str::lower(trim((string) ($existingSocialItem['name'] ?? ''))) === Str::lower($socialName);
                        $sameLink = trim((string) ($existingSocialItem['link'] ?? '')) === $socialLink;
                        if ($sameName && $sameLink) {
                            $hasSameSocialItem = true;
                            break;
                        }
                    }

                    if (!$hasSameSocialItem) {
                        $socialMediaItemsByMemberId[$memberId][] = $socialItem;
                    }
                }
            }

            return $familyMembers->map(function ($member) use ($socialMediaLabelsByMemberId, $socialMediaItemsByMemberId) {
                $memberId = (int) ($member->memberid ?? 0);
                $memberSocialMediaLabels = $socialMediaLabelsByMemberId[$memberId] ?? [];
                $memberSocialMediaItems = $socialMediaItemsByMemberId[$memberId] ?? [];
                $member->social_media = !empty($memberSocialMediaLabels)
                    ? implode(', ', $memberSocialMediaLabels)
                    : '-';
                $member->social_media_items = array_values($memberSocialMediaItems);

                return $member;
            });
        });

        $membersById = $familyMembers->keyBy('memberid');
        $memberIds = $familyMembers
            ->pluck('memberid')
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->values()
            ->all();
        $relationships = Cache::store('file')->remember('family_tree:relationships:v1', now()->addSeconds(30), function () {
            return DB::table('relationship')
                ->select('memberid', 'relatedmemberid', 'relationtype', 'child_parenting_mode')
                ->get();
        });

        $childrenMap = [];
        $childParentingModeMap = [];
        $partnerMap = [];
        $parentMap = [];
        $parentCount = [];
        $rawChildrenMap = [];
        $rawParentMap = [];
        $rawPartnerMap = [];

        foreach ($relationships as $relation) {
            $from = (int) $relation->memberid;
            $to = (int) $relation->relatedmemberid;
            $type = strtolower((string) $relation->relationtype);
            $rawParentingMode = strtolower(trim((string) ($relation->child_parenting_mode ?? '')));
            $parentingMode = in_array($rawParentingMode, ['with_current_partner', 'single_parent'], true)
                ? $rawParentingMode
                : '';

            if ($type === 'child' && $from > 0 && $to > 0) {
                $rawChildrenMap[$from] = $rawChildrenMap[$from] ?? [];
                if (!in_array($to, $rawChildrenMap[$from], true)) {
                    $rawChildrenMap[$from][] = $to;
                }

                $rawParentMap[$to] = $rawParentMap[$to] ?? [];
                if (!in_array($from, $rawParentMap[$to], true)) {
                    $rawParentMap[$to][] = $from;
                }
            }

            if ($type === 'partner' && $from > 0 && $to > 0) {
                $rawPartnerMap[$from] = $rawPartnerMap[$from] ?? [];
                if (!in_array($to, $rawPartnerMap[$from], true)) {
                    $rawPartnerMap[$from][] = $to;
                }

                $rawPartnerMap[$to] = $rawPartnerMap[$to] ?? [];
                if (!in_array($from, $rawPartnerMap[$to], true)) {
                    $rawPartnerMap[$to][] = $from;
                }
            }

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

        foreach ($childParentingModeMap as $parentId => $childModes) {
            foreach ($childModes as $childId => $mode) {
                if ($mode === 'single_parent') {
                    continue;
                }

                $parentId = (int) $parentId;
                $childId = (int) $childId;
                $childParents = array_values(array_filter($parentMap[$childId] ?? [], function ($parentMemberId) use ($parentId) {
                    return (int) $parentMemberId !== $parentId;
                }));
                $parentPartners = $partnerMap[$parentId] ?? [];
                $hasCurrentPartnerAsCoParent = !empty(array_intersect($childParents, $parentPartners));
                $resolvedMode = $hasCurrentPartnerAsCoParent ? 'with_current_partner' : 'single_parent';
                $childParentingModeMap[$parentId][$childId] = $resolvedMode;
            }
        }

        // Normalize marital status for tree display:
        // whenever a member has at least one partner relation, show "married".
        foreach ($familyMembers as $member) {
            $memberId = (int) ($member->memberid ?? 0);
            if ($memberId !== 0 && !empty($partnerMap[$memberId] ?? [])) {
                $member->marital_status = 'married';
            }
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $isAdminOrSuperadmin = in_array($currentRoleId, [1, 2], true);
        $currentMember = $familyMembers->firstWhere('userid', $currentUserId);
        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        $currentMemberPartnerIds = $partnerMap[$currentMemberId] ?? [];
        $currentMemberHasPartner = false;
        foreach ($currentMemberPartnerIds as $partnerId) {
            $partnerMember = $membersById[(int) $partnerId] ?? null;
            if ($partnerMember && strtolower((string) ($partnerMember->life_status ?? '')) !== 'deceased') {
                $currentMemberHasPartner = true;
                break;
            }
        }
        $familyHeadMemberId = $this->resolveFamilyHeadMemberId(
            $memberIds,
            $parentMap
        );
        $directFamilyMemberSet = $this->resolveDirectFamilyBloodlineSet(
            $familyHeadMemberId,
            $memberIds,
            $childrenMap,
            $parentMap
        );
        $canCurrentMemberManageDivorce = $this->canMemberManageDivorce(
            $currentMemberId,
            $familyHeadMemberId,
            $directFamilyMemberSet,
            $parentMap
        );
        $canDeletePartnerMap = [];
        $canDeleteChildMap = [];
        $canUpdateLifeStatusMap = [];
        $canEditProfileMap = [];

        if ($isAdminOrSuperadmin) {
            foreach ($familyMembers as $member) {
                $memberId = (int) ($member->memberid ?? 0);
                if ($memberId > 0) {
                    $canEditProfileMap[$memberId] = true;
                    $canUpdateLifeStatusMap[$memberId] = true;
                }
            }
        }

        if ($currentMemberId !== 0) {
            $canEditProfileMap[$currentMemberId] = true;
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
                    if ($canCurrentMemberManageDivorce) {
                        $canDeletePartnerMap[$partnerId] = true;
                    }
                    $canUpdateLifeStatusMap[$partnerId] = true;
                    $canEditProfileMap[$partnerId] = true;
                }

                if ($type === 'child' && $from === $currentMemberId) {
                    $canEditProfileMap[$to] = true;
                    $canDeleteChildMap[$to] = true;
                    $canUpdateLifeStatusMap[$to] = true;
                }
            }

            $currentParents = $parentMap[$currentMemberId] ?? [];
            $currentParentSet = [];
            foreach ($currentParents as $parentId) {
                $parentId = (int) $parentId;
                if ($parentId !== 0 && isset($membersById[$parentId])) {
                    $currentParentSet[$parentId] = true;
                    $canUpdateLifeStatusMap[$parentId] = true;
                }
            }

            foreach (array_keys($currentParentSet) as $parentId) {
                foreach ($partnerMap[$parentId] ?? [] as $stepParentId) {
                    $stepParentId = (int) $stepParentId;
                    if (
                        $stepParentId !== 0
                        && $stepParentId !== $currentMemberId
                        && !isset($currentParentSet[$stepParentId])
                        && isset($membersById[$stepParentId])
                    ) {
                        $canUpdateLifeStatusMap[$stepParentId] = true;
                    }
                }
            }

            foreach (array_keys($currentParentSet) as $parentId) {
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

            $isStepChild = false;
            foreach ($partnerMap[$currentMemberId] ?? [] as $myPartnerId) {
                if (in_array($targetId, $childrenOf((int) $myPartnerId), true) && !in_array($targetId, $myChildren, true)) {
                    $isStepChild = true;
                    break;
                }
            }
            if ($isStepChild) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Step Son', 'Step Daughter', 'Step Child');
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
            $mySiblingsInLaw = [];
            foreach (array_keys($mySiblings) as $siblingId) {
                foreach ($partnerMap[(int) $siblingId] ?? [] as $siblingInLawId) {
                    $siblingInLawId = (int) $siblingInLawId;
                    $mySiblingsInLaw[$siblingInLawId] = true;
                    if ($siblingInLawId === $targetId) {
                        $isSiblingInLaw = true;
                    }
                }
            }
            foreach (array_keys($myPartnerSiblings) as $partnerSiblingId) {
                $partnerSiblingId = (int) $partnerSiblingId;
                $mySiblingsInLaw[$partnerSiblingId] = true;
                if ($partnerSiblingId === $targetId) {
                    $isSiblingInLaw = true;
                }
            }
            if ($isSiblingInLaw) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Brother in law', 'Sister in law', 'Sibling in law');
                continue;
            }

            $isSiblingInLawSpouse = false;
            foreach (array_keys($mySiblingsInLaw) as $siblingInLawId) {
                if (in_array($targetId, $partnerMap[(int) $siblingInLawId] ?? [], true)) {
                    $isSiblingInLawSpouse = true;
                    break;
                }
            }
            if ($isSiblingInLawSpouse) {
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

            $isStepChildSpouse = false;
            foreach ($partnerMap[$currentMemberId] ?? [] as $myPartnerId) {
                foreach ($childrenOf((int) $myPartnerId) as $partnerChildId) {
                    $partnerChildId = (int) $partnerChildId;
                    if (in_array($partnerChildId, $myChildren, true)) {
                        continue;
                    }

                    if (in_array($targetId, $partnerMap[$partnerChildId] ?? [], true)) {
                        $isStepChildSpouse = true;
                        break 2;
                    }
                }
            }
            if ($isStepChildSpouse) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Step Son in law', 'Step Daughter in law', 'Step Child in law');
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

            $inLawGrandParents = [];
            foreach (array_keys($inLawParents) as $inLawParentId) {
                foreach ($parentsOf((int) $inLawParentId) as $grandInLawParentId) {
                    $inLawGrandParents[(int) $grandInLawParentId] = true;
                }
            }
            if (isset($inLawGrandParents[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandfather in law', 'Grandmother in law', 'Grandparent in law');
                continue;
            }

            $inLawGrandParentSiblings = [];
            foreach (array_keys($inLawGrandParents) as $inLawGrandParentId) {
                foreach ($parentsOf((int) $inLawGrandParentId) as $inLawGreatGrandParentId) {
                    foreach ($childrenOf((int) $inLawGreatGrandParentId) as $inLawGrandParentSiblingId) {
                        $inLawGrandParentSiblingId = (int) $inLawGrandParentSiblingId;
                        if ($inLawGrandParentSiblingId !== (int) $inLawGrandParentId) {
                            $inLawGrandParentSiblings[$inLawGrandParentSiblingId] = true;
                        }
                    }
                }
            }
            if (isset($inLawGrandParentSiblings[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Great-uncle-in-law', 'Great-aunt-in-law', 'Great-relative-in-law');
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

            $isInLawUncleAuntSpouse = false;
            foreach (array_keys($inLawParentSiblings) as $inLawParentSiblingId) {
                if (in_array($targetId, $partnerMap[(int) $inLawParentSiblingId] ?? [], true)) {
                    $isInLawUncleAuntSpouse = true;
                    break;
                }
            }
            if ($isInLawUncleAuntSpouse) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Uncle in law', 'Aunt in law', 'Relative in law');
                continue;
            }

            $inLawUnclesAunts = $inLawParentSiblings;
            foreach (array_keys($inLawParentSiblings) as $inLawParentSiblingId) {
                foreach ($partnerMap[(int) $inLawParentSiblingId] ?? [] as $inLawUncleAuntPartnerId) {
                    $inLawUnclesAunts[(int) $inLawUncleAuntPartnerId] = true;
                }
            }

            $isFirstCousinInLawByUncleAuntInLaw = false;
            foreach (array_keys($inLawUnclesAunts) as $inLawUncleAuntId) {
                if (in_array($targetId, $childrenOf((int) $inLawUncleAuntId), true)) {
                    $isFirstCousinInLawByUncleAuntInLaw = true;
                    break;
                }
            }
            if ($isFirstCousinInLawByUncleAuntInLaw) {
                $relationLabels[$targetId] = 'First Cousin-in-law';
                continue;
            }

            $inLawGreatUnclesAunts = [];
            foreach (array_keys($inLawGrandParents) as $inLawGrandParentId) {
                foreach ($parentsOf((int) $inLawGrandParentId) as $inLawGreatGrandParentId) {
                    foreach ($childrenOf((int) $inLawGreatGrandParentId) as $inLawGrandParentSiblingId) {
                        $inLawGrandParentSiblingId = (int) $inLawGrandParentSiblingId;
                        if ($inLawGrandParentSiblingId !== (int) $inLawGrandParentId) {
                            $inLawGreatUnclesAunts[$inLawGrandParentSiblingId] = true;
                        }
                    }
                }
            }

            $isSecondCousinInLaw = false;
            foreach (array_keys($inLawGreatUnclesAunts) as $inLawGreatUncleAuntId) {
                if (in_array($targetId, $childrenOf((int) $inLawGreatUncleAuntId), true)) {
                    $isSecondCousinInLaw = true;
                    break;
                }
            }
            if ($isSecondCousinInLaw) {
                $relationLabels[$targetId] = 'Second cousin-in-law';
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

            $myGrandParentSet = $asSet($myGrandParents);
            $myStepGrandParents = [];
            foreach ($myGrandParents as $grandParentId) {
                foreach ($partnerMap[(int) $grandParentId] ?? [] as $grandParentPartnerId) {
                    $grandParentPartnerId = (int) $grandParentPartnerId;
                    if (
                        $grandParentPartnerId !== $currentMemberId
                        && !isset($myGrandParentSet[$grandParentPartnerId])
                        && !isset($myParentSet[$grandParentPartnerId])
                    ) {
                        $myStepGrandParents[$grandParentPartnerId] = true;
                    }
                }
            }
            if (isset($myStepGrandParents[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Step Grandfather', 'Step Grandmother', 'Step Grandparent');
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

            $isGrandUncleAuntSpouse = false;
            foreach (array_keys($myGrandParentSiblings) as $grandParentSiblingId) {
                if (in_array($targetId, $partnerMap[(int) $grandParentSiblingId] ?? [], true)) {
                    $isGrandUncleAuntSpouse = true;
                    break;
                }
            }
            if ($isGrandUncleAuntSpouse) {
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
                $relationLabels[$targetId] = 'First Cousin Once Removed';
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

            $myStepChildren = [];
            foreach ($partnerMap[$currentMemberId] ?? [] as $myPartnerId) {
                foreach ($childrenOf((int) $myPartnerId) as $partnerChildId) {
                    $partnerChildId = (int) $partnerChildId;
                    if (!in_array($partnerChildId, $myChildren, true)) {
                        $myStepChildren[$partnerChildId] = true;
                    }
                }
            }

            $myStepGrandChildren = [];
            foreach (array_keys($myStepChildren) as $stepChildId) {
                foreach ($childrenOf((int) $stepChildId) as $stepGrandChildId) {
                    $myStepGrandChildren[] = (int) $stepGrandChildId;
                }
            }
            if (in_array($targetId, $myStepGrandChildren, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Step Grandson', 'Step Granddaughter', 'Step Grandchild');
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

            $isGreatGrandChildSpouse = false;
            foreach ($myGreatGrandChildren as $greatGrandChildId) {
                if (in_array($targetId, $partnerMap[(int) $greatGrandChildId] ?? [], true)) {
                    $isGreatGrandChildSpouse = true;
                    break;
                }
            }
            if ($isGreatGrandChildSpouse) {
                $relationLabels[$targetId] = $genderLabel(
                    $targetId,
                    'Great Grandson-in-law',
                    'Great Granddaughter-in-law',
                    'Great Grandchild-in-law'
                );
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

            $myUnclesAuntsInLaw = [];
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                if (in_array($targetId, $partnerMap[(int) $parentSiblingId] ?? [], true)) {
                    $myUnclesAuntsInLaw[$targetId] = true;
                    break;
                }
            }

            if (isset($myUnclesAuntsInLaw[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Uncle', 'Aunt', 'Relative');
                continue;
            }

            $myCousins = [];
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                if (in_array($targetId, $childrenOf((int) $parentSiblingId), true)) {
                    $myCousins[$targetId] = true;
                    break;
                }
            }

            if (isset($myCousins[$targetId])) {
                $relationLabels[$targetId] = 'Cousin';
                continue;
            }

            $isCousinInLawByUncleAuntInLaw = false;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                foreach ($partnerMap[(int) $parentSiblingId] ?? [] as $uncleAuntInLawId) {
                    if (in_array($targetId, $childrenOf((int) $uncleAuntInLawId), true)) {
                        $isCousinInLawByUncleAuntInLaw = true;
                        break 2;
                    }
                }
            }
            if ($isCousinInLawByUncleAuntInLaw) {
                $relationLabels[$targetId] = 'Cousin-in-law';
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
                $relationLabels[$targetId] = 'Cousin-in-law';
                continue;
            }

            $isFirstCousinChild = false;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                foreach ($childrenOf((int) $parentSiblingId) as $cousinId) {
                    if (in_array($targetId, $childrenOf((int) $cousinId), true)) {
                        $isFirstCousinChild = true;
                        break 2;
                    }
                }
            }
            if ($isFirstCousinChild) {
                $relationLabels[$targetId] = 'First Cousin Once Removed';
                continue;
            }

            $myFirstCousinsOnceRemoved = $myParentsCousins;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                foreach ($childrenOf((int) $parentSiblingId) as $cousinId) {
                    foreach ($childrenOf((int) $cousinId) as $cousinChildId) {
                        $myFirstCousinsOnceRemoved[(int) $cousinChildId] = true;
                    }
                }
            }

            $isFirstCousinOnceRemovedInLaw = false;
            foreach (array_keys($myFirstCousinsOnceRemoved) as $firstCousinOnceRemovedId) {
                if (in_array($targetId, $partnerMap[(int) $firstCousinOnceRemovedId] ?? [], true)) {
                    $isFirstCousinOnceRemovedInLaw = true;
                    break;
                }
            }
            if ($isFirstCousinOnceRemovedInLaw) {
                $relationLabels[$targetId] = 'First Cousin Once Removed-in-law';
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
            if (!$isNephewNieceInLaw) {
                foreach (array_keys($myPartnerSiblings) as $partnerSiblingId) {
                    foreach ($childrenOf((int) $partnerSiblingId) as $partnerSiblingChildId) {
                        if (in_array($targetId, $partnerMap[(int) $partnerSiblingChildId] ?? [], true)) {
                            $isNephewNieceInLaw = true;
                            $isHalfNephewNieceInLaw = false;
                            break 2;
                        }
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
                foreach ($childrenOf((int) $siblingId) as $siblingChildId) {
                    if (in_array($targetId, $childrenOf((int) $siblingChildId), true)) {
                        $isGrandNephewNiece = true;
                        break 2;
                    }
                }
            }
            if (!$isGrandNephewNiece) {
                foreach (array_keys($myPartnerSiblings) as $partnerSiblingId) {
                    foreach ($childrenOf((int) $partnerSiblingId) as $partnerSiblingChildId) {
                        if (in_array($targetId, $childrenOf((int) $partnerSiblingChildId), true)) {
                            $isGrandNephewNiece = true;
                            break 2;
                        }
                    }
                }
            }
            if ($isGrandNephewNiece) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandnephew', 'Grandniece', 'Grand Relative');
                continue;
            }

            $isGrandNephewNieceInLaw = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                foreach ($childrenOf((int) $siblingId) as $siblingChildId) {
                    foreach ($childrenOf((int) $siblingChildId) as $grandSiblingChildId) {
                        if (in_array($targetId, $partnerMap[(int) $grandSiblingChildId] ?? [], true)) {
                            $isGrandNephewNieceInLaw = true;
                            break 3;
                        }
                    }
                }
            }
            if (!$isGrandNephewNieceInLaw) {
                foreach (array_keys($myPartnerSiblings) as $partnerSiblingId) {
                    foreach ($childrenOf((int) $partnerSiblingId) as $partnerSiblingChildId) {
                        foreach ($childrenOf((int) $partnerSiblingChildId) as $grandPartnerSiblingChildId) {
                            if (in_array($targetId, $partnerMap[(int) $grandPartnerSiblingChildId] ?? [], true)) {
                                $isGrandNephewNieceInLaw = true;
                                break 3;
                            }
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

        $buildTreeRoots = function (?array $allowedMemberIds = null) use ($familyMembers, $parentCount, $membersById, $childrenMap, $partnerMap, $parentMap, $childParentingModeMap) {
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
            $buildNode = function (int $memberId, array $ancestorIds = [], int $allowOutOfSetDepth = 0, int $generation = 1) use (&$buildNode, &$usedMemberIds, $membersById, $childrenMap, $partnerMap, $parentMap, $childParentingModeMap, $allowedSet) {
                if (isset($ancestorIds[$memberId]) || !isset($membersById[$memberId])) {
                    return null;
                }

                $isCurrentInAllowedSet = $allowedSet === null || isset($allowedSet[$memberId]);
                if (!$isCurrentInAllowedSet && $allowOutOfSetDepth <= 0) {
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

                    $isDirectChild = in_array($partnerId, $childrenMap[$memberId] ?? [], true);
                    $isDirectParent = in_array($partnerId, $parentMap[$memberId] ?? [], true);
                    if ($isDirectChild || $isDirectParent) {
                        // Guard against inconsistent relationship rows:
                        // someone cannot be both direct parent/child and partner.
                        continue;
                    }

                    if (!isset($usedMemberIds[$partnerId])) {
                        $usedMemberIds[$partnerId] = true;
                        $partnerMembers->push($membersById[$partnerId]);
                    }
                }
                $displayedPartnerIds = $partnerMembers
                    ->pluck('memberid')
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->values()
                    ->all();

                $children = [];
                $childrenById = [];
                $rowParentIds = array_values(array_unique(array_merge([$memberId], $displayedPartnerIds)));
                foreach ($rowParentIds as $sourceParentId) {
                    $sourceParentId = (int) $sourceParentId;
                    foreach ($childrenMap[$sourceParentId] ?? [] as $childId) {
                        $childId = (int) $childId;
                        $childNode = $buildNode($childId, $ancestorIds, 0, $generation + 1);
                        if ($childNode === null) {
                            continue;
                        }

                        $baseChildMode = (string) ($childParentingModeMap[$sourceParentId][$childId] ?? 'with_current_partner');
                        $resolvedChildMode = 'single_parent';
                        if ($baseChildMode !== 'single_parent' && !empty($displayedPartnerIds)) {
                            $resolvedChildMode = 'with_current_partner';
                        }

                        $childNode['parenting_mode'] = $resolvedChildMode;
                        $childNode['single_parent_anchor_memberid'] = $resolvedChildMode === 'single_parent'
                            ? $sourceParentId
                            : 0;

                        if (!isset($childrenById[$childId])) {
                            $childrenById[$childId] = $childNode;
                            continue;
                        }

                        $existingMode = (string) ($childrenById[$childId]['parenting_mode'] ?? 'single_parent');
                        if ($existingMode !== 'with_current_partner' && $resolvedChildMode === 'with_current_partner') {
                            $childrenById[$childId] = $childNode;
                        }
                    }
                }
                $children = array_values($childrenById);

                return [
                    'member' => $membersById[$memberId],
                    'partners' => $partnerMembers->values()->all(),
                    'children' => $children,
                    'generation' => $generation,
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

        $defaultTreeMinGeneration = -2;
        $defaultTreeMaxGeneration = 2;
        // Default view: grandparent -> parent -> me -> child -> grandchild.
        $showUpperTree = $request->boolean('show_upper_tree');
        $showLowerTree = $request->boolean('show_lower_tree');
        if ($request->boolean('show_full_tree')) {
            $showUpperTree = true;
            $showLowerTree = true;
        }
        $hasHiddenUpperTreeLevels = false;
        $hasHiddenLowerTreeLevels = false;
        $toggleUpperTreeUrl = '';
        $toggleLowerTreeUrl = '';
        $treeRoots = $buildTreeRoots();
        if (empty($treeRoots) && $familyMembers->isNotEmpty()) {
            $fallbackMemberId = $currentMemberId !== 0
                ? $currentMemberId
                : (int) ($familyMembers->first()->memberid ?? 0);

            if ($fallbackMemberId > 0) {
                $treeRoots = $buildTreeRoots([$fallbackMemberId]);
            }
        }
        $treeSummaryText = 'Showing grandparent, parent, me, children, and grandchild levels.';

        if ($currentMemberId !== 0 && isset($membersById[$currentMemberId])) {
            $generationByMemberId = [$currentMemberId => 0];
            $generationQueue = new \SplQueue();
            $generationQueue->enqueue($currentMemberId);

            while (!$generationQueue->isEmpty()) {
                $memberId = (int) $generationQueue->dequeue();
                $baseGeneration = (int) ($generationByMemberId[$memberId] ?? 0);

                $neighborLevels = [];
                foreach ($parentMap[$memberId] ?? [] as $parentId) {
                    $neighborLevels[(int) $parentId] = $baseGeneration - 1;
                }
                foreach ($childrenMap[$memberId] ?? [] as $childId) {
                    $neighborLevels[(int) $childId] = $baseGeneration + 1;
                }
                foreach ($partnerMap[$memberId] ?? [] as $partnerId) {
                    $neighborLevels[(int) $partnerId] = $baseGeneration;
                }

                if ($memberId === $currentMemberId) {
                    foreach ($parentMap[$memberId] ?? [] as $parentId) {
                        foreach ($childrenMap[(int) $parentId] ?? [] as $siblingId) {
                            $siblingId = (int) $siblingId;
                            if ($siblingId <= 0 || $siblingId === $currentMemberId) {
                                continue;
                            }

                            $neighborLevels[$siblingId] = $baseGeneration;
                        }
                    }
                }

                foreach ($neighborLevels as $neighborId => $neighborGeneration) {
                    $neighborId = (int) $neighborId;
                    $neighborGeneration = (int) $neighborGeneration;

                    if (!isset($membersById[$neighborId])) {
                        continue;
                    }

                    $shouldUpdateGeneration = !isset($generationByMemberId[$neighborId]);
                    if (!$shouldUpdateGeneration) {
                        $existingGeneration = (int) $generationByMemberId[$neighborId];
                        $neighborAbsoluteGeneration = abs($neighborGeneration);
                        $existingAbsoluteGeneration = abs($existingGeneration);

                        if ($neighborAbsoluteGeneration < $existingAbsoluteGeneration) {
                            $shouldUpdateGeneration = true;
                        } elseif (
                            $neighborAbsoluteGeneration === $existingAbsoluteGeneration
                            && $neighborGeneration < $existingGeneration
                        ) {
                            $shouldUpdateGeneration = true;
                        }
                    }

                    if ($shouldUpdateGeneration) {
                        $generationByMemberId[$neighborId] = $neighborGeneration;
                        $generationQueue->enqueue($neighborId);
                    }
                }
            }

            // Detect hidden ancestor/descendant levels from direct bloodline traversal.
            // This avoids missing toggle buttons when relation graph shortest-path generation
            // gets compressed by partner links.
            $ancestorDistanceByMemberId = [$currentMemberId => 0];
            $ancestorQueue = new \SplQueue();
            $ancestorQueue->enqueue($currentMemberId);
            while (!$ancestorQueue->isEmpty()) {
                $memberId = (int) $ancestorQueue->dequeue();
                $baseDistance = (int) ($ancestorDistanceByMemberId[$memberId] ?? 0);

                foreach ($parentMap[$memberId] ?? [] as $parentId) {
                    $parentId = (int) $parentId;
                    if (!isset($membersById[$parentId])) {
                        continue;
                    }

                    $nextDistance = $baseDistance + 1;
                    if (
                        !isset($ancestorDistanceByMemberId[$parentId])
                        || $nextDistance < (int) $ancestorDistanceByMemberId[$parentId]
                    ) {
                        $ancestorDistanceByMemberId[$parentId] = $nextDistance;
                        $ancestorQueue->enqueue($parentId);
                    }
                }
            }

            $descendantDistanceByMemberId = [$currentMemberId => 0];
            $descendantQueue = new \SplQueue();
            $descendantQueue->enqueue($currentMemberId);
            while (!$descendantQueue->isEmpty()) {
                $memberId = (int) $descendantQueue->dequeue();
                $baseDistance = (int) ($descendantDistanceByMemberId[$memberId] ?? 0);

                foreach ($childrenMap[$memberId] ?? [] as $childId) {
                    $childId = (int) $childId;
                    if (!isset($membersById[$childId])) {
                        continue;
                    }

                    $nextDistance = $baseDistance + 1;
                    if (
                        !isset($descendantDistanceByMemberId[$childId])
                        || $nextDistance < (int) $descendantDistanceByMemberId[$childId]
                    ) {
                        $descendantDistanceByMemberId[$childId] = $nextDistance;
                        $descendantQueue->enqueue($childId);
                    }
                }
            }

            $maxVisibleAncestorDistance = abs((int) $defaultTreeMinGeneration);
            $maxVisibleDescendantDistance = (int) $defaultTreeMaxGeneration;
            $hasHiddenUpperTreeLevels = $hasHiddenUpperTreeLevels || count(array_filter(
                $ancestorDistanceByMemberId,
                function ($distance) use ($maxVisibleAncestorDistance) {
                    return (int) $distance > $maxVisibleAncestorDistance;
                }
            )) > 0;
            $hasHiddenLowerTreeLevels = $hasHiddenLowerTreeLevels || count(array_filter(
                $descendantDistanceByMemberId,
                function ($distance) use ($maxVisibleDescendantDistance) {
                    return (int) $distance > $maxVisibleDescendantDistance;
                }
            )) > 0;

            // Fallback from raw relation graph (without level/user filtering).
            // Treat partner links as 0-generation hops so hidden levels are still detected
            // even when child links are attached to the spouse branch in legacy data.
            $resolveRawPartnerCluster = function (array $seedMemberIds) use ($rawPartnerMap): array {
                $cluster = [];
                $queue = new \SplQueue();
                foreach ($seedMemberIds as $seedMemberId) {
                    $seedMemberId = (int) $seedMemberId;
                    if ($seedMemberId <= 0 || isset($cluster[$seedMemberId])) {
                        continue;
                    }

                    $cluster[$seedMemberId] = true;
                    $queue->enqueue($seedMemberId);
                }

                while (!$queue->isEmpty()) {
                    $memberId = (int) $queue->dequeue();
                    foreach ($rawPartnerMap[$memberId] ?? [] as $partnerId) {
                        $partnerId = (int) $partnerId;
                        if ($partnerId <= 0 || isset($cluster[$partnerId])) {
                            continue;
                        }

                        $cluster[$partnerId] = true;
                        $queue->enqueue($partnerId);
                    }
                }

                return array_keys($cluster);
            };

            $buildRawBloodlineDistanceMap = function (bool $isAncestor) use ($currentMemberId, $rawChildrenMap, $rawParentMap, $resolveRawPartnerCluster): array {
                $distanceByMemberId = [$currentMemberId => 0];
                $generation = 0;
                $frontierMemberIds = $resolveRawPartnerCluster([$currentMemberId]);
                $visitedByGeneration = [];
                foreach ($frontierMemberIds as $frontierMemberId) {
                    $frontierMemberId = (int) $frontierMemberId;
                    $visitedByGeneration[$frontierMemberId] = 0;
                    if (!isset($distanceByMemberId[$frontierMemberId])) {
                        $distanceByMemberId[$frontierMemberId] = 0;
                    }
                }

                while (!empty($frontierMemberIds)) {
                    $nextSeedSet = [];
                    foreach ($frontierMemberIds as $memberId) {
                        $memberId = (int) $memberId;
                        $bloodlineNeighborIds = $isAncestor
                            ? ($rawParentMap[$memberId] ?? [])
                            : ($rawChildrenMap[$memberId] ?? []);

                        foreach ($bloodlineNeighborIds as $neighborId) {
                            $neighborId = (int) $neighborId;
                            if ($neighborId <= 0) {
                                continue;
                            }

                            $neighborDistance = $generation + 1;
                            if (
                                !isset($distanceByMemberId[$neighborId])
                                || $neighborDistance < (int) $distanceByMemberId[$neighborId]
                            ) {
                                $distanceByMemberId[$neighborId] = $neighborDistance;
                            }
                            $nextSeedSet[$neighborId] = true;
                        }
                    }

                    if (empty($nextSeedSet)) {
                        break;
                    }

                    $nextClusterMemberIds = $resolveRawPartnerCluster(array_keys($nextSeedSet));
                    $nextFrontierMemberIds = [];
                    foreach ($nextClusterMemberIds as $clusterMemberId) {
                        $clusterMemberId = (int) $clusterMemberId;
                        $clusterDistance = $generation + 1;
                        if (
                            isset($visitedByGeneration[$clusterMemberId])
                            && (int) $visitedByGeneration[$clusterMemberId] <= $clusterDistance
                        ) {
                            continue;
                        }

                        $visitedByGeneration[$clusterMemberId] = $clusterDistance;
                        $nextFrontierMemberIds[] = $clusterMemberId;
                        if (
                            !isset($distanceByMemberId[$clusterMemberId])
                            || $clusterDistance < (int) $distanceByMemberId[$clusterMemberId]
                        ) {
                            $distanceByMemberId[$clusterMemberId] = $clusterDistance;
                        }
                    }

                    $generation += 1;
                    $frontierMemberIds = $nextFrontierMemberIds;
                }

                return $distanceByMemberId;
            };

            $rawAncestorDistanceByMemberId = $buildRawBloodlineDistanceMap(true);
            $rawDescendantDistanceByMemberId = $buildRawBloodlineDistanceMap(false);
            $hasHiddenUpperTreeLevels = $hasHiddenUpperTreeLevels || count(array_filter(
                $rawAncestorDistanceByMemberId,
                function ($distance) use ($maxVisibleAncestorDistance) {
                    return (int) $distance > $maxVisibleAncestorDistance;
                }
            )) > 0;
            $hasHiddenLowerTreeLevels = $hasHiddenLowerTreeLevels || count(array_filter(
                $rawDescendantDistanceByMemberId,
                function ($distance) use ($maxVisibleDescendantDistance) {
                    return (int) $distance > $maxVisibleDescendantDistance;
                }
            )) > 0;

            $directAncestorMemberIds = [$currentMemberId => true];
            $ancestorTraversalQueue = new \SplQueue();
            $ancestorTraversalQueue->enqueue($currentMemberId);
            while (!$ancestorTraversalQueue->isEmpty()) {
                $memberId = (int) $ancestorTraversalQueue->dequeue();
                foreach ($parentMap[$memberId] ?? [] as $parentId) {
                    $parentId = (int) $parentId;
                    if ($parentId <= 0 || !isset($membersById[$parentId])) {
                        continue;
                    }

                    if (!isset($directAncestorMemberIds[$parentId])) {
                        $directAncestorMemberIds[$parentId] = true;
                        $ancestorTraversalQueue->enqueue($parentId);
                    }
                }
            }

            $defaultVisibleMemberIds = [];
            $markVisibleMemberId = function (int $memberId) use (&$defaultVisibleMemberIds, $membersById, $partnerMap): void {
                if ($memberId <= 0 || !isset($membersById[$memberId])) {
                    return;
                }

                $defaultVisibleMemberIds[$memberId] = true;

                foreach ($partnerMap[$memberId] ?? [] as $partnerId) {
                    $partnerId = (int) $partnerId;
                    if ($partnerId > 0 && isset($membersById[$partnerId])) {
                        $defaultVisibleMemberIds[$partnerId] = true;
                    }
                }
            };

            foreach (array_keys($directAncestorMemberIds) as $ancestorMemberId) {
                $markVisibleMemberId((int) $ancestorMemberId);
            }

            $sideBranchMemberIds = [];
            $siblingMemberIds = [];
            foreach (array_keys($parentMap[$currentMemberId] ?? []) as $parentId) {
                foreach ($childrenMap[(int) $parentId] ?? [] as $siblingId) {
                    $siblingId = (int) $siblingId;
                    if ($siblingId <= 0 || $siblingId === $currentMemberId || !isset($membersById[$siblingId])) {
                        continue;
                    }

                    $siblingMemberIds[$siblingId] = true;
                }
            }

            $siblingBranchQueue = new \SplQueue();
            foreach (array_keys($siblingMemberIds) as $siblingMemberId) {
                $markVisibleMemberId((int) $siblingMemberId);
                $sideBranchMemberIds[(int) $siblingMemberId] = true;
                $siblingBranchQueue->enqueue((int) $siblingMemberId);
            }

            while (!$siblingBranchQueue->isEmpty()) {
                $branchMemberId = (int) $siblingBranchQueue->dequeue();
                foreach ($partnerMap[$branchMemberId] ?? [] as $partnerMemberId) {
                    $partnerMemberId = (int) $partnerMemberId;
                    if ($partnerMemberId <= 0 || !isset($membersById[$partnerMemberId])) {
                        continue;
                    }

                    if (!isset($sideBranchMemberIds[$partnerMemberId])) {
                        $sideBranchMemberIds[$partnerMemberId] = true;
                        $markVisibleMemberId($partnerMemberId);
                        $siblingBranchQueue->enqueue($partnerMemberId);
                    }
                }

                foreach ($childrenMap[$branchMemberId] ?? [] as $childMemberId) {
                    $childMemberId = (int) $childMemberId;
                    if ($childMemberId <= 0 || !isset($membersById[$childMemberId])) {
                        continue;
                    }

                    if (!isset($sideBranchMemberIds[$childMemberId])) {
                        $sideBranchMemberIds[$childMemberId] = true;
                        $markVisibleMemberId($childMemberId);
                        $siblingBranchQueue->enqueue($childMemberId);
                    }
                }
            }

            foreach ($descendantDistanceByMemberId as $memberId => $distance) {
                if ((int) $distance > $maxVisibleDescendantDistance) {
                    continue;
                }

                $markVisibleMemberId((int) $memberId);
            }

            $defaultTreeMemberIds = [];
            $hasDisconnectedHiddenMembers = false;
            $visibleTreeMinGeneration = $showUpperTree ? PHP_INT_MIN : $defaultTreeMinGeneration;
            $visibleTreeMaxGeneration = $showLowerTree ? PHP_INT_MAX : $defaultTreeMaxGeneration;
            foreach ($familyMembers as $member) {
                $memberId = (int) $member->memberid;
                if (!isset($generationByMemberId[$memberId])) {
                    $hasDisconnectedHiddenMembers = true;
                    continue;
                }

                $generationLevel = (int) $generationByMemberId[$memberId];
                if ($generationLevel < $defaultTreeMinGeneration) {
                    $hasHiddenUpperTreeLevels = true;
                }

                if ($generationLevel > $defaultTreeMaxGeneration) {
                    $hasHiddenLowerTreeLevels = true;
                }

                if (
                    $generationLevel < $visibleTreeMinGeneration
                    || $generationLevel > $visibleTreeMaxGeneration
                ) {
                    if (!isset($sideBranchMemberIds[$memberId])) {
                        continue;
                    }

                    $defaultTreeMemberIds[] = $memberId;
                    continue;
                }

                $defaultTreeMemberIds[] = $memberId;
            }

            if ($hasDisconnectedHiddenMembers) {
                $hasHiddenUpperTreeLevels = true;
                $hasHiddenLowerTreeLevels = true;
            }

            if ($showUpperTree && $showLowerTree) {
                $treeRoots = $buildTreeRoots();
            } elseif (!empty($defaultTreeMemberIds)) {
                $treeRoots = $buildTreeRoots($defaultTreeMemberIds);
            } else {
                $treeRoots = $buildTreeRoots();
            }

            if ($showUpperTree && $showLowerTree) {
                $treeSummaryText = 'Showing full family tree.';
            } elseif ($showUpperTree) {
                $treeSummaryText = 'Showing extended ancestor levels and up to grandchild descendants.';
            } elseif ($showLowerTree) {
                $treeSummaryText = 'Showing up to grandparent ancestors and extended descendant levels.';
            } else {
                $treeSummaryText = 'Showing grandparent, parent, me, children, and grandchild levels.';
            }

            $showTopToggleButton = $hasHiddenUpperTreeLevels || $showUpperTree;
            $showBottomToggleButton = $hasHiddenLowerTreeLevels || $showLowerTree;
            if ($showTopToggleButton) {
                $toggleUpperTreeUrl = $request->fullUrlWithQuery([
                    'show_upper_tree' => $showUpperTree ? 0 : 1,
                    'show_lower_tree' => $showLowerTree ? 1 : 0,
                ]);
            }
            $toggleLowerTreeUrl = $request->fullUrlWithQuery([
                'show_upper_tree' => $showUpperTree ? 1 : 0,
                'show_lower_tree' => $showLowerTree ? 0 : 1,
            ]);

            if (!$showUpperTree && !$showLowerTree && ($showTopToggleButton || $showBottomToggleButton)) {
                $treeSummaryText .= ' Use View more to reveal hidden ancestor or descendant levels.';
            }
        }

        $highlightParentMemberId = (int) ($highlightParentMemberId ?? 0);
        $highlightParentForName = (string) ($highlightParentForName ?? '');
        $treeRenderCacheVersion = (string) Cache::store('file')->get('family_tree:render_version:v1', '1');
        $treeRootsSignature = md5(json_encode(array_map(function ($rootNode) {
            return [
                'memberid' => (int) ($rootNode['member']->memberid ?? 0),
                'generation' => (int) ($rootNode['generation'] ?? 0),
                'partner_count' => count((array) ($rootNode['partners'] ?? [])),
                'child_count' => count((array) ($rootNode['children'] ?? [])),
            ];
        }, $treeRoots), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $treeHtml = null;
        $treeRenderCacheKey = sprintf(
            'family_tree:render:%s:%d:%d:%d:%d:%d:%s:%s',
            $treeRenderCacheVersion,
            $currentUserId,
            $showUpperTree ? 1 : 0,
            $showLowerTree ? 1 : 0,
            (int) $familyMembers->count(),
            (int) ($highlightParentMemberId ?? 0),
            md5((string) $highlightParentForName . '|' . (string) $toggleUpperTreeUrl . '|' . (string) $toggleLowerTreeUrl),
            $treeRootsSignature
        );
        $treeHtml = Cache::store('file')->remember($treeRenderCacheKey, now()->addSeconds(30), function () use (
            $familyMembers,
            $treeRoots,
            $currentMember,
            $relationLabels,
            $canCurrentMemberManageDivorce,
            $canDeletePartnerMap,
            $canDeleteChildMap,
            $canUpdateLifeStatusMap,
            $canEditProfileMap,
            $childParentingModeMap,
            $highlightParentMemberId,
            $highlightParentForName,
            $showUpperTree,
            $showLowerTree,
            $hasHiddenUpperTreeLevels,
            $hasHiddenLowerTreeLevels,
            $toggleUpperTreeUrl,
            $toggleLowerTreeUrl
        ) {
            return view('all.partials.family-tree-content', [
                'members' => $familyMembers,
                'renderTreeRoots' => $treeRoots,
                'firstMember' => $currentMember ?: $familyMembers->first(),
                'relationMap' => $relationLabels,
                'canCurrentMemberManageDivorce' => $canCurrentMemberManageDivorce,
                'canDeletePartnerMap' => $canDeletePartnerMap,
                'canDeleteChildMap' => $canDeleteChildMap,
                'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                'canEditProfileMap' => $canEditProfileMap,
                'childParentingModeMap' => $childParentingModeMap,
                'highlightParentMemberId' => $highlightParentMemberId,
                'highlightParentForName' => $highlightParentForName,
                'showUpperTree' => $showUpperTree,
                'showLowerTree' => $showLowerTree,
                'hasHiddenUpperTreeLevels' => $hasHiddenUpperTreeLevels,
                'hasHiddenLowerTreeLevels' => $hasHiddenLowerTreeLevels,
                'toggleUpperTreeUrl' => $toggleUpperTreeUrl,
                'toggleLowerTreeUrl' => $toggleLowerTreeUrl,
            ])->render();
        });

        if (($request->ajax() || $request->expectsJson()) && $request->boolean('tree_section')) {
            return response()->json([
                'tree_html' => $treeHtml,
                'show_upper_tree' => $showUpperTree,
                'show_lower_tree' => $showLowerTree,
                'has_hidden_upper_tree_levels' => $hasHiddenUpperTreeLevels,
                'has_hidden_lower_tree_levels' => $hasHiddenLowerTreeLevels,
                'toggle_upper_tree_url' => $toggleUpperTreeUrl,
                'toggle_lower_tree_url' => $toggleLowerTreeUrl,
                'summary_text' => $treeSummaryText,
            ]);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentFamilyProfile = Cache::store('file')->remember('family_tree:current_profile:' . $currentUserId, now()->addSeconds(30), function () use ($currentUserId) {
            return DB::table('family_member')
                ->where('userid', $currentUserId)
                ->select('userid', 'job', 'address', 'education_status')
                ->first();
        });
        $familyTimelineByMember = [];
        if (Schema::hasTable('family_timelines')) {
            $timelineRows = DB::table('family_timelines as ft')
                ->leftJoin('family_member as fm', 'fm.memberid', '=', 'ft.family_member_id')
                ->leftJoin('user as member_user', 'member_user.userid', '=', 'ft.user_id')
                ->select(
                    'ft.id',
                    'ft.family_member_id',
                    'ft.title',
                    'ft.description',
                    'ft.event_date',
                    'ft.event_year',
                    'ft.category',
                    'ft.location',
                    'ft.attachment_path',
                    'ft.created_at',
                    'fm.name as family_member_name',
                    'member_user.username as member_username'
                )
                ->orderByRaw('COALESCE(ft.event_date, STR_TO_DATE(CONCAT(ft.event_year, "-01-01"), "%Y-%m-%d"), ft.created_at) DESC')
                ->orderByDesc('ft.id')
                ->get();

            foreach ($timelineRows as $row) {
                $memberId = (int) ($row->family_member_id ?? 0);
                if ($memberId <= 0) {
                    continue;
                }

                $attachmentPath = trim((string) ($row->attachment_path ?? ''));
                $entry = [
                    'id' => (int) ($row->id ?? 0),
                    'family_member_id' => $memberId,
                    'title' => trim((string) ($row->title ?? '')),
                    'description' => trim((string) ($row->description ?? '')),
                    'event_date' => trim((string) ($row->event_date ?? '')),
                    'event_year' => trim((string) ($row->event_year ?? '')),
                    'category' => trim((string) ($row->category ?? '')),
                    'category_label' => self::TIMELINE_CATEGORY_OPTIONS[strtolower(trim((string) ($row->category ?? '')))] ?? ucfirst((string) ($row->category ?? 'Other')),
                    'location' => trim((string) ($row->location ?? '')),
                    'attachment_url' => $attachmentPath !== '' ? $this->resolvePublicFileUrl($attachmentPath) : '',
                    'display_date' => $this->formatTimelineDateLabel($row->event_date ?? null, $row->event_year ?? null),
                ];

                $familyTimelineByMember[$memberId] = $familyTimelineByMember[$memberId] ?? [];
                if (count($familyTimelineByMember[$memberId]) < 5) {
                    $familyTimelineByMember[$memberId][] = $entry;
                }
            }
        }

        return view('all.home', compact(
            'systemSettings',
            'familyMembers',
            'currentFamilyProfile',
            'treeHtml',
            'treeRoots',
            'showUpperTree',
            'showLowerTree',
            'hasHiddenUpperTreeLevels',
            'hasHiddenLowerTreeLevels',
            'toggleUpperTreeUrl',
            'toggleLowerTreeUrl',
            'treeSummaryText',
            'relationLabels',
            'currentMemberHasPartner',
            'canCurrentMemberManageDivorce',
            'canDeletePartnerMap',
            'canDeleteChildMap',
            'canUpdateLifeStatusMap',
            'canEditProfileMap',
            'childParentingModeMap'
        ) + [
            'pageClass' => 'page-family-tree',
            'familyTimelineByMember' => $familyTimelineByMember,
        ]);
    }

    public function updateFamilyProfile(Request $request)
    {
        $maxSocialMediaPerMember = self::MAX_SOCIAL_MEDIA_PER_MEMBER;
        $redirectTo = (string) $request->input('redirect_to', '/');
        if (!in_array($redirectTo, ['/', '/account'], true)) {
            $redirectTo = '/';
        }

        $requestedPicture = '';
        if ($request->hasFile('picture')) {
            $originalPictureName = trim((string) $request->file('picture')->getClientOriginalName());
            $requestedPicture = $originalPictureName !== ''
                ? '[uploaded file] ' . $originalPictureName
                : '[uploaded file]';
        }
        $requestedSocialRowIds = collect((array) $request->input('social_row_ids', []))
            ->map(function ($value) {
                return (int) $value;
            })
            ->values()
            ->all();
        $requestedSocialRowLinks = collect((array) $request->input('social_row_links', []))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->values()
            ->all();
        $requestedSocialRows = [];
        $requestedSocialRowsCount = max(count($requestedSocialRowIds), count($requestedSocialRowLinks));
        for ($requestedSocialRowIndex = 0; $requestedSocialRowIndex < $requestedSocialRowsCount; $requestedSocialRowIndex++) {
            $requestedSocialId = (int) ($requestedSocialRowIds[$requestedSocialRowIndex] ?? 0);
            $requestedSocialLink = trim((string) ($requestedSocialRowLinks[$requestedSocialRowIndex] ?? ''));

            if ($requestedSocialId <= 0 && $requestedSocialLink === '') {
                continue;
            }

            $requestedSocialRows[] = [
                'socialid' => $requestedSocialId,
                'link' => $requestedSocialLink,
            ];
        }
        $requestedNewSocialNames = collect((array) $request->input('new_social_names', []))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->values()
            ->all();
        $requestedNewSocialLinks = collect((array) $request->input('new_social_links', []))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->values()
            ->all();
        $requestedNewSocialRows = [];
        $requestedNewSocialCount = max(count($requestedNewSocialNames), count($requestedNewSocialLinks));
        for ($requestedSocialIndex = 0; $requestedSocialIndex < $requestedNewSocialCount; $requestedSocialIndex++) {
            $requestedSocialName = trim((string) ($requestedNewSocialNames[$requestedSocialIndex] ?? ''));
            $requestedSocialLink = trim((string) ($requestedNewSocialLinks[$requestedSocialIndex] ?? ''));

            if ($requestedSocialName === '' && $requestedSocialLink === '') {
                continue;
            }

            $requestedNewSocialRows[] = [
                'name' => $requestedSocialName,
                'link' => $requestedSocialLink,
            ];
        }

        $requestedValues = [
            'name' => trim((string) $request->input('name', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phonenumber' => trim((string) $request->input('phonenumber', '')),
            'bloodtype' => strtoupper(trim((string) $request->input('bloodtype', ''))),
            'job' => trim((string) $request->input('job', '')),
            'address' => trim((string) $request->input('address', '')),
            'education_status' => trim((string) $request->input('education_status', '')),
            'social_ids' => collect((array) $request->input('social_ids', []))
                ->map(function ($value) {
                    return (int) $value;
                })
                ->filter(function ($value) {
                    return $value > 0;
                })
                ->values()
                ->all(),
            'social_links' => collect((array) $request->input('social_links', []))
                ->mapWithKeys(function ($value, $key) {
                    $socialId = (int) $key;
                    if ($socialId <= 0) {
                        return [];
                    }

                    return [$socialId => trim((string) $value)];
                })
                ->all(),
            'social_rows' => $requestedSocialRows,
            'new_social_media' => trim((string) $request->input('new_social_media', '')),
            'new_social_rows' => $requestedNewSocialRows,
        ];
        if ($requestedPicture !== '') {
            $requestedValues['picture'] = $requestedPicture;
        }

        if (!$request->session()->has('authenticated_user')) {
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                'Unauthenticated request.',
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $isAdminOrSuperadmin = in_array($currentRoleId, [1, 2], true);

        if ($currentLevelId !== 2 && !$isAdminOrSuperadmin) {
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                'Unauthorized level. Only family members, admin, or superadmin can update this profile.',
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Only family members, admin, or superadmin can update this profile.'], 403);
            }

            return redirect($redirectTo)->with('error', 'Only family members, admin, or superadmin can update this profile.');
        }

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember && !$isAdminOrSuperadmin) {
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                'Family profile not found.',
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Family profile not found.'], 404);
            }

            return redirect($redirectTo)->with('error', 'Family profile not found.');
        }

        $actorMemberId = (int) ($familyMember->memberid ?? 0);
        $targetMemberIdInput = (int) $request->input('memberid', $actorMemberId);
        if ($targetMemberIdInput <= 0) {
            $targetMemberIdInput = $actorMemberId;
        }

        $editableFamilyMember = $familyMember;
        $isEditingOwnProfile = true;
        if ($targetMemberIdInput !== $actorMemberId) {
            $editableFamilyMember = DB::table('family_member')
                ->where('memberid', $targetMemberIdInput)
                ->first();

            if (!$editableFamilyMember) {
                $message = 'Selected family profile was not found.';
                $this->notifyDiscordEditFailure(
                    $request,
                    'family.edit_profile',
                    $message,
                    [],
                    $requestedValues
                );

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['message' => $message], 404);
                }

                return redirect($redirectTo)->with('error', $message);
            }

            if (!$isAdminOrSuperadmin && !$this->canEditFamilyProfile($actorMemberId, $editableFamilyMember)) {
                $message = 'You can only edit your own profile, partner, or child.';
                $this->notifyDiscordEditFailure(
                    $request,
                    'family.edit_profile',
                    $message,
                    [],
                    $requestedValues
                );

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json(['message' => $message], 403);
                }

                return redirect($redirectTo)->with('error', $message);
            }

            $isEditingOwnProfile = false;
        }

        if ($isAdminOrSuperadmin && !$editableFamilyMember) {
            $message = 'Selected family profile was not found.';
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                $message,
                [],
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $message], 404);
            }

            return redirect($redirectTo)->with('error', $message);
        }

        $targetMemberId = (int) ($editableFamilyMember->memberid ?? 0);
        $targetUserId = (int) ($editableFamilyMember->userid ?? 0);
        $canParentEditMinorContact = $isAdminOrSuperadmin || !$isEditingOwnProfile;
        if (!$isEditingOwnProfile && $redirectTo === '/account') {
            $redirectTo = '/account?memberid=' . $targetMemberId;
        }

        $oldName = trim((string) ($editableFamilyMember->name ?? ''));
        $oldEmail = strtolower(trim((string) ($editableFamilyMember->email ?? '')));
        $oldPhoneNumber = trim((string) ($editableFamilyMember->phonenumber ?? ''));
        $oldBloodType = strtoupper(trim((string) ($editableFamilyMember->bloodtype ?? '')));
        $oldJob = trim((string) ($editableFamilyMember->job ?? ''));
        $oldAddress = trim((string) ($editableFamilyMember->address ?? ''));
        $oldEducationStatus = trim((string) ($editableFamilyMember->education_status ?? ''));
        $oldPicture = trim((string) ($editableFamilyMember->picture ?? ''));
        $formatSocialMediaLabel = function ($socialName, $socialLink) {
            $trimmedName = trim((string) $socialName);
            if ($trimmedName === '') {
                return '';
            }

            $trimmedLink = trim((string) $socialLink);
            return $trimmedLink !== ''
                ? $trimmedName . ' (' . $trimmedLink . ')'
                : $trimmedName;
        };

        $oldOwnSocialRows = DB::table('ownsocial as os')
            ->leftJoin('socialmedia as sm', 'sm.socialid', '=', 'os.socialid')
            ->where('os.memberid', $targetMemberId)
            ->select('os.socialid', 'os.link', 'sm.socialname')
            ->orderBy('sm.socialname')
            ->get();

        $oldSocialMediaNames = $oldOwnSocialRows
            ->map(function ($row) use ($formatSocialMediaLabel) {
                return $formatSocialMediaLabel($row->socialname ?? '', $row->link ?? '');
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values()
            ->all();

        $oldSocialMediaLabel = implode(', ', $oldSocialMediaNames);
        $oldValues = [
            'name' => $oldName,
            'email' => $oldEmail,
            'phonenumber' => $oldPhoneNumber,
            'bloodtype' => $oldBloodType,
            'job' => $oldJob,
            'address' => $oldAddress,
            'education_status' => $oldEducationStatus,
            'social_media' => $oldSocialMediaLabel,
            'picture' => $oldPicture,
        ];

        $request->merge([
            'bloodtype' => strtoupper(trim((string) $request->input('bloodtype', ''))),
        ]);

        $validator = Validator::make($request->all(), [
            'memberid' => ['nullable', 'integer', 'exists:family_member,memberid'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'bloodtype' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'job' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'education_status' => ['nullable', 'string', 'max:255'],
            'social_ids' => ['nullable', 'array', 'max:' . $maxSocialMediaPerMember],
            'social_ids.*' => ['integer', 'exists:socialmedia,socialid'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'string', 'max:255'],
            'social_row_ids' => ['nullable', 'array', 'max:' . $maxSocialMediaPerMember],
            'social_row_ids.*' => ['nullable', 'integer', 'exists:socialmedia,socialid'],
            'social_row_links' => ['nullable', 'array'],
            'social_row_links.*' => ['nullable', 'string', 'max:255'],
            'new_social_names' => ['nullable', 'array'],
            'new_social_names.*' => ['nullable', 'string', 'max:255'],
            'new_social_links' => ['nullable', 'array'],
            'new_social_links.*' => ['nullable', 'string', 'max:255'],
            'new_social_media' => ['nullable', 'string', 'max:255'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'picture_face_verified' => ['nullable', 'in:0,1'],
        ], [
            'memberid.integer' => 'Selected member is invalid.',
            'memberid.exists' => 'Selected member is not found.',
            'name.max' => 'Name max length is 255 characters.',
            'email.email' => 'Email format is invalid.',
            'email.max' => 'Email max length is 255 characters.',
            'phonenumber.max' => 'Phone number max length is 255 characters.',
            'bloodtype.in' => 'Blood type must be one of: A+, A-, B+, B-, AB+, AB-, O+, or O-.',
            'job.max' => 'Job max length is 255 characters.',
            'address.max' => 'Address max length is 255 characters.',
            'education_status.max' => 'Education max length is 255 characters.',
            'social_ids.array' => 'Social media selection is invalid.',
            'social_ids.max' => 'Maximum ' . $maxSocialMediaPerMember . ' social media accounts are allowed.',
            'social_ids.*.integer' => 'Social media selection is invalid.',
            'social_ids.*.exists' => 'Selected social media is not available.',
            'social_links.array' => 'Social media link format is invalid.',
            'social_links.*.max' => 'Social media link max length is 255 characters.',
            'social_row_ids.array' => 'Social media rows format is invalid.',
            'social_row_ids.max' => 'Maximum ' . $maxSocialMediaPerMember . ' social media accounts are allowed.',
            'social_row_ids.*.integer' => 'Social media selection is invalid.',
            'social_row_ids.*.exists' => 'Selected social media is not available.',
            'social_row_links.array' => 'Social media link rows format is invalid.',
            'social_row_links.*.max' => 'Social media link max length is 255 characters.',
            'new_social_names.array' => 'New social media format is invalid.',
            'new_social_names.*.max' => 'New social media name max length is 255 characters.',
            'new_social_links.array' => 'New social media link format is invalid.',
            'new_social_links.*.max' => 'New social media link max length is 255 characters.',
            'new_social_media.max' => 'New social media max length is 255 characters.',
            'picture.image' => 'Profile picture must be an image file.',
            'picture.mimes' => 'Profile picture must be jpg, jpeg, png, webp, or gif.',
            'picture.max' => 'Profile picture max size is 2MB.',
            'picture_face_verified.in' => 'Profile picture must contain a clear human face.',
        ]);
        $validator->after(function ($validator) use ($request, $maxSocialMediaPerMember) {
            if ($request->hasFile('picture')) {
                $faceVerified = (string) $request->input('picture_face_verified', '0');
                if ($faceVerified !== '1') {
                    $validator->errors()->add(
                        'picture',
                        'Profile picture must contain a clear human face.'
                    );
                }
            }

            $socialRowIds = collect((array) $request->input('social_row_ids', []))
                ->map(function ($value) {
                    return (int) $value;
                })
                ->values()
                ->all();
            $socialRowLinks = collect((array) $request->input('social_row_links', []))
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->values()
                ->all();
            $socialRowCount = max(count($socialRowIds), count($socialRowLinks));

            for ($socialRowIndex = 0; $socialRowIndex < $socialRowCount; $socialRowIndex++) {
                $socialRowId = (int) ($socialRowIds[$socialRowIndex] ?? 0);
                if ($socialRowId <= 0) {
                    continue;
                }

                $socialRowLink = trim((string) ($socialRowLinks[$socialRowIndex] ?? ''));
                if ($socialRowLink === '') {
                    $validator->errors()->add(
                        'social_row_links.' . $socialRowIndex,
                        'Profile link is required for selected social media.'
                    );
                }
            }

            $legacySelectedSocialIds = collect((array) $request->input('social_ids', []))
                ->map(function ($value) {
                    return (int) $value;
                })
                ->filter(function ($value) {
                    return $value > 0;
                })
                ->unique()
                ->values()
                ->all();
            $legacySocialLinks = (array) $request->input('social_links', []);
            $selectedSocialIdsForValidation = collect($socialRowIds)
                ->merge($legacySelectedSocialIds)
                ->map(function ($value) {
                    return (int) $value;
                })
                ->filter(function ($value) {
                    return $value > 0;
                })
                ->unique()
                ->values()
                ->all();
            if (count($selectedSocialIdsForValidation) > $maxSocialMediaPerMember) {
                $validator->errors()->add(
                    'social_row_ids',
                    'Maximum ' . $maxSocialMediaPerMember . ' social media accounts are allowed.'
                );
            }
            $socialMediaMetaById = $this->getSocialMediaMetaById($selectedSocialIdsForValidation);

            foreach ($legacySelectedSocialIds as $legacySocialId) {
                $legacySocialLink = trim((string) ($legacySocialLinks[$legacySocialId] ?? ''));
                if ($legacySocialLink === '') {
                    $validator->errors()->add(
                        'social_links.' . $legacySocialId,
                        'Profile link is required for selected social media.'
                    );
                    continue;
                }

                $socialLinkError = $this->getSocialMediaLinkValidationMessage(
                    $legacySocialId,
                    $legacySocialLink,
                    $socialMediaMetaById
                );
                if ($socialLinkError !== null) {
                    $validator->errors()->add(
                        'social_links.' . $legacySocialId,
                        $socialLinkError
                    );
                }
            }

            for ($socialRowIndex = 0; $socialRowIndex < $socialRowCount; $socialRowIndex++) {
                $socialRowId = (int) ($socialRowIds[$socialRowIndex] ?? 0);
                if ($socialRowId <= 0) {
                    continue;
                }

                $socialRowLink = trim((string) ($socialRowLinks[$socialRowIndex] ?? ''));
                if ($socialRowLink === '') {
                    continue;
                }

                $socialLinkError = $this->getSocialMediaLinkValidationMessage(
                    $socialRowId,
                    $socialRowLink,
                    $socialMediaMetaById
                );
                if ($socialLinkError !== null) {
                    $validator->errors()->add(
                        'social_row_links.' . $socialRowIndex,
                        $socialLinkError
                    );
                }
            }
        });

        if ($validator->fails()) {
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                'Validation failed.',
                $oldValues,
                $requestedValues,
                $validator->errors()->toArray()
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect($redirectTo)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $newName = trim((string) ($validated['name'] ?? ''));
        $requestedEmail = strtolower(trim((string) ($validated['email'] ?? '')));
        $newPhoneNumber = trim((string) ($validated['phonenumber'] ?? ''));
        $pendingPhoneNumber = trim((string) ($editableFamilyMember->pending_phonenumber ?? ''));
        $newBloodType = strtoupper(trim((string) ($validated['bloodtype'] ?? '')));
        $newJob = trim((string) ($validated['job'] ?? ''));
        $newAddress = trim((string) ($validated['address'] ?? ''));
        $newEducationStatus = trim((string) ($validated['education_status'] ?? ''));
        $selectedSocialIds = collect((array) ($validated['social_ids'] ?? []))
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();
        $selectedSocialLinksById = collect((array) ($validated['social_links'] ?? []))
            ->mapWithKeys(function ($value, $key) {
                $socialId = (int) $key;
                if ($socialId <= 0) {
                    return [];
                }

                return [$socialId => trim((string) $value)];
            })
            ->all();
        $socialRowIds = collect((array) ($validated['social_row_ids'] ?? []))
            ->map(function ($value) {
                return (int) $value;
            })
            ->values()
            ->all();
        $socialRowLinks = collect((array) ($validated['social_row_links'] ?? []))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->values()
            ->all();
        $socialRowCount = max(count($socialRowIds), count($socialRowLinks));
        for ($socialRowIndex = 0; $socialRowIndex < $socialRowCount; $socialRowIndex++) {
            $socialRowId = (int) ($socialRowIds[$socialRowIndex] ?? 0);
            $socialRowLink = trim((string) ($socialRowLinks[$socialRowIndex] ?? ''));

            if ($socialRowId <= 0) {
                continue;
            }

            $selectedSocialIds[] = $socialRowId;

            if (
                !array_key_exists($socialRowId, $selectedSocialLinksById)
                || trim((string) ($selectedSocialLinksById[$socialRowId] ?? '')) === ''
                || $socialRowLink !== ''
            ) {
                $selectedSocialLinksById[$socialRowId] = $socialRowLink;
            }
        }
        $newSocialMediaRaw = trim((string) ($validated['new_social_media'] ?? ''));
        $legacyNewSocialMediaNames = collect(preg_split('/[\r\n,]+/', $newSocialMediaRaw))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->unique(function ($value) {
                return Str::lower($value);
            })
            ->values()
            ->all();
        $newSocialNamesInput = collect((array) ($validated['new_social_names'] ?? []))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->values()
            ->all();
        $newSocialLinksInput = collect((array) ($validated['new_social_links'] ?? []))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->values()
            ->all();
        $newSocialRowsByName = [];

        foreach ($legacyNewSocialMediaNames as $legacySocialName) {
            $legacyKey = Str::lower($legacySocialName);
            $newSocialRowsByName[$legacyKey] = [
                'name' => $legacySocialName,
                'link' => '',
            ];
        }

        $newSocialInputCount = max(count($newSocialNamesInput), count($newSocialLinksInput));
        for ($newSocialIndex = 0; $newSocialIndex < $newSocialInputCount; $newSocialIndex++) {
            $newSocialName = trim((string) ($newSocialNamesInput[$newSocialIndex] ?? ''));
            $newSocialLink = trim((string) ($newSocialLinksInput[$newSocialIndex] ?? ''));

            if ($newSocialName === '') {
                continue;
            }

            $newSocialKey = Str::lower($newSocialName);
            if (!array_key_exists($newSocialKey, $newSocialRowsByName)) {
                $newSocialRowsByName[$newSocialKey] = [
                    'name' => $newSocialName,
                    'link' => $newSocialLink,
                ];
                continue;
            }

            if ($newSocialLink !== '') {
                $newSocialRowsByName[$newSocialKey]['link'] = $newSocialLink;
            }
        }

        $hasSocialMediaIconColumn = $this->hasSocialMediaIconColumn();
        foreach (array_values($newSocialRowsByName) as $newSocialRow) {
            $socialName = trim((string) ($newSocialRow['name'] ?? ''));
            $socialLink = trim((string) ($newSocialRow['link'] ?? ''));
            if ($socialName === '') {
                continue;
            }

            $socialIconKey = $this->normalizeSocialMediaIconKey('', $socialName);
            $existingSocialQuery = DB::table('socialmedia')
                ->whereRaw('LOWER(socialname) = ?', [Str::lower($socialName)]);
            if ($hasSocialMediaIconColumn) {
                $existingSocialRow = $existingSocialQuery
                    ->select('socialid', 'socialicon')
                    ->first();
            } else {
                $existingSocialRow = $existingSocialQuery
                    ->select('socialid')
                    ->first();
            }

            $existingSocialId = (int) ($existingSocialRow->socialid ?? 0);

            if ($existingSocialId <= 0) {
                $insertPayload = [
                    'socialname' => $socialName,
                ];
                if ($hasSocialMediaIconColumn) {
                    $insertPayload['socialicon'] = $socialIconKey !== '' ? $socialIconKey : null;
                }

                $existingSocialId = (int) DB::table('socialmedia')->insertGetId($insertPayload);
            } elseif ($hasSocialMediaIconColumn) {
                $existingIcon = trim((string) ($existingSocialRow->socialicon ?? ''));
                $existingIconKey = $this->normalizeSocialMediaIconKey($existingIcon, '');
                if ($socialIconKey !== '' && $existingIconKey !== $socialIconKey) {
                    DB::table('socialmedia')
                        ->where('socialid', $existingSocialId)
                        ->update([
                            'socialicon' => $socialIconKey,
                        ]);
                }
            }

            $existingSocialId = (int) $existingSocialId;
            $selectedSocialIds[] = $existingSocialId;

            if (
                !array_key_exists($existingSocialId, $selectedSocialLinksById)
                || trim((string) ($selectedSocialLinksById[$existingSocialId] ?? '')) === ''
            ) {
                $selectedSocialLinksById[$existingSocialId] = $socialLink;
            }
        }

        $selectedSocialIds = collect($selectedSocialIds)
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();
        if (count($selectedSocialIds) > $maxSocialMediaPerMember) {
            $socialMediaLimitMessage = 'Maximum ' . $maxSocialMediaPerMember . ' social media accounts are allowed.';
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                $socialMediaLimitMessage,
                $oldValues,
                $requestedValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $socialMediaLimitMessage,
                    'errors' => [
                        'social_row_ids' => [$socialMediaLimitMessage],
                    ],
                ], 422);
            }

            return redirect($redirectTo)
                ->withErrors(['social_row_ids' => $socialMediaLimitMessage])
                ->withInput();
        }

        $newSocialMediaLabels = [];
        if (!empty($selectedSocialIds)) {
            $newSocialMediaRows = DB::table('socialmedia')
                ->whereIn('socialid', $selectedSocialIds)
                ->select('socialid', 'socialname')
                ->orderBy('socialname')
                ->get();

            $newSocialMediaLabels = $newSocialMediaRows
                ->map(function ($row) use ($formatSocialMediaLabel, $selectedSocialLinksById) {
                    $socialId = (int) ($row->socialid ?? 0);
                    $socialLink = $selectedSocialLinksById[$socialId] ?? '';

                    return $formatSocialMediaLabel($row->socialname ?? '', $socialLink);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->values()
                ->all();
        }
        $newSocialMediaLabel = implode(', ', $newSocialMediaLabels);
        $normalizedOldPhoneNumber = $this->normalizePhoneNumber($oldPhoneNumber);
        $normalizedNewPhoneNumber = $this->normalizePhoneNumber($newPhoneNumber);
        $normalizedPendingPhoneNumber = $this->normalizePhoneNumber($pendingPhoneNumber);
        $newValues = [
            'name' => $newName,
            'email' => $requestedEmail,
            'phonenumber' => $newPhoneNumber,
            'bloodtype' => $newBloodType,
            'job' => $newJob,
            'address' => $newAddress,
            'education_status' => $newEducationStatus,
            'social_media' => $newSocialMediaLabel,
        ];
        if ($requestedPicture !== '') {
            $newValues['picture'] = $requestedPicture;
        }

        if (
            !$isEditingOwnProfile
            && !$canParentEditMinorContact
            && (
                ($requestedEmail !== '' && $requestedEmail !== $oldEmail)
                || (
                    $newPhoneNumber !== ''
                    && $normalizedNewPhoneNumber !== ''
                    && $normalizedNewPhoneNumber !== $normalizedOldPhoneNumber
                )
            )
        ) {
            $blockedContactChangeMessage = 'Email and phone number can only be changed by the account owner.';
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                $blockedContactChangeMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $blockedContactChangeMessage,
                ], 422);
            }

            return redirect($redirectTo)
                ->withErrors(['email' => $blockedContactChangeMessage])
                ->withInput();
        }

        if ($requestedEmail !== '' && $requestedEmail !== $oldEmail) {
            $existingFamilyEmail = DB::table('family_member')
                ->where('memberid', '!=', $targetMemberId)
                ->where(function ($query) use ($requestedEmail) {
                    $query->whereRaw('LOWER(email) = ?', [$requestedEmail])
                        ->orWhereRaw('LOWER(pending_email) = ?', [$requestedEmail]);
                })
                ->exists();

            $existingEmployerEmailQuery = DB::table('employer')
                ->whereRaw('LOWER(email) = ?', [$requestedEmail]);
            if ($this->employerHasPendingEmailColumn()) {
                $existingEmployerEmailQuery->orWhereRaw('LOWER(pending_email) = ?', [$requestedEmail]);
            }
            $existingEmployerEmail = $existingEmployerEmailQuery->exists();

            if ($existingFamilyEmail || $existingEmployerEmail) {
                $duplicateEmailMessage = 'This email is already in use.';
                $this->notifyDiscordEditFailure(
                    $request,
                    'family.edit_profile',
                    $duplicateEmailMessage,
                    $oldValues,
                    $newValues
                );

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => $duplicateEmailMessage,
                    ], 422);
                }

                return redirect($redirectTo)
                    ->withErrors(['email' => $duplicateEmailMessage])
                    ->withInput();
            }
        }

        if ($requestedEmail !== '' && $requestedEmail !== $oldEmail && !$canParentEditMinorContact) {
            $emailChangeMessage = 'Please use the email verification flow to change your email address.';
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                $emailChangeMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $emailChangeMessage,
                    'email_requires_verification' => true,
                ], 422);
            }

            return redirect($redirectTo)
                ->withErrors(['email' => $emailChangeMessage])
                ->withInput();
        }

        if ($newPhoneNumber !== '' && $normalizedNewPhoneNumber === '') {
            $invalidPhoneMessage = 'Please enter a valid phone number.';
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                $invalidPhoneMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $invalidPhoneMessage,
                ], 422);
            }

            return redirect($redirectTo)
                ->withErrors(['phonenumber' => $invalidPhoneMessage])
                ->withInput();
        }

        if (
            $newPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== $normalizedOldPhoneNumber
        ) {
            $existingPhoneAccount = $this->findAccountByPhoneNumber($newPhoneNumber);
            if ($existingPhoneAccount && (int) ($existingPhoneAccount->userid ?? 0) !== $targetUserId) {
                $duplicatePhoneMessage = 'This phone number is already in use.';
                $this->notifyDiscordEditFailure(
                    $request,
                    'family.edit_profile',
                    $duplicatePhoneMessage,
                    $oldValues,
                    $newValues
                );

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => $duplicatePhoneMessage,
                    ], 422);
                }

                return redirect($redirectTo)
                    ->withErrors(['phonenumber' => $duplicatePhoneMessage])
                    ->withInput();
            }
        }

        if (
            $newPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== ''
            && $normalizedNewPhoneNumber !== $normalizedOldPhoneNumber
            && !$canParentEditMinorContact
        ) {
            $phoneChangeMessage = 'Please use the WhatsApp OTP verification flow to change your phone number.';

            if ($normalizedPendingPhoneNumber !== '' && $normalizedPendingPhoneNumber === $normalizedNewPhoneNumber) {
                $phoneChangeMessage = 'Phone number change is pending. Verify the OTP code from WhatsApp first.';
            }
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                $phoneChangeMessage,
                $oldValues,
                $newValues
            );

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $phoneChangeMessage,
                    'phone_requires_verification' => true,
                ], 422);
            }

            return redirect($redirectTo)
                ->withErrors(['phonenumber' => $phoneChangeMessage])
                ->withInput();
        }

        try {
            $resolvedEmailForUpdate = $requestedEmail !== '' ? $requestedEmail : $oldEmail;
            $updatePayload = [
                'name' => $newName !== '' ? $newName : null,
                'email' => $resolvedEmailForUpdate !== '' ? $resolvedEmailForUpdate : null,
                'phonenumber' => $newPhoneNumber !== '' ? $newPhoneNumber : null,
                'bloodtype' => $newBloodType !== '' ? $newBloodType : null,
                'job' => $newJob !== '' ? $newJob : null,
                'address' => $newAddress !== '' ? $newAddress : null,
                'education_status' => $newEducationStatus !== '' ? $newEducationStatus : null,
            ];

            if ($canParentEditMinorContact) {
                $updatePayload['pending_email'] = null;
                $updatePayload['email_verification_token'] = null;
                $updatePayload['email_verification_token_expires_at'] = null;
                $updatePayload['pending_phonenumber'] = null;
                $updatePayload['phone_verification_otp_hash'] = null;
                $updatePayload['phone_verification_otp_expires_at'] = null;
            }

            if ($request->hasFile('picture')) {
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/family-member';
                File::ensureDirectoryExists($uploadDir);

                if (!empty($editableFamilyMember->picture) && str_starts_with((string) $editableFamilyMember->picture, '/uploads/family-member/')) {
                    $oldFile = public_path(ltrim((string) $editableFamilyMember->picture, '/'));
                    if (File::exists($oldFile)) {
                        File::delete($oldFile);
                    }
                }

                $ext = $request->file('picture')->getClientOriginalExtension();
                $fileName = 'family_member_' . $targetUserId . '_' . time() . '.' . $ext;
                $request->file('picture')->move($uploadDir, $fileName);
                $updatePayload['picture'] = '/uploads/family-member/' . $fileName;
                $newValues['picture'] = '/uploads/family-member/' . $fileName;
            }

            DB::table('family_member')
                ->where('memberid', $targetMemberId)
                ->update($updatePayload);

            DB::table('ownsocial')
                ->where('memberid', $targetMemberId)
                ->delete();

            if (!empty($selectedSocialIds)) {
                $ownSocialRows = [];
                foreach ($selectedSocialIds as $socialId) {
                    $ownSocialRows[] = [
                        'socialid' => (int) $socialId,
                        'memberid' => $targetMemberId,
                        'link' => (string) ($selectedSocialLinksById[(int) $socialId] ?? ''),
                    ];
                }

                DB::table('ownsocial')->insert($ownSocialRows);
            }

            $updatedFamilyMember = DB::table('family_member')
                ->where('memberid', $targetMemberId)
                ->select('name', 'email', 'phonenumber', 'bloodtype', 'job', 'address', 'education_status', 'picture')
                ->first();
        } catch (\Throwable $e) {
            $this->notifyDiscordEditFailure(
                $request,
                'family.edit_profile',
                'Database update failed.',
                $oldValues,
                $newValues,
                ['exception' => $e->getMessage()]
            );

            throw $e;
        }

        $newEmail = strtolower(trim((string) ($updatedFamilyMember->email ?? '')));

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

        if ($oldBloodType !== $newBloodType) {
            $activityChanges[] = [
                'field' => 'blood type',
                'old' => $oldBloodType,
                'new' => $newBloodType,
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

        if ($oldSocialMediaLabel !== $newSocialMediaLabel) {
            $activityChanges[] = [
                'field' => 'social media',
                'old' => $oldSocialMediaLabel,
                'new' => $newSocialMediaLabel,
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
                'target_userid' => $targetUserId,
                'target_memberid' => $targetMemberId,
                'edited_by_parent' => !$isEditingOwnProfile,
                'has_picture_upload' => $request->hasFile('picture'),
                'redirect_to' => $redirectTo,
                'changes' => $activityChanges,
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Profile details updated successfully.',
                'family_member' => $updatedFamilyMember,
                'updated_self' => $isEditingOwnProfile,
            ]);
        }

        return redirect($redirectTo)->with('success', 'Profile details updated successfully.');
    }

    public function requestChangeEmail(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            return response()->json(['message' => 'Only family members can change email.'], 403);
        }

        $validated = $request->validate([
            'new_email' => ['required', 'email', 'max:255'],
        ]);

        $newEmail = strtolower(trim((string) $validated['new_email']));

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            return response()->json(['message' => 'Family profile not found.'], 404);
        }

        $currentEmail = strtolower(trim((string) ($familyMember->email ?? '')));
        $pendingEmail = strtolower(trim((string) ($familyMember->pending_email ?? '')));

        if ($currentEmail === $newEmail) {
            return response()->json(['message' => 'New email is the same as current email.'], 400);
        }

        if ($pendingEmail !== '' && $pendingEmail === $newEmail) {
            return response()->json([
                'message' => 'Verification link has already been sent to this email.',
                'old_email' => $currentEmail,
                'new_email' => $newEmail,
                'already_sent' => true,
            ]);
        }

        $existingFamilyEmail = DB::table('family_member')
            ->where('userid', '!=', $currentUserId)
            ->where(function ($query) use ($newEmail) {
                $query->whereRaw('LOWER(email) = ?', [$newEmail])
                    ->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
            })
            ->exists();

        $existingEmployerEmailQuery = DB::table('employer')
            ->whereRaw('LOWER(email) = ?', [$newEmail]);
        if ($this->employerHasPendingEmailColumn()) {
            $existingEmployerEmailQuery->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
        }
        $existingEmployerEmail = $existingEmployerEmailQuery->exists();

        if ($existingFamilyEmail || $existingEmployerEmail) {
            return response()->json(['message' => 'This email is already in use.'], 400);
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(10);
        $verificationUrl = url('/family/verify-email/' . $token);
        $memberName = trim((string) ($familyMember->name ?? ''));

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update([
                'pending_email' => $newEmail,
                'email_verification_token' => $token,
                'email_verification_token_expires_at' => $expiresAt,
            ]);

        $this->logActivity($request, 'family.request_email_change', [
            'userid' => $currentUserId,
            'old_email' => $currentEmail,
            'new_email' => $newEmail,
        ]);

        dispatch(function () use ($memberName, $currentEmail, $newEmail, $verificationUrl) {
            try {
                Mail::to($newEmail)->send(new ChangeEmailVerification(
                    $memberName,
                    $currentEmail,
                    $newEmail,
                    $verificationUrl
                ));
            } catch (\Throwable $e) {
                \Log::error('Failed to send email change verification: ' . $e->getMessage());
            }
        })->afterResponse();

        return response()->json([
            'message' => 'We have sent an email to your new address for confirmation.',
            'old_email' => $currentEmail,
            'new_email' => $newEmail,
            'pending_email' => $newEmail,
        ]);
    }

    public function cancelPendingEmailChange(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            return response()->json(['message' => 'Only family members can cancel email changes.'], 403);
        }

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            return response()->json(['message' => 'Family profile not found.'], 404);
        }

        $currentEmail = strtolower(trim((string) ($familyMember->email ?? '')));
        $pendingEmail = strtolower(trim((string) ($familyMember->pending_email ?? '')));

        if ($pendingEmail === '') {
            return response()->json([
                'message' => 'There is no pending email change request.',
                'current_email' => $currentEmail,
            ]);
        }

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update([
                'pending_email' => null,
                'email_verification_token' => null,
                'email_verification_token_expires_at' => null,
            ]);

        $this->logActivity($request, 'family.cancel_email_change', [
            'userid' => $currentUserId,
            'old_email' => $currentEmail,
            'pending_email' => $pendingEmail,
        ]);

        return response()->json([
            'message' => 'Email change request has been canceled.',
            'current_email' => $currentEmail,
        ]);
    }

    public function verifyEmailChange(Request $request, $token)
    {
        $familyMember = DB::table('family_member')
            ->where('email_verification_token', $token)
            ->where('email_verification_token_expires_at', '>', now())
            ->first();

        if (!$familyMember) {
            return redirect('/account')->with('error', 'Invalid or expired verification link.');
        }

        $oldEmail = strtolower(trim((string) ($familyMember->email ?? '')));
        $newEmail = strtolower(trim((string) ($familyMember->pending_email ?? '')));

        if ($newEmail === '') {
            return redirect('/account')->with('error', 'No pending email change request was found.');
        }

        $existingFamilyEmail = DB::table('family_member')
            ->where('userid', '!=', (int) $familyMember->userid)
            ->whereRaw('LOWER(email) = ?', [$newEmail])
            ->exists();

        $existingEmployerEmailQuery = DB::table('employer')
            ->whereRaw('LOWER(email) = ?', [$newEmail]);
        if ($this->employerHasPendingEmailColumn()) {
            $existingEmployerEmailQuery->orWhereRaw('LOWER(pending_email) = ?', [$newEmail]);
        }
        $existingEmployerEmail = $existingEmployerEmailQuery->exists();

        if ($existingFamilyEmail || $existingEmployerEmail) {
            return redirect('/account')->with('error', 'This email is already in use. Please request a different email.');
        }

        DB::table('family_member')
            ->where('memberid', $familyMember->memberid)
            ->update([
                'email' => $newEmail,
                'pending_email' => null,
                'email_verification_token' => null,
                'email_verification_token_expires_at' => null,
            ]);

        $this->logActivity($request, 'family.verify_email_change', [
            'userid' => $familyMember->userid,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ]);

        $systemSettings = $this->getSystemSettings();
        $redirectTo = '/account';

        return view('all.email-change-success', [
            'pageTitle' => 'Email Updated | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
            'systemSettings' => $systemSettings,
            'oldEmail' => $oldEmail,
            'newEmail' => $newEmail,
            'redirectTo' => $redirectTo,
        ]);
    }

    public function requestPhoneChangeOtp(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            return response()->json(['message' => 'Only family members can change phone numbers.'], 403);
        }

        $validated = $request->validate([
            'new_phone' => ['required', 'string', 'max:255'],
        ], [
            'new_phone.required' => 'Phone number is required.',
            'new_phone.max' => 'Phone number max length is 255 characters.',
        ]);

        $newPhoneRaw = trim((string) $validated['new_phone']);
        $normalizedNewPhone = $this->normalizePhoneNumber($newPhoneRaw);

        if ($normalizedNewPhone === '') {
            return response()->json(['message' => 'Please enter a valid phone number.'], 422);
        }

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            return response()->json(['message' => 'Family profile not found.'], 404);
        }

        $currentPhone = trim((string) ($familyMember->phonenumber ?? ''));
        $normalizedCurrentPhone = $this->normalizePhoneNumber($currentPhone);

        if ($normalizedCurrentPhone !== '' && $normalizedCurrentPhone === $normalizedNewPhone) {
            return response()->json(['message' => 'New phone number is the same as current phone number.'], 400);
        }

        $existingAccount = $this->findAccountByPhoneNumber($newPhoneRaw);
        if ($existingAccount && (int) ($existingAccount->userid ?? 0) !== $currentUserId) {
            return response()->json(['message' => 'This phone number is already in use.'], 400);
        }

        $otp = (string) random_int(100000, 999999);
        $otpExpiresAt = Carbon::now()->addMinutes(5);

        if (!$this->sendWhatsappOtpViaFonnte($normalizedNewPhone, $otp, $otpExpiresAt, 'phone_change')) {
            return response()->json([
                'message' => 'Failed to send OTP to WhatsApp. Please try again later.',
            ], 500);
        }

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update([
                'pending_phonenumber' => $newPhoneRaw,
                'phone_verification_otp_hash' => Hash::make($otp),
                'phone_verification_otp_expires_at' => $otpExpiresAt,
            ]);

        $this->logActivity($request, 'family.request_phone_change_otp', [
            'userid' => $currentUserId,
            'old_phone' => $currentPhone,
            'new_phone' => $newPhoneRaw,
        ]);

        return response()->json([
            'message' => 'OTP has been sent to your WhatsApp number.',
            'old_phone' => $currentPhone,
            'new_phone' => $newPhoneRaw,
            'pending_phone' => $newPhoneRaw,
            'otp_expires_at' => $otpExpiresAt->toDateTimeString(),
        ]);
    }

    public function verifyPhoneChangeOtp(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            return response()->json(['message' => 'Only family members can verify phone changes.'], 403);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must contain 6 digits.',
        ]);

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            return response()->json(['message' => 'Family profile not found.'], 404);
        }

        $pendingPhone = trim((string) ($familyMember->pending_phonenumber ?? ''));
        $otpHash = (string) ($familyMember->phone_verification_otp_hash ?? '');
        $otpExpiresAtRaw = (string) ($familyMember->phone_verification_otp_expires_at ?? '');
        $otpExpiresAt = $otpExpiresAtRaw !== '' ? Carbon::parse($otpExpiresAtRaw) : null;

        if ($pendingPhone === '' || $otpHash === '' || !$otpExpiresAt) {
            return response()->json([
                'message' => 'There is no active phone change verification request.',
            ], 400);
        }

        if ($otpExpiresAt->isPast()) {
            return response()->json([
                'message' => 'OTP has expired. Please cancel and request a new OTP.',
            ], 422);
        }

        if (!Hash::check((string) $validated['otp'], $otpHash)) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        $existingAccount = $this->findAccountByPhoneNumber($pendingPhone);
        if ($existingAccount && (int) ($existingAccount->userid ?? 0) !== $currentUserId) {
            return response()->json(['message' => 'This phone number is already in use.'], 400);
        }

        $oldPhone = trim((string) ($familyMember->phonenumber ?? ''));

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update([
                'phonenumber' => $pendingPhone,
                'pending_phonenumber' => null,
                'phone_verification_otp_hash' => null,
                'phone_verification_otp_expires_at' => null,
            ]);

        $this->logActivity($request, 'family.verify_phone_change_otp', [
            'userid' => $currentUserId,
            'old_phone' => $oldPhone,
            'new_phone' => $pendingPhone,
        ]);

        return response()->json([
            'message' => 'Phone number has been updated successfully.',
            'phone_number' => $pendingPhone,
        ]);
    }

    public function cancelPendingPhoneChange(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            return response()->json(['message' => 'Only family members can cancel phone changes.'], 403);
        }

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            return response()->json(['message' => 'Family profile not found.'], 404);
        }

        $currentPhone = trim((string) ($familyMember->phonenumber ?? ''));
        $pendingPhone = trim((string) ($familyMember->pending_phonenumber ?? ''));

        if ($pendingPhone === '') {
            return response()->json([
                'message' => 'There is no pending phone change request.',
                'current_phone' => $currentPhone,
            ]);
        }

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update([
                'pending_phonenumber' => null,
                'phone_verification_otp_hash' => null,
                'phone_verification_otp_expires_at' => null,
            ]);

        $this->logActivity($request, 'family.cancel_phone_change', [
            'userid' => $currentUserId,
            'old_phone' => $currentPhone,
            'pending_phone' => $pendingPhone,
        ]);

        return response()->json([
            'message' => 'Phone change request has been canceled.',
            'current_phone' => $currentPhone,
        ]);
    }

    public function storeFamilyMemberFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $isSuperadmin = $currentRoleId === 1;
        $isFamilyUser = $currentRoleId === 3 || $currentLevelId === 2;
        if (!$isSuperadmin && !$isFamilyUser) {
            return redirect('/')->with('error', 'Only family users and superadmin can add members from this page.');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = null;

        if ($isSuperadmin) {
            $targetMemberIdInput = (int) $request->input('target_memberid');
            if ($targetMemberIdInput <= 0) {
                return redirect('/')->with('error', 'Please select a valid family member card first.');
            }

            $currentMember = DB::table('family_member')
                ->where('memberid', $targetMemberIdInput)
                ->select('memberid', 'birthdate', 'gender', 'marital_status')
                ->first();
        } else {
            $currentMember = DB::table('family_member')
                ->where('userid', $currentUserId)
                ->select('memberid', 'birthdate', 'gender', 'marital_status')
                ->first();
        }

        if (!$currentMember) {
            return redirect('/')->with('error', 'Only registered family members can add family relations.');
        }

        $targetMemberId = (int) $currentMember->memberid;
        $currentMemberMaritalStatus = strtolower(trim((string) ($currentMember->marital_status ?? '')));

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'relation_type' => ['required', 'string', 'in:child,partner,parent'],
            'child_parenting_mode' => ['nullable', 'string', 'in:with_current_partner,single_parent'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_if:relation_type,partner', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'required_if:relation_type,partner', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'address' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'birthplace' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'relation_type.required' => 'Please choose Add Child, Add Parent, or Add Partner.',
            'relation_type.in' => 'Invalid relation type selected.',
            'child_parenting_mode.in' => 'Invalid child parent mode selected.',
            'email.required_if' => 'Email is required when adding a partner.',
            'phonenumber.required_if' => 'Phone number is required when adding a partner.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
        ]);

        $relationType = (string) $validated['relation_type'];
        $childParentingMode = (string) ($validated['child_parenting_mode'] ?? 'with_current_partner');
        $currentMemberGender = strtolower(trim((string) ($currentMember->gender ?? '')));
        $expectedPartnerGender = $currentMemberGender === 'male'
            ? 'female'
            : ($currentMemberGender === 'female' ? 'male' : '');

        if ($relationType === 'partner' && $expectedPartnerGender === '') {
            throw ValidationException::withMessages([
                'relation_type' => ['Current member gender is invalid. Please update your profile gender first.'],
            ]);
        }

        if ($relationType === 'partner') {
            $validated['gender'] = $expectedPartnerGender;
        }

        if ($relationType === 'partner' && $this->isMemberUnderAge((string) ($currentMember->birthdate ?? ''), 18)) {
            throw ValidationException::withMessages([
                'relation_type' => ['You must be at least 18 years old to add a partner.'],
            ]);
        }

        if ($relationType === 'parent') {
            if ($this->isMemberUnderAge((string) ($validated['birthdate'] ?? ''), 18)) {
                throw ValidationException::withMessages([
                    'birthdate' => ['Parent must be at least 18 years old.'],
                ]);
            }

            $targetBirthdate = trim((string) ($currentMember->birthdate ?? ''));
            if ($targetBirthdate !== '') {
                try {
                    $newParentBirthdate = Carbon::parse((string) $validated['birthdate'])->startOfDay();
                    $targetMemberBirthdate = Carbon::parse($targetBirthdate)->startOfDay();
                    if ($newParentBirthdate->greaterThanOrEqualTo($targetMemberBirthdate)) {
                        throw ValidationException::withMessages([
                            'birthdate' => ['Parent birthdate must be earlier than selected member birthdate.'],
                        ]);
                    }
                } catch (ValidationException $validationException) {
                    throw $validationException;
                } catch (\Throwable $e) {
                    // Ignore unparsable target birthdate because base date validation already runs.
                }
            }
        }

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
        $activePartnerCount = 0;
        if ($partnerCount > 0) {
            $activePartnerCount = DB::table('family_member')
                ->whereIn('memberid', $partnerIds->all())
                ->where(function ($query) {
                    $query->whereNull('life_status')
                        ->orWhereRaw('LOWER(life_status) <> ?', ['deceased']);
                })
                ->count();
        }

        if ($partnerCount > 1) {
            throw ValidationException::withMessages([
                'relation_type' => ['Selected member has more than one partner in current data. Please fix data consistency first.'],
            ]);
        }

        if ($relationType === 'partner' && $activePartnerCount > 0) {
            throw ValidationException::withMessages([
                'relation_type' => ['You already have an active partner and cannot add another partner.'],
            ]);
        }

        if ($relationType === 'partner' && strtolower((string) ($validated['gender'] ?? '')) !== $expectedPartnerGender) {
            throw ValidationException::withMessages([
                'gender' => ['Partner gender must be opposite to your gender.'],
            ]);
        }

        $hasUsableCurrentPartner = $activePartnerCount > 0 || $currentMemberMaritalStatus === 'married';

        if ($relationType === 'child' && $childParentingMode === 'with_current_partner' && !$hasUsableCurrentPartner) {
            throw ValidationException::withMessages([
                'child_parenting_mode' => ['Current partner not found for selected member. Choose Single parent instead.'],
            ]);
        }

        if ($relationType === 'child' && $childParentingMode === 'with_current_partner') {
            $partnerMemberQuery = DB::table('family_member')
                ->whereIn('memberid', $partnerIds->all());

            if ($activePartnerCount > 0) {
                $partnerMemberQuery->where(function ($query) {
                    $query->whereNull('life_status')
                        ->orWhereRaw('LOWER(life_status) <> ?', ['deceased']);
                });
            }

            $partnerMemberId = (int) $partnerMemberQuery
                ->orderBy('memberid')
                ->value('memberid');
        }

        $resolvedEmail = strtolower(trim((string) ($validated['email'] ?? '')));
        $resolvedPhoneNumber = trim((string) ($validated['phonenumber'] ?? ''));
        if ($relationType === 'child' || $relationType === 'parent') {
            if ($resolvedEmail === '') {
                $resolvedEmail = strtolower((string) $validated['username'])
                    . ($relationType === 'parent' ? '@parent.local' : '@child.local');
            }
            if ($resolvedPhoneNumber === '') {
                $resolvedPhoneNumber = '-';
            }
        }

        $createdUserId = 0;
        $createdMemberId = 0;

        DB::transaction(function () use ($validated, $targetMemberId, $relationType, $childParentingMode, $partnerMemberId, $newMemberMaritalStatus, $resolvedEmail, $resolvedPhoneNumber, &$createdUserId, &$createdMemberId) {
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
                'email' => $resolvedEmail,
                'phonenumber' => $resolvedPhoneNumber,
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
                    'child_parenting_mode' => $childParentingMode,
                ]);

                if ($childParentingMode === 'with_current_partner' && $partnerMemberId) {
                    DB::table('relationship')->insert([
                        'memberid' => $partnerMemberId,
                        'relatedmemberid' => $newMemberId,
                        'relationtype' => 'child',
                        'child_parenting_mode' => 'with_current_partner',
                    ]);
                }
            }

            if ($relationType === 'parent') {
                DB::table('relationship')->insert([
                    'memberid' => $newMemberId,
                    'relatedmemberid' => $targetMemberId,
                    'relationtype' => 'child',
                    'child_parenting_mode' => 'single_parent',
                ]);
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

            if ($relationType === 'parent') {
                $relationsPersisted = DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('memberid', $newMemberId)
                    ->where('relatedmemberid', $targetMemberId)
                    ->exists();
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

        $successRedirectUrl = $relationType === 'parent'
            ? '/?show_upper_tree=1&show_lower_tree=0&home_panel=profile'
            : '/';

        return redirect($successRedirectUrl)->with('success', 'New family member has been added.');
    }

    public function updateChildParentingModeFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        if ($currentRoleId !== 1) {
            return redirect('/')->with('error', 'Only superadmin can update child parenting mode.');
        }

        $validated = $request->validate([
            'memberid' => ['required', 'integer', 'exists:family_member,memberid'],
            'child_parenting_mode' => ['required', 'string', 'in:with_current_partner,single_parent'],
        ], [
            'memberid.required' => 'Child member is required.',
            'memberid.exists' => 'Selected child is not found.',
            'child_parenting_mode.required' => 'Child parenting mode is required.',
            'child_parenting_mode.in' => 'Invalid child parenting mode selected.',
        ]);

        $childMemberId = (int) $validated['memberid'];
        $requestedMode = strtolower(trim((string) $validated['child_parenting_mode']));
        $childMember = DB::table('family_member')
            ->where('memberid', $childMemberId)
            ->select('memberid', 'name', 'life_status')
            ->first();

        if (!$childMember) {
            return redirect('/')->with('error', 'Selected child is not found.');
        }

        $childRelations = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('relatedmemberid', $childMemberId)
            ->select('memberid', 'child_parenting_mode')
            ->get();

        if ($childRelations->isEmpty()) {
            $message = 'Selected member is not linked as a child. No changes were made.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'unchanged' => true,
                ]);
            }

            return redirect('/')->with('success', $message);
        }

        $currentMode = 'single_parent';
        foreach ($childRelations as $childRelation) {
            if (strtolower(trim((string) ($childRelation->child_parenting_mode ?? ''))) === 'with_current_partner') {
                $currentMode = 'with_current_partner';
                break;
            }
        }

        $parentIds = $childRelations
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        $anchorParentId = 0;
        $partnerMemberId = 0;

        if ($requestedMode === 'with_current_partner') {
            foreach ($parentIds as $parentId) {
                $resolvedPartnerId = $this->resolveActivePartnerMemberId((int) $parentId, [$childMemberId]);
                if ($resolvedPartnerId > 0) {
                    $anchorParentId = (int) $parentId;
                    $partnerMemberId = (int) $resolvedPartnerId;
                    break;
                }
            }

            if ($partnerMemberId <= 0) {
                return redirect('/')->with('error', 'No active partner was found for the selected child parent.');
            }

            DB::transaction(function () use ($childMemberId, $partnerMemberId, $anchorParentId) {
                DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $childMemberId)
                    ->update(['child_parenting_mode' => 'with_current_partner']);

                $existingPartnerChildRelation = DB::table('relationship')
                    ->where('memberid', $partnerMemberId)
                    ->where('relatedmemberid', $childMemberId)
                    ->where('relationtype', 'child')
                    ->exists();

                if (!$existingPartnerChildRelation) {
                    DB::table('relationship')->insert([
                        'memberid' => $partnerMemberId,
                        'relatedmemberid' => $childMemberId,
                        'relationtype' => 'child',
                        'child_parenting_mode' => 'with_current_partner',
                    ]);
                }

                DB::table('family_member')
                    ->whereIn('memberid', array_values(array_unique(array_filter([
                        $anchorParentId,
                        $partnerMemberId,
                    ], function ($memberId) {
                        return (int) $memberId > 0;
                    }))))
                    ->update(['marital_status' => 'married']);
            });
        } else {
            $parentIds = array_values(array_unique(array_filter($parentIds, function ($memberId) {
                return (int) $memberId > 0;
            })));
            sort($parentIds);

            if (!empty($parentIds)) {
                $anchorParentId = (int) $parentIds[0];
            }

            if ($currentMode === 'with_current_partner' && $anchorParentId <= 0) {
                foreach ($parentIds as $parentId) {
                    $resolvedPartnerId = $this->resolveActivePartnerMemberId((int) $parentId, [$childMemberId]);
                    if ($resolvedPartnerId > 0) {
                        $anchorParentId = (int) $parentId;
                        break;
                    }
                }
            }

            if ($anchorParentId <= 0) {
                return redirect('/')->with('error', 'Unable to determine which parent should be kept for single parent mode.');
            }

            DB::transaction(function () use ($childMemberId, $anchorParentId) {
                DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $childMemberId)
                    ->update(['child_parenting_mode' => 'single_parent']);

                DB::table('relationship')
                    ->where('relationtype', 'child')
                    ->where('relatedmemberid', $childMemberId)
                    ->where('memberid', '<>', $anchorParentId)
                    ->delete();
            });
        }

        Cache::store('file')->forget('family_tree:relationships:v1');
        Cache::store('file')->forget('family_tree:family_members:v1');
        Cache::store('file')->put('family_tree:render_version:v1', (string) now()->timestamp, now()->addDay());

        $this->logActivity($request, 'family.update_child_parenting_mode', [
            'child_memberid' => $childMemberId,
            'child_name' => (string) ($childMember->name ?? ''),
            'old_child_parenting_mode' => $currentMode,
            'new_child_parenting_mode' => $requestedMode,
            'anchor_parent_memberid' => $anchorParentId,
            'partner_memberid' => $partnerMemberId,
        ]);

        return redirect('/')->with('success', 'Child parenting mode has been updated.');
    }

    public function deleteFamilyMemberFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentLevelId = (int) session('authenticated_user.levelid');
        if ($currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family members can delete child data.');
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

        $hasChildRelation = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $currentMemberId)
            ->where('relatedmemberid', $targetMemberId)
            ->exists();

        if (!$hasChildRelation) {
            return redirect('/')->with('error', 'You can only delete your own child.');
        }

        $memberIdsToDelete = $this->resolveCascadeDeleteMemberIdsFromChild($targetMemberId);

        $memberIdsToDelete = array_values(array_unique(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $memberIdsToDelete), function ($id) use ($currentMemberId) {
            return $id > 0 && $id !== $currentMemberId;
        })));

        if (empty($memberIdsToDelete)) {
            return redirect('/')->with('error', 'Selected member is not found.');
        }

        $membersToDelete = DB::table('family_member')
            ->whereIn('memberid', $memberIdsToDelete)
            ->select('memberid', 'userid', 'name', 'gender', 'picture')
            ->get();

        if ($membersToDelete->isEmpty()) {
            return redirect('/')->with('error', 'Selected member is not found.');
        }

        $memberIdsToDelete = $membersToDelete
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

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

        DB::transaction(function () use ($memberIdsToDelete, $userIdsToDelete, $currentMemberId) {
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

        });

        foreach ($membersToDelete as $memberToDelete) {
            $picture = (string) ($memberToDelete->picture ?? '');
            if ($picture !== '' && str_starts_with($picture, '/uploads/family-member/')) {
                $picturePath = public_path(ltrim($picture, '/'));
                if (File::exists($picturePath)) {
                    File::delete($picturePath);
                }
            }
        }

        $this->logActivity($request, 'family.delete_relationship', [
            'target_memberid' => $targetMemberId,
            'target_userid' => (int) ($targetMember->userid ?? 0),
            'target_name' => (string) ($targetMember->name ?? ''),
            'target_gender' => (string) ($targetMember->gender ?? ''),
            'relation_deleted' => $hasPartnerRelation ? 'partner' : 'child',
            'deleted_member_ids' => $memberIdsToDelete,
            'deleted_member_count' => count($memberIdsToDelete),
            'cascade_delete_applied' => true,
        ]);

        return redirect('/')->with('success', 'Family member has been deleted.');
    }

    public function divorcePartnerFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentLevelId = (int) session('authenticated_user.levelid');
        if ($currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family members can divorce partners.');
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
            $targetMemberId = $this->resolveActivePartnerMemberId($currentMemberId);
        }

        if ($targetMemberId <= 0 || $targetMemberId === $currentMemberId) {
            return redirect('/')->with('error', 'Selected member is not valid for divorce.');
        }

        $familyMembers = DB::table('family_member')
            ->select('memberid')
            ->get()
            ->pluck('memberid')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->values()
            ->all();

        $relationships = DB::table('relationship')
            ->select('memberid', 'relatedmemberid', 'relationtype')
            ->get();

        $childrenMap = [];
        $parentMap = [];
        $partnerMap = [];

        foreach ($relationships as $relation) {
            $from = (int) ($relation->memberid ?? 0);
            $to = (int) ($relation->relatedmemberid ?? 0);
            if ($from <= 0 || $to <= 0 || $from === $to) {
                continue;
            }

            $type = strtolower(trim((string) ($relation->relationtype ?? '')));
            if ($type === 'child') {
                $childrenMap[$from] = $childrenMap[$from] ?? [];
                if (!in_array($to, $childrenMap[$from], true)) {
                    $childrenMap[$from][] = $to;
                }

                $parentMap[$to] = $parentMap[$to] ?? [];
                if (!in_array($from, $parentMap[$to], true)) {
                    $parentMap[$to][] = $from;
                }
                continue;
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

        $familyHeadMemberId = $this->resolveFamilyHeadMemberId(
            $familyMembers,
            $parentMap
        );
        $directFamilyMemberSet = $this->resolveDirectFamilyBloodlineSet(
            $familyHeadMemberId,
            $familyMembers,
            $childrenMap,
            $parentMap
        );

        if (!$this->canMemberManageDivorce($currentMemberId, $familyHeadMemberId, $directFamilyMemberSet, $parentMap)) {
            return redirect('/')->with('error', 'Only direct family members can manage divorce.');
        }

        $partnerIds = array_values(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $partnerMap[$currentMemberId] ?? []), function ($id) {
            return $id > 0;
        }));

        if (!in_array($targetMemberId, $partnerIds, true)) {
            return redirect('/')->with('error', 'Selected member is not your active partner.');
        }

        $currentMemberName = (string) DB::table('family_member')
            ->where('memberid', $currentMemberId)
            ->value('name');
        $targetMember = DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->select('name', 'gender')
            ->first();

        DB::transaction(function () use ($currentMemberId, $targetMemberId) {
            DB::table('relationship')
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
                ->delete();

            DB::table('family_member')
                ->whereIn('memberid', [$currentMemberId, $targetMemberId])
                ->update(['marital_status' => 'single']);
        });

        Cache::store('file')->forget('family_tree:relationships:v1');
        Cache::store('file')->forget('family_tree:family_members:v1');
        Cache::store('file')->put('family_tree:render_version:v1', (string) now()->timestamp, now()->addDay());

        $this->logActivity($request, 'family.divorce_partner', [
            'current_memberid' => $currentMemberId,
            'current_name' => $currentMemberName,
            'target_memberid' => $targetMemberId,
            'target_name' => (string) ($targetMember->name ?? ''),
            'target_gender' => (string) ($targetMember->gender ?? ''),
        ]);

        return redirect('/')->with('success', 'Partner relationship has been removed.');
    }

    public function updateFamilyMemberLifeStatus(Request $request)
    {
        $expectsJson = $request->ajax() || $request->expectsJson();

        if (!$request->session()->has('authenticated_user')) {
            if ($expectsJson) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        $currentLevelId = (int) session('authenticated_user.levelid');
        $currentRoleId = (int) session('authenticated_user.roleid');
        $canUpdateLifeStatus = $currentLevelId === 2 || in_array($currentRoleId, [1, 2], true);
        $isAdminOrSuperadmin = in_array($currentRoleId, [1, 2], true);
        if (!$canUpdateLifeStatus) {
            if ($expectsJson) {
                return response()->json(['message' => 'Only family members, admin, or superadmin can update life status.'], 403);
            }

            return redirect('/')->with('error', 'Only family members, admin, or superadmin can update life status.');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('memberid')
            ->first();

        if (!$currentMember && !$isAdminOrSuperadmin) {
            if ($expectsJson) {
                return response()->json(['message' => 'Current family profile was not found.'], 404);
            }

            return redirect('/')->with('error', 'Current family profile was not found.');
        }

        $validated = $request->validate([
            'memberid' => ['required', 'integer', 'exists:family_member,memberid'],
            'life_status' => ['required', 'string', 'in:alive,deceased'],
            'deaddate' => ['nullable', 'date'],
            'grave_location_url' => ['nullable', 'url', 'max:2048'],
        ], [
            'memberid.required' => 'Member target is required.',
            'memberid.exists' => 'Selected member is not found.',
            'life_status.required' => 'Life status is required.',
            'life_status.in' => 'Life status must be alive or deceased.',
            'deaddate.date' => 'Death date must be a valid date.',
            'grave_location_url.url' => 'Grave location must be a valid URL.',
        ]);

        $currentMemberId = (int) ($currentMember->memberid ?? 0);
        $targetMemberId = (int) $validated['memberid'];

        if ($isAdminOrSuperadmin) {
            $targetMember = DB::table('family_member')
                ->where('memberid', $targetMemberId)
                ->select('name', 'gender', 'life_status')
                ->first();

            if (!$targetMember) {
                if ($expectsJson) {
                    return response()->json(['message' => 'Selected member is not found.'], 404);
                }

                return redirect('/')->with('error', 'Selected member is not found.');
            }

            $nextLifeStatus = (string) $validated['life_status'];
            $deadDate = null;
            $graveLocationUrl = trim((string) ($validated['grave_location_url'] ?? ''));
            if ($nextLifeStatus === 'deceased') {
                $deadDate = !empty($validated['deaddate'])
                    ? Carbon::parse((string) $validated['deaddate'])->toDateString()
                    : now()->toDateString();
            }

            $updatePayload = [
                'life_status' => $nextLifeStatus,
                'deaddate' => $deadDate,
            ];
            if ($nextLifeStatus === 'deceased') {
                $updatePayload['grave_location_url'] = $graveLocationUrl !== '' ? $graveLocationUrl : null;
            }

            DB::table('family_member')
                ->where('memberid', $targetMemberId)
                ->update($updatePayload);

            Cache::store('file')->forget('family_tree:relationships:v1');
            Cache::store('file')->forget('family_tree:family_members:v1');
            Cache::store('file')->put('family_tree:render_version:v1', (string) now()->timestamp, now()->addDay());

            $previousLifeStatus = (string) ($targetMember->life_status ?? '');
            if ($previousLifeStatus !== $nextLifeStatus) {
                $this->logActivity($request, 'family.update_life_status', [
                    'target_memberid' => $targetMemberId,
                    'target_name' => (string) ($targetMember->name ?? ''),
                    'target_gender' => (string) ($targetMember->gender ?? ''),
                    'target_relation_label' => 'Family Member',
                    'life_status_old' => $previousLifeStatus,
                    'life_status_new' => $nextLifeStatus,
                ]);
            }

            if ($nextLifeStatus === 'deceased') {
                app(\App\Services\LeaderSuccessionService::class)->promoteHeirForDeceasedMember($targetMemberId, $request);
            }

            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Life status has been updated.',
                    'life_status' => $nextLifeStatus,
                    'memberid' => $targetMemberId,
                    'deaddate' => $deadDate,
                    'grave_location_url' => $updatePayload['grave_location_url'] ?? null,
                ]);
            }

            return redirect('/')->with('success', 'Life status has been updated.');
        }

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

        $partnerIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($currentMemberId) {
                $query->where('memberid', $currentMemberId)
                    ->orWhere('relatedmemberid', $currentMemberId);
            })
            ->get()
            ->map(function ($row) use ($currentMemberId) {
                $leftId = (int) ($row->memberid ?? 0);
                $rightId = (int) ($row->relatedmemberid ?? 0);

                if ($leftId === $currentMemberId) {
                    return $rightId;
                }

                if ($rightId === $currentMemberId) {
                    return $leftId;
                }

                return 0;
            })
            ->filter(function ($id) {
                return (int) $id !== 0;
            })
            ->unique()
            ->values()
            ->all();

        $stepParentIds = [];
        if (!empty($parentIds)) {
            $parentSet = [];
            foreach ($parentIds as $parentId) {
                $parentSet[(int) $parentId] = true;
            }

            $stepParentIds = DB::table('relationship')
                ->where('relationtype', 'partner')
                ->where(function ($query) use ($parentIds) {
                    $query->whereIn('memberid', $parentIds)
                        ->orWhereIn('relatedmemberid', $parentIds);
                })
                ->get()
                ->map(function ($row) use ($parentSet) {
                    $leftId = (int) $row->memberid;
                    $rightId = (int) $row->relatedmemberid;

                    if (isset($parentSet[$leftId])) {
                        return $rightId;
                    }

                    if (isset($parentSet[$rightId])) {
                        return $leftId;
                    }

                    return 0;
                })
                ->filter(function ($id) use ($parentSet, $currentMemberId) {
                    $id = (int) $id;
                    return $id !== 0 && $id !== $currentMemberId && !isset($parentSet[$id]);
                })
                ->unique()
                ->values()
                ->all();
        }

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
        foreach ($partnerIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }
        foreach ($stepParentIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }
        foreach ($siblingIds as $id) {
            $allowedMemberIds[(int) $id] = true;
        }

        if (empty($allowedMemberIds[$targetMemberId])) {
            if ($expectsJson) {
                return response()->json(['message' => 'You can only update life status for yourself, child, parent, step parent, or sibling.'], 403);
            }

            return redirect('/')->with('error', 'You can only update life status for yourself, child, parent, step parent, or sibling.');
        }

        $targetMember = DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->select('name', 'gender', 'life_status')
            ->first();

        $nextLifeStatus = (string) $validated['life_status'];
        $deadDate = null;
        $graveLocationUrl = trim((string) ($validated['grave_location_url'] ?? ''));
        if ($nextLifeStatus === 'deceased') {
            $deadDate = !empty($validated['deaddate'])
                ? Carbon::parse((string) $validated['deaddate'])->toDateString()
                : now()->toDateString();
        }
        $updatePayload = [
            'life_status' => $nextLifeStatus,
            'deaddate' => $deadDate,
        ];
        if ($nextLifeStatus === 'deceased') {
            $updatePayload['grave_location_url'] = $graveLocationUrl !== '' ? $graveLocationUrl : null;
        }
        DB::table('family_member')
            ->where('memberid', $targetMemberId)
            ->update($updatePayload);

        Cache::store('file')->forget('family_tree:relationships:v1');
        Cache::store('file')->forget('family_tree:family_members:v1');
        Cache::store('file')->put('family_tree:render_version:v1', (string) now()->timestamp, now()->addDay());

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

        if ($nextLifeStatus === 'deceased') {
            app(\App\Services\LeaderSuccessionService::class)->promoteHeirForDeceasedMember($targetMemberId, $request);
        }

        if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Life status has been updated.',
                    'life_status' => $nextLifeStatus,
                    'memberid' => $targetMemberId,
                    'deaddate' => $deadDate,
                    'grave_location_url' => $updatePayload['grave_location_url'] ?? null,
                ]);
            }

        return redirect('/')->with('success', 'Life status has been updated.');
    }

    private function canEditFamilyProfile(int $editorMemberId, ?object $targetFamilyMember): bool
    {
        if ($editorMemberId <= 0 || !$targetFamilyMember) {
            return false;
        }

        $targetMemberId = (int) ($targetFamilyMember->memberid ?? 0);
        if ($targetMemberId <= 0) {
            return false;
        }

        if ($editorMemberId === $targetMemberId) {
            return true;
        }

        $partnerIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($editorMemberId) {
                $query->where('memberid', $editorMemberId)
                    ->orWhere('relatedmemberid', $editorMemberId);
            })
            ->get()
            ->map(function ($row) use ($editorMemberId) {
                return (int) ((int) $row->memberid === $editorMemberId
                    ? $row->relatedmemberid
                    : $row->memberid);
            })
            ->filter(function ($id) {
                return (int) $id !== 0;
            })
            ->unique()
            ->values()
            ->all();

        if (in_array($targetMemberId, $partnerIds, true)) {
            return true;
        }

        return DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $editorMemberId)
            ->where('relatedmemberid', $targetMemberId)
            ->exists();
    }

    private function resolveActivePartnerMemberId(int $memberId, array $excludedMemberIds = []): int
    {
        if ($memberId <= 0) {
            return 0;
        }

        $excludedMemberSet = [];
        foreach ($excludedMemberIds as $excludedMemberId) {
            $excludedMemberId = (int) $excludedMemberId;
            if ($excludedMemberId > 0) {
                $excludedMemberSet[$excludedMemberId] = true;
            }
        }

        $partnerIds = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($query) use ($memberId) {
                $query->where('memberid', $memberId)
                    ->orWhere('relatedmemberid', $memberId);
            })
            ->get()
            ->map(function ($row) use ($memberId, $excludedMemberSet) {
                $partnerId = (int) ((int) $row->memberid === $memberId ? $row->relatedmemberid : $row->memberid);
                if ($partnerId <= 0 || isset($excludedMemberSet[$partnerId])) {
                    return 0;
                }

                return $partnerId;
            })
            ->filter(function ($partnerId) {
                return $partnerId > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($partnerIds)) {
            return 0;
        }

        return (int) DB::table('family_member')
            ->whereIn('memberid', $partnerIds)
            ->where(function ($query) {
                $query->whereNull('life_status')
                    ->orWhereRaw('LOWER(life_status) <> ?', ['deceased']);
            })
            ->orderBy('memberid')
            ->value('memberid');
    }

    private function resolveFamilyHeadMemberId(array $memberIds, array $parentMap): int
    {
        $normalizedMemberIds = array_values(array_unique(array_filter(array_map(function ($id) {
            return (int) $id;
        }, $memberIds), function ($id) {
            return $id > 0;
        })));

        if (empty($normalizedMemberIds)) {
            return 0;
        }

        $candidateRoots = [];
        foreach ($normalizedMemberIds as $memberId) {
            $parentIds = array_values(array_filter($parentMap[$memberId] ?? [], function ($parentId) {
                return (int) $parentId > 0;
            }));
            if (empty($parentIds)) {
                $candidateRoots[] = $memberId;
            }
        }

        if (!empty($candidateRoots)) {
            sort($candidateRoots, SORT_NUMERIC);
            return (int) $candidateRoots[0];
        }

        sort($normalizedMemberIds, SORT_NUMERIC);
        return (int) $normalizedMemberIds[0];
    }

    private function resolveDirectFamilyBloodlineSet(
        int $familyHeadMemberId,
        array $memberIds,
        array $childrenMap,
        array $parentMap
    ): array {
        $memberSet = [];
        foreach ($memberIds as $memberId) {
            $memberId = (int) $memberId;
            if ($memberId > 0) {
                $memberSet[$memberId] = true;
            }
        }

        if ($familyHeadMemberId <= 0 || empty($memberSet[$familyHeadMemberId])) {
            return [];
        }

        $bloodlineSet = [$familyHeadMemberId => true];
        $queue = new \SplQueue();
        $queue->enqueue($familyHeadMemberId);

        while (!$queue->isEmpty()) {
            $memberId = (int) $queue->dequeue();
            $neighborIds = [];

            foreach ($childrenMap[$memberId] ?? [] as $childId) {
                $neighborIds[] = (int) $childId;
            }
            foreach ($parentMap[$memberId] ?? [] as $parentId) {
                $neighborIds[] = (int) $parentId;
            }

            foreach ($neighborIds as $neighborId) {
                if ($neighborId <= 0 || empty($memberSet[$neighborId]) || !empty($bloodlineSet[$neighborId])) {
                    continue;
                }

                $bloodlineSet[$neighborId] = true;
                $queue->enqueue($neighborId);
            }
        }

        return $bloodlineSet;
    }

    private function canMemberManageDivorce(
        int $memberId,
        int $familyHeadMemberId,
        array $directFamilyMemberSet,
        array $parentMap
    ): bool {
        if ($memberId <= 0) {
            return false;
        }

        if ($familyHeadMemberId > 0 && $memberId === $familyHeadMemberId) {
            return true;
        }

        if (empty($directFamilyMemberSet[$memberId])) {
            return false;
        }

        // Member who entered the tree by marriage usually has no parent-child lineage
        // recorded in this family tree. Keep divorce access for bloodline members only.
        $parentIds = array_values(array_filter($parentMap[$memberId] ?? [], function ($parentId) {
            return (int) $parentId > 0;
        }));

        return !empty($parentIds);
    }

    private function isMemberUnderAge(string $birthdate, int $ageLimit): bool
    {
        $birthdate = trim($birthdate);
        if ($birthdate === '' || $ageLimit < 0) {
            return false;
        }

        try {
            return Carbon::parse($birthdate)->age < $ageLimit;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function deleteFamilyMemberDataByUserId(int $targetUserId): void
    {
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

        DB::table('family_member')
            ->where('userid', $targetUserId)
            ->delete();
    }

    private function resolveCascadeDeleteMemberIdsFromChild(int $rootChildMemberId): array
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

            foreach (array_keys($childrenMap[$memberId] ?? []) as $childId) {
                $queue->enqueue([
                    'memberid' => (int) $childId,
                    'expand_partners' => true,
                ]);
            }

            if (!$expandPartners) {
                continue;
            }

            foreach (array_keys($partnerMap[$memberId] ?? []) as $partnerId) {
                $queue->enqueue([
                    'memberid' => (int) $partnerId,
                    'expand_partners' => false,
                ]);
            }
        }

        return array_values(array_map(function ($id) {
            return (int) $id;
        }, array_keys($deleteSet)));
    }

    private function hasSocialMediaIconColumn(): bool
    {
        return Schema::hasTable('socialmedia')
            && Schema::hasColumn('socialmedia', 'socialicon');
    }

    private function normalizeSocialMediaIconKey(?string $iconValue, ?string $socialName = null): string
    {
        $keywordMap = [
            'instagram' => ['instagram', 'insta', 'ig'],
            'facebook' => ['facebook', 'fb', 'meta'],
            'x' => ['x', 'xcom', 'twitter'],
            'tiktok' => ['tiktok'],
            'linkedin' => ['linkedin'],
            'youtube' => ['youtube', 'youtu', 'yt'],
            'github' => ['github'],
            'telegram' => ['telegram'],
            'whatsapp' => ['whatsapp', 'wa'],
            'line' => ['line'],
            'discord' => ['discord'],
            'threads' => ['threads'],
            'reddit' => ['reddit'],
            'pinterest' => ['pinterest'],
        ];

        $supportedKeys = array_keys($keywordMap);
        $normalizeRawValue = function (?string $value) use ($keywordMap): string {
            $raw = trim((string) $value);
            if ($raw === '') {
                return '';
            }

            $normalized = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $raw));
            if ($normalized === '') {
                return '';
            }

            foreach ($keywordMap as $platformKey => $keywords) {
                foreach ($keywords as $keyword) {
                    if ($normalized === $keyword || Str::contains($normalized, $keyword)) {
                        return $platformKey;
                    }
                }
            }

            return $normalized;
        };

        $normalizedIconKey = $normalizeRawValue($iconValue);
        $normalizedNameKey = $normalizeRawValue($socialName);
        $hasRawIconValue = trim((string) $iconValue) !== '';
        $nameIsSupported = in_array($normalizedNameKey, $supportedKeys, true);
        $iconIsSupported = in_array($normalizedIconKey, $supportedKeys, true);

        if ($nameIsSupported && (!$hasRawIconValue || !$iconIsSupported)) {
            return $normalizedNameKey;
        }

        if ($normalizedIconKey !== '') {
            return $normalizedIconKey;
        }

        return $normalizedNameKey;
    }

    private function getSocialMediaMetaById(array $socialIds): array
    {
        $normalizedSocialIds = collect($socialIds)
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedSocialIds)) {
            return [];
        }

        $socialMediaQuery = DB::table('socialmedia')
            ->whereIn('socialid', $normalizedSocialIds);
        if ($this->hasSocialMediaIconColumn()) {
            $socialMediaQuery->select('socialid', 'socialname', 'socialicon');
        } else {
            $socialMediaQuery->select('socialid', 'socialname');
        }

        $socialMediaRows = $socialMediaQuery->get();
        $socialMediaMetaById = [];

        foreach ($socialMediaRows as $socialMediaRow) {
            $socialId = (int) ($socialMediaRow->socialid ?? 0);
            if ($socialId <= 0) {
                continue;
            }

            $socialName = trim((string) ($socialMediaRow->socialname ?? ''));
            $socialIcon = trim((string) ($socialMediaRow->socialicon ?? ''));
            $socialMediaMetaById[$socialId] = [
                'name' => $socialName,
                'platform_key' => $this->normalizeSocialMediaIconKey($socialIcon, $socialName),
            ];
        }

        return $socialMediaMetaById;
    }

    private function getSocialMediaLinkValidationMessage(
        int $socialId,
        string $socialLink,
        array $socialMediaMetaById
    ): ?string {
        if ($socialId <= 0) {
            return null;
        }

        $socialMeta = $socialMediaMetaById[$socialId] ?? null;
        if (!is_array($socialMeta)) {
            return null;
        }

        $socialName = trim((string) ($socialMeta['name'] ?? ''));
        $expectedPlatformKey = trim((string) ($socialMeta['platform_key'] ?? ''));
        $platformHostMap = $this->getSocialMediaPlatformHostMap();

        if ($expectedPlatformKey === '' || !array_key_exists($expectedPlatformKey, $platformHostMap)) {
            return null;
        }

        $actualPlatformKey = $this->detectSocialMediaPlatformKeyFromLink($socialLink);
        if ($actualPlatformKey === '') {
            $platformLabel = $socialName !== '' ? $socialName : 'selected social media';
            return 'Profile link must be a valid ' . $platformLabel . ' URL.';
        }

        if ($actualPlatformKey !== $expectedPlatformKey) {
            $platformLabel = $socialName !== '' ? $socialName : ('ID ' . $socialId);
            return 'Profile link does not match selected social media (' . $platformLabel . ').';
        }

        return null;
    }

    private function normalizeSocialMediaProfileLink(?string $socialLink): string
    {
        $value = trim((string) $socialLink);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $value) === 1) {
            return $value;
        }

        if (preg_match('/^\/\//', $value) === 1) {
            return 'https:' . $value;
        }

        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}([\/?#]|$)/i', $value) === 1) {
            return 'https://' . $value;
        }

        return '';
    }

    private function detectSocialMediaPlatformKeyFromLink(?string $socialLink): string
    {
        $normalizedLink = $this->normalizeSocialMediaProfileLink($socialLink);
        if ($normalizedLink === '') {
            return '';
        }

        $host = strtolower((string) parse_url($normalizedLink, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', trim($host));
        if ($host === '') {
            return '';
        }

        foreach ($this->getSocialMediaPlatformHostMap() as $platformKey => $domains) {
            foreach ($domains as $domain) {
                $normalizedDomain = strtolower(trim((string) $domain));
                if ($normalizedDomain === '') {
                    continue;
                }

                if ($host === $normalizedDomain || Str::endsWith($host, '.' . $normalizedDomain)) {
                    return $platformKey;
                }
            }
        }

        return '';
    }

    private function getSocialMediaPlatformHostMap(): array
    {
        return [
            'instagram' => ['instagram.com'],
            'facebook' => ['facebook.com', 'fb.com'],
            'x' => ['x.com', 'twitter.com'],
            'tiktok' => ['tiktok.com'],
            'linkedin' => ['linkedin.com'],
            'youtube' => ['youtube.com', 'youtu.be'],
            'github' => ['github.com'],
            'telegram' => ['telegram.me', 't.me'],
            'whatsapp' => ['whatsapp.com', 'wa.me'],
            'line' => ['line.me'],
            'discord' => ['discord.com', 'discord.gg'],
            'threads' => ['threads.net'],
            'reddit' => ['reddit.com'],
            'pinterest' => ['pinterest.com'],
            'snapchat' => ['snapchat.com'],
        ];
    }


    public function updateLifeStatus(Request $request )
    {
         $validated= $request->validate([
            'memberid' => 'required|integer',
            'life_status' => 'required|string|in:Alive,Deceased,alive,deceased',
        ]);

        \Illuminate\Support\Facades\DB::table('family_member')
            ->where('memberid', ['memberid'])
            ->update(['life_status' => ucfirst(strtolower(['life_status']))]);

        return response()->json(['success' => true]);
    }
}
