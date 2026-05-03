@extends('layouts.app')

@section('title', $systemSettings['website_name'] ?? 'Family Tree')
@section('body-class', 'page-family-tree')

@section('styles')
<style>
    body.page-family-tree {
        overflow-y: auto;
        overflow-x: hidden;
        background: #f5f8fb;
        margin: 0;
    }

    body.page-family-tree .wrapper {
        width: 100%;
        max-width: none;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: visible;
        padding: 12px 15px 15px;
    }

    body.page-family-tree .home-page-stats,
    body.page-family-tree .home-page-panel,
    body.page-family-tree .topbar,
    body.page-family-tree .tree-container,
    body.page-family-tree .detail {
        background: #ffffff;
        border: 1px solid #eaf1f6;
        border-radius: 24px;
        box-shadow: 0 16px 44px rgba(16, 53, 77, 0.11);
    }

    body.page-family-tree .topbar {
        margin: 0 0 10px;
    }

    body.page-family-tree .home-page-stats {
        margin: 0 0 10px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    body.page-family-tree .home-page-panel {
        margin: 0;
        box-sizing: border-box;
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
        overflow: visible;
    }

    body.page-family-tree .home-page-panel .tree-container {
        overflow: visible;
    }

    body.page-family-tree .home-page-panel .tree-scroll {
        min-height: 0;
    }

    @media (max-width: 768px) {
        body.page-family-tree .wrapper {
            padding: 8px 8px 16px;
        }

        body.page-family-tree .home-page-stats {
            grid-template-columns: 1fr;
            margin-bottom: 12px;
        }
    }
</style>
@endsection

@section('content')
<?php
    $childParentingModeDisplayMap = [];
    foreach (($childParentingModeMap ?? []) as $parentId => $childModes) {
        foreach ((array) $childModes as $childId => $mode) {
            $childId = (int) $childId;
            $mode = (string) $mode;
            if ($childId <= 0 || !in_array($mode, ['with_current_partner', 'single_parent'], true)) {
                continue;
            }

            if ($mode === 'with_current_partner') {
                $childParentingModeDisplayMap[$childId] = $mode;
                continue;
            }

            if (!isset($childParentingModeDisplayMap[$childId])) {
                $childParentingModeDisplayMap[$childId] = $mode;
            }
        }
    }
?>
<script>
    window.familyTreeChildParentingModeMap = <?php echo json_encode($childParentingModeDisplayMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>
<div class="wrapper">
    <?php
        $members = $familyMembers ?? collect();
        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = $members->firstWhere('userid', $currentUserId);
        $firstMember = $currentMember ?: $members->first();
        $restMembers = $members->slice(1);
        $renderTreeRoots = $treeRoots ?? [];
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $isSuperadmin = $currentRoleId === 1;
        $canDeleteAnyUserFromHome = in_array($currentRoleId, [1, 2], true);
        $canEditOwnProfile = $currentLevelId === 2 && !empty($currentFamilyProfile);
        $canAddMemberFromHome = $isSuperadmin || $currentRoleId === 3 || $currentLevelId === 2;
        $targetMember = $currentMember ?: $firstMember;
        $canAddByAge = false;
        $currentMemberBirthdate = trim((string) ($targetMember->birthdate ?? ''));
        if ($currentMemberBirthdate !== '') {
            try {
                $canAddByAge = \Carbon\Carbon::parse($currentMemberBirthdate)->age >= 18;
            } catch (\Throwable $e) {
                $canAddByAge = false;
            }
        }
        if ($isSuperadmin) {
            $canAddByAge = true;
        }
        $canAddMemberFromHome = $canAddMemberFromHome && $canAddByAge;
        $activePanel = old('home_panel', 'profile');
        $currentMemberHasPartner = (bool) ($currentMemberHasPartner ?? false);
        $treeSummaryText = (string) ($treeSummaryText ?? '');
        $showUpperTree = (bool) ($showUpperTree ?? false);
        $showLowerTree = (bool) ($showLowerTree ?? false);
        $hasHiddenUpperTreeLevels = (bool) ($hasHiddenUpperTreeLevels ?? false);
        $hasHiddenLowerTreeLevels = (bool) ($hasHiddenLowerTreeLevels ?? false);
        $toggleUpperTreeUrl = (string) ($toggleUpperTreeUrl ?? '');
        $toggleLowerTreeUrl = (string) ($toggleLowerTreeUrl ?? '');
        $showTopToggleButton = ($hasHiddenUpperTreeLevels || $showUpperTree) && $toggleUpperTreeUrl !== '';
        $showBottomToggleButton = ($hasHiddenLowerTreeLevels || $showLowerTree) && $toggleLowerTreeUrl !== '';
        $treeHasInlineViewMore = $hasHiddenUpperTreeLevels
            || $hasHiddenLowerTreeLevels
            || $showUpperTree
            || $showLowerTree;
        $defaultRelationType = old('relation_type', 'child');
        if (!in_array($defaultRelationType, ['child', 'partner', 'parent'], true)) {
            $defaultRelationType = 'child';
        }
        $canAddPartnerByAge = false;
        $currentMemberBirthdate = trim((string) ($targetMember->birthdate ?? ''));
        if ($currentMemberBirthdate !== '') {
            try {
                $canAddPartnerByAge = \Carbon\Carbon::parse($currentMemberBirthdate)->age >= 18;
            } catch (\Throwable $e) {
                $canAddPartnerByAge = false;
            }
        }
        if ($isSuperadmin) {
            $canAddPartnerByAge = true;
        }
        if (!$canAddPartnerByAge && $defaultRelationType === 'partner') {
            $defaultRelationType = 'child';
        }
        if (!$canAddByAge && $defaultRelationType === 'child') {
            $defaultRelationType = 'partner';
        }
        $currentMemberMaritalStatusRaw = strtolower((string) ($currentMember->marital_status ?? ''));
        $currentMemberCanUseCurrentPartner = $currentMemberHasPartner || $currentMemberMaritalStatusRaw === 'married';
        $defaultChildParentingMode = old('child_parenting_mode', $currentMemberCanUseCurrentPartner ? 'with_current_partner' : 'single_parent');
        if (!$currentMemberCanUseCurrentPartner && $defaultChildParentingMode === 'with_current_partner') {
            $defaultChildParentingMode = 'single_parent';
        }
        $defaultTargetMemberId = (int) ($targetMember->memberid ?? 0);
        $currentMemberGenderRaw = strtolower((string) ($targetMember->gender ?? ''));
        $defaultPartnerGender = $currentMemberGenderRaw === 'female' ? 'male' : 'female';
        if (!in_array($defaultPartnerGender, ['male', 'female'], true)) {
            $defaultPartnerGender = 'female';
        }
        $isFirstMemberMe = $firstMember && (int) ($firstMember->userid ?? 0) === $currentUserId;
        $relationMap = $relationLabels ?? [];
        $canDeletePartnerMap = $canDeletePartnerMap ?? [];
        $canDeleteChildMap = $canDeleteChildMap ?? [];
        $canUpdateLifeStatusMap = $canUpdateLifeStatusMap ?? [];
        $canEditProfileMap = $canEditProfileMap ?? [];
        $highlightParentMemberId = (int) ($highlightParentMemberId ?? 0);
        $highlightParentForName = (string) ($highlightParentForName ?? '');
        $firstMemberRelation = $firstMember ? ($relationMap[(int) $firstMember->memberid] ?? 'Family Member') : 'Family Member';
        $firstMemberLifeStatusRaw = strtolower((string) ($firstMember->life_status ?? 'alive'));
        $firstChildParentingModeRaw = $firstMember
            ? strtolower((string) ($childParentingModeDisplayMap[(int) ($firstMember->memberid ?? 0)] ?? 'single_parent'))
            : 'single_parent';
        $firstCanDeleteUser = $firstMember ? $canDeleteAnyUserFromHome && (int) ($firstMember->userid ?? 0) !== $currentUserId : false;
        $firstCanDeletePartner = $firstMember ? !empty($canDeletePartnerMap[(int) $firstMember->memberid]) : false;
        $firstCanDeleteChild = $firstMember ? !empty($canDeleteChildMap[(int) $firstMember->memberid]) : false;
        $firstCanUpdateLifeStatus = $firstMember ? !empty($canUpdateLifeStatusMap[(int) $firstMember->memberid]) : false;
        $firstCanEditProfile = $firstMember ? !empty($canEditProfileMap[(int) $firstMember->memberid]) : false;
        if ($isSuperadmin) {
            $firstCanUpdateLifeStatus = true;
        }
        $firstShowActionBlock = $firstCanDeleteUser || $firstCanDeleteChild || $firstCanUpdateLifeStatus || $firstCanEditProfile;
        if ($isSuperadmin) {
            $firstShowActionBlock = true;
        }
        $totalMembers = $members->count();
        $aliveMembers = $members->filter(function ($member) {
            return strtolower((string) ($member->life_status ?? '')) === 'alive';
        })->count();
        $deceasedMembers = $members->filter(function ($member) {
            return strtolower((string) ($member->life_status ?? '')) === 'deceased';
        })->count();
        $flashMessages = [];
        if (session('success')) {
            $flashMessages[] = ['type' => 'success', 'text' => (string) session('success')];
        }
        if (session('error')) {
            $flashMessages[] = ['type' => 'error', 'text' => (string) session('error')];
        }
        if ($errors->any()) {
            foreach ($errors->all() as $errorText) {
                $flashMessages[] = ['type' => 'error', 'text' => (string) $errorText];
            }
        }
        $addMemberSuccessModal = strtolower((string) session('success', '')) === 'new family member has been added.';

        $memberPicture = trim((string) ($firstMember->picture ?? ''));
$memberPictureUrl = '';

if ($memberPicture !== '') {
    $memberPictureUrl = (preg_match('#^https?://#i', $memberPicture) || str_starts_with($memberPicture, 'data:'))
        ? $memberPicture
        : asset(ltrim($memberPicture, '/'));
}
    ?>

    <?php if (!empty($flashMessages)): ?>
        <div id="flashMessageModal" class="message-modal is-open" role="dialog" aria-modal="true" aria-labelledby="flashMessageTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card <?php echo e($addMemberSuccessModal ? 'is-add-member-success' : ''); ?>">
                <h4 id="flashMessageTitle"><?php echo e($addMemberSuccessModal ? 'Member Added' : 'Message'); ?></h4>
                <?php if ($addMemberSuccessModal): ?>
                    <div class="message-modal-success-icon" aria-hidden="true">
                        <span></span>
                    </div>
                <?php endif; ?>
                <div class="message-modal-body">
                    <?php foreach ($flashMessages as $flashMessage): ?>
                        <p class="message-modal-text <?php echo e($flashMessage['type'] === 'success' ? 'is-success' : 'is-error'); ?>">
                            <?php echo e($flashMessage['text']); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <button id="flashMessageOkBtn" type="button" class="btn btn-primary">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <div id="lifeStatusConfirmModal" class="message-modal" role="dialog" aria-modal="true" aria-labelledby="lifeStatusConfirmTitle" aria-hidden="true" style="display:none;">
        <div class="message-modal-backdrop"></div>
        <div class="message-modal-card">
            <h4 id="lifeStatusConfirmTitle">Set Date of Death</h4>
            <div class="message-modal-body">
                <p id="lifeStatusConfirmText" class="message-modal-text is-error">Choose the date of death before saving this status.</p>
                <div id="lifeStatusDeadDateField" class="detail-form-field hidden" style="margin-top: 0;">
                    <label for="lifeStatusDeadDate">Date of Death</label>
                    <input id="lifeStatusDeadDate" type="date" max="<?php echo e(date('Y-m-d')); ?>">
                    <small>This will be saved as `deaddate`.</small>
                </div>
                <p id="lifeStatusConfirmError" class="message-modal-text is-error" style="display:none;"></p>
            </div>
            <div class="modal-actions" style="display:flex; gap:12px; justify-content:flex-end; margin-top: 20px;">
                <button id="lifeStatusConfirmCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                <button id="lifeStatusConfirmBtn" type="button" class="btn btn-primary">Save Date</button>
            </div>
        </div>
    </div>

    <div id="deleteConfirmModal" class="message-modal" role="dialog" aria-modal="true" aria-labelledby="deleteConfirmTitle" aria-hidden="true" style="display:none;">
        <div class="message-modal-backdrop"></div>
        <div class="message-modal-card">
            <h4 id="deleteConfirmTitle">Confirm Delete</h4>
            <div class="message-modal-body">
                <p id="deleteConfirmText" class="message-modal-text is-error">Are you sure you want to delete this item?</p>
            </div>
            <div class="modal-actions" style="display:flex; gap:12px; justify-content:flex-end; margin-top: 20px;">
                <button id="deleteConfirmCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                <button id="deleteConfirmBtn" type="button" class="btn btn-danger-soft">Delete</button>
            </div>
        </div>
    </div>
  
    <style>
        html,
        body {
            height: 100%;
        }

        body.page-family-tree {
            overflow-y: auto;
            overflow-x: hidden;
        }

        .page-family-tree .wrapper {
            width: 100%;
            max-width: none;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: visible;
            padding: 12px 15px 15px;
        }

        .page-family-tree .topbar {
            width: 100%;
            margin: 0 0 10px;
        }

        .page-family-tree .home-page-panel {
            width: 100%;
            max-width: none;
            margin: 0;
            box-sizing: border-box;
            flex: 0 0 auto;
            min-height: auto;
            height: auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
            overflow: visible;
        }

        .page-family-tree .home-page-panel.has-selected-member {
            grid-template-columns: minmax(0, 1fr) minmax(340px, 420px);
            gap: 14px;
        }

        .page-family-tree .home-page-panel.has-selected-member .tree-container {
            grid-column: 1;
            grid-row: 1;
        }

        .page-family-tree .home-page-panel.has-selected-member .detail-shell {
            grid-column: 2;
            grid-row: 1;
            align-self: start;
        }

        .home-page-stats {
            width: 100%;
            max-width: none;
            margin: 0 0 10px;
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            box-sizing: border-box;
        }

        .home-page-stats .stat-card {
            min-width: 0;
            padding: 10px 12px;
        }

        .home-page-stats .stat-card small {
            font-size: 12px;
        }

        .home-page-stats .stat-card h2 {
            font-size: 26px;
            line-height: 1;
        }

        .page-family-tree .home-page-panel .tree-container {
            width: 100%;
            max-width: none;
            height: auto;
            min-height: auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
            align-items: start;
            flex: 0 0 auto;
        }

        .page-family-tree .home-page-panel .detail-shell {
            width: 100%;
            min-width: 0;
        }

        .page-family-tree .home-page-panel .detail {
            min-width: 0;
            height: auto;
            max-height: none;
            overflow: visible;
            display: flex;
            flex-direction: column;
        }

        .page-family-tree .home-page-panel .tree-scroll {
            min-width: 0;
            flex: 0 0 auto;
            min-height: 0;
            max-height: min(68vh, 720px);
            overflow: auto;
            overflow-anchor: none;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 0 24px 10px;
            opacity: 1;
        }

        .page-family-tree .home-page-panel .tree-scroll.is-tree-ready {
            opacity: 1;
        }

        .page-family-tree .home-page-panel .tree-zoom-stage {
            margin: 0 auto;
        }

        .page-family-tree .home-page-panel .tree {
            margin: 0 auto;
        }

        .page-family-tree .home-page-panel .member-card .member-photo-wrap {
            width: 72px;
            height: 72px;
            overflow: hidden;
            flex: 0 0 auto;
        }

        .page-family-tree .home-page-panel .member-card .member-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .page-family-tree .home-page-panel .detail-card,
        .page-family-tree .home-page-panel .detail-form-wrap {
            width: 100%;
            max-width: none;
        }

        .page-family-tree .home-page-panel .detail-card {
            position: relative;
            padding: 10px;
        }

        .page-family-tree .home-page-panel .detail-edit-toggle-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            flex: 0 0 auto;
            padding: 6px 10px;
            font-size: 12px;
            line-height: 1;
            border-radius: 999px;
        }

        .page-family-tree .home-page-panel .detail-edit-form {
            display: grid;
            gap: 12px;
            width: 100%;
            min-width: 0;
            grid-column: 1 / -1;
        }

        .page-family-tree .home-page-panel .detail-edit-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-items: start;
            width: 100%;
            min-width: 0;
            grid-column: 1 / -1;
        }

        .page-family-tree .home-page-panel .detail-edit-grid .detail-form-field {
            margin-top: 0;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0;
            width: 100%;
        }

        .page-family-tree .home-page-panel .detail-edit-grid .detail-form-field.full-width {
            grid-column: 1 / -1;
        }

        .page-family-tree .home-page-panel .detail-edit-grid .detail-form-field#detailEditGenderField,
        .page-family-tree .home-page-panel .detail-edit-grid .detail-form-field#detailEditBloodTypeField {
            grid-column: span 1;
        }

        .page-family-tree .home-page-panel .detail-edit-footer {
            display: flex;
            flex-direction: column;
            gap: 10px;
            grid-column: 1 / -1;
        }

        .page-family-tree .home-page-panel .detail-edit-settings-panel {
            grid-column: 1 / -1;
            display: grid;
            gap: 12px;
            padding-top: 12px;
            border-top: 1px dashed rgba(49, 131, 176, 0.16);
        }

        .page-family-tree .home-page-panel .detail-edit-settings-panel label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 700;
            color: #224257;
        }

        .page-family-tree .home-page-panel .detail-edit-message {
            min-height: 18px;
            margin: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .page-family-tree .home-page-panel .detail-edit-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
            align-items: center;
        }

        .page-family-tree .home-page-panel .detail-list {
            gap: 6px;
        }

        .page-family-tree .home-page-panel .detail-list li {
            font-size: 13px;
        }

        .page-family-tree .home-page-panel .detail-form-wrap {
            flex: 0 0 auto;
            min-height: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: none;
            overflow: visible;
            padding-right: 2px;
        }

        .page-family-tree .home-page-panel .detail-form {
            width: 100%;
        }

        .page-family-tree .home-page-panel .detail-panel-switch {
            margin-bottom: 8px;
        }

        .page-family-tree .home-page-panel:not(.has-selected-member) .detail-shell {
            display: none;
        }

        .page-family-tree .home-page-panel h4 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        @media (max-width: 600px) {
            .page-family-tree .wrapper {
                min-height: auto;
                overflow: visible;
                padding: 8px 8px 16px;
                overflow-x: hidden;
            }

            .page-family-tree .topbar {
                margin-bottom: 10px;
            }

            .page-family-tree .home-page-panel {
                width: 100%;
                margin: 0;
                height: auto;
                flex: 0 0 auto;
                overflow-x: hidden;
            }

            .page-family-tree .home-page-panel.has-selected-member {
                grid-template-columns: 1fr;
                gap: 10px;
                align-items: start;
            }

            .page-family-tree .home-page-panel.has-selected-member .tree-container,
            .page-family-tree .home-page-panel.has-selected-member .detail-shell {
                grid-column: auto;
                grid-row: auto;
            }

            .home-page-stats {
                width: 100%;
                margin: 0 0 12px;
                grid-template-columns: 1fr;
            }

            .page-family-tree .home-page-panel .tree-container,
            .page-family-tree .home-page-panel.has-selected-member .tree-container {
                width: 100%;
                grid-template-columns: 1fr;
                gap: 8px;
                min-width: 0;
                overflow-x: hidden;
            }

            .page-family-tree .home-page-panel.has-selected-member {
                gap: 10px;
            }

            .page-family-tree .home-page-panel .tree-head {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .page-family-tree .home-page-panel .tree-tools {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
                justify-content: space-between;
            }

            .page-family-tree .home-page-panel .tree-zoom-controls {
                flex: 1 1 auto;
                justify-content: flex-end;
            }

            .page-family-tree .home-page-panel .tree-tools .btn {
                min-height: 34px;
                padding: 6px 10px;
            }

            .page-family-tree .home-page-panel .tree-scroll {
                max-height: min(56vh, 620px);
                padding: 0 8px 8px;
            }

            .page-family-tree .home-page-panel .detail-shell {
                display: block;
                width: 100%;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                min-width: 0;
                overflow-x: hidden;
            }

            .page-family-tree .home-page-panel .detail-card {
                width: 100%;
                margin: 0;
                padding: 12px 12px 13px;
                border-radius: 18px;
                border: 1px solid rgba(49, 131, 176, 0.14);
                box-shadow: 0 10px 22px rgba(38, 104, 148, 0.08);
                box-sizing: border-box;
                overflow: hidden;
            }

            .page-family-tree .home-page-panel .detail-photo-wrap {
                width: 56px;
                height: 56px;
                margin: 0 auto 5px;
            }

            .page-family-tree .home-page-panel .detail-photo {
                width: 56px;
                height: 56px;
            }

            .page-family-tree .home-page-panel .detail-name {
                font-size: 13px;
                margin-bottom: 1px;
            }

            .page-family-tree .home-page-panel .detail-role {
                font-size: 10px;
                margin-bottom: 6px;
            }

            .page-family-tree .home-page-panel .detail-list {
                gap: 3px;
            }

            .page-family-tree .home-page-panel .detail-list li {
                font-size: 12px;
                line-height: 1.25;
            }

            .page-family-tree .home-page-panel .detail-panel-switch {
                width: 100%;
                margin: 0 0 8px;
                padding: 5px;
                box-sizing: border-box;
                display: flex;
                gap: 4px;
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.92);
                border: 1px solid rgba(49, 131, 176, 0.12);
                box-shadow: 0 8px 18px rgba(38, 104, 148, 0.05);
            }

            .page-family-tree .home-page-panel .detail-form-wrap {
                margin-top: 10px;
                gap: 8px;
                min-width: 0;
                overflow-x: hidden;
            }

            .page-family-tree .home-page-panel .detail-form,
            .page-family-tree .home-page-panel .detail-panel {
                width: 100%;
                margin: 0;
            }

            .page-family-tree .home-page-panel .detail-form {
                padding: 12px 12px 14px;
                box-sizing: border-box;
                border-radius: 18px;
                border: 1px solid rgba(49, 131, 176, 0.12);
                box-shadow: 0 8px 18px rgba(38, 104, 148, 0.06);
                background: rgba(255, 255, 255, 0.95);
            }

            .page-family-tree .home-page-panel .detail-panel-switch .panel-switch-btn {
                flex: 1 1 0;
                min-width: 0;
                padding: 6px 8px;
                font-size: 11px;
                line-height: 1.1;
            }

            .page-family-tree .home-page-panel .detail-form-field {
                margin-bottom: 2px;
                width: 100%;
            }

            .page-family-tree .home-page-panel .detail-form-field label {
                font-size: 11px;
                margin-bottom: 6px;
                line-height: 1.25;
            }

            .page-family-tree .home-page-panel .detail-form input,
            .page-family-tree .home-page-panel .detail-form select {
                width: 100%;
                min-width: 0;
                min-height: 38px;
                padding: 8px 10px;
                font-size: 12px;
                box-sizing: border-box;
            }

            .page-family-tree .home-page-panel .relation-btn-row {
                gap: 5px;
            }

            .page-family-tree .home-page-panel .relation-btn-row .relation-btn {
                padding: 6px 7px;
                font-size: 10px;
            }

            .page-family-tree .home-page-panel .detail-form .btn.btn-primary {
                width: 100%;
                padding: 8px 9px;
                font-size: 11px;
            }

            .page-family-tree .home-page-panel .detail-edit-actions {
                justify-content: stretch;
                gap: 8px;
            }

            .page-family-tree .home-page-panel .detail-edit-actions .btn {
                flex: 1 1 0;
                min-width: 0;
            }

            .page-family-tree .home-page-panel .detail-edit-grid {
                grid-template-columns: 1fr;
            }

            .page-family-tree .home-page-panel .detail-form,
            .page-family-tree .home-page-panel .detail-panel {
                min-width: 0;
                overflow-x: hidden;
            }

            body.page-family-tree {
                overflow: auto;
                overflow-x: hidden;
            }

            html.page-family-tree,
            body.page-family-tree {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>

    <section class="stats home-page-stats">
        <article class="stat-card">
            <small>Total Family Members</small>
            <h2><?php echo e($totalMembers); ?></h2>
        </article>
        <article class="stat-card">
            <small>Alive</small>
            <h2><?php echo e($aliveMembers); ?></h2>
        </article>
        <article class="stat-card">
            <small>Deceased</small>
            <h2><?php echo e($deceasedMembers); ?></h2>
        </article>
    </section>

    <section class="panel home-page-panel">
        <div class="tree-container">
            <div class="tree-head">
                <div>
     <!--                <h3>Family Tree Structure</h3> -->
              <!--       <p id="treeSummaryText"><?php echo e($treeSummaryText); ?></p> -->
                </div>
                <div class="tree-tools">
                    <button id="saveTreeImageBtn" class="btn btn-soft" type="button">Save Image</button>
                    <div class="tree-zoom-controls">
                        <button id="treeZoomOutBtn" class="btn btn-ghost tree-zoom-btn" type="button" aria-label="Zoom out">-</button>
                        <span id="treeZoomValue" class="tree-zoom-value">100%</span>
                        <button id="treeZoomInBtn" class="btn btn-ghost tree-zoom-btn" type="button" aria-label="Zoom in">+</button>
                    </div>
                </div>
            </div>

            <div id="treeToggleTopBtn" class="tree-root-toggle-wrap <?php echo e($showTopToggleButton ? '' : 'hidden'); ?>">
                <button
                    type="button"
                    class="btn btn-soft tree-expand-toggle"
                    data-tree-direction="upper"
                    data-tree-expanded="<?php echo e($showUpperTree ? '1' : '0'); ?>"
                    data-tree-toggle-url="<?php echo e($toggleUpperTreeUrl); ?>"
                >
                    <?php echo e($showUpperTree ? 'Hide ancestors' : 'View more ancestors'); ?>
                </button>
            </div>

            <div id="treeScrollArea" class="tree-scroll <?php echo e($treeHasInlineViewMore ? 'has-inline-tree-toggle' : ''); ?>">
                <?php if (isset($treeHtml) && $treeHtml !== ''): ?>
                    <?php echo $treeHtml; ?>
                <?php else: ?>
                    @include('all.partials.family-tree-content', [
                        'members' => $members,
                        'renderTreeRoots' => $renderTreeRoots,
                        'firstMember' => $firstMember,
                        'relationMap' => $relationMap,
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
                    ])
                <?php endif; ?>
            </div>

            <div id="treeToggleBottomWrap" class="tree-root-toggle-wrap <?php echo e($showBottomToggleButton ? '' : 'hidden'); ?>">
                <button
                    type="button"
                    class="btn btn-soft tree-expand-toggle"
                    data-tree-direction="lower"
                    data-tree-expanded="<?php echo e($showLowerTree ? '1' : '0'); ?>"
                    data-tree-toggle-url="<?php echo e($toggleLowerTreeUrl); ?>"
                >
                    <?php echo e($showLowerTree ? 'Hide descendants' : 'View more descendants'); ?>
                </button>
            </div>
        </div>

        <aside id="detailSidebar" class="detail detail-shell hidden">
            <?php if ($canEditOwnProfile || $canAddMemberFromHome): ?>
                <div class="detail-panel-switch">
                    <button
                        id="profilePanelBtn"
                        type="button"
                        class="btn btn-ghost panel-switch-btn <?php echo e($activePanel !== 'add-member' ? 'is-active' : ''); ?>"
                    >
                        Detail
                    </button>
                    <?php if ($canAddMemberFromHome): ?>
                        <button
                            id="addMemberPanelBtn"
                            type="button"
                            class="btn btn-ghost panel-switch-btn <?php echo e($activePanel === 'add-member' ? 'is-active' : ''); ?>"
                        >
                            Add Member
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div id="memberDetailBlock" class="hidden <?php echo e($activePanel === 'add-member' ? 'hidden' : ''); ?>">
                <h4>Member Details</h4>
                <div id="detailCard" class="detail-card <?php echo e($firstMemberLifeStatusRaw === 'deceased' ? 'is-deceased' : ''); ?>">
                    <?php if ($isSuperadmin): ?>
                        <button id="detailEditBtn" type="button" class="btn btn-soft detail-edit-toggle-btn">Edit</button>
                    <?php endif; ?>
                    <div id="detailPhotoWrap" class="detail-photo-wrap">
                        <img
                        id="detailPhoto"
                        class="detail-photo"
                        src="<?php echo e($memberPictureUrl); ?>"
                        alt="<?php echo e($firstMember->name ?? 'Member'); ?>"
                        data-isme="<?php echo e($isFirstMemberMe ? '1' : '0'); ?>"
                        >
                    </div>
                    <h5 id="detailName" class="detail-name"><?php echo e($firstMember->name ?? '-'); ?></h5>
                    <p id="detailRole" class="detail-role"><?php echo e($firstMemberRelation); ?></p>

                    <div id="detailViewBlock">
                        <ul class="detail-list">
                            <li><span>Username</span><strong id="detailUsername"><?php echo e($firstMember->username ?? '-'); ?></strong></li>
                            <li><span>Gender</span><strong id="detailGender"><?php echo e(isset($firstMember->gender) ? ucfirst((string) $firstMember->gender) : '-'); ?></strong></li>
                            <li><span>Age</span><strong id="detailAge"><?php echo e($firstMember->age ?? '-'); ?></strong></li>
                            <li><span>Date of Birth</span><strong id="detailBirthdate"><?php echo e($firstMember->birthdate ?? '-'); ?></strong></li>
                            <li><span>Birthplace</span><strong id="detailBirthplace"><?php echo e($firstMember->birthplace ?? '-'); ?></strong></li>
                            <li><span>Blood Type</span><strong id="detailBloodType"><?php echo e($firstMember->bloodtype ?? '-'); ?></strong></li>
                            <li><span>Life Status</span><strong id="detailStatus"><?php echo e(isset($firstMember->life_status) ? ucfirst((string) $firstMember->life_status) : '-'); ?></strong></li>
                            <li><span>Marital Status</span><strong id="detailMaritalStatus"><?php echo e(isset($firstMember->marital_status) ? ucfirst((string) $firstMember->marital_status) : '-'); ?></strong></li>
                            <li><span>Phone</span><strong id="detailPhone"><?php echo e($firstMember->phonenumber ?? '-'); ?></strong></li>
                            <li><span>Email</span><strong id="detailEmail"><?php echo e($firstMember->email ?? '-'); ?></strong></li>
                            <li class="detail-social-media-row"><span>Social Media</span><strong id="detailSocialMedia" class="detail-social-media"><?php echo e($firstMember->social_media ?? '-'); ?></strong></li>
                            <li><span>Job</span><strong id="detailJob"><?php echo e($firstMember->job ?? '-'); ?></strong></li>
                            <li><span>Address</span><strong id="detailAddress"><?php echo e($firstMember->address ?? '-'); ?></strong></li>
                            <li><span>Education</span><strong id="detailEducation"><?php echo e($firstMember->education_status ?? '-'); ?></strong></li>
                        </ul>
                    </div>

                    <?php if ($isSuperadmin || $currentRoleId === 2 || $currentLevelId === 2 || $firstCanEditProfile): ?>
                        <form
                            id="memberDetailEditForm"
                            class="detail-edit-form hidden"
                            method="POST"
                            action="/management/users/<?php echo e((int) ($firstMember->userid ?? 0)); ?>/update"
                        >
                            <?php echo csrf_field(); ?>
                            <input type="hidden" id="detailEditMemberIdInput" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">

                            <div class="detail-edit-grid">
                                <div class="detail-form-field">
                                    <label for="detailEditUsername">Username</label>
                                    <input id="detailEditUsername" type="text" name="username" value="<?php echo e($firstMember->username ?? ''); ?>" required>
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditName">Name</label>
                                    <input id="detailEditName" type="text" name="name" value="<?php echo e($firstMember->name ?? ''); ?>" placeholder="Full name">
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditEmail">Email</label>
                                    <input id="detailEditEmail" type="email" name="email" value="<?php echo e($firstMember->email ?? ''); ?>" placeholder="Email address">
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditPhone">Phone</label>
                                    <input id="detailEditPhone" type="text" name="phonenumber" value="<?php echo e($firstMember->phonenumber ?? ''); ?>" placeholder="Phone number">
                                </div>
                                <div id="detailEditGenderField" class="detail-form-field">
                                    <label for="detailEditGender">Gender</label>
                                    <select id="detailEditGender" name="gender">
                                        <option value="">Select gender</option>
                                        <option value="male" <?php echo e(strtolower((string) ($firstMember->gender ?? '')) === 'male' ? 'selected' : ''); ?>>Male</option>
                                        <option value="female" <?php echo e(strtolower((string) ($firstMember->gender ?? '')) === 'female' ? 'selected' : ''); ?>>Female</option>
                                    </select>
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditBirthdate">Birthdate</label>
                                    <input id="detailEditBirthdate" type="date" name="birthdate" value="<?php echo e($firstMember->birthdate ?? ''); ?>">
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditBirthplace">Birthplace</label>
                                    <input id="detailEditBirthplace" type="text" name="birthplace" value="<?php echo e($firstMember->birthplace ?? ''); ?>" placeholder="Birthplace">
                                </div>
                                <div id="detailEditBloodTypeField" class="detail-form-field">
                                    <label for="detailEditBloodType">Blood Type</label>
                                    <select id="detailEditBloodType" name="bloodtype">
                                        <option value="">Select blood type</option>
                                        <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bloodTypeOption): ?>
                                            <option value="<?php echo e($bloodTypeOption); ?>" <?php echo e(strtoupper((string) ($firstMember->bloodtype ?? '')) === $bloodTypeOption ? 'selected' : ''); ?>><?php echo e($bloodTypeOption); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditMaritalStatus">Marital Status</label>
                                    <input id="detailEditMaritalStatus" type="text" name="marital_status" value="<?php echo e($firstMember->marital_status ?? ''); ?>" placeholder="Marital status">
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditJob">Job</label>
                                    <input id="detailEditJob" type="text" name="job" value="<?php echo e($firstMember->job ?? ''); ?>" placeholder="Job">
                                </div>
                                <div class="detail-form-field">
                                    <label for="detailEditEducation">Education</label>
                                    <input id="detailEditEducation" type="text" name="education_status" value="<?php echo e($firstMember->education_status ?? ''); ?>" placeholder="Education">
                                </div>
                                <div class="detail-form-field full-width">
                                    <label for="detailEditAddress">Address</label>
                                    <input id="detailEditAddress" type="text" name="address" value="<?php echo e($firstMember->address ?? ''); ?>" placeholder="Address">
                                </div>
                            </div>

                            <div id="detailEditSettingsPanel" class="detail-edit-settings-panel hidden">
                                <div class="detail-edit-grid">
                                    <div id="detailEditLifeStatusField" class="detail-form-field">
                                        <label for="detailEditLifeStatus">Life Status</label>
                                        <select id="detailEditLifeStatus" name="life_status">
                                            <option value="alive" <?php echo e(($firstMemberLifeStatusRaw ?? 'alive') === 'alive' ? 'selected' : ''); ?>>Alive</option>
                                            <option value="deceased" <?php echo e(($firstMemberLifeStatusRaw ?? '') === 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                                        </select>
                                    </div>
                                    <div id="detailEditChildParentingModeField" class="detail-form-field">
                                        <label for="detailEditChildParentingMode">Child Status</label>
                                        <select id="detailEditChildParentingMode" name="child_parenting_mode">
                                            <option value="single_parent" <?php echo e(($firstChildParentingModeRaw ?? 'single_parent') === 'single_parent' ? 'selected' : ''); ?>>Single parent</option>
                                            <option value="with_current_partner" <?php echo e(($firstChildParentingModeRaw ?? '') === 'with_current_partner' ? 'selected' : ''); ?>>With current partner</option>
                                        </select>
                                        <small>Change this child between single parent and with current partner.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-edit-footer">
                                <p id="detailEditMessage" class="detail-edit-message" aria-live="polite"></p>
                                <div class="detail-edit-actions">
                                    <button id="detailEditCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                                    <button id="detailEditSaveBtn" type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

            <?php if ($currentLevelId === 2 || $isSuperadmin || $canDeleteAnyUserFromHome): ?>
                <div id="memberActionBlock" class="member-action-block hidden <?php echo e($firstShowActionBlock ? '' : 'hidden'); ?>">
                    <h5>Actions</h5>

                    <?php if ($currentLevelId === 2 || $isSuperadmin || $canDeleteAnyUserFromHome): ?>
                        <div id="memberActionStatusGrid" class="member-action-status-grid">
                            <form id="lifeStatusForm" method="POST" action="/family/member/life-status" class="<?php echo e($firstCanUpdateLifeStatus ? '' : 'hidden'); ?>">
                                <?php echo csrf_field(); ?>
                                <input id="lifeStatusMemberIdInput" type="hidden" name="memberid" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">
                                <label for="lifeStatusSelect">Life Status</label>
                                <div class="life-status-row">
                                    <select id="lifeStatusSelect" name="life_status">
                                        <option value="alive" <?php echo e($firstMemberLifeStatusRaw === 'alive' ? 'selected' : ''); ?>>Alive</option>
                                        <option value="deceased" <?php echo e($firstMemberLifeStatusRaw === 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                                    </select>
                                    <button id="saveLifeStatusBtn" type="submit" class="btn btn-soft">Save</button>
                                </div>
                            </form>

                            <?php if ($isSuperadmin): ?>
                                <form id="childParentingModeUpdateForm" method="POST" action="/family/member/child-parenting-mode" class="hidden">
                                    <?php echo csrf_field(); ?>
                                    <input id="childParentingModeMemberIdInput" type="hidden" name="memberid" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">
                                    <div class="detail-form-field">
                                        <label for="childParentingModeActionSelect">Child Status</label>
                                        <div class="child-parenting-mode-box">
                                            <select id="childParentingModeActionSelect" name="child_parenting_mode">
                                                <option value="single_parent">Single parent</option>
                                                <option value="with_current_partner">With current partner</option>
                                            </select>
                                            <button id="childParentingModeActionBtn" type="submit" class="btn btn-soft btn-block">Save Status</button>
                                        </div>
                                        <small>Change this child between single parent and with current partner.</small>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($canDeleteAnyUserFromHome): ?>
                        <form id="deleteUserForm" method="POST" action="/management/users/<?php echo e((int) ($firstMember->userid ?? 0)); ?>/delete" class="<?php echo e(($firstCanDeleteUser && !$firstCanDivorcePartner) ? '' : 'hidden'); ?>" data-delete-message="Move this user to the recycle bin?">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-danger-soft btn-block">Delete User</button>
                        </form>
                    <?php else: ?>
                        <form id="deleteChildForm" method="POST" action="/family/member/delete" class="<?php echo e($firstCanDeleteChild ? '' : 'hidden'); ?>" data-delete-message="Delete this child account permanently?">
                            <?php echo csrf_field(); ?>
                            <input id="deleteChildMemberIdInput" type="hidden" name="memberid" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">
                            <button type="submit" class="btn btn-danger-soft btn-block">Delete Child</button>
                        </form>
                    <?php endif; ?>

                    <a
                        id="editProfileLink"
                        href="<?php echo e($firstCanEditProfile ? '/account?memberid=' . (int) ($firstMember->memberid ?? 0) : '/account'); ?>"
                        class="btn btn-soft btn-block btn-edit-profile <?php echo e($firstCanEditProfile ? '' : 'hidden'); ?>"
                    >
                        Edit Profile
                    </a>
                </div>
            <?php endif; ?>
            </div>

            <?php if ($canEditOwnProfile || $canAddMemberFromHome): ?>
                <div class="detail-form-wrap">
                    <?php if ($canAddMemberFromHome): ?>
                        <div id="addMemberPanel" class="detail-panel hidden">
                           <form method="POST" action="/family/member/store" class="detail-form" data-current-member-gender="<?php echo e($currentMemberGenderRaw); ?>" data-default-partner-gender="<?php echo e($defaultPartnerGender); ?>" data-is-superadmin="<?php echo e($isSuperadmin ? '1' : '0'); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="home_panel" value="add-member">
                                <input type="hidden" id="relationTypeInput" name="relation_type" value="<?php echo e($defaultRelationType); ?>">
                                <input type="hidden" id="targetMemberIdInput" name="target_memberid" value="<?php echo e($defaultTargetMemberId); ?>">

                                <div class="detail-form-field">
                                    <label>Relation Type</label>
                                    <div class="relation-btn-row">
                                        <button
                                            id="addChildBtn"
                                            type="button"
                                            class="btn btn-ghost relation-btn <?php echo e($defaultRelationType === 'child' ? 'is-active' : ''); ?>"
                                            data-relation-type="child"
                                            <?php echo e($canAddByAge ? '' : 'disabled'); ?>
                                        >
                                            Add Child
                                        </button>
                                        <button
                                            id="addParentBtn"
                                            type="button"
                                            class="btn btn-ghost relation-btn <?php echo e($defaultRelationType === 'parent' ? 'is-active' : ''); ?>"
                                            data-relation-type="parent"
                                            <?php echo e($canAddByAge ? '' : 'disabled'); ?>
                                        >
                                            Add Parent
                                        </button>
                                        <button
                                            id="addPartnerBtn"
                                            type="button"
                                            class="btn btn-ghost relation-btn <?php echo e($defaultRelationType === 'partner' ? 'is-active' : ''); ?>"
                                            data-relation-type="partner"
                                            <?php echo e($canAddPartnerByAge ? '' : 'disabled'); ?>
                                        >
                                            Add Partner
                                        </button>
                                    </div>
                                    <?php if (!$canAddByAge): ?>
                                        <small>You must be at least 18 years old to add family members.</small>
                                    <?php elseif (!$canAddPartnerByAge): ?>
                                        <small>You must be at least 18 years old to add a partner.</small>
                                    <?php endif; ?>
                                </div>
                                <div class="detail-form-field">
                                    <label>Related To</label>
                                    <input
                                        id="relatedToMemberDisplay"
                                        type="text"
                                        value="<?php echo e($targetMember->name ?? '-'); ?>"
                                        readonly
                                    >
                                </div>

                                <div id="childParentingModeField" class="detail-form-field <?php echo e($defaultRelationType === 'child' ? '' : 'hidden'); ?>">
                                    <label for="childParentingMode">Child Status</label>
                                    <select id="childParentingMode" name="child_parenting_mode">
                                        <option value="with_current_partner" <?php echo e($defaultChildParentingMode === 'with_current_partner' ? 'selected' : ''); ?> <?php echo e(!$currentMemberCanUseCurrentPartner ? 'disabled' : ''); ?>>With current partner</option>
                                        <option value="single_parent" <?php echo e($defaultChildParentingMode === 'single_parent' ? 'selected' : ''); ?>>Single parent</option>
                                    </select>
                                    <small>Choose With current partner when the selected member is married or has an active partner.</small>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberName">Name</label>
                                    <input id="memberName" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="Enter full name" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberUsername">Username</label>
                                    <input id="memberUsername" type="text" name="username" value="<?php echo e(old('username')); ?>" placeholder="Enter username" required>
                                </div>

                                <div id="memberGenderSelectField" class="detail-form-field <?php echo e($defaultRelationType === 'partner' ? 'hidden' : ''); ?>">
                                    <label for="memberGenderSelect">Gender</label>
                                    <select id="memberGenderSelect" required>
                                        <option value="">Select gender</option>
                                        <option value="male" <?php echo e(old('gender') === 'male' ? 'selected' : ''); ?>>Male</option>
                                        <option value="female" <?php echo e(old('gender') === 'female' ? 'selected' : ''); ?>>Female</option>
                                    </select>
                                </div>

                                <div id="memberGenderPartnerInfo" class="detail-form-field <?php echo e($defaultRelationType === 'partner' ? '' : 'hidden'); ?>">
                                    <label for="memberGenderPartnerDisplay">Gender</label>
                                    <input
                                        id="memberGenderPartnerDisplay"
                                        type="text"
                                        value="<?php echo e(ucfirst($defaultPartnerGender)); ?>"
                                        readonly
                                    >
                                </div>
                                <input type="hidden" id="memberGenderInput" name="gender" value="<?php echo e($defaultRelationType === 'partner' ? $defaultPartnerGender : old('gender')); ?>">

                                <div id="memberEmailField" class="detail-form-field <?php echo e($defaultRelationType === 'child' ? 'hidden' : ''); ?>">
                                    <label for="memberEmail">Email</label>
                                    <input id="memberEmail" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="Enter email" <?php echo e($defaultRelationType === 'partner' ? 'required' : ''); ?>>
                                </div>

                                <div id="memberPhoneField" class="detail-form-field <?php echo e($defaultRelationType === 'child' ? 'hidden' : ''); ?>">
                                    <label for="memberPhone">Phone Number</label>
                                    <input id="memberPhone" type="text" name="phonenumber" value="<?php echo e(old('phonenumber')); ?>" placeholder="Enter phone number" <?php echo e($defaultRelationType === 'partner' ? 'required' : ''); ?>>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberBirthdate">Birthdate</label>
                                    <input id="memberBirthdate" type="date" name="birthdate" value="<?php echo e(old('birthdate')); ?>" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberBirthplace">Birthplace</label>
                                    <input id="memberBirthplace" type="text" name="birthplace" value="<?php echo e(old('birthplace')); ?>" placeholder="Enter birthplace" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberAddressCountry">Address</label>
                                    <input id="memberAddress" type="hidden" name="address" value="<?php echo e(old('address')); ?>" required>
                                    <input id="memberAddressCountryOld" type="hidden" value="<?php echo e(old('address_country')); ?>">
                                    <input id="memberAddressProvinceOld" type="hidden" value="<?php echo e(old('address_province')); ?>">
                                    <input id="memberAddressCityOld" type="hidden" value="<?php echo e(old('address_city')); ?>">
                                    <input id="memberAddressDistrictOld" type="hidden" value="<?php echo e(old('address_district')); ?>">
                                    <input id="memberAddressDetailOld" type="hidden" value="<?php echo e(old('address_detail')); ?>">

                                    <select id="memberAddressCountry" name="address_country" required>
                                        <option value="">Select country</option>
                                    </select>
                                </div>

                                <div id="memberAddressProvinceField" class="detail-form-field hidden">
                                    <label for="memberAddressProvince">Province</label>
                                    <select id="memberAddressProvince" name="address_province">
                                        <option value="">Select province</option>
                                    </select>
                                </div>

                                <div id="memberAddressCityField" class="detail-form-field hidden">
                                    <label for="memberAddressCity">City</label>
                                    <select id="memberAddressCity" name="address_city">
                                        <option value="">Select city</option>
                                    </select>
                                </div>

                                <div id="memberAddressDistrictField" class="detail-form-field hidden">
                                    <label for="memberAddressDistrict">District</label>
                                    <select id="memberAddressDistrict" name="address_district">
                                        <option value="">Select district</option>
                                    </select>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberAddressDetail">Address detail</label>
                                    <input id="memberAddressDetail" type="text" name="address_detail" value="<?php echo e(old('address_detail')); ?>" placeholder="Street, RT/RW, postal code (optional)">
                                </div>

                                <button type="submit" class="btn btn-primary">Add Member</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    </section>
</div>
<script async src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    var lifeStatusModal = document.getElementById('lifeStatusConfirmModal');
    var lifeStatusConfirmTitle = document.getElementById('lifeStatusConfirmTitle');
    var lifeStatusConfirmText = document.getElementById('lifeStatusConfirmText');
    var lifeStatusDeadDateField = document.getElementById('lifeStatusDeadDateField');
    var lifeStatusDeadDateInput = document.getElementById('lifeStatusDeadDate');
    var lifeStatusConfirmError = document.getElementById('lifeStatusConfirmError');
    var lifeStatusConfirmBtn = document.getElementById('lifeStatusConfirmBtn');
    var lifeStatusConfirmCancelBtn = document.getElementById('lifeStatusConfirmCancelBtn');
    var lifeStatusPendingStatus = '';
    var lifeStatusPendingMemberId = '';
    var deleteConfirmModal = document.getElementById('deleteConfirmModal');
    var deleteConfirmTitle = document.getElementById('deleteConfirmTitle');
    var deleteConfirmText = document.getElementById('deleteConfirmText');
    var deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
    var deleteConfirmCancelBtn = document.getElementById('deleteConfirmCancelBtn');
    var pendingDeleteForm = null;
    var pendingDeleteMessage = 'Are you sure you want to delete this item?';
    var pendingDeleteTitle = 'Confirm Delete';
    var pendingDeleteButtonLabel = 'Delete';

    function openLifeStatusModal(statusText, memberId) {
        var normalizedStatus = String(statusText || '').trim().toLowerCase();
        var isDeceased = normalizedStatus === 'deceased';

        lifeStatusPendingStatus = isDeceased ? 'deceased' : 'alive';
        lifeStatusPendingMemberId = memberId;
        if (lifeStatusConfirmTitle) {
            lifeStatusConfirmTitle.textContent = isDeceased ? 'Set Date of Death' : 'Confirm Life Status';
        }
        if (lifeStatusConfirmText) {
            lifeStatusConfirmText.textContent = isDeceased
                ? 'Choose the date of death before saving this status.'
                : 'Change life status to ' + statusText + '?';
        }
        if (lifeStatusDeadDateField) {
            lifeStatusDeadDateField.classList.toggle('hidden', !isDeceased);
        }
        if (lifeStatusDeadDateInput) {
            lifeStatusDeadDateInput.required = isDeceased;
            if (isDeceased && !lifeStatusDeadDateInput.value) {
                lifeStatusDeadDateInput.value = new Date().toISOString().slice(0, 10);
            }
            if (!isDeceased) {
                lifeStatusDeadDateInput.value = '';
            }
        }
        if (lifeStatusConfirmError) {
            lifeStatusConfirmError.textContent = '';
            lifeStatusConfirmError.style.display = 'none';
        }
        if (lifeStatusConfirmBtn) {
            lifeStatusConfirmBtn.textContent = isDeceased ? 'Save Date' : 'Confirm';
        }
        if (lifeStatusModal) {
            lifeStatusModal.classList.add('is-open');
            lifeStatusModal.setAttribute('aria-hidden', 'false');
            lifeStatusModal.style.display = 'flex';
        }
    }

    function openDeleteModal(form) {
        if (!form) return;

        pendingDeleteForm = form;
        pendingDeleteMessage = form.getAttribute('data-delete-message') || 'Are you sure you want to delete this item?';
        pendingDeleteTitle = form.getAttribute('data-delete-title') || 'Confirm Delete';
        pendingDeleteButtonLabel = form.getAttribute('data-delete-button') || 'Delete';

        if (deleteConfirmTitle) {
            deleteConfirmTitle.textContent = pendingDeleteTitle;
        }
        if (deleteConfirmText) {
            deleteConfirmText.textContent = pendingDeleteMessage;
        }
        if (deleteConfirmBtn) {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = pendingDeleteButtonLabel;
        }
        if (deleteConfirmModal) {
            deleteConfirmModal.classList.add('is-open');
            deleteConfirmModal.setAttribute('aria-hidden', 'false');
            deleteConfirmModal.style.display = 'flex';
        }
    }

    function closeDeleteModal() {
        pendingDeleteForm = null;
        pendingDeleteMessage = 'Are you sure you want to delete this item?';
        pendingDeleteTitle = 'Confirm Delete';
        pendingDeleteButtonLabel = 'Delete';
        if (deleteConfirmBtn) {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = 'Delete';
        }
        if (deleteConfirmModal) {
            deleteConfirmModal.classList.remove('is-open');
            deleteConfirmModal.setAttribute('aria-hidden', 'true');
            deleteConfirmModal.style.display = 'none';
        }
    }

    function closeLifeStatusModal() {
        lifeStatusPendingStatus = '';
        lifeStatusPendingMemberId = '';
        if (lifeStatusConfirmError) {
            lifeStatusConfirmError.textContent = '';
            lifeStatusConfirmError.style.display = 'none';
        }
        if (lifeStatusDeadDateInput) {
            lifeStatusDeadDateInput.required = false;
            lifeStatusDeadDateInput.value = '';
        }
        if (lifeStatusConfirmBtn) {
            lifeStatusConfirmBtn.textContent = 'Save Date';
        }
        if (lifeStatusModal) {
            lifeStatusModal.classList.remove('is-open');
            lifeStatusModal.setAttribute('aria-hidden', 'true');
            lifeStatusModal.style.display = 'none';
        }
    }

    function showLifeStatusError(message) {
        if (lifeStatusConfirmError) {
            lifeStatusConfirmError.textContent = message;
            lifeStatusConfirmError.style.display = 'block';
        }
        if (lifeStatusConfirmText) {
            lifeStatusConfirmText.textContent = 'Unable to update life status.';
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-status-toggle');
        if (!btn) return;

        var newStatus = btn.getAttribute('data-status') || '';
        var memberIdInput = document.getElementById('lifeStatusMemberIdInput');
        var memberId = memberIdInput ? memberIdInput.value : '';

        if (!memberId) {
            console.error('Could not find member ID.');
            return;
        }

        openLifeStatusModal(newStatus, memberId);
    });

    if (lifeStatusConfirmCancelBtn) {
        lifeStatusConfirmCancelBtn.addEventListener('click', function () {
            closeLifeStatusModal();
        });
    }

    if (lifeStatusModal) {
        lifeStatusModal.addEventListener('click', function (event) {
            if (event.target === lifeStatusModal || event.target.classList.contains('message-modal-backdrop')) {
                closeLifeStatusModal();
            }
        });
    }

    function bindDeleteConfirm(form) {
        if (!form) return;

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openDeleteModal(form);
        });
    }

    bindDeleteConfirm(deleteUserForm);
    bindDeleteConfirm(deleteChildForm);

    if (deleteConfirmCancelBtn) {
        deleteConfirmCancelBtn.addEventListener('click', function () {
            closeDeleteModal();
        });
    }

    if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('click', function (event) {
            if (event.target === deleteConfirmModal || event.target.classList.contains('message-modal-backdrop')) {
                closeDeleteModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && deleteConfirmModal && deleteConfirmModal.classList.contains('is-open')) {
            closeDeleteModal();
        }
    });

    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', function () {
            if (!pendingDeleteForm) {
                return;
            }

            deleteConfirmBtn.disabled = true;
            deleteConfirmBtn.textContent = 'Deleting...';
            pendingDeleteForm.submit();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && lifeStatusModal && lifeStatusModal.classList.contains('is-open')) {
            closeLifeStatusModal();
        }
    });

    if (lifeStatusConfirmBtn) {
        lifeStatusConfirmBtn.addEventListener('click', function () {
            if (!lifeStatusPendingMemberId || !lifeStatusPendingStatus) {
                showLifeStatusError('Life status data is missing.');
                return;
            }

            var deadDate = lifeStatusDeadDateInput ? String(lifeStatusDeadDateInput.value || '').trim() : '';
            if (lifeStatusPendingStatus === 'deceased' && !deadDate) {
                showLifeStatusError('Please choose the date of death.');
                return;
            }

            lifeStatusConfirmBtn.disabled = true;
            lifeStatusConfirmBtn.textContent = 'Saving...';

            fetch('/family/member/life-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    memberid: lifeStatusPendingMemberId,
                    life_status: lifeStatusPendingStatus,
                    deaddate: lifeStatusPendingStatus === 'deceased' ? deadDate : ''
                })
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    location.reload();
                    return;
                }

                var message = (result.data && result.data.message) ? result.data.message : 'Failed to update status.';
                showLifeStatusError(message);
                lifeStatusConfirmBtn.disabled = false;
                lifeStatusConfirmBtn.textContent = lifeStatusPendingStatus === 'deceased' ? 'Save Date' : 'Confirm';
            })
            .catch(function (error) {
                console.error('Error:', error);
                showLifeStatusError('An error occurred while updating the status.');
                lifeStatusConfirmBtn.disabled = false;
                lifeStatusConfirmBtn.textContent = lifeStatusPendingStatus === 'deceased' ? 'Save Date' : 'Confirm';
            });
        });
    }

    var memberDetailBlock = document.getElementById('memberDetailBlock');
    var currentUserId = <?php echo e($currentUserId); ?>;
    var currentRoleId = <?php echo e($currentRoleId); ?>;
    var canDeleteAnyUserFromHome = currentRoleId === 1 || currentRoleId === 2;
    var memberActionBlock = document.getElementById('memberActionBlock');
    var addMemberPanel = document.getElementById('addMemberPanel');
    var detailSidebar = document.getElementById('detailSidebar');
    var homePagePanel = document.querySelector('.home-page-panel');
    var profilePanelBtn = document.getElementById('profilePanelBtn');
    var addMemberPanelBtn = document.getElementById('addMemberPanelBtn');
    var detailCard = document.getElementById('detailCard');
    var detailPhoto = document.getElementById('detailPhoto');
    var detailName = document.getElementById('detailName');
    var detailRole = document.getElementById('detailRole');
    var detailViewBlock = document.getElementById('detailViewBlock');
    var detailUsername = document.getElementById('detailUsername');
    var detailGender = document.getElementById('detailGender');
    var detailAge = document.getElementById('detailAge');
    var detailBirthdate = document.getElementById('detailBirthdate');
    var detailBirthplace = document.getElementById('detailBirthplace');
    var detailBloodType = document.getElementById('detailBloodType');
    var detailStatus = document.getElementById('detailStatus');
    var detailMaritalStatus = document.getElementById('detailMaritalStatus');
    var detailPhone = document.getElementById('detailPhone');
    var detailEmail = document.getElementById('detailEmail');
    var detailSocialMedia = document.getElementById('detailSocialMedia');
    var detailJob = document.getElementById('detailJob');
    var detailAddress = document.getElementById('detailAddress');
    var detailEducation = document.getElementById('detailEducation');
    var detailEditBtn = document.getElementById('detailEditBtn');
    var detailEditForm = document.getElementById('memberDetailEditForm');
    var detailEditSettingsPanel = document.getElementById('detailEditSettingsPanel');
    var detailEditCancelBtn = document.getElementById('detailEditCancelBtn');
    var detailEditSaveBtn = document.getElementById('detailEditSaveBtn');
    var detailEditMessage = document.getElementById('detailEditMessage');
    var detailEditUsername = document.getElementById('detailEditUsername');
    var detailEditName = document.getElementById('detailEditName');
    var detailEditEmail = document.getElementById('detailEditEmail');
    var detailEditPhone = document.getElementById('detailEditPhone');
    var detailEditGender = document.getElementById('detailEditGender');
    var detailEditBirthdate = document.getElementById('detailEditBirthdate');
    var detailEditBirthplace = document.getElementById('detailEditBirthplace');
    var detailEditBloodType = document.getElementById('detailEditBloodType');
    var detailEditMaritalStatus = document.getElementById('detailEditMaritalStatus');
    var detailEditJob = document.getElementById('detailEditJob');
    var detailEditEducation = document.getElementById('detailEditEducation');
    var detailEditAddress = document.getElementById('detailEditAddress');
    var detailEditLifeStatusField = document.getElementById('detailEditLifeStatusField');
    var detailEditLifeStatus = document.getElementById('detailEditLifeStatus');
    var detailEditChildParentingModeField = document.getElementById('detailEditChildParentingModeField');
    var detailEditChildParentingMode = document.getElementById('detailEditChildParentingMode');
    var detailEditMemberIdInput = document.getElementById('detailEditMemberIdInput');
    var deleteUserForm = document.getElementById('deleteUserForm');
    var deleteChildForm = document.getElementById('deleteChildForm');
    var lifeStatusForm = document.getElementById('lifeStatusForm');
    var editProfileLink = document.getElementById('editProfileLink');
    var deleteChildMemberIdInput = document.getElementById('deleteChildMemberIdInput');
    var lifeStatusMemberIdInput = document.getElementById('lifeStatusMemberIdInput');
    var childParentingModeUpdateForm = document.getElementById('childParentingModeUpdateForm');
    var childParentingModeMemberIdInput = document.getElementById('childParentingModeMemberIdInput');
    var childParentingModeActionSelect = document.getElementById('childParentingModeActionSelect');
    var childParentingModeActionBtn = document.getElementById('childParentingModeActionBtn');
    var allMemberCards = document.querySelectorAll('.member-card[data-memberid]');
    var currentSelectedMemberCard = null;
    var detailEditModeActive = false;
    var detailCanUpdateLifeStatus = false;
    var detailCanEditChildParentingMode = false;

    function updateDetailValue(element, value) {
        if (!element) return;
        element.textContent = value && String(value).trim() !== '' ? value : '-';
    }

    function normalizeEditableValue(value) {
        var normalized = value === null || typeof value === 'undefined' ? '' : String(value).trim();
        return normalized === '-' ? '' : normalized;
    }

    function computeAgeFromBirthdate(birthdate) {
        var normalized = String(birthdate || '').trim();
        if (normalized === '') {
            return '';
        }

        var parts = normalized.split('-');
        if (parts.length !== 3) {
            return '';
        }

        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        var day = parseInt(parts[2], 10);
        if (!year || month < 0 || day <= 0) {
            return '';
        }

        var birthDate = new Date(year, month, day);
        if (isNaN(birthDate.getTime())) {
            return '';
        }

        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age -= 1;
        }

        return age >= 0 ? String(age) : '';
    }

    function fitDetailEmailText() {
        if (!detailEmail) {
            return;
        }

        detailEmail.style.display = 'block';
        detailEmail.style.whiteSpace = 'nowrap';
        detailEmail.style.overflow = 'visible';
        detailEmail.style.wordBreak = 'normal';
        detailEmail.style.overflowWrap = 'normal';
        detailEmail.style.hyphens = 'none';
        detailEmail.style.textOverflow = 'clip';
        detailEmail.style.fontSize = '';
        detailEmail.style.width = '100%';
    }

    var detailEmailFitRaf = null;
    function scheduleDetailEmailFit() {
        if (!detailEmail) {
            return;
        }

        if (detailEmailFitRaf) {
            window.cancelAnimationFrame(detailEmailFitRaf);
        }

        detailEmailFitRaf = window.requestAnimationFrame(function () {
            detailEmailFitRaf = window.requestAnimationFrame(function () {
                fitDetailEmailText();
            });
        });
    }

    function setDetailEditMessage(message, isError) {
        if (!detailEditMessage) {
            return;
        }

        var text = message ? String(message).trim() : '';
        detailEditMessage.textContent = text;
        detailEditMessage.classList.toggle('is-success', !isError && text !== '');
        detailEditMessage.classList.toggle('is-error', !!isError);
    }

    function setDetailEditMode(isEditing) {
        detailEditModeActive = !!isEditing;

        if (detailViewBlock) {
            detailViewBlock.classList.toggle('hidden', detailEditModeActive);
        }
        if (detailEditForm) {
            detailEditForm.classList.toggle('hidden', !detailEditModeActive);
        }
        if (detailEditSettingsPanel) {
            detailEditSettingsPanel.classList.toggle(
                'hidden',
                !detailEditModeActive || (!detailCanUpdateLifeStatus && !detailCanEditChildParentingMode)
            );
        }
        if (memberActionBlock && detailEditModeActive) {
            memberActionBlock.classList.add('hidden');
        } else if (memberActionBlock && currentSelectedMemberCard) {
            syncMemberActionAccessBySelectedCard(currentSelectedMemberCard);
        }
        if (childParentingModeUpdateForm) {
            childParentingModeUpdateForm.classList.toggle('hidden', !detailEditModeActive || !detailCanEditChildParentingMode);
        }
        if (detailEditBtn) {
            detailEditBtn.textContent = detailEditModeActive ? 'Close' : 'Edit';
        }
        if (detailEditMessage && !detailEditModeActive) {
            setDetailEditMessage('', false);
        }
        scheduleDetailEmailFit();
    }

    function syncDetailEditFormFromCard(card) {
        if (!detailEditForm || !card) {
            return;
        }

        var userId = card.getAttribute('data-userid') || '';
        var memberId = card.getAttribute('data-memberid') || '';
        var lifeStatusRaw = String(card.getAttribute('data-life-status-raw') || card.getAttribute('data-status') || 'alive').toLowerCase();
        var childParentingMode = String(card.getAttribute('data-child-parenting-mode') || '').toLowerCase();
        var canUpdateLifeStatus = card.getAttribute('data-can-update-life-status') === '1';
        var canEditChildParentingMode = card.getAttribute('data-can-edit-child-parenting-mode') === '1';

        if (detailEditForm) {
            detailEditForm.action = '/management/users/' + encodeURIComponent(userId) + '/update';
        }
        if (detailEditMemberIdInput) {
            detailEditMemberIdInput.value = memberId;
        }
        if (detailEditUsername) {
            detailEditUsername.value = normalizeEditableValue(card.getAttribute('data-username') || card.getAttribute('data-name'));
        }
        if (detailEditName) {
            detailEditName.value = normalizeEditableValue(card.getAttribute('data-name'));
        }
        if (detailEditEmail) {
            detailEditEmail.value = normalizeEditableValue(card.getAttribute('data-email'));
        }
        if (detailEditPhone) {
            detailEditPhone.value = normalizeEditableValue(card.getAttribute('data-phone'));
        }
        if (detailEditGender) {
            detailEditGender.value = normalizeEditableValue(card.getAttribute('data-gender')).toLowerCase();
        }
        if (detailEditBirthdate) {
            detailEditBirthdate.value = normalizeEditableValue(card.getAttribute('data-birthdate'));
        }
        if (detailEditBirthplace) {
            detailEditBirthplace.value = normalizeEditableValue(card.getAttribute('data-birthplace'));
        }
        if (detailEditBloodType) {
            detailEditBloodType.value = normalizeEditableValue(card.getAttribute('data-blood-type')).toUpperCase();
        }
        if (detailEditMaritalStatus) {
            detailEditMaritalStatus.value = normalizeEditableValue(card.getAttribute('data-marital-status'));
        }
        if (detailEditJob) {
            detailEditJob.value = normalizeEditableValue(card.getAttribute('data-job'));
        }
        if (detailEditEducation) {
            detailEditEducation.value = normalizeEditableValue(card.getAttribute('data-education'));
        }
        if (detailEditAddress) {
            detailEditAddress.value = normalizeEditableValue(card.getAttribute('data-address'));
        }
        if (detailEditLifeStatusField) {
            detailEditLifeStatusField.classList.toggle('hidden', !canUpdateLifeStatus);
        }
        if (detailEditLifeStatus) {
            detailEditLifeStatus.value = lifeStatusRaw === 'deceased' ? 'deceased' : 'alive';
            detailEditLifeStatus.disabled = !canUpdateLifeStatus;
        }
        if (detailEditChildParentingModeField) {
            detailEditChildParentingModeField.classList.toggle('hidden', !canEditChildParentingMode);
        }
        if (detailEditChildParentingMode) {
            detailEditChildParentingMode.value = childParentingMode === 'with_current_partner'
                ? 'with_current_partner'
                : 'single_parent';
            detailEditChildParentingMode.disabled = !canEditChildParentingMode;
        }
    }

    function readDetailEditFormValues() {
        return {
            username: detailEditUsername ? String(detailEditUsername.value || '').trim() : '',
            name: detailEditName ? String(detailEditName.value || '').trim() : '',
            email: detailEditEmail ? String(detailEditEmail.value || '').trim() : '',
            phonenumber: detailEditPhone ? String(detailEditPhone.value || '').trim() : '',
            gender: detailEditGender ? String(detailEditGender.value || '').trim() : '',
            birthdate: detailEditBirthdate ? String(detailEditBirthdate.value || '').trim() : '',
            birthplace: detailEditBirthplace ? String(detailEditBirthplace.value || '').trim() : '',
            bloodtype: detailEditBloodType ? String(detailEditBloodType.value || '').trim() : '',
            marital_status: detailEditMaritalStatus ? String(detailEditMaritalStatus.value || '').trim() : '',
            job: detailEditJob ? String(detailEditJob.value || '').trim() : '',
            education_status: detailEditEducation ? String(detailEditEducation.value || '').trim() : '',
            address: detailEditAddress ? String(detailEditAddress.value || '').trim() : '',
            life_status: detailEditLifeStatus ? String(detailEditLifeStatus.value || '').trim() : '',
            child_parenting_mode: detailEditChildParentingMode ? String(detailEditChildParentingMode.value || '').trim() : ''
        };
    }

    function applyDetailValuesFromForm(values) {
        updateDetailValue(detailUsername, values.username);
        updateDetailValue(detailName, values.name);
        updateDetailValue(detailEmail, values.email);
        updateDetailValue(detailPhone, values.phonenumber);
        updateDetailValue(detailGender, values.gender ? values.gender.charAt(0).toUpperCase() + values.gender.slice(1) : '');
        updateDetailValue(detailBirthdate, values.birthdate);
        updateDetailValue(detailBirthplace, values.birthplace);
        updateDetailValue(detailAge, computeAgeFromBirthdate(values.birthdate));
        updateDetailValue(detailBloodType, values.bloodtype ? values.bloodtype.toUpperCase() : '');
        updateDetailValue(detailStatus, values.life_status ? values.life_status.charAt(0).toUpperCase() + values.life_status.slice(1) : '');
        updateDetailValue(detailMaritalStatus, values.marital_status);
        updateDetailValue(detailJob, values.job);
        updateDetailValue(detailEducation, values.education_status);
        updateDetailValue(detailAddress, values.address);
        scheduleDetailEmailFit();
    }

    function syncCardDataFromForm(card, values) {
        if (!card) return;

        card.setAttribute('data-username', values.username || '');
        card.setAttribute('data-name', values.name || '');
        card.setAttribute('data-email', values.email || '');
        card.setAttribute('data-phone', values.phonenumber || '');
        card.setAttribute('data-gender', values.gender ? values.gender.charAt(0).toUpperCase() + values.gender.slice(1) : '');
        card.setAttribute('data-birthdate', values.birthdate || '');
        card.setAttribute('data-age', computeAgeFromBirthdate(values.birthdate));
        card.setAttribute('data-birthplace', values.birthplace || '');
        card.setAttribute('data-blood-type', values.bloodtype ? values.bloodtype.toUpperCase() : '');
        card.setAttribute('data-life-status-raw', values.life_status ? values.life_status.toLowerCase() : '');
        card.setAttribute('data-status', values.life_status ? values.life_status.charAt(0).toUpperCase() + values.life_status.slice(1) : '');
        card.setAttribute('data-marital-status', values.marital_status || '');
        card.setAttribute('data-job', values.job || '');
        card.setAttribute('data-education', values.education_status || '');
        card.setAttribute('data-address', values.address || '');
        card.setAttribute('data-child-parenting-mode', values.child_parenting_mode || '');
    }

    function showMemberDetailFromCard(card) {
        if (!card) return;

        currentSelectedMemberCard = card;

        var memberId = card.getAttribute('data-memberid') || '';
        var username = card.getAttribute('data-username') || '';
        var name = card.getAttribute('data-name') || '-';
        var role = card.getAttribute('data-role') || 'Family Member';
        var gender = card.getAttribute('data-gender') || '-';
        var age = card.getAttribute('data-age') || '-';
        var birthdate = card.getAttribute('data-birthdate') || '-';
        var birthplace = card.getAttribute('data-birthplace') || '-';
        var bloodType = card.getAttribute('data-blood-type') || '-';
        var status = card.getAttribute('data-status') || '-';
        var maritalStatus = card.getAttribute('data-marital-status') || '-';
        var phone = card.getAttribute('data-phone') || '-';
        var email = card.getAttribute('data-email') || '-';
        var socialMedia = card.getAttribute('data-social-media') || '-';
        var job = card.getAttribute('data-job') || '-';
        var address = card.getAttribute('data-address') || '-';
        var education = card.getAttribute('data-education') || '-';
        var childParentingMode = String(card.getAttribute('data-child-parenting-mode') || '').toLowerCase();
        var photo = card.getAttribute('data-photo') || '';
        var userId = card.getAttribute('data-userid') || '';
        var lifeStatusRaw = (card.getAttribute('data-life-status-raw') || '').toLowerCase();
        var isMe = card.getAttribute('data-isme') === '1';
        var canDeleteChild = card.getAttribute('data-can-delete-child') === '1';
        var canUpdateLifeStatus = card.getAttribute('data-can-update-life-status') === '1';
        var canEditProfile = card.getAttribute('data-can-edit-profile') === '1';
        var canEditChildParentingMode = card.getAttribute('data-can-edit-child-parenting-mode') === '1';
        detailCanUpdateLifeStatus = canUpdateLifeStatus;
        detailCanEditChildParentingMode = canEditChildParentingMode;

        if (detailPhoto) {
            detailPhoto.src = photo || detailPhoto.src;
            detailPhoto.alt = name !== '-' ? name : 'Member';
            detailPhoto.setAttribute('data-isme', isMe ? '1' : '0');
        }

        updateDetailValue(detailName, name);
        updateDetailValue(detailRole, role);
        updateDetailValue(detailUsername, username);
        updateDetailValue(detailGender, gender);
        updateDetailValue(detailAge, age);
        updateDetailValue(detailBirthdate, birthdate);
        updateDetailValue(detailBirthplace, birthplace);
        updateDetailValue(detailBloodType, bloodType);
        updateDetailValue(detailStatus, status);
        updateDetailValue(detailMaritalStatus, maritalStatus);
        updateDetailValue(detailPhone, phone);
        updateDetailValue(detailEmail, email);
        updateDetailValue(detailSocialMedia, socialMedia);
        updateDetailValue(detailJob, job);
        updateDetailValue(detailAddress, address);
        updateDetailValue(detailEducation, education);
        scheduleDetailEmailFit();

        if (detailCard) {
            detailCard.classList.toggle('is-deceased', lifeStatusRaw === 'deceased');
        }

        if (memberDetailBlock) {
            memberDetailBlock.classList.remove('hidden');
        }

        if (memberActionBlock) {
            memberActionBlock.classList.remove('hidden');
        }
        if (detailSidebar) {
            detailSidebar.classList.remove('hidden');
        }
        if (homePagePanel) {
            homePagePanel.classList.add('has-selected-member');
        }

        if (canDeleteAnyUserFromHome) {
            if (deleteUserForm) {
                var shouldShowDeleteUser = userId !== '' && String(userId) !== String(currentUserId) && !canDivorcePartner;
                deleteUserForm.classList.toggle('hidden', !shouldShowDeleteUser);
                if (shouldShowDeleteUser) {
                    deleteUserForm.action = '/management/users/' + encodeURIComponent(userId) + '/delete';
                }
            }
            if (deleteChildForm) {
                deleteChildForm.classList.add('hidden');
            }
            if (editProfileLink) {
                editProfileLink.classList.add('hidden');
            }
        } else {
            if (deleteUserForm) {
                deleteUserForm.classList.add('hidden');
            }
            if (deleteChildForm) {
                deleteChildForm.classList.toggle('hidden', !canDeleteChild);
            }
            if (editProfileLink) {
                editProfileLink.classList.toggle('hidden', !canEditProfile);
                if (canEditProfile) {
                    editProfileLink.href = '/account?memberid=' + encodeURIComponent(memberId);
                } else {
                    editProfileLink.href = '/account';
                }
            }
        }

        if (lifeStatusForm) {
            lifeStatusForm.classList.toggle('hidden', !canUpdateLifeStatus);
        }
        if (childParentingModeUpdateForm) {
            childParentingModeUpdateForm.classList.toggle('hidden', !detailEditModeActive || !canEditChildParentingMode);
        }
        if (childParentingModeMemberIdInput) {
            childParentingModeMemberIdInput.value = memberId;
        }
        if (childParentingModeActionSelect) {
            childParentingModeActionSelect.value = childParentingMode === 'with_current_partner' ? 'with_current_partner' : 'single_parent';
        }
        if (childParentingModeActionBtn) {
            childParentingModeActionBtn.disabled = false;
        }

        if (deleteChildMemberIdInput) {
            deleteChildMemberIdInput.value = memberId;
        }
        if (lifeStatusMemberIdInput) {
            lifeStatusMemberIdInput.value = memberId;
        }

        syncDetailEditFormFromCard(card);
        setDetailEditMode(detailEditModeActive);

        showProfilePanel();

        allMemberCards.forEach(function (node) {
            node.classList.toggle('active', node === card);
        });
    }

    function hideMemberDetailPanel() {
        if (homePagePanel) {
            homePagePanel.classList.remove('has-selected-member');
        }
        if (detailSidebar) {
            detailSidebar.classList.add('hidden');
        }
        if (memberDetailBlock) {
            memberDetailBlock.classList.add('hidden');
        }
        if (memberActionBlock) {
            memberActionBlock.classList.add('hidden');
        }
        if (addMemberPanel) {
            addMemberPanel.classList.add('hidden');
        }
        if (detailEditForm) {
            setDetailEditMode(false);
        }
        if (profilePanelBtn) {
            profilePanelBtn.classList.add('is-active');
        }
        if (addMemberPanelBtn) {
            addMemberPanelBtn.classList.remove('is-active');
        }
        allMemberCards.forEach(function (node) {
            node.classList.remove('active');
        });
    }

    document.addEventListener('click', function (event) {
        var card = event.target.closest('.member-card[data-memberid]');
        if (!card) {
            return;
        }

        showMemberDetailFromCard(card);
    });

    var treeContainer = document.querySelector('.home-page-panel .tree-container');
    if (treeContainer) {
        treeContainer.addEventListener('click', function (event) {
            var clickedCard = event.target.closest('.member-card[data-memberid]');
            if (clickedCard) {
                return;
            }

            var clickedControl = event.target.closest('button, a, input, select, textarea, label');
            if (clickedControl) {
                return;
            }

            hideMemberDetailPanel();
        });
    }

    function showProfilePanel() {
        if (memberDetailBlock) {
            memberDetailBlock.classList.remove('hidden');
        }
        if (memberActionBlock) {
            memberActionBlock.classList.remove('hidden');
        }
        if (addMemberPanel) {
            addMemberPanel.classList.add('hidden');
        }
        if (detailSidebar) {
            detailSidebar.classList.remove('hidden');
        }
        if (homePagePanel) {
            homePagePanel.classList.add('has-selected-member');
        }
        if (profilePanelBtn) {
            profilePanelBtn.classList.add('is-active');
        }
        if (addMemberPanelBtn) {
            addMemberPanelBtn.classList.remove('is-active');
        }
    }

    function showAddMemberPanel() {
        if (!homePagePanel || !homePagePanel.classList.contains('has-selected-member')) {
            return;
        }
        if (memberDetailBlock) {
            memberDetailBlock.classList.add('hidden');
        }
        if (memberActionBlock) {
            memberActionBlock.classList.add('hidden');
        }
        if (addMemberPanel) {
            addMemberPanel.classList.remove('hidden');
        }
        if (profilePanelBtn) {
            profilePanelBtn.classList.remove('is-active');
        }
        if (addMemberPanelBtn) {
            addMemberPanelBtn.classList.add('is-active');
        }
    }

    if (profilePanelBtn) {
        profilePanelBtn.addEventListener('click', function () {
            showProfilePanel();
        });
    }

    if (addMemberPanelBtn) {
        addMemberPanelBtn.addEventListener('click', function () {
            showAddMemberPanel();
        });
    }

    if (detailEditBtn && detailEditForm) {
        detailEditBtn.addEventListener('click', function () {
            if (!currentSelectedMemberCard) {
                currentSelectedMemberCard = document.querySelector('.member-card[data-memberid].active')
                    || document.querySelector('.member-card[data-memberid]');
            }

            if (!currentSelectedMemberCard) {
                return;
            }

            syncDetailEditFormFromCard(currentSelectedMemberCard);
            setDetailEditMode(!detailEditModeActive);
            if (detailEditModeActive && detailEditUsername) {
                window.setTimeout(function () {
                    detailEditUsername.focus();
                }, 0);
            }
        });
    }

    if (detailEditCancelBtn && detailEditForm) {
        detailEditCancelBtn.addEventListener('click', function () {
            setDetailEditMode(false);
            if (currentSelectedMemberCard) {
                showMemberDetailFromCard(currentSelectedMemberCard);
            }
        });
    }

    if (detailEditForm) {
        detailEditForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!currentSelectedMemberCard) {
                return;
            }

            var actionUrl = detailEditForm.getAttribute('action') || '';
            if (actionUrl === '') {
                setDetailEditMessage('Update endpoint is missing.', true);
                return;
            }

            if (detailEditSaveBtn) {
                detailEditSaveBtn.disabled = true;
                detailEditSaveBtn.textContent = 'Saving...';
            }
            setDetailEditMessage('Saving changes...', false);

            var payload = new FormData(detailEditForm);
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: payload
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: null };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.message) {
                    var values = readDetailEditFormValues();
                    syncCardDataFromForm(currentSelectedMemberCard, values);
                    applyDetailValuesFromForm(values);
                    setDetailEditMessage(result.data.message, false);
                    if (detailEditSaveBtn) {
                        detailEditSaveBtn.disabled = false;
                        detailEditSaveBtn.textContent = 'Save Changes';
                    }

                    window.setTimeout(function () {
                        setDetailEditMode(false);
                        if (currentSelectedMemberCard) {
                            showMemberDetailFromCard(currentSelectedMemberCard);
                        }
                    }, 500);

                    return;
                }

                var errorMessage = 'Failed to update member details.';
                if (result.data && result.data.message) {
                    errorMessage = result.data.message;
                } else if (result.data && result.data.errors) {
                    var firstErrorKey = Object.keys(result.data.errors)[0];
                    if (firstErrorKey && result.data.errors[firstErrorKey] && result.data.errors[firstErrorKey][0]) {
                        errorMessage = result.data.errors[firstErrorKey][0];
                    }
                }

                setDetailEditMessage(errorMessage, true);
                if (detailEditSaveBtn) {
                    detailEditSaveBtn.disabled = false;
                    detailEditSaveBtn.textContent = 'Save Changes';
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                setDetailEditMessage('An error occurred while updating member details.', true);
                if (detailEditSaveBtn) {
                    detailEditSaveBtn.disabled = false;
                    detailEditSaveBtn.textContent = 'Save Changes';
                }
            });
        });
    }

    if (detailSidebar) {
        detailSidebar.classList.add('hidden');
    }
    if (homePagePanel) {
        homePagePanel.classList.remove('has-selected-member');
    }
    if (memberDetailBlock) {
        memberDetailBlock.classList.add('hidden');
    }
    if (memberActionBlock) {
        memberActionBlock.classList.add('hidden');
    }
    if (addMemberPanel) {
        addMemberPanel.classList.add('hidden');
    }
    if (detailEditForm) {
        setDetailEditMode(false);
    }

    if (detailEmail) {
        scheduleDetailEmailFit();
    }

    var treeScrollArea = document.getElementById('treeScrollArea');
    if (treeScrollArea) {
        treeScrollArea.classList.add('is-tree-ready');
        var treeCenterRaf = null;
        var treeInitialCenterApplied = false;
        var treeCenterRetryCount = 0;
        var treeMaxCenterRetries = 6;

        var getInitialTreeFocusCard = function () {
            return treeScrollArea.querySelector('.member-card.active')
                || treeScrollArea.querySelector('.member-card[data-memberid]')
                || null;
        };

        var centerTreeScroll = function () {
            if (treeInitialCenterApplied) {
                return;
            }

            var areaWidth = treeScrollArea.clientWidth;
            var areaHeight = treeScrollArea.clientHeight;
            var focusCard = getInitialTreeFocusCard();
            if (areaWidth <= 0 || areaHeight <= 0 || !focusCard) {
                if (treeCenterRetryCount < treeMaxCenterRetries) {
                    treeCenterRetryCount += 1;
                    scheduleTreeCenterScroll();
                }
                return;
            }

            var areaRect = treeScrollArea.getBoundingClientRect();
            var cardRect = focusCard.getBoundingClientRect();
            var nextScrollLeft = treeScrollArea.scrollLeft + (cardRect.left + (cardRect.width / 2)) - (areaRect.left + (areaWidth / 2));
            var nextScrollTop = treeScrollArea.scrollTop + (cardRect.top + (cardRect.height / 2)) - (areaRect.top + (areaHeight / 2));

            treeScrollArea.scrollLeft = Math.max(0, Math.round(nextScrollLeft));
            treeScrollArea.scrollTop = Math.max(0, Math.round(nextScrollTop));

            treeInitialCenterApplied = true;
            treeScrollArea.classList.add('is-tree-ready');
        };

        var scheduleTreeCenterScroll = function () {
            if (treeInitialCenterApplied) {
                return;
            }

            if (treeCenterRaf) {
                window.cancelAnimationFrame(treeCenterRaf);
            }

            treeCenterRaf = window.requestAnimationFrame(function () {
                treeCenterRaf = window.requestAnimationFrame(function () {
                    centerTreeScroll();
                });
            });
        };

        var revealTree = function () {
            treeCenterRetryCount = 0;
            scheduleTreeCenterScroll();
            window.setTimeout(scheduleTreeCenterScroll, 80);
            window.setTimeout(scheduleTreeCenterScroll, 180);
            window.setTimeout(scheduleTreeCenterScroll, 320);
        };

        if (document.readyState === 'complete') {
            revealTree();
        } else {
            window.addEventListener('load', revealTree, { once: true });
        }

        if (typeof ResizeObserver !== 'undefined') {
            var treeStage = document.getElementById('treeZoomStage');
            if (treeStage) {
                var treeCenterObserver = new ResizeObserver(function () {
                    if (!treeInitialCenterApplied) {
                        scheduleTreeCenterScroll();
                    }
                });
                treeCenterObserver.observe(treeScrollArea);
                treeCenterObserver.observe(treeStage);
            }
        }

        window.addEventListener('resize', scheduleTreeCenterScroll);
        window.addEventListener('resize', scheduleDetailEmailFit);
    }
});
</script>
@endsection
