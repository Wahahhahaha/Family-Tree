<?php
    $nodeMember = $node['member'];
    $nodePartners = $node['partners'] ?? [];
    $nodeChildren = $node['children'] ?? [];
    $relationMap = $relationMap ?? [];
    $canDeletePartnerMap = $canDeletePartnerMap ?? [];
    $canDeleteChildMap = $canDeleteChildMap ?? [];
    $canUpdateLifeStatusMap = $canUpdateLifeStatusMap ?? [];
    $depth = (int) ($depth ?? 0);
    $maxVisibleDepth = isset($maxVisibleDepth) ? (int) $maxVisibleDepth : 99;
    $isActive = (int) ($nodeMember->memberid ?? 0) === (int) ($initialMemberId ?? 0);
    $currentUserId = (int) (session('authenticated_user.userid') ?? 0);
    $isMe = (int) ($nodeMember->userid ?? 0) === $currentUserId;
    $nodeRelation = $relationMap[(int) ($nodeMember->memberid ?? 0)] ?? ($isMe ? 'Me' : 'Family Member');
    $memberLifeStatusRaw = strtolower((string) ($nodeMember->life_status ?? 'alive'));
    $memberCanDeletePartner = !empty($canDeletePartnerMap[(int) ($nodeMember->memberid ?? 0)]);
    $memberCanDeleteChild = !empty($canDeleteChildMap[(int) ($nodeMember->memberid ?? 0)]);
    $memberCanUpdateLifeStatus = !empty($canUpdateLifeStatusMap[(int) ($nodeMember->memberid ?? 0)]);
?>

<li>
    <div class="partner-row">
        <article
            class="member-card <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($memberLifeStatusRaw === 'deceased' ? 'is-deceased' : ''); ?>"
            data-memberid="<?php echo e($nodeMember->memberid); ?>"
            data-name="<?php echo e($nodeMember->name); ?>"
            data-role="<?php echo e($nodeRelation); ?>"
            data-age="<?php echo e($nodeMember->age ?? '-'); ?>"
            data-birthdate="<?php echo e($nodeMember->birthdate ?? '-'); ?>"
            data-birthplace="<?php echo e($nodeMember->birthplace ?? '-'); ?>"
            data-status="<?php echo e(ucfirst((string) ($nodeMember->life_status ?? '-'))); ?>"
            data-life-status-raw="<?php echo e($memberLifeStatusRaw); ?>"
            data-email="<?php echo e($nodeMember->email ?: '-'); ?>"
            data-phone="<?php echo e($nodeMember->phonenumber ?: '-'); ?>"
            data-job="<?php echo e($nodeMember->job ?: '-'); ?>"
            data-address="<?php echo e($nodeMember->address ?: '-'); ?>"
            data-education="<?php echo e($nodeMember->education_status ?: '-'); ?>"
            data-photo="<?php echo e($nodeMember->picture); ?>"
            data-isme="<?php echo e($isMe ? '1' : '0'); ?>"
            data-can-delete-partner="<?php echo e($memberCanDeletePartner ? '1' : '0'); ?>"
            data-can-delete-child="<?php echo e($memberCanDeleteChild ? '1' : '0'); ?>"
            data-can-update-life-status="<?php echo e($memberCanUpdateLifeStatus ? '1' : '0'); ?>"
        >
            <img class="member-photo" src="<?php echo e($nodeMember->picture); ?>" alt="<?php echo e($nodeMember->name); ?>">
            <h4 class="member-name"><?php echo e($nodeMember->name); ?></h4>
            <p class="member-role"><?php echo e($nodeRelation); ?></p>
            <p class="member-age">Age: <?php echo e($nodeMember->age ?? '-'); ?></p>
            <?php if ($isMe): ?>
                <span class="member-me-badge">Me</span>
            <?php endif; ?>
        </article>

        <?php foreach ($nodePartners as $partner): ?>
            <?php $isPartnerMe = (int) ($partner->userid ?? 0) === $currentUserId; ?>
            <?php $partnerRelation = $relationMap[(int) ($partner->memberid ?? 0)] ?? ($isPartnerMe ? 'Me' : 'Partner'); ?>
            <?php $partnerLifeStatusRaw = strtolower((string) ($partner->life_status ?? 'alive')); ?>
            <?php $partnerCanDeletePartner = !empty($canDeletePartnerMap[(int) ($partner->memberid ?? 0)]); ?>
            <?php $partnerCanDeleteChild = !empty($canDeleteChildMap[(int) ($partner->memberid ?? 0)]); ?>
            <?php $partnerCanUpdateLifeStatus = !empty($canUpdateLifeStatusMap[(int) ($partner->memberid ?? 0)]); ?>
            <article
                class="member-card <?php echo e($partnerLifeStatusRaw === 'deceased' ? 'is-deceased' : ''); ?>"
                data-memberid="<?php echo e($partner->memberid); ?>"
                data-name="<?php echo e($partner->name); ?>"
                data-role="<?php echo e($partnerRelation); ?>"
                data-age="<?php echo e($partner->age ?? '-'); ?>"
                data-birthdate="<?php echo e($partner->birthdate ?? '-'); ?>"
                data-birthplace="<?php echo e($partner->birthplace ?? '-'); ?>"
                data-status="<?php echo e(ucfirst((string) ($partner->life_status ?? '-'))); ?>"
                data-life-status-raw="<?php echo e($partnerLifeStatusRaw); ?>"
                data-email="<?php echo e($partner->email ?: '-'); ?>"
                data-phone="<?php echo e($partner->phonenumber ?: '-'); ?>"
                data-job="<?php echo e($partner->job ?: '-'); ?>"
                data-address="<?php echo e($partner->address ?: '-'); ?>"
                data-education="<?php echo e($partner->education_status ?: '-'); ?>"
                data-photo="<?php echo e($partner->picture); ?>"
                data-isme="<?php echo e($isPartnerMe ? '1' : '0'); ?>"
                data-can-delete-partner="<?php echo e($partnerCanDeletePartner ? '1' : '0'); ?>"
                data-can-delete-child="<?php echo e($partnerCanDeleteChild ? '1' : '0'); ?>"
                data-can-update-life-status="<?php echo e($partnerCanUpdateLifeStatus ? '1' : '0'); ?>"
            >
                <img class="member-photo" src="<?php echo e($partner->picture); ?>" alt="<?php echo e($partner->name); ?>">
                <h4 class="member-name"><?php echo e($partner->name); ?></h4>
                <p class="member-role"><?php echo e($partnerRelation); ?></p>
                <p class="member-age">Age: <?php echo e($partner->age ?? '-'); ?></p>
                <?php if ($isPartnerMe): ?>
                    <span class="member-me-badge">Me</span>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($nodeChildren) && $depth < $maxVisibleDepth): ?>
        <ul>
            <?php foreach ($nodeChildren as $childNode): ?>
                <?php echo view('all.partials.family-tree-node', [
                    'node' => $childNode,
                    'initialMemberId' => $initialMemberId,
                    'relationMap' => $relationMap,
                    'canDeletePartnerMap' => $canDeletePartnerMap,
                    'canDeleteChildMap' => $canDeleteChildMap,
                    'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                    'maxVisibleDepth' => $maxVisibleDepth,
                    'depth' => $depth + 1,
                ]); ?>
            <?php endforeach; ?>
        </ul>
    <?php elseif (!empty($nodeChildren)): ?>
        <button type="button" class="btn btn-ghost tree-see-more-btn" data-open="0">See more</button>
        <ul class="tree-extra-children hidden">
            <?php foreach ($nodeChildren as $childNode): ?>
                <?php echo view('all.partials.family-tree-node', [
                    'node' => $childNode,
                    'initialMemberId' => $initialMemberId,
                    'relationMap' => $relationMap,
                    'canDeletePartnerMap' => $canDeletePartnerMap,
                    'canDeleteChildMap' => $canDeleteChildMap,
                    'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                    'maxVisibleDepth' => $maxVisibleDepth,
                    'depth' => $depth + 1,
                ]); ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
