<?php
    $members = $members ?? collect();
    $renderTreeRoots = $renderTreeRoots ?? [];
    $firstMember = $firstMember ?? null;
    $relationMap = $relationMap ?? [];
    $canDeletePartnerMap = $canDeletePartnerMap ?? [];
    $canDeleteChildMap = $canDeleteChildMap ?? [];
    $canUpdateLifeStatusMap = $canUpdateLifeStatusMap ?? [];
?>

<?php if ($members->isEmpty()): ?>
    <p>No family member data found for users with level ID 2.</p>
<?php else: ?>
    <div id="treeZoomStage" class="tree-zoom-stage">
        <div id="treeCanvas" class="tree">
            <ul>
                <?php foreach ($renderTreeRoots as $node): ?>
                    <?php echo view('all.partials.family-tree-node', [
                        'node' => $node,
                        'initialMemberId' => $firstMember->memberid ?? 0,
                        'relationMap' => $relationMap,
                        'canDeletePartnerMap' => $canDeletePartnerMap,
                        'canDeleteChildMap' => $canDeleteChildMap,
                        'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                        'maxVisibleDepth' => 99,
                        'depth' => 0,
                    ]); ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>
