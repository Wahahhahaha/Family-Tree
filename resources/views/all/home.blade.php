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

    .relationship-validation-modal .message-modal-card {
        width: min(720px, calc(100vw - 28px));
    }

    .relationship-validation-modal-card {
        gap: 0;
    }

    .relationship-validation-summary {
        margin: 10px 0 14px;
        padding: 12px 14px;
        border-radius: 16px;
        background: #f5fbff;
        border: 1px solid #d8e5ef;
        color: #17384f;
        font-weight: 700;
        line-height: 1.6;
    }

    .relationship-validation-modal .detail-form-field {
        margin-top: 0;
        margin-bottom: 14px;
    }

    .relationship-validation-modal textarea,
    .relationship-validation-modal input[type="file"] {
        width: 100%;
        box-sizing: border-box;
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
    window.appBaseUrl = <?php echo json_encode(url('/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    window.familyTreeChildParentingModeMap = <?php echo json_encode($childParentingModeDisplayMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    window.familyTimelineByMember = <?php echo json_encode($familyTimelineByMember ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
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
        $canAddMemberFromHome = $isSuperadmin || $currentRoleId === 2 || $currentRoleId === 3 || $currentLevelId === 2;
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
        if ($isSuperadmin || $currentRoleId === 2) {
            $canAddByAge = true;
        }
        $canAddMemberFromHome = $isSuperadmin || $currentRoleId === 2 ? true : ($canAddMemberFromHome && $canAddByAge);
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
        $canCurrentMemberManageDivorce = $canCurrentMemberManageDivorce ?? false;
        $canDeletePartnerMap = $canDeletePartnerMap ?? [];
        $canDeleteChildMap = $canDeleteChildMap ?? [];
        $canUpdateLifeStatusMap = $canUpdateLifeStatusMap ?? [];
        $canEditProfileMap = $canEditProfileMap ?? [];
        $highlightParentMemberId = (int) ($highlightParentMemberId ?? 0);
        $highlightParentForName = (string) ($highlightParentForName ?? '');
        $firstMemberRelation = $firstMember ? ($relationMap[(int) $firstMember->memberid] ?? 'Family Member') : 'Family Member';
        $firstMemberLifeStatusRaw = strtolower((string) ($firstMember->life_status ?? 'alive'));
        $firstMemberDeadDate = trim((string) ($firstMember->deaddate ?? ''));
        $firstChildParentingModeRaw = $firstMember
            ? strtolower((string) ($childParentingModeDisplayMap[(int) ($firstMember->memberid ?? 0)] ?? 'single_parent'))
            : 'single_parent';
        $firstCanDeleteUser = $firstMember ? $canDeleteAnyUserFromHome && (int) ($firstMember->userid ?? 0) !== $currentUserId : false;
        $firstCanDeletePartner = $firstMember ? !empty($canDeletePartnerMap[(int) $firstMember->memberid]) : false;
        $firstCanDeleteChild = $firstMember ? !empty($canDeleteChildMap[(int) $firstMember->memberid]) : false;
        $firstCanUpdateLifeStatus = $firstMember ? !empty($canUpdateLifeStatusMap[(int) $firstMember->memberid]) : false;
        $firstCanEditProfile = $firstMember ? !empty($canEditProfileMap[(int) $firstMember->memberid]) : false;
        $firstCanDivorcePartner = $firstMember
            ? (
                (($isFirstMemberMe && $canCurrentMemberManageDivorce && $currentMemberHasPartner) ? true : false)
                || !empty($canDeletePartnerMap[(int) $firstMember->memberid])
            )
            : false;
        if ($isSuperadmin) {
            $firstCanUpdateLifeStatus = true;
        }
        $firstShowActionBlock = $firstCanDeleteUser || $firstCanDeleteChild || $firstCanUpdateLifeStatus || $firstCanEditProfile || $firstCanDivorcePartner || $firstCanDeletePartner;
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
        $firstMemberGraveLocationUrl = trim((string) ($firstMember->grave_location_url ?? ''));
        $firstMemberTimelineEntries = $firstMember ? ($familyTimelineByMember[(int) $firstMember->memberid] ?? []) : [];

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
                <div id="lifeStatusGraveLocationField" class="detail-form-field hidden" style="margin-top: 0;">
                    <label for="lifeStatusGraveLocationUrl">Grave Location / Cemetery Google Maps Link</label>
                    <input id="lifeStatusGraveLocationUrl" type="url" placeholder="https://maps.google.com/..." maxlength="2048">
                    <small>Optional. Enter a valid Google Maps link for the burial location.</small>
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

    <div id="relationshipValidationModal" class="message-modal relationship-validation-modal" role="dialog" aria-modal="true" aria-labelledby="relationshipValidationTitle" aria-hidden="true" style="display:none;">
        <div class="message-modal-backdrop"></div>
        <div class="message-modal-card relationship-validation-modal-card">
            <h4 id="relationshipValidationTitle">Relationship Validation</h4>
            <div class="message-modal-body">
                <p id="relationshipValidationText" class="message-modal-text">Upload proof document and explain why this relationship action should be reviewed.</p>
                <div id="relationshipValidationSummary" class="relationship-validation-summary"></div>
                <form id="relationshipValidationForm" method="POST" action="/relationship-validations/store" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action_type" id="relationshipValidationActionType" value="<?php echo e(old('action_type', '')); ?>">
                    <input type="hidden" name="memberid" id="relationshipValidationMemberId" value="<?php echo e(old('memberid', '')); ?>">
                    <div class="detail-form-field">
                        <label for="relationshipValidationReason">Reason</label>
                        <textarea id="relationshipValidationReason" name="reason" rows="4" maxlength="5000" placeholder="Describe the request reason..." required><?php echo e(old('reason', '')); ?></textarea>
                    </div>
                    <div class="detail-form-field">
                        <label for="relationshipValidationDocument">Upload Proof Document</label>
                        <input id="relationshipValidationDocument" type="file" name="document" accept=".pdf,image/jpeg,image/png,image/webp" required>
                        <small>Accepted formats: PDF, JPG, JPEG, PNG, and WebP. Maximum size: 8MB.</small>
                    </div>
                    <p id="relationshipValidationError" class="message-modal-text is-error" style="display:none;"></p>
                    <div class="modal-actions" style="display:flex; gap:12px; justify-content:flex-end; margin-top: 20px;">
                        <button id="relationshipValidationCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                        <button id="relationshipValidationSubmitBtn" type="submit" class="btn btn-primary">Submit for Verification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="detailEditResultModal" class="message-modal" role="dialog" aria-modal="true" aria-labelledby="detailEditResultTitle" aria-hidden="true" style="display:none;">
        <div class="message-modal-backdrop"></div>
        <div class="message-modal-card">
            <h4 id="detailEditResultTitle" style="text-align: center;">Status</h4>
            <p id="detailEditResultText" class="message-modal-text" style="text-align: center; margin-top: 10px;"></p>
            <div class="modal-actions" style="display:flex; gap:12px; justify-content:center; margin-top: 20px;">
                <button id="detailEditResultOkBtn" type="button" class="btn btn-primary">OK</button>
            </div>
        </div>
    </div>

    <div id="detailPhotoCropModal" class="photo-crop-modal hidden" role="dialog" aria-modal="true" aria-labelledby="detailPhotoCropTitle">
        <div class="photo-crop-backdrop"></div>
        <div class="photo-crop-card">
            <h4 id="detailPhotoCropTitle">Crop Profile Photo</h4>
            <p id="detailPhotoCropDescription"></p>
            <div class="photo-crop-stage-wrap">
                <canvas id="detailPhotoCropCanvas" class="photo-crop-canvas" width="320" height="320"></canvas>
                <div id="detailPhotoCropFrame" class="photo-crop-frame" aria-hidden="true"></div>
            </div>
            <div class="photo-crop-actions">
                <button id="detailPhotoCropCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                <button id="detailPhotoCropApplyBtn" type="button" class="btn btn-primary">Apply</button>
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

        .page-family-tree .home-page-panel .detail-photo-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .page-family-tree .home-page-panel .detail-photo-wrap.is-editable {
            cursor: pointer;
        }

        .page-family-tree .home-page-panel .detail-photo-wrap.is-editable::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .page-family-tree .home-page-panel .detail-photo-wrap .detail-photo-camera-btn {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2;
            border: 0;
            background: transparent;
            color: #ffffff;
            cursor: pointer;
        }

        .page-family-tree .home-page-panel .detail-photo-wrap.is-editable .detail-photo-camera-btn {
            display: flex;
        }

        .page-family-tree .home-page-panel .detail-photo-wrap.is-editable .detail-photo {
            opacity: 0.4;
        }

        .page-family-tree .home-page-panel .detail-photo-upload-input {
            display: none;
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

        .page-family-tree .home-page-panel .detail-edit-grid .detail-form-field.is-span-full {
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
                    <button id="centerTreeBtn" class="btn btn-soft" type="button">Center Tree</button>
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
                <button
                    id="wikiPanelBtn"
                    type="button"
                    class="btn btn-ghost panel-switch-btn"
                >
                    Wiki
                </button>
            </div>

            <div id="memberDetailBlock" class="hidden <?php echo e($activePanel === 'add-member' ? 'hidden' : ''); ?>">
                <h4>Member Details</h4>
                <div
                    id="detailCard"
                    class="detail-card <?php echo e($firstMemberLifeStatusRaw === 'deceased' ? 'is-deceased' : ''); ?>"
                    data-memberid="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>"
                    data-userid="<?php echo e((int) ($firstMember->userid ?? 0)); ?>"
                    data-username="<?php echo e($firstMember->username ?? ''); ?>"
                    data-name="<?php echo e($firstMember->name ?? ''); ?>"
                    data-email="<?php echo e($firstMember->email ?? ''); ?>"
                    data-phone="<?php echo e($firstMember->phonenumber ?? ''); ?>"
                    data-gender="<?php echo e(isset($firstMember->gender) ? ucfirst((string) $firstMember->gender) : ''); ?>"
                    data-birthdate="<?php echo e($firstMember->birthdate ?? ''); ?>"
                    data-birthplace="<?php echo e($firstMember->birthplace ?? ''); ?>"
                    data-blood-type="<?php echo e($firstMember->bloodtype ?? ''); ?>"
                    data-status="<?php echo e(isset($firstMember->life_status) ? ucfirst((string) $firstMember->life_status) : ''); ?>"
                    data-life-status-raw="<?php echo e($firstMemberLifeStatusRaw); ?>"
                    data-deaddate="<?php echo e($firstMemberDeadDate); ?>"
                    data-marital-status="<?php echo e($firstMember->marital_status ?? ''); ?>"
                    data-job="<?php echo e($firstMember->job ?? ''); ?>"
                    data-education="<?php echo e($firstMember->education_status ?? ''); ?>"
                    data-address="<?php echo e($firstMember->address ?? ''); ?>"
                    data-child-parenting-mode="<?php echo e($firstChildParentingModeRaw ?? ''); ?>"
                    data-grave-location-url="<?php echo e($firstMemberGraveLocationUrl); ?>"
                    data-has-partner="<?php echo e(($firstHasPartner ?? false) ? '1' : '0'); ?>"
                    data-can-divorce-partner="<?php echo e($firstCanDivorcePartner ? '1' : '0'); ?>"
                    data-photo="<?php echo e($memberPictureUrl ?? ''); ?>"
                    data-isme="<?php echo e($isFirstMemberMe ? '1' : '0'); ?>"
                >
                    <?php if ($isSuperadmin || $currentRoleId === 2 || $currentLevelId === 2 || $firstCanEditProfile): ?>
                        <button id="detailEditBtn" type="button" class="btn btn-soft detail-edit-toggle-btn <?php echo e(($firstCanEditProfile || $isSuperadmin || $currentRoleId === 2) ? '' : 'hidden'); ?>">Edit</button>
                    <?php endif; ?>
                    <div id="detailPhotoWrap" class="detail-photo-wrap">
                        <img
                        id="detailPhoto"
                        class="detail-photo"
                        src="<?php echo e($memberPictureUrl); ?>"
                        alt="<?php echo e($firstMember->name ?? 'Member'); ?>"
                        data-isme="<?php echo e($isFirstMemberMe ? '1' : '0'); ?>"
                        >
                        <input id="detailEditPictureInput" type="file" name="picture" accept="image/*" class="detail-photo-upload-input">
                        <?php if ($isSuperadmin || $currentRoleId === 2 || $currentLevelId === 2 || $firstCanEditProfile): ?>
                            <button id="detailPhotoCameraBtn" type="button" class="detail-photo-camera-btn" aria-label="Change profile picture">
                                <i data-lucide="camera" aria-hidden="true"></i>
                            </button>
                        <?php endif; ?>
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
                            <li><span>Death Date</span><strong id="detailDeadDate"><?php echo e($firstMemberDeadDate !== '' ? $firstMemberDeadDate : '-'); ?></strong></li>
                            <li><span>Grave Location</span><strong id="detailGraveLocation"><?php echo $firstMemberGraveLocationUrl !== '' ? '<a href="' . e($firstMemberGraveLocationUrl) . '" target="_blank" rel="noopener noreferrer" class="detail-grave-location-link">View Grave Location</a>' : '-'; ?></strong></li>
                            <li><span>Marital Status</span><strong id="detailMaritalStatus"><?php echo e(isset($firstMember->marital_status) ? ucfirst((string) $firstMember->marital_status) : '-'); ?></strong></li>
                            <li><span>Phone</span><strong id="detailPhone"><?php echo e($firstMember->phonenumber ?? '-'); ?></strong></li>
                            <li><span>Email</span><strong id="detailEmail"><?php echo e($firstMember->email ?? '-'); ?></strong></li>
                            <li class="detail-social-media-row"><span>Social Media</span><table id="detailSocialMediaTable" class="detail-social-media-table"><tbody id="detailSocialMediaList"></tbody></table></li>
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
                            <input type="hidden" name="_from_home" value="1">

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
                                    <label for="detailEditAddressCountry">Address</label>
                                    <input id="detailEditAddress" type="hidden" name="address" value="<?php echo e($firstMember->address ?? ''); ?>">
                                    <input id="detailEditAddressCountryOld" type="hidden" value="">
                                    <input id="detailEditAddressProvinceOld" type="hidden" value="">
                                    <input id="detailEditAddressCityOld" type="hidden" value="">
                                    <input id="detailEditAddressDistrictOld" type="hidden" value="">
                                    <input id="detailEditAddressDetailOld" type="hidden" value="">
                                    <select id="detailEditAddressCountry" name="address_country" required>
                                        <option value="">Select country</option>
                                    </select>
                                </div>

                                <div id="detailEditAddressProvinceField" class="detail-form-field hidden">
                                    <label for="detailEditAddressProvince">Province</label>
                                    <select id="detailEditAddressProvince" name="address_province">
                                        <option value="">Select province</option>
                                    </select>
                                </div>

                                <div id="detailEditAddressCityField" class="detail-form-field hidden">
                                    <label for="detailEditAddressCity">City</label>
                                    <select id="detailEditAddressCity" name="address_city">
                                        <option value="">Select city</option>
                                    </select>
                                </div>

                                <div id="detailEditAddressDistrictField" class="detail-form-field hidden">
                                    <label for="detailEditAddressDistrict">District</label>
                                    <select id="detailEditAddressDistrict" name="address_district">
                                        <option value="">Select district</option>
                                    </select>
                                </div>

                                <div class="detail-form-field full-width">
                                    <label for="detailEditAddressDetail">Address detail</label>
                                    <input id="detailEditAddressDetail" type="text" name="address_detail" value="" placeholder="Street, RT/RW, postal code (optional)">
                                </div>
                            </div>

                            <div id="detailEditSettingsPanel" class="detail-edit-settings-panel hidden">
                                <div class="detail-edit-grid">
                                    <div class="detail-form-field">
                                        <label for="detailEditLifeStatus">Life Status</label>
                                        <select id="detailEditLifeStatus" name="life_status">
                                            <option value="alive" <?php echo e($firstMemberLifeStatusRaw === 'alive' ? 'selected' : ''); ?>>Alive</option>
                                            <option value="deceased" <?php echo e($firstMemberLifeStatusRaw === 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                                        </select>
                                    </div>
                                    <div class="detail-form-field">
                                        <label for="detailEditDeadDate">Death Date</label>
                                        <input id="detailEditDeadDate" type="date" name="deaddate" value="<?php echo e($firstMemberDeadDate); ?>">
                                    </div>
                                    <div class="detail-form-field full-width">
                                        <label for="detailEditGraveLocationUrl">Grave Location / Cemetery Google Maps Link</label>
                                        <input id="detailEditGraveLocationUrl" type="url" name="grave_location_url" value="<?php echo e($firstMemberGraveLocationUrl); ?>" placeholder="https://maps.google.com/...">
                                        <small style="display:block;margin-top:6px;color:var(--muted-text,#7a7a7a);">Optional. Paste a Google Maps link or another valid URL.</small>
                                    </div>
                                    <div class="detail-form-field">
                                        <label for="detailEditChildParentingMode">Child Status</label>
                                        <select id="detailEditChildParentingMode" name="child_parenting_mode">
                                            <option value="single_parent" <?php echo e(($firstChildParentingModeRaw ?? 'single_parent') === 'single_parent' ? 'selected' : ''); ?>>Single parent</option>
                                            <option value="with_current_partner" <?php echo e(($firstChildParentingModeRaw ?? '') === 'with_current_partner' ? 'selected' : ''); ?>>With current partner</option>
                                        </select>
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

                        <form id="divorcePartnerForm" method="POST" action="/relationship-validations/store" class="<?php echo e($firstCanDivorcePartner ? '' : 'hidden'); ?>" data-delete-message="Divorce this partner relationship?" data-validation-action-type="divorce" data-validation-action-label="Divorce">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action_type" value="divorce">
                            <input id="divorcePartnerMemberIdInput" type="hidden" name="memberid" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">
                            <button type="submit" class="btn btn-danger-soft btn-block">Divorce</button>
                        </form>

                        <?php if ($canDeleteAnyUserFromHome): ?>
                            <form id="deleteUserForm" method="POST" action="/management/users/<?php echo e((int) ($firstMember->userid ?? 0)); ?>/delete" class="<?php echo e(($firstCanDeleteUser && !$firstCanDivorcePartner) ? '' : 'hidden'); ?>" data-delete-message="Move this user to the recycle bin?">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-danger-soft btn-block">Delete User</button>
                            </form>
                        <?php else: ?>
                            <form id="deleteChildForm" method="POST" action="/relationship-validations/store" class="<?php echo e($firstCanDeleteChild ? '' : 'hidden'); ?>" data-delete-message="Delete this child account permanently?" data-validation-action-type="delete_child" data-validation-action-label="Delete Child">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action_type" value="delete_child">
                                <input id="deleteChildMemberIdInput" type="hidden" name="memberid" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">
                                <button type="submit" class="btn btn-danger-soft btn-block">Delete Child</button>
                            </form>
                        <?php endif; ?>

                        <div class="member-life-status-actions">
                            <input id="lifeStatusMemberIdInput" type="hidden" value="<?php echo e((int) ($firstMember->memberid ?? 0)); ?>">

                            <button
                                id="lifeStatusToggleBtn"
                                type="button"
                                class="btn btn-soft btn-block btn-status-toggle <?php echo e($firstCanUpdateLifeStatus ? '' : 'hidden'); ?>"
                                data-status="<?php echo e($firstMemberLifeStatusRaw === 'deceased' ? 'alive' : 'deceased'); ?>"
                            >
                                <?php echo e($firstMemberLifeStatusRaw === 'deceased' ? 'Mark as Alive' : 'Mark as Deceased'); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($canEditOwnProfile || $canAddMemberFromHome): ?>
                <div class="detail-form-wrap">
                    <?php if ($canAddMemberFromHome): ?>
                        <div id="addMemberPanel" class="detail-panel hidden">
                           <form method="POST" action="/family/member/store" class="detail-form" data-current-member-gender="<?php echo e($currentMemberGenderRaw); ?>" data-default-partner-gender="<?php echo e($defaultPartnerGender); ?>" data-is-superadmin="<?php echo e($isSuperadmin ? '1' : '0'); ?>" data-can-use-current-partner="<?php echo e($currentMemberCanUseCurrentPartner ? '1' : '0'); ?>">
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
    var lifeStatusGraveLocationField = document.getElementById('lifeStatusGraveLocationField');
    var lifeStatusGraveLocationInput = document.getElementById('lifeStatusGraveLocationUrl');
    var lifeStatusConfirmError = document.getElementById('lifeStatusConfirmError');
    var lifeStatusConfirmBtn = document.getElementById('lifeStatusConfirmBtn');
    var lifeStatusConfirmCancelBtn = document.getElementById('lifeStatusConfirmCancelBtn');
    var lifeStatusPendingStatus = '';
    var lifeStatusPendingMemberId = '';
    var lifeStatusPendingGraveLocationUrl = '';
    var deleteConfirmModal = document.getElementById('deleteConfirmModal');
    var deleteConfirmTitle = document.getElementById('deleteConfirmTitle');
    var deleteConfirmText = document.getElementById('deleteConfirmText');
    var deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
    var deleteConfirmCancelBtn = document.getElementById('deleteConfirmCancelBtn');
    var pendingDeleteForm = null;
    var pendingDeleteMessage = 'Are you sure you want to delete this item?';
    var relationshipValidationModal = document.getElementById('relationshipValidationModal');
    var relationshipValidationTitle = document.getElementById('relationshipValidationTitle');
    var relationshipValidationText = document.getElementById('relationshipValidationText');
    var relationshipValidationSummary = document.getElementById('relationshipValidationSummary');
    var relationshipValidationForm = document.getElementById('relationshipValidationForm');
    var relationshipValidationActionType = document.getElementById('relationshipValidationActionType');
    var relationshipValidationMemberId = document.getElementById('relationshipValidationMemberId');
    var relationshipValidationReason = document.getElementById('relationshipValidationReason');
    var relationshipValidationDocument = document.getElementById('relationshipValidationDocument');
    var relationshipValidationError = document.getElementById('relationshipValidationError');
    var relationshipValidationCancelBtn = document.getElementById('relationshipValidationCancelBtn');
    var relationshipValidationSubmitBtn = document.getElementById('relationshipValidationSubmitBtn');
    var pendingRelationshipValidationForm = null;
    var shouldReopenRelationshipValidationModal = <?php echo json_encode((bool) session('openRelationshipValidationModal')); ?>;
    var detailEditResultModal = document.getElementById('detailEditResultModal');
    var detailEditResultTitle = document.getElementById('detailEditResultTitle');
    var detailEditResultText = document.getElementById('detailEditResultText');
    var detailEditResultOkBtn = document.getElementById('detailEditResultOkBtn');
    var detailEditResultModalTimer = 0;
    var detailEditResultAutoCloseAction = null;

    function closeDetailEditResultModal(shouldRunAction) {
        if (detailEditResultModalTimer) {
            window.clearTimeout(detailEditResultModalTimer);
            detailEditResultModalTimer = 0;
        }

        if (!detailEditResultModal || detailEditResultModal.classList.contains('hidden')) {
            if (shouldRunAction && typeof detailEditResultAutoCloseAction === 'function') {
                var action = detailEditResultAutoCloseAction;
                detailEditResultAutoCloseAction = null;
                action();
            }
            return;
        }

        detailEditResultModal.classList.remove('is-open');
        detailEditResultModal.classList.add('is-closing');
        detailEditResultModal.setAttribute('aria-hidden', 'true');
        detailEditResultModal.style.display = 'none';
        detailEditResultModalTimer = window.setTimeout(function () {
            var action = detailEditResultAutoCloseAction;
            detailEditResultModal.classList.add('hidden');
            detailEditResultModal.classList.remove('is-closing');
            detailEditResultModalTimer = 0;
            detailEditResultAutoCloseAction = null;
            if (shouldRunAction && typeof action === 'function') {
                action();
            }
        }, 220);
    }

    function openDetailEditResultModal(title, message, isError, autoCloseAction) {
        if (!detailEditResultModal) {
            return;
        }

        if (detailEditResultModalTimer) {
            window.clearTimeout(detailEditResultModalTimer);
            detailEditResultModalTimer = 0;
        }

        detailEditResultAutoCloseAction = typeof autoCloseAction === 'function' ? autoCloseAction : null;
        if (detailEditResultTitle) {
            detailEditResultTitle.textContent = title || (isError ? 'Error' : 'Success');
        }
        if (detailEditResultText) {
            detailEditResultText.textContent = message || '';
            detailEditResultText.classList.toggle('is-error', !!isError);
        }
        if (detailEditResultOkBtn) {
            detailEditResultOkBtn.textContent = 'OK';
        }

        detailEditResultModal.classList.remove('hidden', 'is-closing');
        detailEditResultModal.setAttribute('aria-hidden', 'false');
        detailEditResultModal.style.display = 'flex';
        void detailEditResultModal.offsetWidth;
        detailEditResultModal.classList.add('is-open');

        if (!isError) {
            detailEditResultModalTimer = window.setTimeout(function () {
                closeDetailEditResultModal(true);
            }, 1800);
        }
    }

    function getFirstValidationErrorMessage(errors) {
        if (!errors || typeof errors !== 'object') {
            return '';
        }

        var keys = Object.keys(errors);
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var value = errors[key];
            if (Array.isArray(value) && value.length > 0 && String(value[0] || '').trim() !== '') {
                return String(value[0]);
            }
        }

        return '';
    }

    function getValidationErrorSummary(errors) {
        if (!errors || typeof errors !== 'object') {
            return '';
        }

        var messages = [];
        Object.keys(errors).forEach(function (key) {
            var value = errors[key];
            if (!Array.isArray(value)) {
                return;
            }

            value.forEach(function (message) {
                var normalizedMessage = String(message || '').trim();
                if (normalizedMessage !== '' && messages.indexOf(normalizedMessage) === -1) {
                    messages.push(normalizedMessage);
                }
            });
        });

        return messages.join('\n');
    }

    function openLifeStatusModal(statusText, memberId) {
        var normalizedStatus = String(statusText || '').trim().toLowerCase();
        var isDeceased = normalizedStatus === 'deceased';
        var sourceCard = currentSelectedMemberCard || detailCard || document.querySelector('.member-card[data-memberid].active') || null;
        var currentGraveLocationUrl = sourceCard ? String(sourceCard.getAttribute('data-grave-location-url') || '').trim() : '';

        lifeStatusPendingStatus = isDeceased ? 'deceased' : 'alive';
        lifeStatusPendingMemberId = memberId;
        lifeStatusPendingGraveLocationUrl = currentGraveLocationUrl;
        if (lifeStatusConfirmTitle) {
            lifeStatusConfirmTitle.textContent = isDeceased ? 'Set Date of Death' : 'Confirm Life Status';
        }
        if (lifeStatusConfirmText) {
            lifeStatusConfirmText.textContent = isDeceased
                ? 'Choose the date of death and optionally add a grave location before saving this status.'
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
        if (lifeStatusGraveLocationField) {
            lifeStatusGraveLocationField.classList.toggle('hidden', !isDeceased);
        }
        if (lifeStatusGraveLocationInput) {
            lifeStatusGraveLocationInput.value = isDeceased ? currentGraveLocationUrl : '';
            lifeStatusGraveLocationInput.required = false;
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

        if (deleteConfirmTitle) {
            deleteConfirmTitle.textContent = 'Confirm Delete';
        }
        if (deleteConfirmText) {
            deleteConfirmText.textContent = pendingDeleteMessage;
        }
        if (deleteConfirmBtn) {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = 'Delete';
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

    function openRelationshipValidationModal(form, preserveExistingValues) {
        if (!form || !relationshipValidationModal) return;

        var shouldPreserveExistingValues = !!preserveExistingValues;
        pendingRelationshipValidationForm = form;
        var actionTypeInput = form.querySelector('input[name="action_type"]');
        var memberIdInput = form.querySelector('input[name="memberid"]');
        var actionType = form.getAttribute('data-validation-action-type') || (actionTypeInput ? actionTypeInput.value : '') || '';
        var actionLabel = form.getAttribute('data-validation-action-label') || 'Relationship Action';
        var selectedCard = currentSelectedMemberCard || detailCard || document.querySelector('.member-card[data-memberid].active') || null;
        var detailNameText = detailName ? detailName.textContent : '';
        var memberName = selectedCard ? (selectedCard.getAttribute('data-name') || detailNameText || 'Selected member') : 'Selected member';
        var memberId = selectedCard
            ? (selectedCard.getAttribute('data-memberid') || (memberIdInput ? memberIdInput.value : '') || '')
            : ((memberIdInput ? memberIdInput.value : '') || '');
        var actionDescription = 'Upload proof document and explain why the request for ' + memberName + ' should be reviewed.';

        if (actionType === 'divorce') {
            actionDescription = 'Upload the divorce document and explain why the divorce request for ' + memberName + ' should be reviewed.';
        } else if (actionType === 'delete_child') {
            actionDescription = 'Upload proof document and explain why the request to remove ' + memberName + ' should be reviewed.';
        } else if (actionType === 'delete_partner') {
            actionDescription = 'Upload proof document and explain why the request to remove the partner relationship for ' + memberName + ' should be reviewed.';
        }

        if (relationshipValidationTitle) {
            relationshipValidationTitle.textContent = actionLabel + ' - Relationship Validation';
        }
        if (relationshipValidationText) {
            relationshipValidationText.textContent = actionDescription;
        }
        if (relationshipValidationSummary) {
            relationshipValidationSummary.textContent = actionLabel + ' for ' + memberName + (memberId ? ' (Member #' + memberId + ')' : '');
        }
        if (relationshipValidationActionType) {
            relationshipValidationActionType.value = actionType;
        }
        if (relationshipValidationMemberId) {
            relationshipValidationMemberId.value = memberId;
        }
        if (relationshipValidationReason && !shouldPreserveExistingValues) {
            relationshipValidationReason.value = '';
        }
        if (relationshipValidationDocument && !shouldPreserveExistingValues) {
            relationshipValidationDocument.value = '';
        }
        if (relationshipValidationError) {
            relationshipValidationError.textContent = '';
            relationshipValidationError.style.display = 'none';
        }
        if (relationshipValidationSubmitBtn) {
            relationshipValidationSubmitBtn.disabled = false;
            relationshipValidationSubmitBtn.textContent = 'Submit for Verification';
        }
        if (relationshipValidationModal) {
            relationshipValidationModal.classList.add('is-open');
            relationshipValidationModal.setAttribute('aria-hidden', 'false');
            relationshipValidationModal.style.display = 'flex';
        }

        if (relationshipValidationReason) {
            window.setTimeout(function () {
                relationshipValidationReason.focus();
            }, 0);
        }
    }

    function closeRelationshipValidationModal() {
        pendingRelationshipValidationForm = null;
        if (relationshipValidationActionType) {
            relationshipValidationActionType.value = '';
        }
        if (relationshipValidationMemberId) {
            relationshipValidationMemberId.value = '';
        }
        if (relationshipValidationReason) {
            relationshipValidationReason.value = '';
        }
        if (relationshipValidationDocument) {
            relationshipValidationDocument.value = '';
        }
        if (relationshipValidationError) {
            relationshipValidationError.textContent = '';
            relationshipValidationError.style.display = 'none';
        }
        if (relationshipValidationSubmitBtn) {
            relationshipValidationSubmitBtn.disabled = false;
            relationshipValidationSubmitBtn.textContent = 'Submit for Verification';
        }
        if (relationshipValidationModal) {
            relationshipValidationModal.classList.remove('is-open');
            relationshipValidationModal.setAttribute('aria-hidden', 'true');
            relationshipValidationModal.style.display = 'none';
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
        if (lifeStatusGraveLocationInput) {
            lifeStatusGraveLocationInput.required = false;
            lifeStatusGraveLocationInput.value = '';
        }
        lifeStatusPendingGraveLocationUrl = '';
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

    function bindRelationshipValidationForm(form) {
        if (!form) return;

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openRelationshipValidationModal(form);
        });
    }

    if (deleteConfirmCancelBtn) {
        deleteConfirmCancelBtn.addEventListener('click', function () {
            closeDeleteModal();
        });
    }

    if (relationshipValidationCancelBtn) {
        relationshipValidationCancelBtn.addEventListener('click', function () {
            closeRelationshipValidationModal();
        });
    }

    if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('click', function (event) {
            if (event.target === deleteConfirmModal || event.target.classList.contains('message-modal-backdrop')) {
                closeDeleteModal();
            }
        });
    }

    if (relationshipValidationModal) {
        relationshipValidationModal.addEventListener('click', function (event) {
            if (event.target === relationshipValidationModal || event.target.classList.contains('message-modal-backdrop')) {
                closeRelationshipValidationModal();
            }
        });
    }

    if (detailEditResultOkBtn) {
        detailEditResultOkBtn.addEventListener('click', function () {
            closeDetailEditResultModal(true);
        });
    }

    if (detailEditResultModal) {
        detailEditResultModal.addEventListener('click', function (event) {
            if (event.target === detailEditResultModal || event.target.classList.contains('message-modal-backdrop')) {
                closeDetailEditResultModal(true);
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && deleteConfirmModal && deleteConfirmModal.classList.contains('is-open')) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && relationshipValidationModal && relationshipValidationModal.classList.contains('is-open')) {
            closeRelationshipValidationModal();
        }
    });

    if (shouldReopenRelationshipValidationModal) {
        var reopenActionType = relationshipValidationActionType ? relationshipValidationActionType.value : '';
        var reopenForm = null;
        if (reopenActionType === 'divorce') {
            reopenForm = divorcePartnerForm;
        } else if (reopenActionType === 'delete_child') {
            reopenForm = deleteChildForm;
        } else if (reopenActionType === 'delete_partner') {
        }

        if (reopenForm) {
            openRelationshipValidationModal(reopenForm, true);
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && detailEditResultModal && detailEditResultModal.classList.contains('is-open')) {
            closeDetailEditResultModal(true);
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
            var graveLocationUrl = lifeStatusGraveLocationInput ? String(lifeStatusGraveLocationInput.value || '').trim() : '';
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
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    memberid: lifeStatusPendingMemberId,
                    life_status: lifeStatusPendingStatus,
                    deaddate: lifeStatusPendingStatus === 'deceased' ? deadDate : '',
                    grave_location_url: lifeStatusPendingStatus === 'deceased' ? graveLocationUrl : ''
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

                var validationMessage = getFirstValidationErrorMessage(result.data && result.data.errors);
                var message = (result.data && result.data.message) ? result.data.message : validationMessage;
                if (!message) {
                    message = 'Failed to update status.';
                }
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
    var detailPhotoWrap = document.getElementById('detailPhotoWrap');
    var detailPhotoCameraBtn = document.getElementById('detailPhotoCameraBtn');
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
    var detailDeadDate = document.getElementById('detailDeadDate');
    var detailGraveLocation = document.getElementById('detailGraveLocation');
    var detailMaritalStatus = document.getElementById('detailMaritalStatus');
    var detailPhone = document.getElementById('detailPhone');
    var detailEmail = document.getElementById('detailEmail');
    var detailSocialMediaList = document.getElementById('detailSocialMediaList');
    var firstMemberSocialMediaItems = <?php echo json_encode($firstMember->social_media_items ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
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
    var detailEditDeadDate = document.getElementById('detailEditDeadDate');
    var detailEditGraveLocationUrl = document.getElementById('detailEditGraveLocationUrl');
    var detailEditAddressCountry = document.getElementById('detailEditAddressCountry');
    var detailEditAddressCountryOld = document.getElementById('detailEditAddressCountryOld');
    var detailEditAddressProvinceField = document.getElementById('detailEditAddressProvinceField');
    var detailEditAddressProvince = document.getElementById('detailEditAddressProvince');
    var detailEditAddressProvinceOld = document.getElementById('detailEditAddressProvinceOld');
    var detailEditAddressCityField = document.getElementById('detailEditAddressCityField');
    var detailEditAddressCity = document.getElementById('detailEditAddressCity');
    var detailEditAddressCityOld = document.getElementById('detailEditAddressCityOld');
    var detailEditAddressDistrictField = document.getElementById('detailEditAddressDistrictField');
    var detailEditAddressDistrict = document.getElementById('detailEditAddressDistrict');
    var detailEditAddressDistrictOld = document.getElementById('detailEditAddressDistrictOld');
    var detailEditAddressDetail = document.getElementById('detailEditAddressDetail');
    var detailEditAddressDetailOld = document.getElementById('detailEditAddressDetailOld');
    var detailEditLifeStatus = document.getElementById('detailEditLifeStatus');
    var detailEditChildParentingMode = document.getElementById('detailEditChildParentingMode');
    var detailEditChildParentingModeWithCurrentPartnerOption = detailEditChildParentingMode
        ? detailEditChildParentingMode.querySelector('option[value="with_current_partner"]')
        : null;
    var detailEditMemberIdInput = document.getElementById('detailEditMemberIdInput');
    var detailEditPictureInput = document.getElementById('detailEditPictureInput');
    var detailPhotoCropModal = document.getElementById('detailPhotoCropModal');
    var detailPhotoCropCanvas = document.getElementById('detailPhotoCropCanvas');
    var detailPhotoCropFrame = document.getElementById('detailPhotoCropFrame');
    var detailPhotoCropApplyBtn = document.getElementById('detailPhotoCropApplyBtn');
    var detailPhotoCropCancelBtn = document.getElementById('detailPhotoCropCancelBtn');
    var detailPhotoCropTitle = document.getElementById('detailPhotoCropTitle');
    var detailPhotoCropDescription = document.getElementById('detailPhotoCropDescription');
    var detailPhotoCropStageWrap = document.querySelector('.photo-crop-stage-wrap');
    var divorcePartnerForm = document.getElementById('divorcePartnerForm');
    var divorcePartnerMemberIdInput = document.getElementById('divorcePartnerMemberIdInput');
    var deletePartnerMemberIdInput = document.getElementById('deletePartnerMemberIdInput');
    var deleteUserForm = document.getElementById('deleteUserForm');
    var deleteChildForm = document.getElementById('deleteChildForm');
    var lifeStatusForm = document.getElementById('lifeStatusForm');
    var lifeStatusToggleBtn = document.getElementById('lifeStatusToggleBtn');
    var deleteChildMemberIdInput = document.getElementById('deleteChildMemberIdInput');
    var lifeStatusMemberIdInput = document.getElementById('lifeStatusMemberIdInput');
    var childParentingModeUpdateForm = document.getElementById('childParentingModeUpdateForm');
    var childParentingModeMemberIdInput = document.getElementById('childParentingModeMemberIdInput');
    var childParentingModeActionSelect = document.getElementById('childParentingModeActionSelect');
    var childParentingModeActionBtn = document.getElementById('childParentingModeActionBtn');
    var centerTreeBtn = document.getElementById('centerTreeBtn');
    var familyTreeChildParentingModeMap = window.familyTreeChildParentingModeMap || {};
    var familyTimelineByMember = window.familyTimelineByMember || {};
    var allMemberCards = document.querySelectorAll('.member-card[data-memberid]');
    var currentSelectedMemberCard = null;

    bindDeleteConfirm(deleteUserForm);
    bindRelationshipValidationForm(divorcePartnerForm);
    bindRelationshipValidationForm(deleteChildForm);
    var detailEditModeActive = false;
    var detailCanEditChildParentingMode = false;
    var detailPhotoPreviewObjectUrl = '';
    var detailPhotoOriginalSrc = '';
    var detailPhotoSelectedFile = null;
    var detailPhotoCropCloseTimer = 0;
    var detailPhotoCropRequestToken = 0;
    var detailPhotoCropState = {
        input: null,
        previewSrc: '',
        file: null,
        image: null,
        imageUrl: '',
        scaleBase: 1,
        zoom: 1,
        offsetX: 0,
        offsetY: 0,
        dragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOffsetX: 0,
        dragOffsetY: 0,
        pinching: false,
        pinchStartDistance: 0,
        pinchStartZoom: 1,
        pinchCenterX: 0,
        pinchCenterY: 0,
        frameX: 0,
        frameY: 0,
        frameSize: 0
    };

    function updateDetailValue(element, value) {
        if (!element) return;
        element.textContent = value && String(value).trim() !== '' ? value : '-';
    }

    function normalizeExternalUrl(value) {
        var raw = String(value || '').trim();
        if (raw === '') {
            return '';
        }

        try {
            var parsed = /^[a-z][a-z0-9+.-]*:/i.test(raw)
                ? new URL(raw)
                : new URL('https://' + raw.replace(/^\/+/, ''));
            if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') {
                return '';
            }
            return parsed.toString();
        } catch (error) {
            return '';
        }
    }

    function renderDetailGraveLocation(element, url) {
        if (!element) return;

        clearElementChildren(element);
        var normalizedUrl = normalizeExternalUrl(url);
        if (!normalizedUrl) {
            element.textContent = '-';
            return;
        }

        var link = document.createElement('a');
        link.href = normalizedUrl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.className = 'detail-grave-location-link';
        link.textContent = 'View Grave Location';
        element.appendChild(link);
    }

    function normalizeEditableValue(value) {
        var normalized = value === null || typeof value === 'undefined' ? '' : String(value).trim();
        return normalized === '-' ? '' : normalized;
    }

    function getDetailEditSourceCard() {
        if (currentSelectedMemberCard) {
            return currentSelectedMemberCard;
        }

        if (detailCard) {
            var detailMemberId = String(detailCard.getAttribute('data-memberid') || '').trim();
            var detailUserId = String(detailCard.getAttribute('data-userid') || '').trim();
            var allCards = Array.prototype.slice.call(document.querySelectorAll('.member-card[data-memberid]'));

            for (var i = 0; i < allCards.length; i += 1) {
                var candidateCard = allCards[i];
                var candidateMemberId = String(candidateCard.getAttribute('data-memberid') || '').trim();
                var candidateUserId = String(candidateCard.getAttribute('data-userid') || '').trim();

                if ((detailMemberId !== '' && candidateMemberId === detailMemberId) || (detailUserId !== '' && candidateUserId === detailUserId)) {
                    return candidateCard;
                }
            }

            return detailCard;
        }

        return document.querySelector('.member-card[data-memberid].active')
            || document.querySelector('.member-card[data-memberid]')
            || null;
    }

    function syncDetailCardDataFromCard(card) {
        if (!detailCard || !card) {
            return;
        }

        [
            'memberid',
            'userid',
            'username',
            'name',
            'email',
            'phone',
            'gender',
            'birthdate',
            'birthplace',
            'blood-type',
            'status',
            'life-status-raw',
            'marital-status',
            'job',
            'education',
            'address',
            'child-parenting-mode',
            'grave-location-url',
            'has-partner',
            'can-divorce-partner',
            'photo',
            'isme',
            'can-delete-child',
            'can-update-life-status',
            'can-edit-profile',
            'can-edit-child-parenting-mode',
            'social-media-items'
        ].forEach(function (attributeName) {
            var value = card.getAttribute('data-' + attributeName);
            if (value !== null) {
                detailCard.setAttribute('data-' + attributeName, value);
            }
        });
    }

    function resolveDetailPhotoUrl(photoUrl) {
        var normalizedPhotoUrl = normalizeEditableValue(photoUrl);
        if (normalizedPhotoUrl === '') {
            return '';
        }

        if (/^(?:https?:|data:|blob:)/i.test(normalizedPhotoUrl)) {
            return normalizedPhotoUrl;
        }

        var baseUrl = normalizeEditableValue(window.appBaseUrl || '');
        if (baseUrl !== '') {
            return baseUrl.replace(/\/+$/, '') + '/' + normalizedPhotoUrl.replace(/^\/+/, '');
        }

        return normalizedPhotoUrl;
    }

    function getDetailSocialMediaPlatform(rawValue) {
        var value = String(rawValue || '').toLowerCase();
        var parsedUrl = null;
        try {
            parsedUrl = /^[a-z][a-z0-9+.-]*:/i.test(value)
                ? new URL(value)
                : new URL('https://' + value.replace(/^\/+/, ''));
        } catch (error) {
            parsedUrl = null;
        }

        var host = parsedUrl ? String(parsedUrl.hostname || '').toLowerCase() : '';
        var raw = value;

        if (host.indexOf('facebook.com') !== -1 || host === 'fb.com' || raw.indexOf('facebook') !== -1 || raw.indexOf('fb.com') !== -1) {
            return 'facebook';
        }
        if (host.indexOf('instagram.com') !== -1 || raw.indexOf('instagram') !== -1 || raw.indexOf('ig.') !== -1) {
            return 'instagram';
        }
        if (host.indexOf('tiktok.com') !== -1 || raw.indexOf('tiktok') !== -1) {
            return 'tiktok';
        }
        if (host.indexOf('youtube.com') !== -1 || host === 'youtu.be' || raw.indexOf('youtube') !== -1) {
            return 'youtube';
        }
        if (host.indexOf('x.com') !== -1 || host.indexOf('twitter.com') !== -1 || raw.indexOf('twitter') !== -1 || raw === 'x') {
            return 'x';
        }
        if (host.indexOf('whatsapp.com') !== -1 || host.indexOf('wa.me') !== -1 || raw.indexOf('whatsapp') !== -1 || raw.indexOf('wa.me') !== -1) {
            return 'whatsapp';
        }
        if (host.indexOf('linkedin.com') !== -1 || raw.indexOf('linkedin') !== -1) {
            return 'linkedin';
        }
        if (host.indexOf('telegram.me') !== -1 || host.indexOf('t.me') !== -1 || raw.indexOf('telegram') !== -1) {
            return 'telegram';
        }

        return 'link';
    }

    function resolveDetailSocialMediaInfo(rawValue, fallbackName) {
        var normalizedValue = normalizeEditableValue(rawValue);
        if (normalizedValue === '') {
            return {
                label: '-',
                href: '',
                platform: 'link'
            };
        }

        var href = normalizedValue;
        var label = '';
        var hasScheme = /^[a-z][a-z0-9+.-]*:/i.test(normalizedValue);
        var parsedUrl = null;

        try {
            parsedUrl = hasScheme
                ? new URL(normalizedValue)
                : new URL('https://' + normalizedValue.replace(/^\/+/, ''));
            href = parsedUrl.href;
            var segments = parsedUrl.pathname.split('/').filter(Boolean);
            var lastSegment = segments.length ? segments[segments.length - 1] : '';
            label = normalizeEditableValue(lastSegment || parsedUrl.searchParams.get('username') || parsedUrl.searchParams.get('user') || '');
        } catch (error) {
            href = normalizedValue;
        }

        if (label === '') {
            label = normalizeEditableValue(fallbackName);
        }
        if (label === '') {
            label = normalizedValue.replace(/^https?:\/\//i, '').replace(/^www\./i, '');
            label = label.split('/').filter(Boolean).pop() || label;
        }

        label = decodeURIComponent(label).replace(/^@+/, '').replace(/\.[^.]+$/, '');
        label = label.replace(/[-_]+/g, ' ').trim();
        if (label === '') {
            label = normalizedValue;
        }

        return {
            label: label,
            href: href,
            platform: parsedUrl ? getDetailSocialMediaPlatform(normalizedValue) : 'link'
        };
    }

    function parseDetailSocialMediaItems(rawItems, fallbackValue) {
        if (Array.isArray(rawItems) && rawItems.length) {
            return rawItems;
        }

        var fallbackItems = normalizeEditableValue(fallbackValue) === ''
            ? []
            : normalizeEditableValue(fallbackValue)
                .split(/[\n\r;|]+/)
                .map(function (part) {
                    return normalizeEditableValue(part);
                })
                .filter(function (part) {
                    return part !== '';
                })
                .map(function (value) {
                    return { name: '', link: value };
                });

        return fallbackItems;
    }

    function detailSocialMediaIconSvg(platform) {
        if (platform === 'facebook') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 8h2.5V5H14c-2.2 0-4 1.8-4 4v2H8v3h2v8h3v-8h2.3l.7-3H13V9c0-.6.4-1 1-1z"></path></svg>';
        }
        if (platform === 'instagram') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="4" ry="4" fill="none" stroke="currentColor" stroke-width="2"></rect><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="2"></circle><circle cx="17" cy="7" r="1.2" fill="currentColor"></circle></svg>';
        }
        if (platform === 'tiktok') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 4c.5 2.8 2.3 4.3 5 4.5v2.8c-1.8 0-3.4-.5-5-1.6V16c0 3-2.4 5-5 5s-5-2-5-5 2.4-5 5-5c.4 0 .7 0 1 .1v3.1c-.3-.1-.7-.2-1-.2-1.3 0-2.4 1-2.4 2.2S8.7 18.4 10 18.4s2.4-1 2.4-2.2V4h1.6z"></path></svg>';
        }
        if (platform === 'youtube') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="3" ry="3" fill="currentColor"></rect><path d="M10 9.5L15 12l-5 2.5v-5z" fill="#ffffff"></path></svg>';
        }
        if (platform === 'x') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 4h4.2l4 5.7L16.8 4H20l-6.8 9.1L20 20h-4.2l-4.4-6.1L6.8 20H4l7.2-9.6L4 4z"></path></svg>';
        }
        if (platform === 'whatsapp') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3.5A8.5 8.5 0 0 0 4.2 16.1L3 21l5-1.1A8.5 8.5 0 1 0 12 3.5zm0 15.3a6.7 6.7 0 0 1-3.4-.9l-.2-.1-2.9.6.6-2.8-.2-.3A6.7 6.7 0 1 1 12 18.8zm3.9-4.8c-.2-.1-1.2-.6-1.4-.7-.2-.1-.4-.1-.6.1-.2.2-.6.7-.8.8-.1.1-.3.1-.5 0-.2-.1-.9-.3-1.7-1a6.3 6.3 0 0 1-1.2-1.5c-.1-.2 0-.3.1-.4l.4-.4c.1-.1.2-.3.3-.5.1-.2 0-.4 0-.5-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.2-.9.9-.9 2.1s.9 2.3 1 2.5c.1.2 1.7 2.7 4.1 3.8.6.3 1 .4 1.4.5.6.2 1.1.2 1.4.1.4-.1 1.2-.5 1.4-1 .2-.5.2-.9.1-1 0-.1-.2-.2-.4-.3z"></path></svg>';
        }
        if (platform === 'linkedin') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.5 8.5H4V20h2.5V8.5zM5.2 4A1.5 1.5 0 1 0 5.2 7a1.5 1.5 0 0 0 0-3zM20 20h-2.5v-5.9c0-1.4-.5-2.3-1.7-2.3-1 0-1.5.7-1.8 1.4-.1.2-.1.6-.1.9V20H11.4s0-9.5 0-10.4H14v1.5c.3-.7 1.1-1.7 2.8-1.7 2 0 3.2 1.3 3.2 4.1V20z"></path></svg>';
        }
        if (platform === 'telegram') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.7 4.8 18.6 19c-.2 1-1 1.3-1.9.8l-4.2-3.1-2 2c-.2.2-.4.4-.8.4l.3-4.4 8.1-7.3c.4-.4-.1-.6-.6-.3L8.5 12.5 4.2 11.1c-1-.3-1-1 .2-1.5L20.1 4.1c.8-.3 1.5.2 1.6.7z"></path></svg>';
        }

        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10 13.5a1.5 1.5 0 0 1 0-3h4a1.5 1.5 0 0 1 0 3h-4z"></path><path fill="currentColor" d="M8 7h8a5 5 0 0 1 0 10H8A5 5 0 0 1 8 7zm0 2a3 3 0 0 0 0 6h8a3 3 0 0 0 0-6H8z"></path></svg>';
    }

    function clearElementChildren(element) {
        if (!element) {
            return;
        }

        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
    }

    function escapeHtml(value) {
        return String(value === null || typeof value === 'undefined' ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderDetailSocialMediaList(items) {
        if (!detailSocialMediaList) {
            return;
        }

        clearElementChildren(detailSocialMediaList);

        var socialItems = Array.isArray(items) ? items : [];
        if (!socialItems.length) {
            detailSocialMediaList.classList.add('is-empty');
            detailSocialMediaList.innerHTML = '<tr><td class="detail-social-media-empty" colspan="2">-</td></tr>';
            return;
        }

        detailSocialMediaList.classList.remove('is-empty');

        socialItems.forEach(function (itemData) {
            var itemName = itemData && itemData.name ? String(itemData.name) : '';
            var itemLink = itemData && itemData.link ? String(itemData.link) : '';
            var info = resolveDetailSocialMediaInfo(itemLink, itemName);
            if (!info.href) {
                return;
            }

            var platformLink = document.createElement('a');
            platformLink.className = 'detail-social-media-item detail-social-media-item--' + info.platform;
            platformLink.href = info.href;
            platformLink.target = '_blank';
            platformLink.rel = 'noopener noreferrer';
            var iconSpan = document.createElement('span');
            iconSpan.className = 'detail-social-media-icon detail-social-media-icon--' + info.platform;
            iconSpan.setAttribute('aria-hidden', 'true');
            iconSpan.innerHTML = detailSocialMediaIconSvg(info.platform);
            platformLink.appendChild(iconSpan);

            var usernameSpan = document.createElement('span');
            usernameSpan.className = 'detail-social-media-username';
            usernameSpan.textContent = info.label;
            platformLink.appendChild(usernameSpan);
            var row = document.createElement('tr');
            var cell = document.createElement('td');
            cell.colSpan = 2;
            cell.appendChild(platformLink);
            row.appendChild(cell);
            detailSocialMediaList.appendChild(row);
        });

        if (!detailSocialMediaList.children.length) {
            detailSocialMediaList.classList.add('is-empty');
            detailSocialMediaList.innerHTML = '<tr><td class="detail-social-media-empty" colspan="2">-</td></tr>';
        }
    }

    if (detailSocialMediaList) {
        renderDetailSocialMediaList(parseDetailSocialMediaItems(firstMemberSocialMediaItems, ''));
    }

    function parseAddressParts(addressValue) {
        var parts = String(addressValue || '').split(',');
        var normalizedParts = parts.map(function (part) {
            return String(part || '').trim();
        });

        return {
            country: normalizedParts[0] || '',
            province: normalizedParts[1] || '',
            city: normalizedParts[2] || '',
            district: normalizedParts[3] || '',
            detail: normalizedParts.slice(4).join(', ').trim()
        };
    }

    function setDetailEditAddressOldValues(parsedAddress) {
        if (detailEditAddressCountryOld) {
            detailEditAddressCountryOld.value = parsedAddress.country || '';
        }
        if (detailEditAddressProvinceOld) {
            detailEditAddressProvinceOld.value = parsedAddress.province || '';
        }
        if (detailEditAddressCityOld) {
            detailEditAddressCityOld.value = parsedAddress.city || '';
        }
        if (detailEditAddressDistrictOld) {
            detailEditAddressDistrictOld.value = parsedAddress.district || '';
        }
        if (detailEditAddressDetailOld) {
            detailEditAddressDetailOld.value = parsedAddress.detail || '';
        }
    }

    function refreshDetailEditAddressCascade() {
        if (window.familyTreeRefreshDetailAddressCascade) {
            window.familyTreeRefreshDetailAddressCascade();
        }
    }

    function clearDetailPhotoPreviewObjectUrl() {
        if (detailPhotoPreviewObjectUrl) {
            URL.revokeObjectURL(detailPhotoPreviewObjectUrl);
            detailPhotoPreviewObjectUrl = '';
        }
    }

    function resetDetailPhotoPreview() {
        clearDetailPhotoPreviewObjectUrl();
        if (detailPhoto && detailPhotoOriginalSrc) {
            detailPhoto.src = detailPhotoOriginalSrc;
        }
        detailPhotoSelectedFile = null;
        if (detailEditPictureInput) {
            detailEditPictureInput.value = '';
        }
    }

    function syncDetailPhotoEditableState() {
        var isEditable = detailEditModeActive && !!detailEditPictureInput;
        if (detailPhotoWrap) {
            detailPhotoWrap.classList.toggle('is-editable', isEditable);
        }
    }

    function setDetailPhotoPreviewFromFile(file) {
        if (!detailPhoto || !file) {
            return;
        }

        clearDetailPhotoPreviewObjectUrl();
        detailPhotoPreviewObjectUrl = URL.createObjectURL(file);
        detailPhoto.src = detailPhotoPreviewObjectUrl;
    }

    function setDetailEditPictureInputFile(input, file) {
        if (!input) {
            return;
        }

        if (!file) {
            input.value = '';
            return;
        }

        if (typeof DataTransfer === 'undefined') {
            return;
        }

        var dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
    }

    function detailPhotoCropClampNumber(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function detailPhotoCropGetCanvasSize() {
        return detailPhotoCropCanvas ? detailPhotoCropCanvas.width : 0;
    }

    function detailPhotoCropClampFrame() {
        if (!detailPhotoCropCanvas) {
            return;
        }

        var canvasSize = detailPhotoCropGetCanvasSize();
        var minSize = 96;
        var maxSize = Math.max(minSize, canvasSize - 16);

        detailPhotoCropState.frameSize = detailPhotoCropClampNumber(detailPhotoCropState.frameSize || minSize, minSize, maxSize);
        detailPhotoCropState.frameX = detailPhotoCropClampNumber(detailPhotoCropState.frameX || 0, 0, canvasSize - detailPhotoCropState.frameSize);
        detailPhotoCropState.frameY = detailPhotoCropClampNumber(detailPhotoCropState.frameY || 0, 0, canvasSize - detailPhotoCropState.frameSize);
    }

    function detailPhotoCropRenderFrame() {
        if (!detailPhotoCropFrame) {
            return;
        }

        detailPhotoCropClampFrame();
        detailPhotoCropFrame.style.left = detailPhotoCropState.frameX + 'px';
        detailPhotoCropFrame.style.top = detailPhotoCropState.frameY + 'px';
        detailPhotoCropFrame.style.width = detailPhotoCropState.frameSize + 'px';
        detailPhotoCropFrame.style.height = detailPhotoCropState.frameSize + 'px';
    }

    function detailPhotoCropDrawCanvas() {
        if (!detailPhotoCropCanvas || !detailPhotoCropState.image) {
            return;
        }

        var ctx = detailPhotoCropCanvas.getContext('2d');
        var canvasSize = detailPhotoCropCanvas.width;
        var scaledWidth = detailPhotoCropState.image.width * detailPhotoCropState.scaleBase * detailPhotoCropState.zoom;
        var scaledHeight = detailPhotoCropState.image.height * detailPhotoCropState.scaleBase * detailPhotoCropState.zoom;
        var cropCenterX = detailPhotoCropState.frameX + (detailPhotoCropState.frameSize / 2);
        var cropCenterY = detailPhotoCropState.frameY + (detailPhotoCropState.frameSize / 2);
        var cropRadius = detailPhotoCropState.frameSize / 2;

        ctx.clearRect(0, 0, canvasSize, canvasSize);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvasSize, canvasSize);
        ctx.drawImage(detailPhotoCropState.image, detailPhotoCropState.offsetX, detailPhotoCropState.offsetY, scaledWidth, scaledHeight);

        ctx.save();
        ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
        ctx.beginPath();
        ctx.rect(0, 0, canvasSize, canvasSize);
        ctx.arc(cropCenterX, cropCenterY, cropRadius, 0, Math.PI * 2);
        ctx.fill('evenodd');
        ctx.restore();
    }

    function detailPhotoCropClampOffsets() {
        if (!detailPhotoCropCanvas || !detailPhotoCropState.image) {
            return;
        }

        var canvasSize = detailPhotoCropCanvas.width;
        var scaledWidth = detailPhotoCropState.image.width * detailPhotoCropState.scaleBase * detailPhotoCropState.zoom;
        var scaledHeight = detailPhotoCropState.image.height * detailPhotoCropState.scaleBase * detailPhotoCropState.zoom;

        if (scaledWidth <= canvasSize) {
            detailPhotoCropState.offsetX = (canvasSize - scaledWidth) / 2;
        } else {
            detailPhotoCropState.offsetX = detailPhotoCropClampNumber(detailPhotoCropState.offsetX, canvasSize - scaledWidth, 0);
        }

        if (scaledHeight <= canvasSize) {
            detailPhotoCropState.offsetY = (canvasSize - scaledHeight) / 2;
        } else {
            detailPhotoCropState.offsetY = detailPhotoCropClampNumber(detailPhotoCropState.offsetY, canvasSize - scaledHeight, 0);
        }
    }

    function detailPhotoCropRenderCanvas() {
        detailPhotoCropClampOffsets();
        detailPhotoCropDrawCanvas();
        detailPhotoCropRenderFrame();
    }

    function detailPhotoCropGetCanvasPoint(clientX, clientY) {
        var rect = detailPhotoCropCanvas.getBoundingClientRect();

        return {
            x: (clientX - rect.left) * (detailPhotoCropCanvas.width / rect.width),
            y: (clientY - rect.top) * (detailPhotoCropCanvas.height / rect.height)
        };
    }

    function detailPhotoCropGetTouchDistance(touchA, touchB) {
        var deltaX = touchA.clientX - touchB.clientX;
        var deltaY = touchA.clientY - touchB.clientY;
        return Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    }

    function detailPhotoCropApplyZoom(nextZoom, anchorX, anchorY) {
        if (!detailPhotoCropState.image) {
            return;
        }

        var minZoom = 0.5;
        var maxZoom = 4;
        var currentZoom = detailPhotoCropClampNumber(detailPhotoCropState.zoom || 1, minZoom, maxZoom);
        var clampedZoom = detailPhotoCropClampNumber(nextZoom, minZoom, maxZoom);

        if (clampedZoom === currentZoom) {
            return;
        }

        var zoomRatio = clampedZoom / currentZoom;
        detailPhotoCropState.offsetX = anchorX - ((anchorX - detailPhotoCropState.offsetX) * zoomRatio);
        detailPhotoCropState.offsetY = anchorY - ((anchorY - detailPhotoCropState.offsetY) * zoomRatio);
        detailPhotoCropState.zoom = clampedZoom;
        detailPhotoCropRenderCanvas();
    }

    function restoreDetailPhotoCropState() {
        if (detailPhotoCropState.previewSrc && detailPhoto) {
            detailPhoto.src = detailPhotoCropState.previewSrc;
        }

        setDetailEditPictureInputFile(detailPhotoCropState.input, detailPhotoSelectedFile);
    }

    function closeDetailPhotoCropModal(restoreState) {
        if (!detailPhotoCropModal) {
            return;
        }

        if (detailPhotoCropCloseTimer) {
            window.clearTimeout(detailPhotoCropCloseTimer);
        }

        detailPhotoCropRequestToken += 1;
        detailPhotoCropModal.classList.add('is-closing');
        detailPhotoCropModal.classList.remove('is-open');
        detailPhotoCropState.dragging = false;
        detailPhotoCropState.pinching = false;

        if (detailPhotoCropCanvas) {
            detailPhotoCropCanvas.classList.remove('is-dragging');
            detailPhotoCropCanvas.style.cursor = 'grab';
        }

        if (detailPhotoCropFrame) {
            detailPhotoCropFrame.style.left = '';
            detailPhotoCropFrame.style.top = '';
            detailPhotoCropFrame.style.width = '';
            detailPhotoCropFrame.style.height = '';
        }

        if (detailPhotoCropState.imageUrl) {
            URL.revokeObjectURL(detailPhotoCropState.imageUrl);
        }

        detailPhotoCropState.image = null;
        detailPhotoCropState.imageUrl = '';

        if (restoreState) {
            restoreDetailPhotoCropState();
        }

        detailPhotoCropState.input = null;
        detailPhotoCropState.previewSrc = '';
        detailPhotoCropState.file = null;
        detailPhotoCropState.scaleBase = 1;
        detailPhotoCropState.zoom = 1;
        detailPhotoCropState.offsetX = 0;
        detailPhotoCropState.offsetY = 0;
        detailPhotoCropState.dragStartX = 0;
        detailPhotoCropState.dragStartY = 0;
        detailPhotoCropState.dragOffsetX = 0;
        detailPhotoCropState.dragOffsetY = 0;
        detailPhotoCropState.pinchStartDistance = 0;
        detailPhotoCropState.pinchStartZoom = 1;
        detailPhotoCropState.pinchCenterX = 0;
        detailPhotoCropState.pinchCenterY = 0;
        detailPhotoCropState.frameX = 0;
        detailPhotoCropState.frameY = 0;
        detailPhotoCropState.frameSize = 0;
        detailPhotoCropCloseTimer = window.setTimeout(function () {
            detailPhotoCropModal.classList.add('hidden');
            detailPhotoCropModal.classList.remove('is-closing');
            detailPhotoCropCloseTimer = 0;
        }, 200);
    }

    function openDetailPhotoCropModal(input, file) {
        if (!detailPhotoCropModal || !detailPhotoCropCanvas || !file) {
            return;
        }

        if (detailPhotoCropCloseTimer) {
            window.clearTimeout(detailPhotoCropCloseTimer);
            detailPhotoCropCloseTimer = 0;
        }

        detailPhotoCropState.input = input;
        detailPhotoCropState.previewSrc = detailPhoto ? (detailPhoto.getAttribute('src') || '') : '';
        detailPhotoCropState.file = file;

        if (detailPhotoCropTitle) {
            detailPhotoCropTitle.textContent = 'Crop Profile Photo';
        }
        if (detailPhotoCropDescription) {
            detailPhotoCropDescription.textContent = '';
        }

        var requestToken = ++detailPhotoCropRequestToken;
        var imageUrl = URL.createObjectURL(file);
        detailPhotoCropState.image = new Image();
        detailPhotoCropState.imageUrl = imageUrl;
        detailPhotoCropState.image.onload = function () {
            if (requestToken !== detailPhotoCropRequestToken) {
                URL.revokeObjectURL(imageUrl);
                return;
            }

            var size = detailPhotoCropCanvas.width;
            detailPhotoCropState.scaleBase = Math.min(1, Math.min(size / detailPhotoCropState.image.width, size / detailPhotoCropState.image.height));
            detailPhotoCropState.zoom = 1;
            detailPhotoCropState.offsetX = (size - detailPhotoCropState.image.width * detailPhotoCropState.scaleBase) / 2;
            detailPhotoCropState.offsetY = (size - detailPhotoCropState.image.height * detailPhotoCropState.scaleBase) / 2;
            detailPhotoCropState.dragging = false;
            detailPhotoCropState.pinching = false;
            detailPhotoCropState.pinchStartDistance = 0;
            detailPhotoCropState.pinchStartZoom = 1;
            detailPhotoCropState.pinchCenterX = 0;
            detailPhotoCropState.pinchCenterY = 0;
            detailPhotoCropState.frameSize = detailPhotoCropClampNumber(Math.round(size * 0.68), 96, size - 16);
            detailPhotoCropState.frameX = Math.round((size - detailPhotoCropState.frameSize) / 2);
            detailPhotoCropState.frameY = Math.round((size - detailPhotoCropState.frameSize) / 2);
            detailPhotoCropRenderCanvas();
            detailPhotoCropModal.classList.remove('hidden', 'is-closing');
            detailPhotoCropModal.classList.add('is-open');
            detailPhotoCropCanvas.style.cursor = 'grab';
        };

        detailPhotoCropState.image.src = imageUrl;
    }

    function applyDetailPhotoCropModal() {
        if (!detailPhotoCropCanvas || !detailPhotoCropState.image || !detailPhotoCropState.input) {
            return;
        }

        var outputSize = 512;
        var exportCanvas = document.createElement('canvas');
        exportCanvas.width = outputSize;
        exportCanvas.height = outputSize;

        var ctx = exportCanvas.getContext('2d');
        var cropX = Math.round(detailPhotoCropState.frameX);
        var cropY = Math.round(detailPhotoCropState.frameY);
        var cropSize = Math.round(detailPhotoCropState.frameSize);

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, outputSize, outputSize);
        ctx.drawImage(detailPhotoCropCanvas, cropX, cropY, cropSize, cropSize, 0, 0, outputSize, outputSize);

        exportCanvas.toBlob(function (blob) {
            if (!blob) {
                closeDetailPhotoCropModal(false);
                return;
            }

            var baseName = detailPhotoCropState.file && detailPhotoCropState.file.name
                ? detailPhotoCropState.file.name.replace(/\.[^.]+$/, '')
                : 'cropped-photo';
            var croppedFile = new File([blob], baseName + '.png', {
                type: 'image/png'
            });

            detailPhotoSelectedFile = croppedFile;
            setDetailEditPictureInputFile(detailPhotoCropState.input, croppedFile);
            setDetailPhotoPreviewFromFile(croppedFile);
            closeDetailPhotoCropModal(false);
        }, 'image/png');
    }

    function updateMemberCardPhotoSrc(memberId, pictureUrl) {
        var normalizedPictureUrl = resolveDetailPhotoUrl(pictureUrl);
        var normalizedMemberId = normalizeEditableValue(memberId);
        if (normalizedMemberId === '' || normalizedPictureUrl === '') {
            return;
        }

        var allCards = Array.prototype.slice.call(document.querySelectorAll('.member-card[data-memberid]'));
        allCards.forEach(function (card) {
            if (String(card.getAttribute('data-memberid') || '') !== normalizedMemberId) {
                return;
            }

            card.setAttribute('data-photo', normalizedPictureUrl);
            var cardPhoto = card.querySelector('.member-photo');
            if (cardPhoto) {
                cardPhoto.src = normalizedPictureUrl;
            }
        });
    }

    function validateDetailEditPhotoFile(file) {
        if (!file) {
            return Promise.resolve({ valid: true });
        }

        if (typeof window.familyTreeValidateProfilePictureHasFace === 'function') {
            return window.familyTreeValidateProfilePictureHasFace(file);
        }

        return Promise.resolve({
            valid: false,
            message: 'Face verification is unavailable right now.'
        });
    }

    function showDetailEditPhotoError(message) {
        var errorMessage = message || 'Invalid photo. A face photo is required.';
        if (
            errorMessage === 'Profile picture must contain a clear human face.'
            || errorMessage === 'Face detection failed. Please use a clear face photo.'
            || errorMessage === 'Face verification is unavailable right now.'
        ) {
            errorMessage = 'Invalid photo. A face photo is required.';
        }
        if (typeof window.familyTreeOpenProfileFaceErrorModal === 'function') {
            window.familyTreeOpenProfileFaceErrorModal(errorMessage);
            return;
        }

        setDetailEditMessage(errorMessage, true);
    }

    function updateDetailPhotoFromResponse(pictureUrl) {
        var normalizedPictureUrl = resolveDetailPhotoUrl(pictureUrl);
        if (normalizedPictureUrl === '') {
            return;
        }

        clearDetailPhotoPreviewObjectUrl();
        detailPhotoOriginalSrc = normalizedPictureUrl;
        detailPhotoSelectedFile = null;
        if (detailPhoto) {
            detailPhoto.src = normalizedPictureUrl;
        }

        var sourceCard = getDetailEditSourceCard();
        if (sourceCard) {
            updateMemberCardPhotoSrc(sourceCard.getAttribute('data-memberid') || '', normalizedPictureUrl);
        }

        if (detailEditPictureInput) {
            detailEditPictureInput.value = '';
        }
    }

    function updateDetailEditChildParentingModeAvailability(hasPartner) {
        var canUseCurrentPartner = !!hasPartner;
        if (detailEditChildParentingModeWithCurrentPartnerOption) {
            detailEditChildParentingModeWithCurrentPartnerOption.disabled = !canUseCurrentPartner;
        }
        if (!canUseCurrentPartner && detailEditChildParentingMode && detailEditChildParentingMode.value === 'with_current_partner') {
            detailEditChildParentingMode.value = 'single_parent';
        }
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
            detailEditSettingsPanel.classList.toggle('hidden', !detailEditModeActive);
        }
        if (detailEditBtn) {
            detailEditBtn.textContent = detailEditModeActive ? 'Close' : 'Edit';
        }
        if (detailEditMessage && !detailEditModeActive) {
            setDetailEditMessage('', false);
            resetDetailPhotoPreview();
        }
        syncDetailPhotoEditableState();
        scheduleDetailEmailFit();
    }

    function syncDetailEditFormFromCard(card) {
        if (!detailEditForm || !card) {
            return;
        }

        var userId = card.getAttribute('data-userid') || '';
        var memberId = card.getAttribute('data-memberid') || '';

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
        if (detailEditDeadDate) {
            detailEditDeadDate.value = normalizeEditableValue(card.getAttribute('data-deaddate'));
        }
        if (detailEditGraveLocationUrl) {
            detailEditGraveLocationUrl.value = normalizeEditableValue(card.getAttribute('data-grave-location-url'));
        }
        if (detailEditAddressDetail) {
            var parsedAddress = parseAddressParts(card.getAttribute('data-address'));
            setDetailEditAddressOldValues(parsedAddress);
            detailEditAddressDetail.value = parsedAddress.detail;
        }
        if (detailEditLifeStatus) {
            var selectedLifeStatus = String(card.getAttribute('data-life-status-raw') || card.getAttribute('data-status') || 'alive').toLowerCase();
            detailEditLifeStatus.value = selectedLifeStatus === 'deceased' ? 'deceased' : 'alive';
        }
        var canUpdateLifeStatus = String(card.getAttribute('data-can-update-life-status') || '0') === '1';
        if (detailEditChildParentingMode) {
            var memberId = String(card.getAttribute('data-memberid') || '');
            var childParentingMode = String(card.getAttribute('data-child-parenting-mode') || familyTreeChildParentingModeMap[memberId] || '').toLowerCase();
            var hasPartner = String(card.getAttribute('data-has-partner') || '0') === '1';
            detailEditChildParentingMode.value = childParentingMode === 'with_current_partner'
                && hasPartner
                ? 'with_current_partner'
                : 'single_parent';
            updateDetailEditChildParentingModeAvailability(hasPartner);
        }

        refreshDetailEditAddressCascade();
        syncDetailPhotoEditableState();
    }

    function openDetailEditModeFromCurrentSelection() {
        var sourceCard = getDetailEditSourceCard();

        if (!sourceCard) {
            return;
        }

        if (detailEditBtn && detailEditBtn.classList.contains('hidden')) {
            return;
        }

        syncDetailEditFormFromCard(sourceCard);
        setDetailEditMode(!detailEditModeActive);
        if (detailEditModeActive && detailEditUsername) {
            window.setTimeout(function () {
                detailEditUsername.focus();
            }, 0);
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
            deaddate: detailEditDeadDate ? String(detailEditDeadDate.value || '').trim() : '',
            grave_location_url: detailEditGraveLocationUrl ? String(detailEditGraveLocationUrl.value || '').trim() : '',
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
        updateDetailValue(detailDeadDate, values.deaddate);
        renderDetailGraveLocation(detailGraveLocation, values.grave_location_url);
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
        card.setAttribute('data-deaddate', values.deaddate || '');
        card.setAttribute('data-grave-location-url', values.grave_location_url || '');
        card.setAttribute('data-child-parenting-mode', values.child_parenting_mode || '');

        var isDeceased = String(values.life_status || '').toLowerCase() === 'deceased';
        card.classList.toggle('is-deceased', isDeceased);

        if (card === currentSelectedMemberCard && detailCard) {
            detailCard.classList.toggle('is-deceased', isDeceased);
        }
    }

    function showMemberDetailFromCard(card) {
        if (!card) return;

        currentSelectedMemberCard = card;
        syncDetailCardDataFromCard(card);

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
        var socialMediaItems = [];
        try {
            socialMediaItems = JSON.parse(card.getAttribute('data-social-media-items') || '[]') || [];
        } catch (error) {
            socialMediaItems = [];
        }
        var job = card.getAttribute('data-job') || '-';
        var address = card.getAttribute('data-address') || '-';
        var education = card.getAttribute('data-education') || '-';
        var childParentingMode = String(card.getAttribute('data-child-parenting-mode') || '').toLowerCase();
        var graveLocationUrl = card.getAttribute('data-grave-location-url') || '';
        var deadDate = card.getAttribute('data-deaddate') || '';
        var photo = card.getAttribute('data-photo') || '';
        var userId = card.getAttribute('data-userid') || '';
        var lifeStatusRaw = (card.getAttribute('data-life-status-raw') || '').toLowerCase();
        var isMe = card.getAttribute('data-isme') === '1';
        var canDeleteChild = card.getAttribute('data-can-delete-child') === '1';
        var canUpdateLifeStatus = card.getAttribute('data-can-update-life-status') === '1';
        var canEditProfile = card.getAttribute('data-can-edit-profile') === '1';
        var canEditChildParentingMode = card.getAttribute('data-can-edit-child-parenting-mode') === '1';
        var canDeletePartner = card.getAttribute('data-can-delete-partner') === '1';
        var canDivorcePartner = card.getAttribute('data-can-divorce-partner') === '1';
        var canManageAllProfiles = currentRoleId === 1 || currentRoleId === 2;
        detailCanEditChildParentingMode = canEditChildParentingMode;

        if (detailPhoto) {
            detailPhoto.src = resolveDetailPhotoUrl(photo) || detailPhoto.src;
            detailPhoto.alt = name !== '-' ? name : 'Member';
            detailPhoto.setAttribute('data-isme', isMe ? '1' : '0');
        }
        detailPhotoOriginalSrc = photo || detailPhotoOriginalSrc || '';

        updateDetailValue(detailName, name);
        updateDetailValue(detailRole, role);
        updateDetailValue(detailUsername, username);
        updateDetailValue(detailGender, gender);
        updateDetailValue(detailAge, age);
        updateDetailValue(detailBirthdate, birthdate);
        updateDetailValue(detailBirthplace, birthplace);
        updateDetailValue(detailBloodType, bloodType);
        updateDetailValue(detailStatus, status);
        updateDetailValue(detailDeadDate, deadDate);
        renderDetailGraveLocation(detailGraveLocation, graveLocationUrl);
        updateDetailValue(detailMaritalStatus, maritalStatus);
        updateDetailValue(detailPhone, phone);
        updateDetailValue(detailEmail, email);
        renderDetailSocialMediaList(parseDetailSocialMediaItems(socialMediaItems, socialMedia));
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
            if (divorcePartnerForm) {
                divorcePartnerForm.classList.toggle('hidden', !canDivorcePartner);
                if (divorcePartnerMemberIdInput) {
                    divorcePartnerMemberIdInput.value = memberId;
                }
            }
        } else {
            if (deleteUserForm) {
                deleteUserForm.classList.add('hidden');
            }
            if (deleteChildForm) {
                deleteChildForm.classList.toggle('hidden', !canDeleteChild);
            }
            if (divorcePartnerForm) {
                divorcePartnerForm.classList.toggle('hidden', !canDivorcePartner);
                if (divorcePartnerMemberIdInput) {
                    divorcePartnerMemberIdInput.value = memberId;
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
        var canOpenDetailEdit = canEditProfile || canManageAllProfiles;
        if (detailEditBtn) {
            detailEditBtn.classList.toggle('hidden', !canOpenDetailEdit);
        }
        if (lifeStatusToggleBtn) {
            var nextLifeStatus = lifeStatusRaw === 'deceased' ? 'alive' : 'deceased';
            lifeStatusToggleBtn.classList.toggle('hidden', !canUpdateLifeStatus);
            lifeStatusToggleBtn.setAttribute('data-status', nextLifeStatus);
            lifeStatusToggleBtn.textContent = nextLifeStatus === 'deceased' ? 'Mark as Deceased' : 'Mark as Alive';
        }

        syncDetailEditFormFromCard(card);
        setDetailEditMode(detailEditModeActive && canOpenDetailEdit);

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

    var wikiPanelBtn = document.getElementById('wikiPanelBtn');
    if (wikiPanelBtn) {
        wikiPanelBtn.addEventListener('click', function () {
            var sourceCard = getDetailEditSourceCard();
            var memberId = '';

            if (sourceCard) {
                memberId = String(sourceCard.getAttribute('data-memberid') || '').trim();
            }

            if (memberId === '' && detailCard) {
                memberId = String(detailCard.getAttribute('data-memberid') || '').trim();
            }

            if (memberId !== '') {
                window.location.href = '/member/' + encodeURIComponent(memberId) + '/wiki';
                return;
            }

            window.location.href = '/wiki';
        });
    }

    if (detailEditBtn && detailEditForm) {
        detailEditBtn.addEventListener('click', openDetailEditModeFromCurrentSelection);
    }

    if (detailPhotoCameraBtn && detailEditPictureInput) {
        detailPhotoCameraBtn.addEventListener('click', function (event) {
            if (event && event.stopPropagation) {
                event.stopPropagation();
            }
            if (!detailEditModeActive) {
                return;
            }

            detailEditPictureInput.click();
        });
    }

    if (detailPhotoWrap && detailEditPictureInput) {
        detailPhotoWrap.addEventListener('click', function (event) {
            if (!detailEditModeActive) {
                return;
            }

            if (event.target && event.target.closest && event.target.closest('#detailPhotoCameraBtn')) {
                return;
            }

            detailEditPictureInput.click();
        });
    }

    if (detailEditPictureInput) {
        detailEditPictureInput.addEventListener('change', function () {
            var file = detailEditPictureInput.files && detailEditPictureInput.files[0]
                ? detailEditPictureInput.files[0]
                : null;

            if (!file) {
                resetDetailPhotoPreview();
                return;
            }

            openDetailPhotoCropModal(detailEditPictureInput, file);
        });
    }

    if (detailPhotoCropApplyBtn) {
        detailPhotoCropApplyBtn.addEventListener('click', applyDetailPhotoCropModal);
    }

    if (detailPhotoCropCancelBtn) {
        detailPhotoCropCancelBtn.addEventListener('click', function () {
            closeDetailPhotoCropModal(true);
        });
    }

    if (detailPhotoCropModal) {
        detailPhotoCropModal.addEventListener('click', function (event) {
            if (event.target && event.target.classList && event.target.classList.contains('photo-crop-backdrop')) {
                closeDetailPhotoCropModal(true);
            }
        });
    }

    if (detailPhotoCropCanvas) {
        detailPhotoCropCanvas.addEventListener('mousedown', function (event) {
            if (!detailPhotoCropState.image) {
                return;
            }
            detailPhotoCropState.dragging = true;
            detailPhotoCropState.dragStartX = event.clientX;
            detailPhotoCropState.dragStartY = event.clientY;
            detailPhotoCropState.dragOffsetX = detailPhotoCropState.offsetX;
            detailPhotoCropState.dragOffsetY = detailPhotoCropState.offsetY;
            detailPhotoCropCanvas.classList.add('is-dragging');
        });

        window.addEventListener('mousemove', function (event) {
            if (!detailPhotoCropState.dragging || !detailPhotoCropState.image) {
                return;
            }

            detailPhotoCropState.offsetX = detailPhotoCropState.dragOffsetX + (event.clientX - detailPhotoCropState.dragStartX);
            detailPhotoCropState.offsetY = detailPhotoCropState.dragOffsetY + (event.clientY - detailPhotoCropState.dragStartY);
            detailPhotoCropRenderCanvas();
        });

        window.addEventListener('mouseup', function () {
            if (!detailPhotoCropState.dragging) {
                return;
            }

            detailPhotoCropState.dragging = false;
            detailPhotoCropCanvas.classList.remove('is-dragging');
            detailPhotoCropCanvas.style.cursor = 'grab';
        });

        detailPhotoCropCanvas.addEventListener('touchstart', function (event) {
            if (!detailPhotoCropState.image || !event.touches || !event.touches[0]) {
                return;
            }

            if (event.touches.length >= 2) {
                var pinchPointA = detailPhotoCropGetCanvasPoint(event.touches[0].clientX, event.touches[0].clientY);
                var pinchPointB = detailPhotoCropGetCanvasPoint(event.touches[1].clientX, event.touches[1].clientY);
                detailPhotoCropState.dragging = false;
                detailPhotoCropCanvas.classList.remove('is-dragging');
                detailPhotoCropState.pinching = true;
                detailPhotoCropState.pinchStartDistance = detailPhotoCropGetTouchDistance(event.touches[0], event.touches[1]);
                detailPhotoCropState.pinchStartZoom = detailPhotoCropState.zoom || 1;
                detailPhotoCropState.pinchCenterX = (pinchPointA.x + pinchPointB.x) / 2;
                detailPhotoCropState.pinchCenterY = (pinchPointA.y + pinchPointB.y) / 2;
                event.preventDefault();
                return;
            }

            detailPhotoCropState.dragging = true;
            detailPhotoCropState.dragStartX = event.touches[0].clientX;
            detailPhotoCropState.dragStartY = event.touches[0].clientY;
            detailPhotoCropState.dragOffsetX = detailPhotoCropState.offsetX;
            detailPhotoCropState.dragOffsetY = detailPhotoCropState.offsetY;
            detailPhotoCropCanvas.classList.add('is-dragging');
            event.preventDefault();
        }, { passive: false });

        detailPhotoCropCanvas.addEventListener('touchmove', function (event) {
            if (!detailPhotoCropState.image || !event.touches || !event.touches[0]) {
                return;
            }

            if (detailPhotoCropState.pinching && event.touches.length >= 2) {
                var pinchMovePointA = detailPhotoCropGetCanvasPoint(event.touches[0].clientX, event.touches[0].clientY);
                var pinchMovePointB = detailPhotoCropGetCanvasPoint(event.touches[1].clientX, event.touches[1].clientY);
                var currentDistance = detailPhotoCropGetTouchDistance(event.touches[0], event.touches[1]);
                var pinchCenterX = (pinchMovePointA.x + pinchMovePointB.x) / 2;
                var pinchCenterY = (pinchMovePointA.y + pinchMovePointB.y) / 2;
                var distanceRatio = detailPhotoCropState.pinchStartDistance > 0
                    ? (currentDistance / detailPhotoCropState.pinchStartDistance)
                    : 1;
                var nextZoom = detailPhotoCropState.pinchStartZoom * Math.pow(distanceRatio, 1.12);

                detailPhotoCropState.pinchCenterX = pinchCenterX;
                detailPhotoCropState.pinchCenterY = pinchCenterY;
                detailPhotoCropApplyZoom(nextZoom, pinchCenterX, pinchCenterY);
                event.preventDefault();
                return;
            }

            if (!detailPhotoCropState.dragging) {
                return;
            }

            detailPhotoCropState.offsetX = detailPhotoCropState.dragOffsetX + (event.touches[0].clientX - detailPhotoCropState.dragStartX);
            detailPhotoCropState.offsetY = detailPhotoCropState.dragOffsetY + (event.touches[0].clientY - detailPhotoCropState.dragStartY);
            detailPhotoCropRenderCanvas();
            event.preventDefault();
        }, { passive: false });

        detailPhotoCropCanvas.addEventListener('touchend', function () {
            detailPhotoCropState.dragging = false;
            detailPhotoCropState.pinching = false;
            detailPhotoCropCanvas.classList.remove('is-dragging');
        });

        detailPhotoCropCanvas.addEventListener('touchcancel', function () {
            detailPhotoCropState.dragging = false;
            detailPhotoCropState.pinching = false;
            detailPhotoCropCanvas.classList.remove('is-dragging');
        });

        detailPhotoCropCanvas.addEventListener('wheel', function (event) {
            if (!detailPhotoCropState.image) {
                return;
            }

            if (!event.ctrlKey && !event.metaKey) {
                return;
            }

            var pointerPoint = detailPhotoCropGetCanvasPoint(event.clientX, event.clientY);
            var wheelZoomFactor = Math.exp(-event.deltaY * 0.0022);
            detailPhotoCropApplyZoom((detailPhotoCropState.zoom || 1) * wheelZoomFactor, pointerPoint.x, pointerPoint.y);
            event.preventDefault();
        }, { passive: false });
    }

    if (detailEditCancelBtn && detailEditForm) {
        detailEditCancelBtn.addEventListener('click', function () {
            var sourceCard = getDetailEditSourceCard();
            resetDetailPhotoPreview();
            setDetailEditMode(false);
            if (sourceCard) {
                showMemberDetailFromCard(sourceCard);
            }
        });
    }

    if (detailEditForm) {
        detailEditForm.addEventListener('submit', function (event) {
            event.preventDefault();

            var sourceCard = getDetailEditSourceCard();
            if (!sourceCard) {
                return;
            }

            var actionUrl = detailEditForm.getAttribute('action') || '';
            if (actionUrl === '') {
                setDetailEditMessage('Update endpoint is missing.', true);
                return;
            }

            var selectedPictureFile = detailEditPictureInput && detailEditPictureInput.files && detailEditPictureInput.files[0]
                ? detailEditPictureInput.files[0]
                : null;

            validateDetailEditPhotoFile(selectedPictureFile)
            .then(function (result) {
                if (!result || !result.valid) {
                    throw new Error(result && result.message
                        ? result.message
                        : 'Invalid photo. A face photo is required.');
                }

                if (detailEditSaveBtn) {
                    detailEditSaveBtn.disabled = true;
                    detailEditSaveBtn.textContent = 'Saving...';
                }
                setDetailEditMessage('Saving changes...', false);

                var payload = new FormData(detailEditForm);
                return fetch(actionUrl, {
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
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.message) {
                    var values = readDetailEditFormValues();
                    syncCardDataFromForm(sourceCard, values);
                    syncDetailCardDataFromCard(sourceCard);
                    applyDetailValuesFromForm(values);
                    if (result.data.family_member && result.data.family_member.picture) {
                        updateDetailPhotoFromResponse(result.data.family_member.picture);
                    } else {
                        detailPhotoOriginalSrc = detailPhoto && detailPhoto.src ? detailPhoto.src : detailPhotoOriginalSrc;
                    }
                    if (detailEditSaveBtn) {
                        detailEditSaveBtn.disabled = false;
                        detailEditSaveBtn.textContent = 'Save Changes';
                    }

                    openDetailEditResultModal(
                        'Profile updated successfully!',
                        '',
                        false,
                        function () {
                            resetDetailPhotoPreview();
                            setDetailEditMode(false);
                            if (sourceCard) {
                                showMemberDetailFromCard(sourceCard);
                            }
                        }
                    );

                    return;
                }

                var errorMessage = 'Failed to update member details.';
                if (result.data && result.data.errors) {
                    errorMessage = getFirstValidationErrorMessage(result.data.errors) || getValidationErrorSummary(result.data.errors) || errorMessage;
                }
                if (errorMessage === 'Failed to update member details.' && result.data && result.data.message) {
                    errorMessage = result.data.message;
                }

                if (detailEditSaveBtn) {
                    detailEditSaveBtn.disabled = false;
                    detailEditSaveBtn.textContent = 'Save Changes';
                }

                openDetailEditResultModal(
                    'Update failed',
                    errorMessage,
                    true
                );
            })
            .catch(function (error) {
                console.error('Error:', error);
                if (detailEditSaveBtn) {
                    detailEditSaveBtn.disabled = false;
                    detailEditSaveBtn.textContent = 'Save Changes';
                }

                var validationMessage = error && error.message
                    ? error.message
                    : 'An error occurred while updating member details.';
                if (
                    validationMessage === 'Invalid photo. A face photo is required.'
                    || validationMessage === 'Profile picture must contain a clear human face.'
                    || validationMessage === 'Face detection failed. Please use a clear face photo.'
                    || validationMessage === 'Face verification is unavailable right now.'
                ) {
                    if (detailEditPictureInput) {
                        detailEditPictureInput.value = '';
                    }
                    resetDetailPhotoPreview();
                    showDetailEditPhotoError(validationMessage);
                    return;
                }

                openDetailEditResultModal(
                    'Update failed',
                    validationMessage,
                    true
                );
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
        var treeAutoCenterFlagKey = 'family_tree_auto_center_after_toggle';

        var getInitialTreeFocusCard = function () {
            return treeScrollArea.querySelector('.member-card.active')
                || treeScrollArea.querySelector('.member-card[data-memberid]')
                || null;
        };

        var recenterTreeViewport = function () {
            var areaWidth = treeScrollArea.clientWidth;
            var areaHeight = treeScrollArea.clientHeight;
            var focusCard = treeScrollArea.querySelector('.member-card.active')
                || treeScrollArea.querySelector('.member-card[data-memberid]')
                || null;

            if (areaWidth <= 0 || areaHeight <= 0) {
                return;
            }

            if (!focusCard) {
                treeScrollArea.scrollLeft = Math.max(0, Math.round((treeScrollArea.scrollWidth - areaWidth) / 2));
                treeScrollArea.scrollTop = Math.max(0, Math.round((treeScrollArea.scrollHeight - areaHeight) / 2));
                return;
            }

            var areaRect = treeScrollArea.getBoundingClientRect();
            var cardRect = focusCard.getBoundingClientRect();
            var nextScrollLeft = treeScrollArea.scrollLeft + (cardRect.left + (cardRect.width / 2)) - (areaRect.left + (areaWidth / 2));
            var nextScrollTop = treeScrollArea.scrollTop + (cardRect.top + (cardRect.height / 2)) - (areaRect.top + (areaHeight / 2));

            treeScrollArea.scrollLeft = Math.max(0, Math.round(nextScrollLeft));
            treeScrollArea.scrollTop = Math.max(0, Math.round(nextScrollTop));
            treeScrollArea.classList.add('is-tree-ready');
        };

        var requestTreeRecentering = function () {
            treeInitialCenterApplied = false;
            treeCenterRetryCount = 0;
            scheduleTreeCenterScroll();
            window.setTimeout(scheduleTreeCenterScroll, 80);
            window.setTimeout(scheduleTreeCenterScroll, 180);
            window.setTimeout(scheduleTreeCenterScroll, 320);
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

            recenterTreeViewport();
            treeInitialCenterApplied = true;
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
            requestTreeRecentering();
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

        if (centerTreeBtn) {
            centerTreeBtn.addEventListener('click', function () {
                recenterTreeViewport();
            });
        }

        document.addEventListener('click', function (event) {
            var toggleButton = event.target.closest('.tree-expand-toggle[data-tree-toggle-url]');
            if (!toggleButton) {
                return;
            }

            var toggleUrl = String(toggleButton.getAttribute('data-tree-toggle-url') || '').trim();
            if (toggleUrl === '') {
                return;
            }

            try {
                window.sessionStorage.setItem(treeAutoCenterFlagKey, '1');
            } catch (error) {
                // Ignore storage failures and continue navigation.
            }

            if (treeScrollArea) {
                treeScrollArea.classList.add('is-tree-ready');
            }

            window.location.href = toggleUrl;
        });

        var shouldAutoCenterAfterToggle = false;
        try {
            shouldAutoCenterAfterToggle = window.sessionStorage.getItem(treeAutoCenterFlagKey) === '1';
            if (shouldAutoCenterAfterToggle) {
                window.sessionStorage.removeItem(treeAutoCenterFlagKey);
            }
        } catch (error) {
            shouldAutoCenterAfterToggle = false;
        }

        if (shouldAutoCenterAfterToggle) {
            requestTreeRecentering();
        }

        window.addEventListener('pageshow', function () {
            if (!treeScrollArea) {
                return;
            }

            treeScrollArea.classList.add('is-tree-ready');
            requestTreeRecentering();
        });
    }
});
</script>
@endsection
