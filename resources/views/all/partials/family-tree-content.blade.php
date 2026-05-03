<?php
    $members = $members ?? collect();
    $renderTreeRoots = $renderTreeRoots ?? [];
    $firstMember = $firstMember ?? null;
    $relationMap = $relationMap ?? [];
    $canDeletePartnerMap = $canDeletePartnerMap ?? [];
    $canDeleteChildMap = $canDeleteChildMap ?? [];
    $canUpdateLifeStatusMap = $canUpdateLifeStatusMap ?? [];
    $canEditProfileMap = $canEditProfileMap ?? [];
    $childParentingModeMap = $childParentingModeMap ?? [];
    $highlightParentMemberId = (int) ($highlightParentMemberId ?? 0);
    $highlightParentForName = (string) ($highlightParentForName ?? '');
    $showUpperTree = (bool) ($showUpperTree ?? false);
    $showLowerTree = (bool) ($showLowerTree ?? false);
    $hasHiddenUpperTreeLevels = (bool) ($hasHiddenUpperTreeLevels ?? false);
    $hasHiddenLowerTreeLevels = (bool) ($hasHiddenLowerTreeLevels ?? false);
    $toggleUpperTreeUrl = (string) ($toggleUpperTreeUrl ?? '');
    $toggleLowerTreeUrl = (string) ($toggleLowerTreeUrl ?? '');
    $showBottomToggleButton = ($hasHiddenLowerTreeLevels || $showLowerTree) && $toggleLowerTreeUrl !== '';
?>

<?php if ($members->isEmpty()): ?>
    <p>No family member data found for users with level ID 2.</p>
<?php else: ?>
    <div id="treeZoomStage" class="tree-zoom-stage">
        <div id="treeCanvas" class="tree">
            <div class="tree-connector-layer" aria-hidden="true">
                <svg id="treeConnectorSvg" class="tree-connector-svg" focusable="false"></svg>
            </div>
            <ul>
                <?php foreach (array_reverse($renderTreeRoots) as $node): ?>
                    <?php echo view('all.partials.family-tree-node', [
                        'node' => $node,
                        'initialMemberId' => $firstMember->memberid ?? 0,
                        'relationMap' => $relationMap,
                        'canDeletePartnerMap' => $canDeletePartnerMap,
                        'canDeleteChildMap' => $canDeleteChildMap,
                        'canUpdateLifeStatusMap' => $canUpdateLifeStatusMap,
                        'canEditProfileMap' => $canEditProfileMap,
                        'childParentingModeMap' => $childParentingModeMap,
                        'highlightParentMemberId' => $highlightParentMemberId,
                        'highlightParentForName' => $highlightParentForName,
                        'maxVisibleDepth' => 99,
                        'depth' => 0,
                    ]); ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

<?php endif; ?>
