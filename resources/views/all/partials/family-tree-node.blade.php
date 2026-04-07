<?php
    $nodeMember = $node['member'];
    $nodePartners = $node['partners'] ?? [];
    $nodeChildren = $node['children'] ?? [];
    $relationMap = $relationMap ?? [];
    $isActive = (int) ($nodeMember->memberid ?? 0) === (int) ($initialMemberId ?? 0);
    $currentUserId = (int) (session('authenticated_user.userid') ?? 0);
    $isMe = (int) ($nodeMember->userid ?? 0) === $currentUserId;
    $nodeRelation = $relationMap[(int) ($nodeMember->memberid ?? 0)] ?? ($isMe ? 'Me' : 'Family Member');
?>

<li>
    <div class="partner-row">
        <article
            class="member-card <?php echo e($isActive ? 'active' : ''); ?>"
            data-memberid="<?php echo e($nodeMember->memberid); ?>"
            data-name="<?php echo e($nodeMember->name); ?>"
            data-role="<?php echo e($nodeRelation); ?>"
            data-age="<?php echo e($nodeMember->age ?? '-'); ?>"
            data-status="<?php echo e(ucfirst((string) ($nodeMember->life_status ?? '-'))); ?>"
            data-job="<?php echo e($nodeMember->job ?: '-'); ?>"
            data-address="<?php echo e($nodeMember->address ?: '-'); ?>"
            data-education="<?php echo e($nodeMember->education_status ?: '-'); ?>"
            data-photo="<?php echo e($nodeMember->picture); ?>"
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
            <article
                class="member-card"
                data-memberid="<?php echo e($partner->memberid); ?>"
                data-name="<?php echo e($partner->name); ?>"
                data-role="<?php echo e($partnerRelation); ?>"
                data-age="<?php echo e($partner->age ?? '-'); ?>"
                data-status="<?php echo e(ucfirst((string) ($partner->life_status ?? '-'))); ?>"
                data-job="<?php echo e($partner->job ?: '-'); ?>"
                data-address="<?php echo e($partner->address ?: '-'); ?>"
                data-education="<?php echo e($partner->education_status ?: '-'); ?>"
                data-photo="<?php echo e($partner->picture); ?>"
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

    <?php if (!empty($nodeChildren)): ?>
        <ul>
            <?php foreach ($nodeChildren as $childNode): ?>
                <?php echo view('all.partials.family-tree-node', [
                    'node' => $childNode,
                    'initialMemberId' => $initialMemberId,
                    'relationMap' => $relationMap,
                ]); ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
