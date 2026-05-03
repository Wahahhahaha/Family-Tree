<?php
    $nodeGeneration = (int) ($node['generation'] ?? 1);
    $nodeMember = $node['member'];
    $nodePartners = $node['partners'] ?? [];
    $nodeChildren = $node['children'] ?? [];
    $relationMap = $relationMap ?? [];
    $canDeletePartnerMap = $canDeletePartnerMap ?? [];
    $canCurrentMemberManageDivorce = $canCurrentMemberManageDivorce ?? false;
    $canDeleteChildMap = $canDeleteChildMap ?? [];
    $canUpdateLifeStatusMap = $canUpdateLifeStatusMap ?? [];
    $canEditProfileMap = $canEditProfileMap ?? [];
    $childParentingModeMap = $childParentingModeMap ?? [];
    $isSuperadmin = (int) (session('authenticated_user.roleid') ?? 0) === 1;
    $depth = (int) ($depth ?? 0);
    $maxVisibleDepth = isset($maxVisibleDepth) ? (int) $maxVisibleDepth : 99;
    $isActive = (int) ($nodeMember->memberid ?? 0) === (int) ($initialMemberId ?? 0);
    $currentUserId = (int) (session('authenticated_user.userid') ?? 0);
    $isMe = (int) ($nodeMember->userid ?? 0) === $currentUserId;
    $nodeRelation = $relationMap[(int) ($nodeMember->memberid ?? 0)] ?? ($isMe ? 'Me' : 'Family Member');
    $memberLifeStatusRaw = strtolower((string) ($nodeMember->life_status ?? 'alive'));
    $memberGenderRaw = strtolower((string) ($nodeMember->gender ?? 'male'));
    $memberCanDeletePartner = !empty($canDeletePartnerMap[(int) ($nodeMember->memberid ?? 0)]);
    $memberCanDivorcePartner = $memberCanDeletePartner || ($isMe && $canCurrentMemberManageDivorce && $hasPartner);
    $memberCanDeleteChild = !empty($canDeleteChildMap[(int) ($nodeMember->memberid ?? 0)]);
    $memberCanUpdateLifeStatus = $isSuperadmin || !empty($canUpdateLifeStatusMap[(int) ($nodeMember->memberid ?? 0)]);
    $memberCanEditProfile = !empty($canEditProfileMap[(int) ($nodeMember->memberid ?? 0)]);
    $nodeMemberId = (int) ($nodeMember->memberid ?? 0);
    $memberChildParentingModeRaw = '';
    foreach ($childParentingModeMap as $parentId => $childModes) {
        if (!is_array($childModes) || !array_key_exists($nodeMemberId, $childModes)) {
            continue;
        }

        $resolvedMode = strtolower((string) ($childModes[$nodeMemberId] ?? ''));
        if (in_array($resolvedMode, ['with_current_partner', 'single_parent'], true)) {
            $memberChildParentingModeRaw = $resolvedMode;
            break;
        }
    }
    $highlightParentMemberId = (int) ($highlightParentMemberId ?? 0);
    $highlightParentForName = trim((string) ($highlightParentForName ?? ''));
    $nodeRoleDisplay = $nodeRelation;
    if ($highlightParentMemberId > 0 && $nodeMemberId === $highlightParentMemberId && $highlightParentForName !== '') {
        $parentGenderRaw = strtolower(trim((string) ($nodeMember->gender ?? '')));
        if ($parentGenderRaw === 'female') {
            $nodeRoleDisplay = 'Mother untuk si ' . $highlightParentForName;
        } elseif ($parentGenderRaw === 'male') {
            $nodeRoleDisplay = 'Father untuk si ' . $highlightParentForName;
        } else {
            $nodeRoleDisplay = 'Parent untuk si ' . $highlightParentForName;
        }
    }
    $hasPartner = !empty($nodePartners);
    $parentingMode = (string) ($parentingMode ?? 'with_current_partner');
    $isSingleParentChild = $parentingMode === 'single_parent';
    $singleParentAnchorMemberId = (int) ($singleParentAnchorMemberId ?? 0);
    $childrenOrdered = [];
    foreach ($nodeChildren as $childNode) {
        $childMemberId = (int) ($childNode['member']->memberid ?? 0);
        $childMode = (string) ($childNode['parenting_mode'] ?? ($childParentingModeMap[$nodeMemberId][$childMemberId] ?? 'with_current_partner'));
        $childSingleParentAnchorMemberId = (int) ($childNode['single_parent_anchor_memberid'] ?? $nodeMemberId);
        $childrenOrdered[] = [
            'node' => $childNode,
            'mode' => $childMode,
            'single_parent_anchor_memberid' => $childSingleParentAnchorMemberId,
        ];
    }
    if ($hasPartner && !empty($childrenOrdered)) {
        usort($childrenOrdered, function ($leftChild, $rightChild) {
            $leftIsSingleParent = (string) ($leftChild['mode'] ?? '') === 'single_parent';
            $rightIsSingleParent = (string) ($rightChild['mode'] ?? '') === 'single_parent';

            if ($leftIsSingleParent === $rightIsSingleParent) {
                $leftMemberId = (int) ($leftChild['node']['member']->memberid ?? 0);
                $rightMemberId = (int) ($rightChild['node']['member']->memberid ?? 0);
                return $rightMemberId <=> $leftMemberId;
            }

            return $leftIsSingleParent ? -1 : 1;
        });
    }
    $allChildrenSingleParent = !empty($childrenOrdered) && count(array_filter($childrenOrdered, function ($childData) {
        return (string) ($childData['mode'] ?? '') !== 'single_parent';
    })) === 0;
    $childGroupClass = 'child-group child-group-mixed';
    if ($allChildrenSingleParent) {
        $childGroupClass .= ' child-group-single-parent';
    }
?>

<?php
    $liClass = trim(
        ($isSingleParentChild ? 'single-parent-child ' : '')
        . ($hasPartner ? 'has-partner-node ' : '')
        . ($allChildrenSingleParent ? 'has-single-parent-children' : '')
    );
?>
<li class="<?php echo e($liClass); ?>" <?php if($isSingleParentChild && $singleParentAnchorMemberId > 0): ?>data-single-parent-anchor-memberid="<?php echo e($singleParentAnchorMemberId); ?>"<?php endif; ?>>
    <div class="partner-row <?php echo e($hasPartner ? 'has-partner' : ''); ?>">
        <article
            class="member-card <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($memberLifeStatusRaw === 'deceased' ? 'is-deceased' : ''); ?> <?php echo e($memberGenderRaw === 'female' ? 'is-female' : 'is-male'); ?>"
            data-memberid="<?php echo e($nodeMember->memberid); ?>"
            data-userid="<?php echo e($nodeMember->userid); ?>"
            data-username="<?php echo e($nodeMember->username ?? ''); ?>"
            data-name="<?php echo e($nodeMember->name); ?>"
            data-role="<?php echo e($nodeRelation); ?>"
            data-gender="<?php echo e(isset($nodeMember->gender) ? ucfirst((string) $nodeMember->gender) : '-'); ?>"
            data-age="<?php echo e($nodeMember->age ?? '-'); ?>"
            data-birthdate="<?php echo e($nodeMember->birthdate ?? '-'); ?>"
            data-birthplace="<?php echo e($nodeMember->birthplace ?? '-'); ?>"
            data-status="<?php echo e(ucfirst((string) ($nodeMember->life_status ?? '-'))); ?>"
            data-marital-status="<?php echo e(ucfirst((string) ($nodeMember->marital_status ?? '-'))); ?>"
            data-life-status-raw="<?php echo e($memberLifeStatusRaw); ?>"
            data-deaddate="<?php echo e($nodeMember->deaddate ?? ''); ?>"
            data-child-parenting-mode="<?php echo e($memberChildParentingModeRaw); ?>"
            data-grave-location-url="<?php echo e($nodeMember->grave_location_url ?? ''); ?>"
            data-has-partner="<?php echo e($hasPartner ? '1' : '0'); ?>"
            data-blood-type="<?php echo e($nodeMember->bloodtype ?: '-'); ?>"
            data-email="<?php echo e($nodeMember->email ?: '-'); ?>"
            data-phone="<?php echo e($nodeMember->phonenumber ?: '-'); ?>"
            data-social-media="<?php echo e($nodeMember->social_media ?: '-'); ?>"
            data-social-media-items="<?php echo e(json_encode($nodeMember->social_media_items ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>"
            data-job="<?php echo e($nodeMember->job ?: '-'); ?>"
            data-address="<?php echo e($nodeMember->address ?: '-'); ?>"
            data-education="<?php echo e($nodeMember->education_status ?: '-'); ?>"
            data-photo="<?php echo e($nodeMember->picture); ?>"
            data-isme="<?php echo e($isMe ? '1' : '0'); ?>"
            data-can-delete-partner="<?php echo e($memberCanDeletePartner ? '1' : '0'); ?>"
            data-can-divorce-partner="<?php echo e($memberCanDivorcePartner ? '1' : '0'); ?>"
            data-can-delete-child="<?php echo e($memberCanDeleteChild ? '1' : '0'); ?>"
            data-can-update-life-status="<?php echo e($memberCanUpdateLifeStatus ? '1' : '0'); ?>"
            data-can-edit-profile="<?php echo e($memberCanEditProfile ? '1' : '0'); ?>"
            data-can-edit-child-parenting-mode="<?php echo e($isSuperadmin || $memberChildParentingModeRaw !== '' ? '1' : '0'); ?>"
        >
            <span class="member-photo-wrap">
                <img class="member-photo" src="<?php echo e($nodeMember->picture); ?>" alt="<?php echo e($nodeMember->name); ?>">
            </span>
            <h4 class="member-name"><?php echo e($nodeMember->name); ?></h4>
            <p class="member-role"><?php echo e($nodeRoleDisplay); ?></p>
            <p class="member-age">Age: <?php echo e($nodeMember->age ?? '-'); ?></p>
            <p class="member-marital">Marital: <?php echo e(isset($nodeMember->marital_status) ? ucfirst((string) $nodeMember->marital_status) : '-'); ?></p>
            <span class="member-generation-badge">Gen <?php echo e($nodeGeneration); ?></span>
            <?php if ($isMe): ?>
                <span class="member-me-badge">Me</span>
            <?php endif; ?>
        </article>

        <?php foreach ($nodePartners as $partner): ?>
            <?php $isPartnerMe = (int) ($partner->userid ?? 0) === $currentUserId; ?>
            <?php $partnerRelation = $relationMap[(int) ($partner->memberid ?? 0)] ?? ($isPartnerMe ? 'Me' : 'Partner'); ?>
            <?php $partnerLifeStatusRaw = strtolower((string) ($partner->life_status ?? 'alive')); ?>
            <?php $partnerGenderRaw = strtolower((string) ($partner->gender ?? 'male')); ?>
            <?php $partnerCanDeletePartner = !empty($canDeletePartnerMap[(int) ($partner->memberid ?? 0)]); ?>
            <?php $partnerCanDivorcePartner = $partnerCanDeletePartner || ($isPartnerMe && $canCurrentMemberManageDivorce && $hasPartner); ?>
            <?php $partnerCanDeleteChild = !empty($canDeleteChildMap[(int) ($partner->memberid ?? 0)]); ?>
            <?php $partnerCanUpdateLifeStatus = $isSuperadmin || !empty($canUpdateLifeStatusMap[(int) ($partner->memberid ?? 0)]); ?>
            <?php $partnerCanEditProfile = !empty($canEditProfileMap[(int) ($partner->memberid ?? 0)]); ?>
            <?php $partnerMemberId = (int) ($partner->memberid ?? 0); ?>
            <?php $partnerChildParentingModeRaw = ''; ?>
            <?php foreach ($childParentingModeMap as $parentId => $childModes): ?>
                <?php if (!is_array($childModes) || !array_key_exists($partnerMemberId, $childModes)) continue; ?>
                <?php $resolvedMode = strtolower((string) ($childModes[$partnerMemberId] ?? '')); ?>
                <?php if (in_array($resolvedMode, ['with_current_partner', 'single_parent'], true)): ?>
                    <?php $partnerChildParentingModeRaw = $resolvedMode; ?>
                    <?php break; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <article
                class="member-card <?php echo e($partnerLifeStatusRaw === 'deceased' ? 'is-deceased' : ''); ?> <?php echo e($partnerGenderRaw === 'female' ? 'is-female' : 'is-male'); ?>"
                data-memberid="<?php echo e($partner->memberid); ?>"
                data-userid="<?php echo e($partner->userid); ?>"
                data-username="<?php echo e($partner->username ?? ''); ?>"
                data-name="<?php echo e($partner->name); ?>"
                data-role="<?php echo e($partnerRelation); ?>"
                data-gender="<?php echo e(isset($partner->gender) ? ucfirst((string) $partner->gender) : '-'); ?>"
                data-age="<?php echo e($partner->age ?? '-'); ?>"
                data-birthdate="<?php echo e($partner->birthdate ?? '-'); ?>"
                data-birthplace="<?php echo e($partner->birthplace ?? '-'); ?>"
                data-status="<?php echo e(ucfirst((string) ($partner->life_status ?? '-'))); ?>"
                data-marital-status="<?php echo e(ucfirst((string) ($partner->marital_status ?? '-'))); ?>"
                data-life-status-raw="<?php echo e($partnerLifeStatusRaw); ?>"
                data-deaddate="<?php echo e($partner->deaddate ?? ''); ?>"
                data-child-parenting-mode="<?php echo e($partnerChildParentingModeRaw); ?>"
                data-grave-location-url="<?php echo e($partner->grave_location_url ?? ''); ?>"
                data-has-partner="<?php echo e($hasPartner ? '1' : '0'); ?>"
                data-blood-type="<?php echo e($partner->bloodtype ?: '-'); ?>"
                data-email="<?php echo e($partner->email ?: '-'); ?>"
                data-phone="<?php echo e($partner->phonenumber ?: '-'); ?>"
                data-social-media="<?php echo e($partner->social_media ?: '-'); ?>"
                data-social-media-items="<?php echo e(json_encode($partner->social_media_items ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?>"
                data-job="<?php echo e($partner->job ?: '-'); ?>"
                data-address="<?php echo e($partner->address ?: '-'); ?>"
                data-education="<?php echo e($partner->education_status ?: '-'); ?>"
                data-photo="<?php echo e($partner->picture); ?>"
                data-isme="<?php echo e($isPartnerMe ? '1' : '0'); ?>"
                data-can-delete-partner="<?php echo e($partnerCanDeletePartner ? '1' : '0'); ?>"
                data-can-divorce-partner="<?php echo e($partnerCanDivorcePartner ? '1' : '0'); ?>"
                data-can-delete-child="<?php echo e($partnerCanDeleteChild ? '1' : '0'); ?>"
                data-can-update-life-status="<?php echo e($partnerCanUpdateLifeStatus ? '1' : '0'); ?>"
                data-can-edit-profile="<?php echo e($partnerCanEditProfile ? '1' : '0'); ?>"
                data-can-edit-child-parenting-mode="<?php echo e($isSuperadmin || $partnerChildParentingModeRaw !== '' ? '1' : '0'); ?>"
            >
                <span class="member-photo-wrap">
                    <img class="member-photo" src="<?php echo e($partner->picture); ?>" alt="<?php echo e($partner->name); ?>">
                </span>
                <h4 class="member-name"><?php echo e($partner->name); ?></h4>
                <p class="member-role"><?php echo e($partnerRelation); ?></p>
                <p class="member-age">Age: <?php echo e($partner->age ?? '-'); ?></p>
                <p class="member-marital">Marital: <?php echo e(isset($partner->marital_status) ? ucfirst((string) $partner->marital_status) : '-'); ?></p>
                <span class="member-generation-badge">Gen <?php echo e($nodeGeneration); ?></span>
                <?php if ($isPartnerMe): ?>
                    <span class="member-me-badge">Me</span>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($nodeChildren)): ?>
        <ul class="<?php echo e($childGroupClass); ?>">
            <?php foreach ($childrenOrdered as $childData): ?>
                <?php echo view('all.partials.family-tree-node', [
                    'node' => $childData['node'],
                    'initialMemberId' => $initialMemberId,
                    'relationMap' => $relationMap,
                    'canDeletePartnerMap' => $canDeletePartnerMap,
                    'canCurrentMemberManageDivorce' => $canCurrentMemberManageDivorce,
                    'canDeleteChildMap' => $canDeleteChildMap,
                    'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                    'canEditProfileMap' => $canEditProfileMap,
                    'childParentingModeMap' => $childParentingModeMap,
                    'highlightParentMemberId' => $highlightParentMemberId,
                    'highlightParentForName' => $highlightParentForName,
                    'parentingMode' => $childData['mode'],
                    'singleParentAnchorMemberId' => (string) ($childData['mode'] ?? '') === 'single_parent'
                        ? (int) ($childData['single_parent_anchor_memberid'] ?? $nodeMemberId)
                        : 0,
                    'maxVisibleDepth' => $maxVisibleDepth,
                    'depth' => $depth + 1,
                ]); ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>






