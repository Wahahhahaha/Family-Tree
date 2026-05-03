@extends('layouts.app')

@section('title', __('account.title'))
@section('body-class', 'page-account')

@section('styles')
<style>
body.page-account main,
body.page-account .wrapper {
    width: 100% !important;
    max-width: none !important;
    box-sizing: border-box;
}

body.page-account .wrapper {
    margin: 0;
    padding: 30px 15px 48px;
}

body.page-account .account-grid {
    width: 100%;
    max-width: none !important;
    margin-top: 18px;
}

body.page-account .account-card {
    width: 100%;
    max-width: none !important;
    box-sizing: border-box;
    margin-left: 0;
    margin-right: 0;
}

body.page-account .account-edit-card,
body.page-account .account-password-card {
    width: 100%;
    max-width: none !important;
}

body.page-account .account-password-form {
    width: 100%;
    max-width: none !important;
}

@media (max-width: 960px) {
    body.page-account .account-password-form {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')

    
    <div class="wrapper" style="width:100%;max-width:none;margin:0;padding:30px 15px 48px;box-sizing:border-box;">
    <?php $hideAdminProfileCard = (int) (session('authenticated_user.levelid') ?? 0) === 2; ?>

    <?php if ($canEditAdminProfile && !$hideAdminProfileCard): ?>
        <div class="account-grid" style="width:100%;max-width:none;margin-top:18px;">
            <section class="account-card account-edit-card" style="width:100%;max-width:none;box-sizing:border-box;">
                <h2><?php echo e(__('account.profile')); ?></h2>
                <p><?php echo e(__('account.update_identity_details')); ?></p>

                <div id="accountAdminAjaxAlert" class="hidden"></div>

                <?php
                    $adminCurrentEmailDisplay = trim((string) ($currentEmployerProfile->email ?? ''));
                    $adminCurrentEmail = strtolower($adminCurrentEmailDisplay);
                    $adminPendingEmailDisplay = trim((string) ($currentEmployerProfile->pending_email ?? ''));
                    $adminPendingEmail = strtolower($adminPendingEmailDisplay);
                    $adminHasPendingEmailChange = $adminPendingEmail !== '';
                    $adminCurrentPhoneDisplay = trim((string) ($currentEmployerProfile->phonenumber ?? ''));
                    $adminPendingPhoneDisplay = trim((string) ($currentEmployerProfile->pending_phonenumber ?? ''));
                    $adminHasPendingPhoneChange = $adminPendingPhoneDisplay !== '';
                ?>

                <form id="adminProfileForm" method="POST" action="/account/profile" class="settings-form account-form">
                    <?php echo csrf_field(); ?>

                    <div class="settings-field">
                        <label for="accountUsernameReadonly"><?php echo e(__('account.username')); ?></label>
                        <input
                            id="accountUsernameReadonly"
                            type="text"
                            value="<?php echo e(session('authenticated_user.username')); ?>"
                            readonly
                            disabled
                        >
                        <small><?php echo e(__('account.username_cannot_be_changed')); ?></small>
                    </div>

                    <div class="settings-field">
                        <label for="accountAdminName"><?php echo e(__('account.name')); ?></label>
                        <input
                            id="accountAdminName"
                            type="text"
                            name="name"
                            value="<?php echo e(old('name', $currentEmployerProfile->name ?? '')); ?>"
                            required
                        >
                    </div>

                    <div class="settings-field">
                        <label for="accountAdminEmail"><?php echo e(__('account.email')); ?></label>
                        <div id="adminEmailInputWrap" class="<?php echo e($adminHasPendingEmailChange ? 'hidden' : ''); ?>">
                            <input
                                id="accountAdminEmail"
                                type="email"
                                name="email"
                                value="<?php echo e(old('email', $adminCurrentEmailDisplay)); ?>"
                                required
                                data-current-email="<?php echo e($adminCurrentEmail); ?>"
                                data-pending-email="<?php echo e($adminPendingEmail); ?>"
                            >
                        </div>
                        <?php if ($adminHasPendingEmailChange): ?>
                            <div id="adminEmailPendingState" class="email-pending-state">
                                <span><?php echo e(__('account.change_pending_open_link_sent_to', ['value' => $adminPendingEmailDisplay])); ?></span>
                                <button id="adminEmailChangeCancelBtn" type="button" class="email-cancel-btn"><?php echo e(__('account.cancel_email_change')); ?></button>
                            </div>
                        <?php else: ?>
                            <div id="adminEmailPendingState" class="email-pending-state hidden"></div>
                        <?php endif; ?>
                    </div>

                    <div class="settings-field">
                        <label for="accountAdminPhone"><?php echo e(__('account.phone_number')); ?></label>
                        <div id="adminPhoneInputWrap" class="<?php echo e($adminHasPendingPhoneChange ? 'hidden' : ''); ?>">
                            <input
                                id="accountAdminPhone"
                                type="text"
                                name="phonenumber"
                                value="<?php echo e(old('phonenumber', $adminCurrentPhoneDisplay)); ?>"
                                required
                                data-current-phone="<?php echo e($adminCurrentPhoneDisplay); ?>"
                                data-pending-phone="<?php echo e($adminPendingPhoneDisplay); ?>"
                            >
                        </div>
                        <?php if ($adminHasPendingPhoneChange): ?>
                            <div id="adminPhonePendingState" class="email-pending-state">
                                <span><?php echo e(__('account.change_pending_enter_otp_sent_to', ['value' => $adminPendingPhoneDisplay])); ?></span>
                                <a id="adminPhoneChangeVerifyLink" href="#" class="email-cancel-btn"><?php echo e(__('account.enter_otp_code')); ?></a>
                                <button id="adminPhoneChangeCancelBtn" type="button" class="email-cancel-btn"><?php echo e(__('account.cancel_phone_change')); ?></button>
                            </div>
                        <?php else: ?>
                            <div id="adminPhonePendingState" class="email-pending-state hidden"></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary"><?php echo e(__('account.save_profile')); ?></button>
                </form>

                <div id="adminEmailChangeFeedbackModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="adminEmailChangeModalTitle">
                    <div class="message-modal-backdrop"></div>
                    <div class="message-modal-card email-change-modal-card">
                        <div class="email-change-modal-icon" aria-hidden="true">@</div>
                        <h4 id="adminEmailChangeModalTitle"><?php echo e(__('account.email_change')); ?></h4>
                        <div class="message-modal-body">
                            <p id="adminEmailChangeModalText" class="message-modal-text"></p>
                        </div>
                        <div class="email-change-modal-actions">
                            <button id="adminEmailChangeModalOkBtn" type="button" class="btn btn-primary email-change-modal-ok-btn"><?php echo e(__('account.ok')); ?></button>
                        </div>
                    </div>
                </div>

                <div id="adminPhoneChangeFeedbackModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="adminPhoneChangeModalTitle">
                    <div class="message-modal-backdrop"></div>
                    <div class="message-modal-card email-change-modal-card">
                        <div class="email-change-modal-icon" aria-hidden="true">#</div>
                        <h4 id="adminPhoneChangeModalTitle"><?php echo e(__('account.phone_number_change')); ?></h4>
                        <div class="message-modal-body">
                            <p id="adminPhoneChangeModalText" class="message-modal-text"></p>
                        </div>
                        <div class="email-change-modal-actions">
                            <button id="adminPhoneChangeModalOkBtn" type="button" class="btn btn-primary email-change-modal-ok-btn"><?php echo e(__('account.ok')); ?></button>
                        </div>
                    </div>
                </div>

                <div id="adminPhoneOtpModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="adminPhoneOtpModalTitle">
                    <div class="message-modal-backdrop"></div>
                    <div class="message-modal-card email-change-modal-card phone-otp-modal-card">
                        <div class="email-change-modal-icon" aria-hidden="true">6</div>
                        <h4 id="adminPhoneOtpModalTitle"><?php echo e(__('account.verify_phone_number')); ?></h4>
                        <div class="message-modal-body phone-otp-modal-body">
                            <p id="adminPhoneOtpModalDescription" class="message-modal-text is-success"><?php echo e(__('account.enter_6_digit_otp_sent_to_whatsapp')); ?></p>
                            <div class="settings-field phone-otp-field">
                                <label for="adminPhoneOtpInput"><?php echo e(__('account.otp_code')); ?></label>
                                <input id="adminPhoneOtpInput" type="text" inputmode="numeric" pattern="\d{6}" minlength="6" maxlength="6" placeholder="<?php echo e(__('account.enter_6_digit_otp')); ?>" autocomplete="one-time-code">
                            </div>
                            <p id="adminPhoneOtpErrorText" class="message-modal-text is-error hidden"></p>
                        </div>
                        <div class="email-change-modal-actions phone-otp-modal-actions">
                            <button id="adminPhoneOtpCancelBtn" type="button" class="btn btn-ghost"><?php echo e(__('account.cancel')); ?></button>
                            <button id="adminPhoneOtpVerifyBtn" type="button" class="btn btn-primary"><?php echo e(__('account.verify_otp')); ?></button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    <?php endif; ?>

    <?php if ($canEditOwnProfile): ?>
        <?php
            $isEditingOwnFamilyProfile = (bool) ($isEditingOwnFamilyProfile ?? true);
            $canEditMinorContactFields = (bool) ($canEditMinorContactFields ?? $isEditingOwnFamilyProfile);
            $profileEditTargetMemberId = (int) ($profileEditTargetMemberId ?? (int) ($currentFamilyProfile->memberid ?? 0));
            $accountFamilyUsername = (string) ($accountFamilyUsername ?? session('authenticated_user.username'));
        ?>
        <section class="account-card account-edit-card account-family-profile-card" style="width:100%;max-width:none;box-sizing:border-box;">
            <h2><?php echo e(__('account.profile')); ?></h2>
            <?php if ($isEditingOwnFamilyProfile): ?>
                <p><?php echo e(__('account.manage_profile_details')); ?></p>
            <?php else: ?>
                <p><?php echo e(__('account.editing_child_profile')); ?></p>
            <?php endif; ?>

            <div id="profileAjaxAlert" class="hidden"></div>

            <div class="account-photo-editor">
                <div id="detailPhotoWrap" class="detail-photo-wrap is-editable account-detail-photo-wrap">
                    <img
                        id="detailPhoto"
                        class="detail-photo is-editable"
                        src="<?php echo e($currentFamilyProfile->picture ?? '/images/avatar-male.svg'); ?>"
                        alt="<?php echo e($currentFamilyProfile->name ?? 'My Profile'); ?>"
                        data-isme="1"
                    >
                    <span class="detail-photo-overlay" aria-hidden="true">
                        <span class="detail-photo-icon"></span>
                    </span>
                </div>
                <small id="detailPhotoHint"><?php echo e(__('account.click_photo_to_choose_new_profile_picture')); ?></small>
            </div>

            <?php
                $familyCurrentEmailDisplay = trim((string) ($currentFamilyProfile->email ?? ''));
                $familyCurrentEmail = strtolower($familyCurrentEmailDisplay);
                $familyPendingEmailDisplay = trim((string) ($currentFamilyProfile->pending_email ?? ''));
                $familyPendingEmail = strtolower($familyPendingEmailDisplay);
                $hasPendingEmailChange = $familyPendingEmail !== '';
                $familyCurrentPhoneDisplay = trim((string) ($currentFamilyProfile->phonenumber ?? ''));
                $familyPendingPhoneDisplay = trim((string) ($currentFamilyProfile->pending_phonenumber ?? ''));
                $hasPendingPhoneChange = $familyPendingPhoneDisplay !== '';
                if (!$isEditingOwnFamilyProfile) {
                    $familyPendingEmailDisplay = '';
                    $familyPendingEmail = '';
                    $hasPendingEmailChange = false;
                    $familyPendingPhoneDisplay = '';
                    $hasPendingPhoneChange = false;
                }
                $socialMediaOptionRows = [];
                $formatSocialMediaOptionLabel = function (string $rawName): string {
                    $normalizedName = trim($rawName);
                    if ($normalizedName === '') {
                        return '';
                    }

                    $lowerName = strtolower($normalizedName);
                    $labelMap = [
                        'youtube' => 'YouTube',
                        'linkedin' => 'LinkedIn',
                        'tiktok' => 'TikTok',
                        'wechat' => 'WeChat',
                        'thread' => 'Threads',
                        'threads' => 'Threads',
                        'x' => 'X',
                    ];

                    if (isset($labelMap[$lowerName])) {
                        return $labelMap[$lowerName];
                    }

                    return ucwords($lowerName);
                };
                if (!empty($socialMediaOptions) && count($socialMediaOptions) > 0) {
                    foreach ($socialMediaOptions as $socialMediaOption) {
                        $socialMediaOptionId = (int) ($socialMediaOption->socialid ?? 0);
                        $socialMediaOptionName = trim((string) ($socialMediaOption->socialname ?? ''));
                        if ($socialMediaOptionId <= 0 || $socialMediaOptionName === '') {
                            continue;
                        }

                        $socialMediaOptionRows[] = [
                            'id' => $socialMediaOptionId,
                            'name' => $socialMediaOptionName,
                            'display_name' => $formatSocialMediaOptionLabel($socialMediaOptionName),
                            'icon' => trim((string) ($socialMediaOption->socialicon ?? '')),
                        ];
                    }
                }
                $socialMediaOptionRowsJson = json_encode(
                    $socialMediaOptionRows,
                    JSON_UNESCAPED_UNICODE
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT
                );
                $socialMediaOptionRowsJson = is_string($socialMediaOptionRowsJson) ? $socialMediaOptionRowsJson : '[]';

                $selectedSocialMediaLinksSet = [];
                foreach (($selectedSocialMediaLinks ?? []) as $socialIdKey => $socialLinkValue) {
                    $socialIdKey = (int) $socialIdKey;
                    if ($socialIdKey <= 0) {
                        continue;
                    }

                    $selectedSocialMediaLinksSet[$socialIdKey] = trim((string) $socialLinkValue);
                }

                $socialRowsFromOldIds = old('social_row_ids');
                $socialRowsFromOldLinks = old('social_row_links');
                $maxSocialMediaPerMember = max(1, (int) ($maxSocialMediaPerMember ?? 3));
                $socialRowsInitial = [];
                if (is_array($socialRowsFromOldIds) || is_array($socialRowsFromOldLinks)) {
                    $socialRowsFromOldIds = is_array($socialRowsFromOldIds) ? array_values($socialRowsFromOldIds) : [];
                    $socialRowsFromOldLinks = is_array($socialRowsFromOldLinks) ? array_values($socialRowsFromOldLinks) : [];
                    $socialRowsFromOldCount = max(count($socialRowsFromOldIds), count($socialRowsFromOldLinks));

                    for ($socialRowIndex = 0; $socialRowIndex < $socialRowsFromOldCount; $socialRowIndex++) {
                        $socialRowId = (int) ($socialRowsFromOldIds[$socialRowIndex] ?? 0);
                        $socialRowLink = trim((string) ($socialRowsFromOldLinks[$socialRowIndex] ?? ''));

                        if ($socialRowId <= 0 && $socialRowLink === '') {
                            continue;
                        }

                        $socialRowsInitial[] = [
                            'socialid' => $socialRowId,
                            'link' => $socialRowLink,
                        ];
                    }
                } else {
                    foreach ((array) ($selectedSocialMediaIds ?? []) as $selectedSocialId) {
                        $selectedSocialId = (int) $selectedSocialId;
                        if ($selectedSocialId <= 0) {
                            continue;
                        }

                        $socialRowsInitial[] = [
                            'socialid' => $selectedSocialId,
                            'link' => (string) ($selectedSocialMediaLinksSet[$selectedSocialId] ?? ''),
                        ];
                    }
                }

                $socialRowIdErrorsByIndex = [];
                $socialRowLinkErrorsByIndex = [];
                $legacySocialLinkErrorsById = [];
                $socialRowsGlobalError = trim((string) $errors->first('social_row_ids'));
                foreach ((array) $errors->getMessages() as $errorKey => $errorMessages) {
                    $firstErrorMessage = '';
                    if (is_array($errorMessages) && count($errorMessages) > 0) {
                        $firstErrorMessage = trim((string) $errorMessages[0]);
                    }

                    if ($firstErrorMessage === '') {
                        continue;
                    }

                    if (preg_match('/^social_row_ids\.(\d+)$/', (string) $errorKey, $matches) === 1) {
                        $socialRowIdErrorsByIndex[(int) ($matches[1] ?? 0)] = $firstErrorMessage;
                        continue;
                    }

                    if (preg_match('/^social_row_links\.(\d+)$/', (string) $errorKey, $matches) === 1) {
                        $socialRowLinkErrorsByIndex[(int) ($matches[1] ?? 0)] = $firstErrorMessage;
                        continue;
                    }

                    if (preg_match('/^social_links\.(\d+)$/', (string) $errorKey, $matches) === 1) {
                        $legacySocialLinkErrorsById[(int) ($matches[1] ?? 0)] = $firstErrorMessage;
                    }
                }
            ?>

            <form id="profileForm" method="POST" action="/family/profile" class="settings-form account-form account-form-grid" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="/account">
                <input type="hidden" name="memberid" value="<?php echo e($profileEditTargetMemberId); ?>">
                <input type="hidden" name="is_editing_own_profile" value="<?php echo e($isEditingOwnFamilyProfile ? '1' : '0'); ?>">
                <input id="profilePictureFaceVerified" type="hidden" name="picture_face_verified" value="0">
                <input id="profilePictureInput" type="file" name="picture" accept="image/*" class="hidden">

                <div class="settings-field">
                    <label for="accountFamilyUsername"><?php echo e(__('account.username')); ?></label>
                    <input id="accountFamilyUsername" type="text" value="<?php echo e($accountFamilyUsername); ?>" disabled>
                    <small><?php echo e(__('account.username_cannot_be_edited')); ?></small>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyName"><?php echo e(__('account.name')); ?></label>
                    <input id="accountFamilyName" type="text" name="name" value="<?php echo e(old('name', $currentFamilyProfile->name ?? '')); ?>" placeholder="<?php echo e(__('account.enter_full_name')); ?>">
                </div>

                <div class="settings-field">
                    <label for="accountFamilyEmail"><?php echo e(__('account.email')); ?></label>
                    <div id="emailInputWrap" class="<?php echo e($hasPendingEmailChange ? 'hidden' : ''); ?>">
                        <input
                            id="accountFamilyEmail"
                            type="email"
                            name="email"
                            value="<?php echo e(old('email', $familyCurrentEmailDisplay)); ?>"
                            placeholder="<?php echo e(__('account.enter_email')); ?>"
                            <?php echo e($canEditMinorContactFields ? '' : 'readonly'); ?>
                            data-current-email="<?php echo e($familyCurrentEmail); ?>"
                            data-pending-email="<?php echo e($familyPendingEmail); ?>"
                        >
                    </div>
                    <?php if ($hasPendingEmailChange): ?>
                        <div id="emailPendingState" class="email-pending-state">
                            <span><?php echo e(__('account.change_pending_open_link_sent_to', ['value' => $familyPendingEmailDisplay])); ?></span>
                            <button id="emailChangeCancelBtn" type="button" class="email-cancel-btn"><?php echo e(__('account.cancel_email_change')); ?></button>
                        </div>
                    <?php else: ?>
                        <div id="emailPendingState" class="email-pending-state hidden"></div>
                    <?php endif; ?>
                    <?php if (!$canEditMinorContactFields): ?>
                        <small><?php echo e(__('account.email_can_only_be_changed_by_owner')); ?></small>
                    <?php endif; ?>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyPhone"><?php echo e(__('account.phone_number')); ?></label>
                    <div id="phoneInputWrap" class="<?php echo e($hasPendingPhoneChange ? 'hidden' : ''); ?>">
                        <input
                            id="accountFamilyPhone"
                            type="text"
                            name="phonenumber"
                            value="<?php echo e(old('phonenumber', $familyCurrentPhoneDisplay)); ?>"
                            placeholder="<?php echo e(__('account.enter_phone_number')); ?>"
                            <?php echo e($canEditMinorContactFields ? '' : 'readonly'); ?>
                            data-current-phone="<?php echo e($familyCurrentPhoneDisplay); ?>"
                            data-pending-phone="<?php echo e($familyPendingPhoneDisplay); ?>"
                        >
                    </div>
                    <?php if ($hasPendingPhoneChange): ?>
                        <div id="phonePendingState" class="email-pending-state">
                            <span><?php echo e(__('account.change_pending_enter_otp_sent_to', ['value' => $familyPendingPhoneDisplay])); ?></span>
                            <a id="phoneChangeVerifyLink" href="#" class="email-cancel-btn"><?php echo e(__('account.enter_otp_code')); ?></a>
                            <button id="phoneChangeCancelBtn" type="button" class="email-cancel-btn"><?php echo e(__('account.cancel_phone_change')); ?></button>
                        </div>
                    <?php else: ?>
                        <div id="phonePendingState" class="email-pending-state hidden"></div>
                    <?php endif; ?>
                    <?php if (!$canEditMinorContactFields): ?>
                        <small><?php echo e(__('account.phone_can_only_be_changed_by_owner')); ?></small>
                    <?php endif; ?>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyGender"><?php echo e(__('account.gender')); ?></label>
                    <select id="accountFamilyGender" disabled>
                        <option value="">-</option>
                        <option value="male" <?php echo e(old('gender', $currentFamilyProfile->gender ?? '') === 'male' ? 'selected' : ''); ?>><?php echo e(__('common.male')); ?></option>
                        <option value="female" <?php echo e(old('gender', $currentFamilyProfile->gender ?? '') === 'female' ? 'selected' : ''); ?>><?php echo e(__('common.female')); ?></option>
                    </select>
                    <small><?php echo e(__('account.gender_cannot_be_edited')); ?></small>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyBirthdate"><?php echo e(__('account.date_of_birth')); ?></label>
                    <input id="accountFamilyBirthdate" type="date" value="<?php echo e(old('birthdate', $currentFamilyProfile->birthdate ?? '')); ?>" disabled>
                    <small><?php echo e(__('account.date_of_birth_cannot_be_edited')); ?></small>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyBirthplace"><?php echo e(__('account.birthplace')); ?></label>
                    <input id="accountFamilyBirthplace" type="text" value="<?php echo e(old('birthplace', $currentFamilyProfile->birthplace ?? '')); ?>" disabled>
                    <small><?php echo e(__('account.birthplace_cannot_be_edited')); ?></small>
                </div>

                <div class="settings-field">
                    <label for="accountBloodType"><?php echo e(__('account.blood_type')); ?></label>
                    <?php $accountBloodType = strtoupper(trim((string) old('bloodtype', $currentFamilyProfile->bloodtype ?? ''))); ?>
                    <?php $isBloodTypeLocked = $accountBloodType !== ''; ?>
                    <?php if ($isBloodTypeLocked): ?>
                        <input type="hidden" name="bloodtype" value="<?php echo e($accountBloodType); ?>">
                    <?php endif; ?>
                    <select id="accountBloodType" name="bloodtype" <?php echo e($isBloodTypeLocked ? 'disabled' : ''); ?>>
                        <option value=""><?php echo e(__('account.select_blood_type')); ?></option>
                        <option value="A+" <?php echo e($accountBloodType === 'A+' ? 'selected' : ''); ?>>A+</option>
                        <option value="A-" <?php echo e($accountBloodType === 'A-' ? 'selected' : ''); ?>>A-</option>
                        <option value="B+" <?php echo e($accountBloodType === 'B+' ? 'selected' : ''); ?>>B+</option>
                        <option value="B-" <?php echo e($accountBloodType === 'B-' ? 'selected' : ''); ?>>B-</option>
                        <option value="AB+" <?php echo e($accountBloodType === 'AB+' ? 'selected' : ''); ?>>AB+</option>
                        <option value="AB-" <?php echo e($accountBloodType === 'AB-' ? 'selected' : ''); ?>>AB-</option>
                        <option value="O+" <?php echo e($accountBloodType === 'O+' ? 'selected' : ''); ?>>O+</option>
                        <option value="O-" <?php echo e($accountBloodType === 'O-' ? 'selected' : ''); ?>>O-</option>
                    </select>
                    <?php if ($isBloodTypeLocked): ?>
                        <small><?php echo e(__('account.blood_type_cannot_be_edited')); ?></small>
                    <?php endif; ?>
                </div>

                <div class="settings-field">
                    <label for="accountJob"><?php echo e(__('account.job')); ?></label>
                    <input id="accountJob" type="text" name="job" value="<?php echo e(old('job', $currentFamilyProfile->job ?? '')); ?>" placeholder="<?php echo e(__('account.example_job')); ?>">
                </div>

                <div class="settings-field">
                    <label for="accountAddress"><?php echo e(__('account.address')); ?></label>
                    <input id="accountAddress" type="text" name="address" value="<?php echo e(old('address', $currentFamilyProfile->address ?? '')); ?>" placeholder="<?php echo e(__('account.enter_full_address')); ?>">
                </div>

                <div class="settings-field">
                    <label for="accountEducation"><?php echo e(__('account.education')); ?></label>
                    <input id="accountEducation" type="text" name="education_status" value="<?php echo e(old('education_status', $currentFamilyProfile->education_status ?? '')); ?>" placeholder="<?php echo e(__('account.example_education')); ?>">
                </div>

                <div class="settings-field settings-field-full">
                    <label><?php echo e(__('account.social_media')); ?></label>
                    <?php $isAddSocialMediaDisabled = count($socialMediaOptionRows) <= 0 || count($socialRowsInitial) >= $maxSocialMediaPerMember; ?>
                    <button
                        id="addSocialMediaRowBtn"
                        type="button"
                        class="btn btn-ghost account-add-social-btn <?php echo e(count($socialRowsInitial) >= $maxSocialMediaPerMember ? 'hidden' : ''); ?>"
                        data-max-social-count="<?php echo e($maxSocialMediaPerMember); ?>"
                        <?php echo e($isAddSocialMediaDisabled ? 'disabled' : ''); ?>
                    >
                        <?php echo e(__('account.add_social_media')); ?>
                    </button>
                    <small id="socialMediaLimitNotice" class="account-social-media-empty <?php echo e(count($socialRowsInitial) >= $maxSocialMediaPerMember ? '' : 'hidden'); ?>">
                        <?php echo e(__('account.maximum_social_accounts', ['max' => $maxSocialMediaPerMember])); ?>
                    </small>
                    <div id="accountNewSocialRows" class="account-new-social-rows <?php echo e(count($socialRowsInitial) > 0 ? '' : 'hidden'); ?>">
                        <?php foreach ($socialRowsInitial as $socialRowIndex => $socialRow): ?>
                            <?php
                                $socialRowIdErrorMessage = trim((string) ($socialRowIdErrorsByIndex[$socialRowIndex] ?? ''));
                                $socialRowLinkErrorMessage = trim((string) ($socialRowLinkErrorsByIndex[$socialRowIndex] ?? ''));
                                $socialRowSocialId = (int) ($socialRow['socialid'] ?? 0);
                                if ($socialRowLinkErrorMessage === '' && $socialRowSocialId > 0) {
                                    $socialRowLinkErrorMessage = trim((string) ($legacySocialLinkErrorsById[$socialRowSocialId] ?? ''));
                                }
                            ?>
                            <div class="account-new-social-row">
                                <div class="account-new-social-field">
                                    <div class="account-social-select-wrap">
                                        <select
                                            name="social_row_ids[]"
                                            class="account-social-row-select <?php echo e($socialRowIdErrorMessage !== '' ? 'is-invalid' : ''); ?>"
                                            required
                                        >
                                            <option value=""><?php echo e(__('account.select_social_media')); ?></option>
                                            <?php foreach ($socialMediaOptionRows as $socialMediaOptionRow): ?>
                                                <option
                                                    value="<?php echo e($socialMediaOptionRow['id']); ?>"
                                                    <?php echo e((int) ($socialRow['socialid'] ?? 0) === (int) $socialMediaOptionRow['id'] ? 'selected' : ''); ?>
                                                >
                                                    <?php echo e($socialMediaOptionRow['display_name'] ?? $socialMediaOptionRow['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if ($socialRowIdErrorMessage !== ''): ?>
                                        <small class="field-error-text"><?php echo e($socialRowIdErrorMessage); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="account-new-social-field">
                                    <input
                                        type="text"
                                        name="social_row_links[]"
                                        class="account-social-row-link <?php echo e($socialRowLinkErrorMessage !== '' ? 'is-invalid' : ''); ?>"
                                        value="<?php echo e($socialRow['link'] ?? ''); ?>"
                                        placeholder="<?php echo e(__('account.profile_link')); ?>"
                                        required
                                    >
                                    <?php if ($socialRowLinkErrorMessage !== ''): ?>
                                        <small class="field-error-text"><?php echo e($socialRowLinkErrorMessage); ?></small>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-ghost account-remove-social-row-btn"><?php echo e(__('account.remove')); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($socialMediaOptionRows) <= 0): ?>
                        <small class="account-social-media-empty"><?php echo e(__('account.no_social_media_options')); ?></small>
                    <?php endif; ?>
                    <?php if ($socialRowsGlobalError !== ''): ?>
                        <small class="field-error-text"><?php echo e($socialRowsGlobalError); ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo e(__('account.save_profile')); ?></button>
            </form>
        </section>

        <div id="emailChangeFeedbackModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="emailChangeModalTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card email-change-modal-card">
                <div class="email-change-modal-icon" aria-hidden="true">@</div>
                <h4 id="emailChangeModalTitle"><?php echo e(__('account.email_change')); ?></h4>
                <div class="message-modal-body">
                    <p id="emailChangeModalText" class="message-modal-text"></p>
                </div>
                <div class="email-change-modal-actions">
                    <button id="emailChangeModalOkBtn" type="button" class="btn btn-primary email-change-modal-ok-btn"><?php echo e(__('account.ok')); ?></button>
                </div>
            </div>
        </div>

        <div id="phoneChangeFeedbackModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="phoneChangeModalTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card email-change-modal-card">
                <div class="email-change-modal-icon" aria-hidden="true">#</div>
                <h4 id="phoneChangeModalTitle"><?php echo e(__('account.phone_number_change')); ?></h4>
                <div class="message-modal-body">
                    <p id="phoneChangeModalText" class="message-modal-text"></p>
                </div>
                <div class="email-change-modal-actions">
                    <button id="phoneChangeModalOkBtn" type="button" class="btn btn-primary email-change-modal-ok-btn"><?php echo e(__('account.ok')); ?></button>
                </div>
            </div>
        </div>

        <div id="phoneOtpModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="phoneOtpModalTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card email-change-modal-card phone-otp-modal-card">
                <div class="email-change-modal-icon" aria-hidden="true">6</div>
                <h4 id="phoneOtpModalTitle"><?php echo e(__('account.verify_phone_number')); ?></h4>
                <div class="message-modal-body phone-otp-modal-body">
                    <p id="phoneOtpModalDescription" class="message-modal-text is-success"><?php echo e(__('account.enter_6_digit_otp_sent_to_whatsapp')); ?></p>
                    <div class="settings-field phone-otp-field">
                        <label for="phoneOtpInput"><?php echo e(__('account.otp_code')); ?></label>
                        <input id="phoneOtpInput" type="text" inputmode="numeric" pattern="\d{6}" minlength="6" maxlength="6" placeholder="<?php echo e(__('account.enter_6_digit_otp')); ?>" autocomplete="one-time-code">
                    </div>
                    <p id="phoneOtpErrorText" class="message-modal-text is-error hidden"></p>
                </div>
                <div class="email-change-modal-actions phone-otp-modal-actions">
                    <button id="phoneOtpCancelBtn" type="button" class="btn btn-ghost"><?php echo e(__('account.cancel')); ?></button>
                    <button id="phoneOtpVerifyBtn" type="button" class="btn btn-primary"><?php echo e(__('account.verify_otp')); ?></button>
                </div>
            </div>
        </div>

        <div id="profileSaveSuccessModal" class="message-modal hidden" role="dialog" aria-modal="true" aria-labelledby="profileSaveSuccessTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card is-add-member-success">
                <h4 id="profileSaveSuccessTitle"><?php echo e(__('account.profile_saved')); ?></h4>
                <div class="message-modal-success-icon" aria-hidden="true">
                    <span></span>
                </div>
                <div class="message-modal-body">
                    <p id="profileSaveSuccessText" class="message-modal-text is-success"><?php echo e(__('account.profile_details_updated_successfully')); ?></p>
                </div>
                <button id="profileSaveSuccessOkBtn" type="button" class="btn btn-primary">OK</button>
            </div>
        </div>

        <div id="profileFaceErrorModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="profileFaceErrorTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card email-change-modal-card is-error">
                <div class="email-change-modal-icon" aria-hidden="true">!</div>
                <h4 id="profileFaceErrorTitle"><?php echo e(__('account.invalid_profile_photo')); ?></h4>
                <div class="message-modal-body">
                    <p id="profileFaceErrorText" class="message-modal-text is-error"><?php echo e(__('account.profile_picture_must_contain_clear_face')); ?></p>
                </div>
                <div class="email-change-modal-actions">
                    <button id="profileFaceErrorOkBtn" type="button" class="btn btn-primary email-change-modal-ok-btn">OK</button>
                </div>
            </div>
        </div>

        <div id="photoCropModal" class="photo-crop-modal hidden" role="dialog" aria-modal="true" aria-labelledby="photoCropTitle">
            <div class="photo-crop-backdrop"></div>
            <div class="photo-crop-card">
                <h4 id="photoCropTitle"><?php echo e(__('account.crop_profile_photo')); ?></h4>
                <p><?php echo e(__('account.move_image_to_adjust_photo')); ?></p>
                <div class="photo-crop-stage-wrap">
                    <canvas id="photoCropCanvas" class="photo-crop-canvas" width="320" height="320"></canvas>
                </div>
                <div class="photo-crop-actions">
                    <button id="photoCropCancelBtn" type="button" class="btn btn-ghost"><?php echo e(__('account.cancel')); ?></button>
                    <button id="photoCropApplyBtn" type="button" class="btn btn-primary"><?php echo e(__('account.apply')); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section class="account-card account-edit-card account-password-card" style="width:100%;max-width:none;box-sizing:border-box;">
        <h2><?php echo e(__('account.change_password')); ?></h2>
        <p><?php echo e(__('account.enter_current_password_and_set_new_one')); ?></p>

        <form id="accountPasswordForm" method="POST" action="/account/password" class="settings-form account-form account-password-form">
            <?php echo csrf_field(); ?>

            <div class="settings-field account-password-field">
                <label for="accountCurrentPassword"><?php echo e(__('account.current_password')); ?></label>
                <div class="account-password-wrap">
                    <input id="accountCurrentPassword" type="password" name="current_password" autocomplete="current-password" required>
                    <button
                        type="button"
                        class="account-password-toggle"
                        data-target="accountCurrentPassword"
                        aria-label="<?php echo e(__('account.show_current_password')); ?>"
                        aria-pressed="false"
                    >
                        <svg class="icon-show" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M2.6 12.4a1 1 0 0 1 0-.8C4.4 7.7 7.9 5 12 5s7.6 2.7 9.4 6.6a1 1 0 0 1 0 .8C19.6 16.3 16.1 19 12 19s-7.6-2.7-9.4-6.6z"></path>
                            <circle cx="12" cy="12" r="3.1"></circle>
                        </svg>
                        <svg class="icon-hide hidden" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3.5 4.9 19.1 20.5"></path>
                            <path d="M10.2 6.2A8.2 8.2 0 0 1 12 6c4.1 0 7.6 2.7 9.4 6.6a1 1 0 0 1 0 .8 12.6 12.6 0 0 1-3.2 4.3"></path>
                            <path d="M14.7 14.8A3.1 3.1 0 0 1 10 10.1"></path>
                            <path d="M6.2 8.1a12.8 12.8 0 0 0-3.6 4.1 1 1 0 0 0 0 .8C4.4 16.9 7.9 19.6 12 19.6c1 0 2-.2 2.9-.5"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="settings-field account-password-field">
                <label for="accountNewPassword"><?php echo e(__('account.new_password')); ?></label>
                <div class="account-password-wrap">
                    <input id="accountNewPassword" type="password" name="new_password" minlength="8" autocomplete="new-password" required>
                    <button
                        type="button"
                        class="account-password-toggle"
                        data-target="accountNewPassword"
                        aria-label="<?php echo e(__('account.show_new_password')); ?>"
                        aria-pressed="false"
                    >
                        <svg class="icon-show" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M2.6 12.4a1 1 0 0 1 0-.8C4.4 7.7 7.9 5 12 5s7.6 2.7 9.4 6.6a1 1 0 0 1 0 .8C19.6 16.3 16.1 19 12 19s-7.6-2.7-9.4-6.6z"></path>
                            <circle cx="12" cy="12" r="3.1"></circle>
                        </svg>
                        <svg class="icon-hide hidden" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3.5 4.9 19.1 20.5"></path>
                            <path d="M10.2 6.2A8.2 8.2 0 0 1 12 6c4.1 0 7.6 2.7 9.4 6.6a1 1 0 0 1 0 .8 12.6 12.6 0 0 1-3.2 4.3"></path>
                            <path d="M14.7 14.8A3.1 3.1 0 0 1 10 10.1"></path>
                            <path d="M6.2 8.1a12.8 12.8 0 0 0-3.6 4.1 1 1 0 0 0 0 .8C4.4 16.9 7.9 19.6 12 19.6c1 0 2-.2 2.9-.5"></path>
                        </svg>
                    </button>
                </div>
                <small><?php echo e(__('account.at_least_8_characters')); ?></small>
            </div>

            <div class="settings-field account-password-field">
                <label for="accountNewPasswordConfirmation"><?php echo e(__('account.confirm_new_password')); ?></label>
                <div class="account-password-wrap">
                    <input id="accountNewPasswordConfirmation" type="password" name="new_password_confirmation" minlength="8" autocomplete="new-password" required>
                    <button
                        type="button"
                        class="account-password-toggle"
                        data-target="accountNewPasswordConfirmation"
                        aria-label="<?php echo e(__('account.show_confirmation_password')); ?>"
                        aria-pressed="false"
                    >
                        <svg class="icon-show" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M2.6 12.4a1 1 0 0 1 0-.8C4.4 7.7 7.9 5 12 5s7.6 2.7 9.4 6.6a1 1 0 0 1 0 .8C19.6 16.3 16.1 19 12 19s-7.6-2.7-9.4-6.6z"></path>
                            <circle cx="12" cy="12" r="3.1"></circle>
                        </svg>
                        <svg class="icon-hide hidden" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3.5 4.9 19.1 20.5"></path>
                            <path d="M10.2 6.2A8.2 8.2 0 0 1 12 6c4.1 0 7.6 2.7 9.4 6.6a1 1 0 0 1 0 .8 12.6 12.6 0 0 1-3.2 4.3"></path>
                            <path d="M14.7 14.8A3.1 3.1 0 0 1 10 10.1"></path>
                            <path d="M6.2 8.1a12.8 12.8 0 0 0-3.6 4.1 1 1 0 0 0 0 .8C4.4 16.9 7.9 19.6 12 19.6c1 0 2-.2 2.9-.5"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo e(__('account.save_password')); ?></button>
        </form>

        <div id="passwordChangeFeedbackModal" class="message-modal email-change-modal hidden" role="dialog" aria-modal="true" aria-labelledby="passwordChangeModalTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card email-change-modal-card">
                <div class="email-change-modal-icon" aria-hidden="true">*</div>
                <h4 id="passwordChangeModalTitle"><?php echo e(__('account.password_change')); ?></h4>
                <div class="message-modal-body">
                    <p id="passwordChangeModalText" class="message-modal-text"></p>
                </div>
                <div class="email-change-modal-actions">
                    <button id="passwordChangeModalOkBtn" type="button" class="btn btn-primary email-change-modal-ok-btn"><?php echo e(__('account.ok')); ?></button>
                </div>
            </div>
        </div>
    </section>
    </div>

<script>
(function() {
    var profileForm = document.getElementById('profileForm');
    var ownProfileModeInput = profileForm ? profileForm.querySelector('input[name="is_editing_own_profile"]') : null;
    var isEditingOwnProfile = !ownProfileModeInput || (ownProfileModeInput.value || '1') === '1';
    var emailInput = document.getElementById('accountFamilyEmail');
    var emailInputWrap = document.getElementById('emailInputWrap');
    var emailPendingState = document.getElementById('emailPendingState');
    var emailChangeCancelBtn = document.getElementById('emailChangeCancelBtn');
    var emailChangeFeedbackModal = document.getElementById('emailChangeFeedbackModal');
    var emailChangeModalCard = document.querySelector('#emailChangeFeedbackModal .message-modal-card');
    var emailChangeModalText = document.getElementById('emailChangeModalText');
    var emailChangeModalOkBtn = document.getElementById('emailChangeModalOkBtn');
    var phoneInput = document.getElementById('accountFamilyPhone');
    var phoneInputWrap = document.getElementById('phoneInputWrap');
    var phonePendingState = document.getElementById('phonePendingState');
    var phoneChangeCancelBtn = document.getElementById('phoneChangeCancelBtn');
    var phoneChangeVerifyLink = document.getElementById('phoneChangeVerifyLink');
    var phoneChangeFeedbackModal = document.getElementById('phoneChangeFeedbackModal');
    var phoneChangeModalCard = document.querySelector('#phoneChangeFeedbackModal .message-modal-card');
    var phoneChangeModalText = document.getElementById('phoneChangeModalText');
    var phoneChangeModalOkBtn = document.getElementById('phoneChangeModalOkBtn');
    var phoneOtpModal = document.getElementById('phoneOtpModal');
    var phoneOtpModalDescription = document.getElementById('phoneOtpModalDescription');
    var phoneOtpInput = document.getElementById('phoneOtpInput');
    var phoneOtpErrorText = document.getElementById('phoneOtpErrorText');
    var phoneOtpCancelBtn = document.getElementById('phoneOtpCancelBtn');
    var phoneOtpVerifyBtn = document.getElementById('phoneOtpVerifyBtn');
    var accountPasswordForm = document.getElementById('accountPasswordForm');
    var passwordChangeFeedbackModal = document.getElementById('passwordChangeFeedbackModal');
    var passwordChangeModalCard = document.querySelector('#passwordChangeFeedbackModal .message-modal-card');
    var passwordChangeModalText = document.getElementById('passwordChangeModalText');
    var passwordChangeModalOkBtn = document.getElementById('passwordChangeModalOkBtn');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var emailChangeModalCloseTimer = null;
    var phoneChangeModalCloseTimer = null;
    var phoneOtpModalCloseTimer = null;
    var passwordChangeModalCloseTimer = null;
    var addSocialMediaRowBtn = document.getElementById('addSocialMediaRowBtn');
    var accountNewSocialRows = document.getElementById('accountNewSocialRows');
    var socialMediaLimitNotice = document.getElementById('socialMediaLimitNotice');
    var passwordToggleButtons = document.querySelectorAll('.account-password-toggle');
    var socialMediaOptions = <?php echo isset($socialMediaOptionRowsJson) ? $socialMediaOptionRowsJson : '[]'; ?>;
    var maxSocialMediaPerMember = parseInt('<?php echo e((int) ($maxSocialMediaPerMember ?? 3)); ?>', 10) || 3;
    var socialMediaOptionsById = {};

    if (Array.isArray(socialMediaOptions)) {
        socialMediaOptions.forEach(function(option) {
            var optionId = parseInt(option.id, 10) || 0;
            var optionName = String(option.name || '').trim();
            if (optionId > 0 && optionName !== '') {
                socialMediaOptionsById[optionId] = optionName;
            }
        });
    }

    function formatSocialMediaOptionLabel(rawName) {
        var value = String(rawName || '').trim();
        if (!value) {
            return '';
        }

        var lower = value.toLowerCase();
        var labelMap = {
            youtube: 'YouTube',
            linkedin: 'LinkedIn',
            tiktok: 'TikTok',
            wechat: 'WeChat',
            thread: 'Threads',
            threads: 'Threads',
            x: 'X'
        };

        if (labelMap[lower]) {
            return labelMap[lower];
        }

        return lower.replace(/\b\w/g, function(ch) {
            return ch.toUpperCase();
        });
    }

    function setPasswordToggleState(toggleButton, isVisible) {
        if (!toggleButton) {
            return;
        }

        var showIcon = toggleButton.querySelector('.icon-show');
        var hideIcon = toggleButton.querySelector('.icon-hide');
        var targetId = toggleButton.getAttribute('data-target') || '';
        var targetInput = targetId ? document.getElementById(targetId) : null;
        var baseLabel = 'password';
        if (targetInput && targetInput.id === 'accountCurrentPassword') {
            baseLabel = 'current password';
        } else if (targetInput && targetInput.id === 'accountNewPassword') {
            baseLabel = 'new password';
        } else if (targetInput && targetInput.id === 'accountNewPasswordConfirmation') {
            baseLabel = 'confirmation password';
        }

        toggleButton.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
        toggleButton.setAttribute('aria-label', (isVisible ? 'Hide ' : 'Show ') + baseLabel);
        toggleButton.setAttribute('title', isVisible ? 'Hide password' : 'Show password');

        if (showIcon) {
            showIcon.classList.toggle('hidden', isVisible);
        }
        if (hideIcon) {
            hideIcon.classList.toggle('hidden', !isVisible);
        }
    }

    function bindPasswordToggles() {
        if (!passwordToggleButtons || passwordToggleButtons.length === 0) {
            return;
        }

        passwordToggleButtons.forEach(function(toggleButton) {
            if (!toggleButton || toggleButton.getAttribute('data-bound') === '1') {
                return;
            }

            var targetId = toggleButton.getAttribute('data-target') || '';
            var targetInput = targetId ? document.getElementById(targetId) : null;
            if (!targetInput) {
                return;
            }

            setPasswordToggleState(toggleButton, targetInput.type === 'text');
            toggleButton.setAttribute('data-bound', '1');
            toggleButton.addEventListener('click', function() {
                var isVisible = targetInput.type === 'text';
                targetInput.type = isVisible ? 'password' : 'text';
                setPasswordToggleState(toggleButton, !isVisible);
                targetInput.focus();
            });
        });
    }

    function toggleNewSocialRowsVisibility() {
        if (!accountNewSocialRows) {
            return;
        }

        if (accountNewSocialRows.children.length > 0) {
            accountNewSocialRows.classList.remove('hidden');
        } else {
            accountNewSocialRows.classList.add('hidden');
        }

        updateAddSocialMediaRowButtonState();
    }

    function getCurrentSocialRowCount() {
        if (!accountNewSocialRows) {
            return 0;
        }

        return accountNewSocialRows.querySelectorAll('.account-new-social-row').length;
    }

    function updateAddSocialMediaRowButtonState() {
        if (!addSocialMediaRowBtn) {
            return;
        }

        var hasOptions = Array.isArray(socialMediaOptions) && socialMediaOptions.length > 0;
        var hasReachedLimit = getCurrentSocialRowCount() >= maxSocialMediaPerMember;
        addSocialMediaRowBtn.disabled = !hasOptions || hasReachedLimit;
        addSocialMediaRowBtn.classList.toggle('hidden', hasReachedLimit);

        if (socialMediaLimitNotice) {
            if (hasReachedLimit) {
                socialMediaLimitNotice.classList.remove('hidden');
            } else {
                socialMediaLimitNotice.classList.add('hidden');
            }
        }
    }

    bindPasswordToggles();

    function normalizeSocialMediaName(rawName) {
        return String(rawName || '').trim().toLowerCase().replace(/\s+/g, '');
    }

    function getSocialSelectVisual(rawName) {
        var name = normalizeSocialMediaName(rawName);
        var visualMap = {
            instagram: { label: 'IG', bg: '#fde8f2', color: '#b4236f' },
            facebook: { label: 'FB', bg: '#e8f0ff', color: '#1c4ed8' },
            tiktok: { label: 'TT', bg: '#e9fbf7', color: '#047857' },
            youtube: { label: 'YT', bg: '#feeceb', color: '#b42318' },
            linkedin: { label: 'IN', bg: '#eaf5ff', color: '#0b66c2' },
            whatsapp: { label: 'WA', bg: '#ebfbf0', color: '#15803d' },
            telegram: { label: 'TG', bg: '#e6f6ff', color: '#0d6ea8' },
            x: { label: 'X', bg: '#f3f4f6', color: '#111827' },
            thread: { label: 'TH', bg: '#f3f4f6', color: '#111827' },
            threads: { label: 'TH', bg: '#f3f4f6', color: '#111827' },
            wechat: { label: 'WC', bg: '#ebfff3', color: '#166534' }
        };

        if (visualMap[name]) {
            return visualMap[name];
        }

        return { label: 'SM', bg: '#edf3f7', color: '#24506c' };
    }

    function updateSocialSelectVisual(select) {
        if (!select) {
            return;
        }

        var selectWrap = select.closest('.account-social-select-wrap');
        if (!selectWrap) {
            return;
        }

        var selectedSocialId = parseInt(select.value, 10) || 0;
        var selectedSocialName = selectedSocialId > 0 ? String(socialMediaOptionsById[selectedSocialId] || '') : '';
        var visual = getSocialSelectVisual(selectedSocialName);

        selectWrap.setAttribute('data-platform-label', visual.label);
        selectWrap.style.setProperty('--social-badge-bg', visual.bg);
        selectWrap.style.setProperty('--social-badge-color', visual.color);
    }

    function bindSocialSelects(scope) {
        var root = scope && scope.querySelectorAll ? scope : document;
        var selectElements = root.querySelectorAll('.account-social-row-select');
        selectElements.forEach(function(select) {
            if (select.dataset.socialSelectBound !== '1') {
                select.dataset.socialSelectBound = '1';
                select.addEventListener('change', function() {
                    updateSocialSelectVisual(select);
                });
            }

            updateSocialSelectVisual(select);
        });
    }

    function createSocialMediaSelect(selectedIdValue) {
        var select = document.createElement('select');
        select.name = 'social_row_ids[]';
        select.className = 'account-social-row-select';
        select.required = true;

        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select social media';
        select.appendChild(defaultOption);

        var selectedId = parseInt(selectedIdValue, 10) || 0;
        socialMediaOptions.forEach(function(option) {
            var optionId = parseInt(option.id, 10) || 0;
            var optionName = String(option.name || '').trim();
            if (optionId <= 0 || optionName === '') {
                return;
            }

            var optionElement = document.createElement('option');
            optionElement.value = String(optionId);
            optionElement.textContent = formatSocialMediaOptionLabel(optionName);
            if (optionId === selectedId) {
                optionElement.selected = true;
            }

            select.appendChild(optionElement);
        });

        return select;
    }

    function createNewSocialRow(socialIdValue, linkValue) {
        if (!accountNewSocialRows) {
            return null;
        }

        var row = document.createElement('div');
        row.className = 'account-new-social-row';
        var socialSelect = createSocialMediaSelect(socialIdValue);
        var socialSelectWrap = document.createElement('div');
        socialSelectWrap.className = 'account-new-social-field';
        var socialSelectShell = document.createElement('div');
        socialSelectShell.className = 'account-social-select-wrap';

        var socialLinkInput = document.createElement('input');
        socialLinkInput.type = 'text';
        socialLinkInput.name = 'social_row_links[]';
        socialLinkInput.className = 'account-social-row-link';
        socialLinkInput.placeholder = 'Profile link';
        socialLinkInput.value = linkValue || '';
        socialLinkInput.required = true;
        var socialLinkWrap = document.createElement('div');
        socialLinkWrap.className = 'account-new-social-field';

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-ghost account-remove-social-row-btn';
        removeBtn.textContent = 'Remove';

        socialSelectShell.appendChild(socialSelect);
        socialSelectWrap.appendChild(socialSelectShell);
        socialLinkWrap.appendChild(socialLinkInput);

        row.appendChild(socialSelectWrap);
        row.appendChild(socialLinkWrap);
        row.appendChild(removeBtn);

        accountNewSocialRows.appendChild(row);
        toggleNewSocialRowsVisibility();
        bindSocialSelects(row);

        return socialSelect;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openEmailChangeModal(message, isError) {
        if (!emailChangeFeedbackModal || !emailChangeModalText) {
            return;
        }

        if (emailChangeModalCloseTimer !== null) {
            window.clearTimeout(emailChangeModalCloseTimer);
            emailChangeModalCloseTimer = null;
        }

        if (emailChangeModalCard) {
            emailChangeModalCard.classList.remove('is-success', 'is-error');
            emailChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        emailChangeModalText.classList.remove('is-success', 'is-error');
        emailChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        emailChangeModalText.textContent = message || '';

        emailChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void emailChangeFeedbackModal.offsetWidth;
        emailChangeFeedbackModal.classList.add('is-open');
    }

    function closeEmailChangeModal() {
        if (!emailChangeFeedbackModal || emailChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        emailChangeFeedbackModal.classList.remove('is-open');
        emailChangeFeedbackModal.classList.add('is-closing');

        if (emailChangeModalCloseTimer !== null) {
            window.clearTimeout(emailChangeModalCloseTimer);
        }

        emailChangeModalCloseTimer = window.setTimeout(function() {
            emailChangeFeedbackModal.classList.add('hidden');
            emailChangeFeedbackModal.classList.remove('is-closing');
            emailChangeModalCloseTimer = null;
        }, 220);
    }

    function openPhoneChangeModal(message, isError) {
        if (!phoneChangeFeedbackModal || !phoneChangeModalText) {
            return;
        }

        if (phoneChangeModalCloseTimer !== null) {
            window.clearTimeout(phoneChangeModalCloseTimer);
            phoneChangeModalCloseTimer = null;
        }

        if (phoneChangeModalCard) {
            phoneChangeModalCard.classList.remove('is-success', 'is-error');
            phoneChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        phoneChangeModalText.classList.remove('is-success', 'is-error');
        phoneChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        phoneChangeModalText.textContent = message || '';

        phoneChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void phoneChangeFeedbackModal.offsetWidth;
        phoneChangeFeedbackModal.classList.add('is-open');
    }

    function closePhoneChangeModal() {
        if (!phoneChangeFeedbackModal || phoneChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        phoneChangeFeedbackModal.classList.remove('is-open');
        phoneChangeFeedbackModal.classList.add('is-closing');

        if (phoneChangeModalCloseTimer !== null) {
            window.clearTimeout(phoneChangeModalCloseTimer);
        }

        phoneChangeModalCloseTimer = window.setTimeout(function() {
            phoneChangeFeedbackModal.classList.add('hidden');
            phoneChangeFeedbackModal.classList.remove('is-closing');
            phoneChangeModalCloseTimer = null;
        }, 220);
    }

    function openPasswordChangeModal(message, isError) {
        if (!passwordChangeFeedbackModal || !passwordChangeModalText) {
            return;
        }

        if (passwordChangeModalCloseTimer !== null) {
            window.clearTimeout(passwordChangeModalCloseTimer);
            passwordChangeModalCloseTimer = null;
        }

        if (passwordChangeModalCard) {
            passwordChangeModalCard.classList.remove('is-success', 'is-error');
            passwordChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        passwordChangeModalText.classList.remove('is-success', 'is-error');
        passwordChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        passwordChangeModalText.textContent = message || '';

        passwordChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void passwordChangeFeedbackModal.offsetWidth;
        passwordChangeFeedbackModal.classList.add('is-open');
    }

    function closePasswordChangeModal() {
        if (!passwordChangeFeedbackModal || passwordChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        passwordChangeFeedbackModal.classList.remove('is-open');
        passwordChangeFeedbackModal.classList.add('is-closing');

        if (passwordChangeModalCloseTimer !== null) {
            window.clearTimeout(passwordChangeModalCloseTimer);
        }

        passwordChangeModalCloseTimer = window.setTimeout(function() {
            passwordChangeFeedbackModal.classList.add('hidden');
            passwordChangeFeedbackModal.classList.remove('is-closing');
            passwordChangeModalCloseTimer = null;
        }, 220);
    }

    function normalizePhoneNumber(value) {
        var digits = String(value || '').replace(/\D+/g, '');
        if (!digits) {
            return '';
        }

        if (digits.indexOf('0') === 0) {
            return '62' + digits.slice(1);
        }

        if (digits.indexOf('8') === 0) {
            return '62' + digits;
        }

        return digits;
    }

    function setPendingEmailState(pendingEmail) {
        if (!emailPendingState) {
            return;
        }

        if (!pendingEmail) {
            if (emailInputWrap) {
                emailInputWrap.classList.remove('hidden');
            }
            emailPendingState.classList.add('hidden');
            emailPendingState.innerHTML = '';
            return;
        }

        if (emailInputWrap) {
            emailInputWrap.classList.add('hidden');
        }

        emailPendingState.classList.remove('hidden');
        emailPendingState.innerHTML =
            '<span>Change pending. Open the link sent to you at ' + escapeHtml(pendingEmail) + '.</span>' +
            '<button id="emailChangeCancelBtn" type="button" class="email-cancel-btn">Cancel email change</button>';

        emailChangeCancelBtn = document.getElementById('emailChangeCancelBtn');
        bindCancelEmailButton();
    }

    function setPendingPhoneState(pendingPhone) {
        if (!phonePendingState) {
            return;
        }

        if (!pendingPhone) {
            if (phoneInputWrap) {
                phoneInputWrap.classList.remove('hidden');
            }
            phonePendingState.classList.add('hidden');
            phonePendingState.innerHTML = '';
            return;
        }

        if (phoneInputWrap) {
            phoneInputWrap.classList.add('hidden');
        }

        phonePendingState.classList.remove('hidden');
        phonePendingState.innerHTML =
            '<span>Change pending. Enter the OTP code sent to WhatsApp number ' + escapeHtml(pendingPhone) + '.</span>' +
            '<a id="phoneChangeVerifyLink" href="#" class="email-cancel-btn">Enter OTP code</a>' +
            '<button id="phoneChangeCancelBtn" type="button" class="email-cancel-btn">Cancel phone change</button>';

        phoneChangeVerifyLink = document.getElementById('phoneChangeVerifyLink');
        phoneChangeCancelBtn = document.getElementById('phoneChangeCancelBtn');
        bindPhonePendingActions();
    }

    function parseJsonResponse(response) {
        return response.text().then(function(text) {
            var data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                throw new Error(text || 'Invalid server response');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        });
    }

    function bindCancelEmailButton() {
        if (!emailChangeCancelBtn || !emailInput) {
            return;
        }

        if (emailChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        emailChangeCancelBtn.setAttribute('data-bound', '1');
        emailChangeCancelBtn.addEventListener('click', function() {
            var rollbackEmail = (emailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var previousPendingEmail = (emailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            emailInput.value = rollbackEmail;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/family/change-email/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentEmail = (data.current_email || emailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
                emailInput.value = currentEmail;
                emailInput.setAttribute('data-current-email', currentEmail);
                emailInput.setAttribute('data-pending-email', '');
                setPendingEmailState('');
                openEmailChangeModal(data.message || 'Email change request has been canceled.', false);
            })
            .catch(function(error) {
                emailInput.setAttribute('data-pending-email', previousPendingEmail);
                setPendingEmailState(previousPendingEmail);
                openEmailChangeModal(error.message || 'Failed to cancel email change request.', true);
            });
        });
    }

    function setPhoneOtpError(message) {
        if (!phoneOtpErrorText) {
            return;
        }

        if (!message) {
            phoneOtpErrorText.textContent = '';
            phoneOtpErrorText.classList.add('hidden');
            return;
        }

        phoneOtpErrorText.textContent = message;
        phoneOtpErrorText.classList.remove('hidden');
    }

    function openPhoneOtpModal() {
        if (!phoneOtpModal || !phoneInput) {
            return;
        }

        var pendingPhone = (phoneInput.getAttribute('data-pending-phone') || '').trim();
        if (pendingPhone === '') {
            openPhoneChangeModal('There is no pending phone change request.', true);
            return;
        }

        if (phoneOtpModalDescription) {
            phoneOtpModalDescription.classList.remove('is-error');
            phoneOtpModalDescription.classList.add('is-success');
            phoneOtpModalDescription.textContent = 'Enter the 6-digit OTP sent to WhatsApp number ' + pendingPhone + '.';
        }

        if (phoneOtpInput) {
            phoneOtpInput.value = '';
        }
        setPhoneOtpError('');

        if (phoneOtpModalCloseTimer !== null) {
            window.clearTimeout(phoneOtpModalCloseTimer);
            phoneOtpModalCloseTimer = null;
        }

        phoneOtpModal.classList.remove('hidden', 'is-closing');
        void phoneOtpModal.offsetWidth;
        phoneOtpModal.classList.add('is-open');

        if (phoneOtpInput) {
            window.setTimeout(function() {
                phoneOtpInput.focus();
            }, 40);
        }
    }

    function closePhoneOtpModal() {
        if (!phoneOtpModal || phoneOtpModal.classList.contains('hidden')) {
            return;
        }

        phoneOtpModal.classList.remove('is-open');
        phoneOtpModal.classList.add('is-closing');

        if (phoneOtpModalCloseTimer !== null) {
            window.clearTimeout(phoneOtpModalCloseTimer);
        }

        phoneOtpModalCloseTimer = window.setTimeout(function() {
            phoneOtpModal.classList.add('hidden');
            phoneOtpModal.classList.remove('is-closing');
            phoneOtpModalCloseTimer = null;
        }, 220);
    }

    function bindPhonePendingActions() {
        if (phoneChangeVerifyLink && phoneChangeVerifyLink.getAttribute('data-bound') !== '1') {
            phoneChangeVerifyLink.setAttribute('data-bound', '1');
            phoneChangeVerifyLink.addEventListener('click', function(e) {
                e.preventDefault();
                openPhoneOtpModal();
            });
        }

        if (!phoneChangeCancelBtn || !phoneInput) {
            return;
        }

        if (phoneChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        phoneChangeCancelBtn.setAttribute('data-bound', '1');
        phoneChangeCancelBtn.addEventListener('click', function() {
            var rollbackPhone = (phoneInput.getAttribute('data-current-phone') || '').trim();
            var previousPendingPhone = (phoneInput.getAttribute('data-pending-phone') || '').trim();
            phoneInput.value = rollbackPhone;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/family/change-phone/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentPhone = (data.current_phone || phoneInput.getAttribute('data-current-phone') || '').trim();
                phoneInput.value = currentPhone;
                phoneInput.setAttribute('data-current-phone', currentPhone);
                phoneInput.setAttribute('data-pending-phone', '');
                setPendingPhoneState('');
                closePhoneOtpModal();
                openPhoneChangeModal(data.message || 'Phone change request has been canceled.', false);
            })
            .catch(function(error) {
                phoneInput.setAttribute('data-pending-phone', previousPendingPhone);
                setPendingPhoneState(previousPendingPhone);
                openPhoneChangeModal(error.message || 'Failed to cancel phone change request.', true);
            });
        });
    }

    if (isEditingOwnProfile) {
        bindCancelEmailButton();
        bindPhonePendingActions();
    }

    if (addSocialMediaRowBtn && accountNewSocialRows) {
        addSocialMediaRowBtn.addEventListener('click', function() {
            if (!Array.isArray(socialMediaOptions) || socialMediaOptions.length === 0) {
                return;
            }
            if (getCurrentSocialRowCount() >= maxSocialMediaPerMember) {
                updateAddSocialMediaRowButtonState();
                return;
            }

            var socialSelectInput = createNewSocialRow('', '');
            if (socialSelectInput) {
                socialSelectInput.focus();
            }
        });

        accountNewSocialRows.addEventListener('click', function(event) {
            var target = event.target;
            if (!target || !target.classList || !target.classList.contains('account-remove-social-row-btn')) {
                return;
            }

            var row = target.closest('.account-new-social-row');
            if (row && row.parentNode) {
                row.parentNode.removeChild(row);
                toggleNewSocialRowsVisibility();
            }
        });

        bindSocialSelects(accountNewSocialRows);
        toggleNewSocialRowsVisibility();
        updateAddSocialMediaRowButtonState();
    }

    if (isEditingOwnProfile && phoneOtpInput) {
        phoneOtpInput.addEventListener('input', function() {
            var digits = phoneOtpInput.value.replace(/\D+/g, '').slice(0, 6);
            if (phoneOtpInput.value !== digits) {
                phoneOtpInput.value = digits;
            }
            if (digits.length > 0) {
                setPhoneOtpError('');
            }
        });
    }

    if (isEditingOwnProfile && phoneOtpVerifyBtn && phoneInput) {
        phoneOtpVerifyBtn.addEventListener('click', function() {
            var otp = phoneOtpInput ? phoneOtpInput.value.replace(/\D+/g, '') : '';
            if (otp.length !== 6) {
                setPhoneOtpError('Please enter a valid 6-digit OTP.');
                return;
            }

            var submitBtnText = phoneOtpVerifyBtn.textContent;
            phoneOtpVerifyBtn.disabled = true;
            phoneOtpVerifyBtn.textContent = 'Verifying...';
            setPhoneOtpError('');

            var formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('otp', otp);

            fetch('/family/change-phone/verify-otp', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var verifiedPhone = (data.phone_number || phoneInput.getAttribute('data-pending-phone') || '').trim();
                phoneInput.value = verifiedPhone;
                phoneInput.setAttribute('data-current-phone', verifiedPhone);
                phoneInput.setAttribute('data-pending-phone', '');
                setPendingPhoneState('');
                closePhoneOtpModal();
                openPhoneChangeModal(data.message || 'Phone number has been updated successfully.', false);
            })
            .catch(function(error) {
                setPhoneOtpError(error.message || 'Failed to verify OTP.');
            })
            .finally(function() {
                phoneOtpVerifyBtn.disabled = false;
                phoneOtpVerifyBtn.textContent = submitBtnText;
            });
        });
    }

    if (isEditingOwnProfile && profileForm && emailInput && phoneInput) {
        profileForm.addEventListener('submit', function(e) {
            var currentEmail = (emailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var pendingEmail = (emailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            var newEmail = emailInput.value.toLowerCase().trim();
            var currentPhone = (phoneInput.getAttribute('data-current-phone') || '').trim();
            var pendingPhone = (phoneInput.getAttribute('data-pending-phone') || '').trim();
            var newPhone = phoneInput.value.trim();
            var normalizedCurrentPhone = normalizePhoneNumber(currentPhone);
            var normalizedPendingPhone = normalizePhoneNumber(pendingPhone);
            var normalizedNewPhone = normalizePhoneNumber(newPhone);

            if (newEmail !== '' && newEmail !== currentEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('new_email', newEmail);

                fetch('/family/change-email', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    if (data.message && data.old_email && data.new_email) {
                        var oldEmail = (data.old_email || currentEmail).toLowerCase().trim();
                        var requestedEmail = data.new_email.toLowerCase().trim();
                        emailInput.value = oldEmail;
                        emailInput.setAttribute('data-current-email', oldEmail);
                        emailInput.setAttribute('data-pending-email', requestedEmail);
                        setPendingEmailState(requestedEmail);
                        openEmailChangeModal('Change pending. Open the link sent to you at ' + data.new_email + '.', false);
                    } else {
                        openEmailChangeModal(data.message || 'An unexpected response was returned.', true);
                    }
                })
                .catch(function(error) {
                    openEmailChangeModal(error.message || 'An error occurred while sending confirmation. Please try again later.', true);
                });

                return;
            }

            if (pendingEmail !== '' && newEmail === pendingEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();
                emailInput.value = currentEmail;
                openEmailChangeModal('Change pending. Open the link sent to you at ' + pendingEmail + '.', false);
                return;
            }

            if (newPhone !== '' && normalizedNewPhone !== '' && normalizedNewPhone !== normalizedCurrentPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var phoneFormData = new FormData();
                phoneFormData.append('_token', csrfToken);
                phoneFormData.append('new_phone', newPhone);

                fetch('/family/change-phone/send-otp', {
                    method: 'POST',
                    body: phoneFormData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    var oldPhone = (data.old_phone || currentPhone).trim();
                    var requestedPhone = (data.new_phone || newPhone).trim();
                    phoneInput.value = oldPhone;
                    phoneInput.setAttribute('data-current-phone', oldPhone);
                    phoneInput.setAttribute('data-pending-phone', requestedPhone);
                    setPendingPhoneState(requestedPhone);
                    openPhoneChangeModal(data.message || 'OTP has been sent to your WhatsApp number.', false);
                })
                .catch(function(error) {
                    openPhoneChangeModal(error.message || 'An error occurred while sending OTP. Please try again later.', true);
                });

                return;
            }

            if (pendingPhone !== '' && normalizedPendingPhone !== '' && normalizedNewPhone === normalizedPendingPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();
                phoneInput.value = currentPhone;
                openPhoneChangeModal('Change pending. Click "Enter OTP code" to complete verification.', false);
            }
        });
    }

    if (emailChangeModalOkBtn) {
        emailChangeModalOkBtn.addEventListener('click', function() {
            closeEmailChangeModal();
        });
    }

    if (phoneChangeModalOkBtn) {
        phoneChangeModalOkBtn.addEventListener('click', function() {
            closePhoneChangeModal();
        });
    }

    if (phoneOtpCancelBtn) {
        phoneOtpCancelBtn.addEventListener('click', function() {
            closePhoneOtpModal();
        });
    }

    if (accountPasswordForm) {
        accountPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var submitButton = accountPasswordForm.querySelector('button[type="submit"]');
            var submitButtonText = submitButton ? submitButton.textContent : '';
            var formData = new FormData(accountPasswordForm);

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            }

            fetch('/account/password', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                accountPasswordForm.reset();
                passwordToggleButtons.forEach(function(toggleButton) {
                    var targetId = toggleButton.getAttribute('data-target') || '';
                    var targetInput = targetId ? document.getElementById(targetId) : null;
                    if (targetInput) {
                        targetInput.type = 'password';
                    }
                    setPasswordToggleState(toggleButton, false);
                });
                openPasswordChangeModal(data.message || 'Password updated successfully.', false);
            })
            .catch(function(error) {
                openPasswordChangeModal(error.message || 'Failed to update password.', true);
            })
            .finally(function() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = submitButtonText || 'Save Password';
                }
            });
        });
    }

    if (passwordChangeModalOkBtn) {
        passwordChangeModalOkBtn.addEventListener('click', function() {
            closePasswordChangeModal();
        });
    }

    if (emailChangeFeedbackModal) {
        emailChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === emailChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeEmailChangeModal();
            }
        });
    }

    if (phoneChangeFeedbackModal) {
        phoneChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === phoneChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closePhoneChangeModal();
            }
        });
    }

    if (phoneOtpModal) {
        phoneOtpModal.addEventListener('click', function(e) {
            if (e.target === phoneOtpModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closePhoneOtpModal();
            }
        });
    }

    if (passwordChangeFeedbackModal) {
        passwordChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === passwordChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closePasswordChangeModal();
            }
        });
    }
})();
</script>

<script>
(function() {
    var adminProfileForm = document.getElementById('adminProfileForm');
    if (!adminProfileForm) {
        return;
    }

    var adminEmailInput = document.getElementById('accountAdminEmail');
    var adminEmailInputWrap = document.getElementById('adminEmailInputWrap');
    var adminEmailPendingState = document.getElementById('adminEmailPendingState');
    var adminEmailChangeCancelBtn = document.getElementById('adminEmailChangeCancelBtn');
    var adminPhoneInput = document.getElementById('accountAdminPhone');
    var adminPhoneInputWrap = document.getElementById('adminPhoneInputWrap');
    var adminPhonePendingState = document.getElementById('adminPhonePendingState');
    var adminPhoneChangeCancelBtn = document.getElementById('adminPhoneChangeCancelBtn');
    var adminPhoneChangeVerifyLink = document.getElementById('adminPhoneChangeVerifyLink');
    var adminEmailChangeFeedbackModal = document.getElementById('adminEmailChangeFeedbackModal');
    var adminEmailChangeModalCard = document.querySelector('#adminEmailChangeFeedbackModal .message-modal-card');
    var adminEmailChangeModalText = document.getElementById('adminEmailChangeModalText');
    var adminEmailChangeModalOkBtn = document.getElementById('adminEmailChangeModalOkBtn');
    var adminPhoneChangeFeedbackModal = document.getElementById('adminPhoneChangeFeedbackModal');
    var adminPhoneChangeModalCard = document.querySelector('#adminPhoneChangeFeedbackModal .message-modal-card');
    var adminPhoneChangeModalText = document.getElementById('adminPhoneChangeModalText');
    var adminPhoneChangeModalOkBtn = document.getElementById('adminPhoneChangeModalOkBtn');
    var adminPhoneOtpModal = document.getElementById('adminPhoneOtpModal');
    var adminPhoneOtpModalDescription = document.getElementById('adminPhoneOtpModalDescription');
    var adminPhoneOtpInput = document.getElementById('adminPhoneOtpInput');
    var adminPhoneOtpErrorText = document.getElementById('adminPhoneOtpErrorText');
    var adminPhoneOtpCancelBtn = document.getElementById('adminPhoneOtpCancelBtn');
    var adminPhoneOtpVerifyBtn = document.getElementById('adminPhoneOtpVerifyBtn');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var adminEmailModalCloseTimer = null;
    var adminPhoneModalCloseTimer = null;
    var adminPhoneOtpModalCloseTimer = null;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizePhoneNumber(value) {
        var digits = String(value || '').replace(/\D+/g, '');
        if (!digits) {
            return '';
        }
        if (digits.indexOf('0') === 0) {
            return '62' + digits.slice(1);
        }
        if (digits.indexOf('8') === 0) {
            return '62' + digits;
        }
        return digits;
    }

    function parseJsonResponse(response) {
        return response.text().then(function(text) {
            var data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                throw new Error(text || 'Invalid server response');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        });
    }

    function openAdminEmailChangeModal(message, isError) {
        if (!adminEmailChangeFeedbackModal || !adminEmailChangeModalText) {
            return;
        }

        if (adminEmailModalCloseTimer !== null) {
            window.clearTimeout(adminEmailModalCloseTimer);
            adminEmailModalCloseTimer = null;
        }

        if (adminEmailChangeModalCard) {
            adminEmailChangeModalCard.classList.remove('is-success', 'is-error');
            adminEmailChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        adminEmailChangeModalText.classList.remove('is-success', 'is-error');
        adminEmailChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        adminEmailChangeModalText.textContent = message || '';

        adminEmailChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void adminEmailChangeFeedbackModal.offsetWidth;
        adminEmailChangeFeedbackModal.classList.add('is-open');
    }

    function closeAdminEmailChangeModal() {
        if (!adminEmailChangeFeedbackModal || adminEmailChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        adminEmailChangeFeedbackModal.classList.remove('is-open');
        adminEmailChangeFeedbackModal.classList.add('is-closing');

        if (adminEmailModalCloseTimer !== null) {
            window.clearTimeout(adminEmailModalCloseTimer);
        }

        adminEmailModalCloseTimer = window.setTimeout(function() {
            adminEmailChangeFeedbackModal.classList.add('hidden');
            adminEmailChangeFeedbackModal.classList.remove('is-closing');
            adminEmailModalCloseTimer = null;
        }, 220);
    }

    function openAdminPhoneChangeModal(message, isError) {
        if (!adminPhoneChangeFeedbackModal || !adminPhoneChangeModalText) {
            return;
        }

        if (adminPhoneModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneModalCloseTimer);
            adminPhoneModalCloseTimer = null;
        }

        if (adminPhoneChangeModalCard) {
            adminPhoneChangeModalCard.classList.remove('is-success', 'is-error');
            adminPhoneChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        adminPhoneChangeModalText.classList.remove('is-success', 'is-error');
        adminPhoneChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        adminPhoneChangeModalText.textContent = message || '';

        adminPhoneChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void adminPhoneChangeFeedbackModal.offsetWidth;
        adminPhoneChangeFeedbackModal.classList.add('is-open');
    }

    function closeAdminPhoneChangeModal() {
        if (!adminPhoneChangeFeedbackModal || adminPhoneChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        adminPhoneChangeFeedbackModal.classList.remove('is-open');
        adminPhoneChangeFeedbackModal.classList.add('is-closing');

        if (adminPhoneModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneModalCloseTimer);
        }

        adminPhoneModalCloseTimer = window.setTimeout(function() {
            adminPhoneChangeFeedbackModal.classList.add('hidden');
            adminPhoneChangeFeedbackModal.classList.remove('is-closing');
            adminPhoneModalCloseTimer = null;
        }, 220);
    }

    function setAdminPendingEmailState(pendingEmail) {
        if (!adminEmailPendingState) {
            return;
        }

        if (!pendingEmail) {
            if (adminEmailInputWrap) {
                adminEmailInputWrap.classList.remove('hidden');
            }
            adminEmailPendingState.classList.add('hidden');
            adminEmailPendingState.innerHTML = '';
            return;
        }

        if (adminEmailInputWrap) {
            adminEmailInputWrap.classList.add('hidden');
        }

        adminEmailPendingState.classList.remove('hidden');
        adminEmailPendingState.innerHTML =
            '<span>Change pending. Open the link sent to you at ' + escapeHtml(pendingEmail) + '.</span>' +
            '<button id="adminEmailChangeCancelBtn" type="button" class="email-cancel-btn">Cancel email change</button>';

        adminEmailChangeCancelBtn = document.getElementById('adminEmailChangeCancelBtn');
        bindAdminCancelEmailButton();
    }

    function setAdminPendingPhoneState(pendingPhone) {
        if (!adminPhonePendingState) {
            return;
        }

        if (!pendingPhone) {
            if (adminPhoneInputWrap) {
                adminPhoneInputWrap.classList.remove('hidden');
            }
            adminPhonePendingState.classList.add('hidden');
            adminPhonePendingState.innerHTML = '';
            return;
        }

        if (adminPhoneInputWrap) {
            adminPhoneInputWrap.classList.add('hidden');
        }

        adminPhonePendingState.classList.remove('hidden');
        adminPhonePendingState.innerHTML =
            '<span>Change pending. Enter the OTP code sent to WhatsApp number ' + escapeHtml(pendingPhone) + '.</span>' +
            '<a id="adminPhoneChangeVerifyLink" href="#" class="email-cancel-btn">Enter OTP code</a>' +
            '<button id="adminPhoneChangeCancelBtn" type="button" class="email-cancel-btn">Cancel phone change</button>';

        adminPhoneChangeVerifyLink = document.getElementById('adminPhoneChangeVerifyLink');
        adminPhoneChangeCancelBtn = document.getElementById('adminPhoneChangeCancelBtn');
        bindAdminPhonePendingActions();
    }

    function bindAdminCancelEmailButton() {
        if (!adminEmailChangeCancelBtn || !adminEmailInput) {
            return;
        }

        if (adminEmailChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        adminEmailChangeCancelBtn.setAttribute('data-bound', '1');
        adminEmailChangeCancelBtn.addEventListener('click', function() {
            var rollbackEmail = (adminEmailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var previousPendingEmail = (adminEmailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            adminEmailInput.value = rollbackEmail;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/employer/change-email/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentEmail = (data.current_email || adminEmailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
                adminEmailInput.value = currentEmail;
                adminEmailInput.setAttribute('data-current-email', currentEmail);
                adminEmailInput.setAttribute('data-pending-email', '');
                setAdminPendingEmailState('');
                openAdminEmailChangeModal(data.message || 'Email change request has been canceled.', false);
            })
            .catch(function(error) {
                adminEmailInput.setAttribute('data-pending-email', previousPendingEmail);
                setAdminPendingEmailState(previousPendingEmail);
                openAdminEmailChangeModal(error.message || 'Failed to cancel email change request.', true);
            });
        });
    }

    function setAdminPhoneOtpError(message) {
        if (!adminPhoneOtpErrorText) {
            return;
        }

        if (!message) {
            adminPhoneOtpErrorText.textContent = '';
            adminPhoneOtpErrorText.classList.add('hidden');
            return;
        }

        adminPhoneOtpErrorText.textContent = message;
        adminPhoneOtpErrorText.classList.remove('hidden');
    }

    function openAdminPhoneOtpModal() {
        if (!adminPhoneOtpModal || !adminPhoneInput) {
            return;
        }

        var pendingPhone = (adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
        if (pendingPhone === '') {
            openAdminPhoneChangeModal('There is no pending phone change request.', true);
            return;
        }

        if (adminPhoneOtpModalDescription) {
            adminPhoneOtpModalDescription.classList.remove('is-error');
            adminPhoneOtpModalDescription.classList.add('is-success');
            adminPhoneOtpModalDescription.textContent = 'Enter the 6-digit OTP sent to WhatsApp number ' + pendingPhone + '.';
        }

        if (adminPhoneOtpInput) {
            adminPhoneOtpInput.value = '';
        }
        setAdminPhoneOtpError('');

        if (adminPhoneOtpModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneOtpModalCloseTimer);
            adminPhoneOtpModalCloseTimer = null;
        }

        adminPhoneOtpModal.classList.remove('hidden', 'is-closing');
        void adminPhoneOtpModal.offsetWidth;
        adminPhoneOtpModal.classList.add('is-open');

        if (adminPhoneOtpInput) {
            window.setTimeout(function() {
                adminPhoneOtpInput.focus();
            }, 40);
        }
    }

    function closeAdminPhoneOtpModal() {
        if (!adminPhoneOtpModal || adminPhoneOtpModal.classList.contains('hidden')) {
            return;
        }

        adminPhoneOtpModal.classList.remove('is-open');
        adminPhoneOtpModal.classList.add('is-closing');

        if (adminPhoneOtpModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneOtpModalCloseTimer);
        }

        adminPhoneOtpModalCloseTimer = window.setTimeout(function() {
            adminPhoneOtpModal.classList.add('hidden');
            adminPhoneOtpModal.classList.remove('is-closing');
            adminPhoneOtpModalCloseTimer = null;
        }, 220);
    }

    function bindAdminPhonePendingActions() {
        if (adminPhoneChangeVerifyLink && adminPhoneChangeVerifyLink.getAttribute('data-bound') !== '1') {
            adminPhoneChangeVerifyLink.setAttribute('data-bound', '1');
            adminPhoneChangeVerifyLink.addEventListener('click', function(e) {
                e.preventDefault();
                openAdminPhoneOtpModal();
            });
        }

        if (!adminPhoneChangeCancelBtn || !adminPhoneInput) {
            return;
        }

        if (adminPhoneChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        adminPhoneChangeCancelBtn.setAttribute('data-bound', '1');
        adminPhoneChangeCancelBtn.addEventListener('click', function() {
            var rollbackPhone = (adminPhoneInput.getAttribute('data-current-phone') || '').trim();
            var previousPendingPhone = (adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
            adminPhoneInput.value = rollbackPhone;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/employer/change-phone/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentPhone = (data.current_phone || adminPhoneInput.getAttribute('data-current-phone') || '').trim();
                adminPhoneInput.value = currentPhone;
                adminPhoneInput.setAttribute('data-current-phone', currentPhone);
                adminPhoneInput.setAttribute('data-pending-phone', '');
                setAdminPendingPhoneState('');
                closeAdminPhoneOtpModal();
                openAdminPhoneChangeModal(data.message || 'Phone change request has been canceled.', false);
            })
            .catch(function(error) {
                adminPhoneInput.setAttribute('data-pending-phone', previousPendingPhone);
                setAdminPendingPhoneState(previousPendingPhone);
                openAdminPhoneChangeModal(error.message || 'Failed to cancel phone change request.', true);
            });
        });
    }

    bindAdminCancelEmailButton();
    bindAdminPhonePendingActions();

    if (adminPhoneOtpInput) {
        adminPhoneOtpInput.addEventListener('input', function() {
            var digits = adminPhoneOtpInput.value.replace(/\D+/g, '').slice(0, 6);
            if (adminPhoneOtpInput.value !== digits) {
                adminPhoneOtpInput.value = digits;
            }
            if (digits.length > 0) {
                setAdminPhoneOtpError('');
            }
        });
    }

    if (adminPhoneOtpVerifyBtn && adminPhoneInput) {
        adminPhoneOtpVerifyBtn.addEventListener('click', function() {
            var otp = adminPhoneOtpInput ? adminPhoneOtpInput.value.replace(/\D+/g, '') : '';
            if (otp.length !== 6) {
                setAdminPhoneOtpError('Please enter a valid 6-digit OTP.');
                return;
            }

            var submitBtnText = adminPhoneOtpVerifyBtn.textContent;
            adminPhoneOtpVerifyBtn.disabled = true;
            adminPhoneOtpVerifyBtn.textContent = 'Verifying...';
            setAdminPhoneOtpError('');

            var formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('otp', otp);

            fetch('/employer/change-phone/verify-otp', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var verifiedPhone = (data.phone_number || adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
                adminPhoneInput.value = verifiedPhone;
                adminPhoneInput.setAttribute('data-current-phone', verifiedPhone);
                adminPhoneInput.setAttribute('data-pending-phone', '');
                setAdminPendingPhoneState('');
                closeAdminPhoneOtpModal();
                openAdminPhoneChangeModal(data.message || 'Phone number has been updated successfully.', false);
            })
            .catch(function(error) {
                setAdminPhoneOtpError(error.message || 'Failed to verify OTP.');
            })
            .finally(function() {
                adminPhoneOtpVerifyBtn.disabled = false;
                adminPhoneOtpVerifyBtn.textContent = submitBtnText;
            });
        });
    }

    if (adminProfileForm && adminEmailInput && adminPhoneInput) {
        adminProfileForm.addEventListener('submit', function(e) {
            var currentEmail = (adminEmailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var pendingEmail = (adminEmailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            var newEmail = adminEmailInput.value.toLowerCase().trim();
            var currentPhone = (adminPhoneInput.getAttribute('data-current-phone') || '').trim();
            var pendingPhone = (adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
            var newPhone = adminPhoneInput.value.trim();
            var normalizedCurrentPhone = normalizePhoneNumber(currentPhone);
            var normalizedPendingPhone = normalizePhoneNumber(pendingPhone);
            var normalizedNewPhone = normalizePhoneNumber(newPhone);

            if (newEmail !== '' && newEmail !== currentEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var emailFormData = new FormData();
                emailFormData.append('_token', csrfToken);
                emailFormData.append('new_email', newEmail);

                fetch('/employer/change-email', {
                    method: 'POST',
                    body: emailFormData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    var oldEmail = (data.old_email || currentEmail).toLowerCase().trim();
                    var requestedEmail = (data.new_email || newEmail).toLowerCase().trim();
                    adminEmailInput.value = oldEmail;
                    adminEmailInput.setAttribute('data-current-email', oldEmail);
                    adminEmailInput.setAttribute('data-pending-email', requestedEmail);
                    setAdminPendingEmailState(requestedEmail);
                    openAdminEmailChangeModal('Change pending. Open the link sent to you at ' + requestedEmail + '.', false);
                })
                .catch(function(error) {
                    openAdminEmailChangeModal(error.message || 'An error occurred while sending confirmation. Please try again later.', true);
                });

                return;
            }

            if (pendingEmail !== '' && newEmail === pendingEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();
                adminEmailInput.value = currentEmail;
                openAdminEmailChangeModal('Change pending. Open the link sent to you at ' + pendingEmail + '.', false);
                return;
            }

            if (newPhone !== '' && normalizedNewPhone !== '' && normalizedNewPhone !== normalizedCurrentPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var phoneFormData = new FormData();
                phoneFormData.append('_token', csrfToken);
                phoneFormData.append('new_phone', newPhone);

                fetch('/employer/change-phone/send-otp', {
                    method: 'POST',
                    body: phoneFormData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    var oldPhone = (data.old_phone || currentPhone).trim();
                    var requestedPhone = (data.new_phone || newPhone).trim();
                    adminPhoneInput.value = oldPhone;
                    adminPhoneInput.setAttribute('data-current-phone', oldPhone);
                    adminPhoneInput.setAttribute('data-pending-phone', requestedPhone);
                    setAdminPendingPhoneState(requestedPhone);
                    openAdminPhoneChangeModal(data.message || 'OTP has been sent to your WhatsApp number.', false);
                })
                .catch(function(error) {
                    openAdminPhoneChangeModal(error.message || 'An error occurred while sending OTP. Please try again later.', true);
                });

                return;
            }

            if (pendingPhone !== '' && normalizedPendingPhone !== '' && normalizedNewPhone === normalizedPendingPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();
                adminPhoneInput.value = currentPhone;
                openAdminPhoneChangeModal('Change pending. Click "Enter OTP code" to complete verification.', false);
            }
        });
    }

    if (adminEmailChangeModalOkBtn) {
        adminEmailChangeModalOkBtn.addEventListener('click', function() {
            closeAdminEmailChangeModal();
        });
    }

    if (adminPhoneChangeModalOkBtn) {
        adminPhoneChangeModalOkBtn.addEventListener('click', function() {
            closeAdminPhoneChangeModal();
        });
    }

    if (adminPhoneOtpCancelBtn) {
        adminPhoneOtpCancelBtn.addEventListener('click', function() {
            closeAdminPhoneOtpModal();
        });
    }

    if (adminEmailChangeFeedbackModal) {
        adminEmailChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === adminEmailChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeAdminEmailChangeModal();
            }
        });
    }

    if (adminPhoneChangeFeedbackModal) {
        adminPhoneChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === adminPhoneChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeAdminPhoneChangeModal();
            }
        });
    }

    if (adminPhoneOtpModal) {
        adminPhoneOtpModal.addEventListener('click', function(e) {
            if (e.target === adminPhoneOtpModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeAdminPhoneOtpModal();
            }
        });
    }
})();
</script>

@endsection

@section('scripts')
<script>
(function() {
    var profileForm = document.getElementById('profileForm');
    var ownProfileModeInput = profileForm ? profileForm.querySelector('input[name="is_editing_own_profile"]') : null;
    var isEditingOwnProfile = !ownProfileModeInput || (ownProfileModeInput.value || '1') === '1';
    var emailInput = document.getElementById('accountFamilyEmail');
    var emailInputWrap = document.getElementById('emailInputWrap');
    var emailPendingState = document.getElementById('emailPendingState');
    var emailChangeCancelBtn = document.getElementById('emailChangeCancelBtn');
    var emailChangeFeedbackModal = document.getElementById('emailChangeFeedbackModal');
    var emailChangeModalCard = document.querySelector('#emailChangeFeedbackModal .message-modal-card');
    var emailChangeModalText = document.getElementById('emailChangeModalText');
    var emailChangeModalOkBtn = document.getElementById('emailChangeModalOkBtn');
    var phoneInput = document.getElementById('accountFamilyPhone');
    var phoneInputWrap = document.getElementById('phoneInputWrap');
    var phonePendingState = document.getElementById('phonePendingState');
    var phoneChangeCancelBtn = document.getElementById('phoneChangeCancelBtn');
    var phoneChangeVerifyLink = document.getElementById('phoneChangeVerifyLink');
    var phoneChangeFeedbackModal = document.getElementById('phoneChangeFeedbackModal');
    var phoneChangeModalCard = document.querySelector('#phoneChangeFeedbackModal .message-modal-card');
    var phoneChangeModalText = document.getElementById('phoneChangeModalText');
    var phoneChangeModalOkBtn = document.getElementById('phoneChangeModalOkBtn');
    var phoneOtpModal = document.getElementById('phoneOtpModal');
    var phoneOtpModalDescription = document.getElementById('phoneOtpModalDescription');
    var phoneOtpInput = document.getElementById('phoneOtpInput');
    var phoneOtpErrorText = document.getElementById('phoneOtpErrorText');
    var phoneOtpCancelBtn = document.getElementById('phoneOtpCancelBtn');
    var phoneOtpVerifyBtn = document.getElementById('phoneOtpVerifyBtn');
    var accountPasswordForm = document.getElementById('accountPasswordForm');
    var passwordChangeFeedbackModal = document.getElementById('passwordChangeFeedbackModal');
    var passwordChangeModalCard = document.querySelector('#passwordChangeFeedbackModal .message-modal-card');
    var passwordChangeModalText = document.getElementById('passwordChangeModalText');
    var passwordChangeModalOkBtn = document.getElementById('passwordChangeModalOkBtn');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var emailChangeModalCloseTimer = null;
    var phoneChangeModalCloseTimer = null;
    var phoneOtpModalCloseTimer = null;
    var passwordChangeModalCloseTimer = null;
    var addSocialMediaRowBtn = document.getElementById('addSocialMediaRowBtn');
    var accountNewSocialRows = document.getElementById('accountNewSocialRows');
    var socialMediaLimitNotice = document.getElementById('socialMediaLimitNotice');
    var passwordToggleButtons = document.querySelectorAll('.account-password-toggle');
    var socialMediaOptions = <?php echo isset($socialMediaOptionRowsJson) ? $socialMediaOptionRowsJson : '[]'; ?>;
    var maxSocialMediaPerMember = parseInt('<?php echo e((int) ($maxSocialMediaPerMember ?? 3)); ?>', 10) || 3;
    var socialMediaOptionsById = {};

    if (Array.isArray(socialMediaOptions)) {
        socialMediaOptions.forEach(function(option) {
            var optionId = parseInt(option.id, 10) || 0;
            var optionName = String(option.name || '').trim();
            if (optionId > 0 && optionName !== '') {
                socialMediaOptionsById[optionId] = optionName;
            }
        });
    }

    function formatSocialMediaOptionLabel(rawName) {
        var value = String(rawName || '').trim();
        if (!value) {
            return '';
        }

        var lower = value.toLowerCase();
        var labelMap = {
            youtube: 'YouTube',
            linkedin: 'LinkedIn',
            tiktok: 'TikTok',
            wechat: 'WeChat',
            thread: 'Threads',
            threads: 'Threads',
            x: 'X'
        };

        if (labelMap[lower]) {
            return labelMap[lower];
        }

        return lower.replace(/\b\w/g, function(ch) {
            return ch.toUpperCase();
        });
    }

    function setPasswordToggleState(toggleButton, isVisible) {
        if (!toggleButton) {
            return;
        }

        var showIcon = toggleButton.querySelector('.icon-show');
        var hideIcon = toggleButton.querySelector('.icon-hide');
        var targetId = toggleButton.getAttribute('data-target') || '';
        var targetInput = targetId ? document.getElementById(targetId) : null;
        var baseLabel = 'password';
        if (targetInput && targetInput.id === 'accountCurrentPassword') {
            baseLabel = 'current password';
        } else if (targetInput && targetInput.id === 'accountNewPassword') {
            baseLabel = 'new password';
        } else if (targetInput && targetInput.id === 'accountNewPasswordConfirmation') {
            baseLabel = 'confirmation password';
        }

        toggleButton.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
        toggleButton.setAttribute('aria-label', (isVisible ? 'Hide ' : 'Show ') + baseLabel);
        toggleButton.setAttribute('title', isVisible ? 'Hide password' : 'Show password');

        if (showIcon) {
            showIcon.classList.toggle('hidden', isVisible);
        }
        if (hideIcon) {
            hideIcon.classList.toggle('hidden', !isVisible);
        }
    }

    function bindPasswordToggles() {
        if (!passwordToggleButtons || passwordToggleButtons.length === 0) {
            return;
        }

        passwordToggleButtons.forEach(function(toggleButton) {
            if (!toggleButton || toggleButton.getAttribute('data-bound') === '1') {
                return;
            }

            var targetId = toggleButton.getAttribute('data-target') || '';
            var targetInput = targetId ? document.getElementById(targetId) : null;
            if (!targetInput) {
                return;
            }

            setPasswordToggleState(toggleButton, targetInput.type === 'text');
            toggleButton.setAttribute('data-bound', '1');
            toggleButton.addEventListener('click', function() {
                var isVisible = targetInput.type === 'text';
                targetInput.type = isVisible ? 'password' : 'text';
                setPasswordToggleState(toggleButton, !isVisible);
                targetInput.focus();
            });
        });
    }

    function toggleNewSocialRowsVisibility() {
        if (!accountNewSocialRows) {
            return;
        }

        if (accountNewSocialRows.children.length > 0) {
            accountNewSocialRows.classList.remove('hidden');
        } else {
            accountNewSocialRows.classList.add('hidden');
        }

        updateAddSocialMediaRowButtonState();
    }

    function getCurrentSocialRowCount() {
        if (!accountNewSocialRows) {
            return 0;
        }

        return accountNewSocialRows.querySelectorAll('.account-new-social-row').length;
    }

    function updateAddSocialMediaRowButtonState() {
        if (!addSocialMediaRowBtn) {
            return;
        }

        var hasOptions = Array.isArray(socialMediaOptions) && socialMediaOptions.length > 0;
        var hasReachedLimit = getCurrentSocialRowCount() >= maxSocialMediaPerMember;
        addSocialMediaRowBtn.disabled = !hasOptions || hasReachedLimit;
        addSocialMediaRowBtn.classList.toggle('hidden', hasReachedLimit);

        if (socialMediaLimitNotice) {
            if (hasReachedLimit) {
                socialMediaLimitNotice.classList.remove('hidden');
            } else {
                socialMediaLimitNotice.classList.add('hidden');
            }
        }
    }

    bindPasswordToggles();

    function normalizeSocialMediaName(rawName) {
        return String(rawName || '').trim().toLowerCase().replace(/\s+/g, '');
    }

    function getSocialSelectVisual(rawName) {
        var name = normalizeSocialMediaName(rawName);
        var visualMap = {
            instagram: { label: 'IG', bg: '#fde8f2', color: '#b4236f' },
            facebook: { label: 'FB', bg: '#e8f0ff', color: '#1c4ed8' },
            tiktok: { label: 'TT', bg: '#e9fbf7', color: '#047857' },
            youtube: { label: 'YT', bg: '#feeceb', color: '#b42318' },
            linkedin: { label: 'IN', bg: '#eaf5ff', color: '#0b66c2' },
            whatsapp: { label: 'WA', bg: '#ebfbf0', color: '#15803d' },
            telegram: { label: 'TG', bg: '#e6f6ff', color: '#0d6ea8' },
            x: { label: 'X', bg: '#f3f4f6', color: '#111827' },
            thread: { label: 'TH', bg: '#f3f4f6', color: '#111827' },
            threads: { label: 'TH', bg: '#f3f4f6', color: '#111827' },
            wechat: { label: 'WC', bg: '#ebfff3', color: '#166534' }
        };

        if (visualMap[name]) {
            return visualMap[name];
        }

        return { label: 'SM', bg: '#edf3f7', color: '#24506c' };
    }

    function updateSocialSelectVisual(select) {
        if (!select) {
            return;
        }

        var selectWrap = select.closest('.account-social-select-wrap');
        if (!selectWrap) {
            return;
        }

        var selectedSocialId = parseInt(select.value, 10) || 0;
        var selectedSocialName = selectedSocialId > 0 ? String(socialMediaOptionsById[selectedSocialId] || '') : '';
        var visual = getSocialSelectVisual(selectedSocialName);

        selectWrap.setAttribute('data-platform-label', visual.label);
        selectWrap.style.setProperty('--social-badge-bg', visual.bg);
        selectWrap.style.setProperty('--social-badge-color', visual.color);
    }

    function bindSocialSelects(scope) {
        var root = scope && scope.querySelectorAll ? scope : document;
        var selectElements = root.querySelectorAll('.account-social-row-select');
        selectElements.forEach(function(select) {
            if (select.dataset.socialSelectBound !== '1') {
                select.dataset.socialSelectBound = '1';
                select.addEventListener('change', function() {
                    updateSocialSelectVisual(select);
                });
            }

            updateSocialSelectVisual(select);
        });
    }

    function createSocialMediaSelect(selectedIdValue) {
        var select = document.createElement('select');
        select.name = 'social_row_ids[]';
        select.className = 'account-social-row-select';
        select.required = true;

        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select social media';
        select.appendChild(defaultOption);

        var selectedId = parseInt(selectedIdValue, 10) || 0;
        socialMediaOptions.forEach(function(option) {
            var optionId = parseInt(option.id, 10) || 0;
            var optionName = String(option.name || '').trim();
            if (optionId <= 0 || optionName === '') {
                return;
            }

            var optionElement = document.createElement('option');
            optionElement.value = String(optionId);
            optionElement.textContent = formatSocialMediaOptionLabel(optionName);
            if (optionId === selectedId) {
                optionElement.selected = true;
            }

            select.appendChild(optionElement);
        });

        return select;
    }

    function createNewSocialRow(socialIdValue, linkValue) {
        if (!accountNewSocialRows) {
            return null;
        }

        var row = document.createElement('div');
        row.className = 'account-new-social-row';
        var socialSelect = createSocialMediaSelect(socialIdValue);
        var socialSelectWrap = document.createElement('div');
        socialSelectWrap.className = 'account-new-social-field';
        var socialSelectShell = document.createElement('div');
        socialSelectShell.className = 'account-social-select-wrap';

        var socialLinkInput = document.createElement('input');
        socialLinkInput.type = 'text';
        socialLinkInput.name = 'social_row_links[]';
        socialLinkInput.className = 'account-social-row-link';
        socialLinkInput.placeholder = 'Profile link';
        socialLinkInput.value = linkValue || '';
        socialLinkInput.required = true;
        var socialLinkWrap = document.createElement('div');
        socialLinkWrap.className = 'account-new-social-field';

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-ghost account-remove-social-row-btn';
        removeBtn.textContent = 'Remove';

        socialSelectShell.appendChild(socialSelect);
        socialSelectWrap.appendChild(socialSelectShell);
        socialLinkWrap.appendChild(socialLinkInput);

        row.appendChild(socialSelectWrap);
        row.appendChild(socialLinkWrap);
        row.appendChild(removeBtn);

        accountNewSocialRows.appendChild(row);
        toggleNewSocialRowsVisibility();
        bindSocialSelects(row);

        return socialSelect;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openEmailChangeModal(message, isError) {
        if (!emailChangeFeedbackModal || !emailChangeModalText) {
            return;
        }

        if (emailChangeModalCloseTimer !== null) {
            window.clearTimeout(emailChangeModalCloseTimer);
            emailChangeModalCloseTimer = null;
        }

        if (emailChangeModalCard) {
            emailChangeModalCard.classList.remove('is-success', 'is-error');
            emailChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        emailChangeModalText.classList.remove('is-success', 'is-error');
        emailChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        emailChangeModalText.textContent = message || '';

        emailChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void emailChangeFeedbackModal.offsetWidth;
        emailChangeFeedbackModal.classList.add('is-open');
    }

    function closeEmailChangeModal() {
        if (!emailChangeFeedbackModal || emailChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        emailChangeFeedbackModal.classList.remove('is-open');
        emailChangeFeedbackModal.classList.add('is-closing');

        if (emailChangeModalCloseTimer !== null) {
            window.clearTimeout(emailChangeModalCloseTimer);
        }

        emailChangeModalCloseTimer = window.setTimeout(function() {
            emailChangeFeedbackModal.classList.add('hidden');
            emailChangeFeedbackModal.classList.remove('is-closing');
            emailChangeModalCloseTimer = null;
        }, 220);
    }

    function openPhoneChangeModal(message, isError) {
        if (!phoneChangeFeedbackModal || !phoneChangeModalText) {
            return;
        }

        if (phoneChangeModalCloseTimer !== null) {
            window.clearTimeout(phoneChangeModalCloseTimer);
            phoneChangeModalCloseTimer = null;
        }

        if (phoneChangeModalCard) {
            phoneChangeModalCard.classList.remove('is-success', 'is-error');
            phoneChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        phoneChangeModalText.classList.remove('is-success', 'is-error');
        phoneChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        phoneChangeModalText.textContent = message || '';

        phoneChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void phoneChangeFeedbackModal.offsetWidth;
        phoneChangeFeedbackModal.classList.add('is-open');
    }

    function closePhoneChangeModal() {
        if (!phoneChangeFeedbackModal || phoneChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        phoneChangeFeedbackModal.classList.remove('is-open');
        phoneChangeFeedbackModal.classList.add('is-closing');

        if (phoneChangeModalCloseTimer !== null) {
            window.clearTimeout(phoneChangeModalCloseTimer);
        }

        phoneChangeModalCloseTimer = window.setTimeout(function() {
            phoneChangeFeedbackModal.classList.add('hidden');
            phoneChangeFeedbackModal.classList.remove('is-closing');
            phoneChangeModalCloseTimer = null;
        }, 220);
    }

    function openPasswordChangeModal(message, isError) {
        if (!passwordChangeFeedbackModal || !passwordChangeModalText) {
            return;
        }

        if (passwordChangeModalCloseTimer !== null) {
            window.clearTimeout(passwordChangeModalCloseTimer);
            passwordChangeModalCloseTimer = null;
        }

        if (passwordChangeModalCard) {
            passwordChangeModalCard.classList.remove('is-success', 'is-error');
            passwordChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        passwordChangeModalText.classList.remove('is-success', 'is-error');
        passwordChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        passwordChangeModalText.textContent = message || '';

        passwordChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void passwordChangeFeedbackModal.offsetWidth;
        passwordChangeFeedbackModal.classList.add('is-open');
    }

    function closePasswordChangeModal() {
        if (!passwordChangeFeedbackModal || passwordChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        passwordChangeFeedbackModal.classList.remove('is-open');
        passwordChangeFeedbackModal.classList.add('is-closing');

        if (passwordChangeModalCloseTimer !== null) {
            window.clearTimeout(passwordChangeModalCloseTimer);
        }

        passwordChangeModalCloseTimer = window.setTimeout(function() {
            passwordChangeFeedbackModal.classList.add('hidden');
            passwordChangeFeedbackModal.classList.remove('is-closing');
            passwordChangeModalCloseTimer = null;
        }, 220);
    }

    function normalizePhoneNumber(value) {
        var digits = String(value || '').replace(/\D+/g, '');
        if (!digits) {
            return '';
        }

        if (digits.indexOf('0') === 0) {
            return '62' + digits.slice(1);
        }

        if (digits.indexOf('8') === 0) {
            return '62' + digits;
        }

        return digits;
    }

    function setPendingEmailState(pendingEmail) {
        if (!emailPendingState) {
            return;
        }

        if (!pendingEmail) {
            if (emailInputWrap) {
                emailInputWrap.classList.remove('hidden');
            }
            emailPendingState.classList.add('hidden');
            emailPendingState.innerHTML = '';
            return;
        }

        if (emailInputWrap) {
            emailInputWrap.classList.add('hidden');
        }

        emailPendingState.classList.remove('hidden');
        emailPendingState.innerHTML =
            '<span>Change pending. Open the link sent to you at ' + escapeHtml(pendingEmail) + '.</span>' +
            '<button id="emailChangeCancelBtn" type="button" class="email-cancel-btn">Cancel email change</button>';

        emailChangeCancelBtn = document.getElementById('emailChangeCancelBtn');
        bindCancelEmailButton();
    }

    function setPendingPhoneState(pendingPhone) {
        if (!phonePendingState) {
            return;
        }

        if (!pendingPhone) {
            if (phoneInputWrap) {
                phoneInputWrap.classList.remove('hidden');
            }
            phonePendingState.classList.add('hidden');
            phonePendingState.innerHTML = '';
            return;
        }

        if (phoneInputWrap) {
            phoneInputWrap.classList.add('hidden');
        }

        phonePendingState.classList.remove('hidden');
        phonePendingState.innerHTML =
            '<span>Change pending. Enter the OTP code sent to WhatsApp number ' + escapeHtml(pendingPhone) + '.</span>' +
            '<a id="phoneChangeVerifyLink" href="#" class="email-cancel-btn">Enter OTP code</a>' +
            '<button id="phoneChangeCancelBtn" type="button" class="email-cancel-btn">Cancel phone change</button>';

        phoneChangeVerifyLink = document.getElementById('phoneChangeVerifyLink');
        phoneChangeCancelBtn = document.getElementById('phoneChangeCancelBtn');
        bindPhonePendingActions();
    }

    function parseJsonResponse(response) {
        return response.text().then(function(text) {
            var data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                throw new Error(text || 'Invalid server response');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        });
    }

    function bindCancelEmailButton() {
        if (!emailChangeCancelBtn || !emailInput) {
            return;
        }

        if (emailChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        emailChangeCancelBtn.setAttribute('data-bound', '1');
        emailChangeCancelBtn.addEventListener('click', function() {
            var rollbackEmail = (emailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var previousPendingEmail = (emailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            emailInput.value = rollbackEmail;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/family/change-email/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentEmail = (data.current_email || emailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
                emailInput.value = currentEmail;
                emailInput.setAttribute('data-current-email', currentEmail);
                emailInput.setAttribute('data-pending-email', '');
                setPendingEmailState('');
                openEmailChangeModal(data.message || 'Email change request has been canceled.', false);
            })
            .catch(function(error) {
                emailInput.setAttribute('data-pending-email', previousPendingEmail);
                setPendingEmailState(previousPendingEmail);
                openEmailChangeModal(error.message || 'Failed to cancel email change request.', true);
            });
        });
    }

    function setPhoneOtpError(message) {
        if (!phoneOtpErrorText) {
            return;
        }

        if (!message) {
            phoneOtpErrorText.textContent = '';
            phoneOtpErrorText.classList.add('hidden');
            return;
        }

        phoneOtpErrorText.textContent = message;
        phoneOtpErrorText.classList.remove('hidden');
    }

    function openPhoneOtpModal() {
        if (!phoneOtpModal || !phoneInput) {
            return;
        }

        var pendingPhone = (phoneInput.getAttribute('data-pending-phone') || '').trim();
        if (pendingPhone === '') {
            openPhoneChangeModal('There is no pending phone change request.', true);
            return;
        }

        if (phoneOtpModalDescription) {
            phoneOtpModalDescription.classList.remove('is-error');
            phoneOtpModalDescription.classList.add('is-success');
            phoneOtpModalDescription.textContent = 'Enter the 6-digit OTP sent to WhatsApp number ' + pendingPhone + '.';
        }

        if (phoneOtpInput) {
            phoneOtpInput.value = '';
        }
        setPhoneOtpError('');

        if (phoneOtpModalCloseTimer !== null) {
            window.clearTimeout(phoneOtpModalCloseTimer);
            phoneOtpModalCloseTimer = null;
        }

        phoneOtpModal.classList.remove('hidden', 'is-closing');
        void phoneOtpModal.offsetWidth;
        phoneOtpModal.classList.add('is-open');

        if (phoneOtpInput) {
            window.setTimeout(function() {
                phoneOtpInput.focus();
            }, 40);
        }
    }

    function closePhoneOtpModal() {
        if (!phoneOtpModal || phoneOtpModal.classList.contains('hidden')) {
            return;
        }

        phoneOtpModal.classList.remove('is-open');
        phoneOtpModal.classList.add('is-closing');

        if (phoneOtpModalCloseTimer !== null) {
            window.clearTimeout(phoneOtpModalCloseTimer);
        }

        phoneOtpModalCloseTimer = window.setTimeout(function() {
            phoneOtpModal.classList.add('hidden');
            phoneOtpModal.classList.remove('is-closing');
            phoneOtpModalCloseTimer = null;
        }, 220);
    }

    function bindPhonePendingActions() {
        if (phoneChangeVerifyLink && phoneChangeVerifyLink.getAttribute('data-bound') !== '1') {
            phoneChangeVerifyLink.setAttribute('data-bound', '1');
            phoneChangeVerifyLink.addEventListener('click', function(e) {
                e.preventDefault();
                openPhoneOtpModal();
            });
        }

        if (!phoneChangeCancelBtn || !phoneInput) {
            return;
        }

        if (phoneChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        phoneChangeCancelBtn.setAttribute('data-bound', '1');
        phoneChangeCancelBtn.addEventListener('click', function() {
            var rollbackPhone = (phoneInput.getAttribute('data-current-phone') || '').trim();
            var previousPendingPhone = (phoneInput.getAttribute('data-pending-phone') || '').trim();
            phoneInput.value = rollbackPhone;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/family/change-phone/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentPhone = (data.current_phone || phoneInput.getAttribute('data-current-phone') || '').trim();
                phoneInput.value = currentPhone;
                phoneInput.setAttribute('data-current-phone', currentPhone);
                phoneInput.setAttribute('data-pending-phone', '');
                setPendingPhoneState('');
                closePhoneOtpModal();
                openPhoneChangeModal(data.message || 'Phone change request has been canceled.', false);
            })
            .catch(function(error) {
                phoneInput.setAttribute('data-pending-phone', previousPendingPhone);
                setPendingPhoneState(previousPendingPhone);
                openPhoneChangeModal(error.message || 'Failed to cancel phone change request.', true);
            });
        });
    }

    if (isEditingOwnProfile) {
        bindCancelEmailButton();
        bindPhonePendingActions();
    }

    if (addSocialMediaRowBtn && accountNewSocialRows) {
        addSocialMediaRowBtn.addEventListener('click', function() {
            if (!Array.isArray(socialMediaOptions) || socialMediaOptions.length === 0) {
                return;
            }
            if (getCurrentSocialRowCount() >= maxSocialMediaPerMember) {
                updateAddSocialMediaRowButtonState();
                return;
            }

            var socialSelectInput = createNewSocialRow('', '');
            if (socialSelectInput) {
                socialSelectInput.focus();
            }
        });

        accountNewSocialRows.addEventListener('click', function(event) {
            var target = event.target;
            if (!target || !target.classList || !target.classList.contains('account-remove-social-row-btn')) {
                return;
            }

            var row = target.closest('.account-new-social-row');
            if (row && row.parentNode) {
                row.parentNode.removeChild(row);
                toggleNewSocialRowsVisibility();
            }
        });

        bindSocialSelects(accountNewSocialRows);
        toggleNewSocialRowsVisibility();
        updateAddSocialMediaRowButtonState();
    }

    if (isEditingOwnProfile && phoneOtpInput) {
        phoneOtpInput.addEventListener('input', function() {
            var digits = phoneOtpInput.value.replace(/\D+/g, '').slice(0, 6);
            if (phoneOtpInput.value !== digits) {
                phoneOtpInput.value = digits;
            }
            if (digits.length > 0) {
                setPhoneOtpError('');
            }
        });
    }

    if (isEditingOwnProfile && phoneOtpVerifyBtn && phoneInput) {
        phoneOtpVerifyBtn.addEventListener('click', function() {
            var otp = phoneOtpInput ? phoneOtpInput.value.replace(/\D+/g, '') : '';
            if (otp.length !== 6) {
                setPhoneOtpError('Please enter a valid 6-digit OTP.');
                return;
            }

            var submitBtnText = phoneOtpVerifyBtn.textContent;
            phoneOtpVerifyBtn.disabled = true;
            phoneOtpVerifyBtn.textContent = 'Verifying...';
            setPhoneOtpError('');

            var formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('otp', otp);

            fetch('/family/change-phone/verify-otp', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var verifiedPhone = (data.phone_number || phoneInput.getAttribute('data-pending-phone') || '').trim();
                phoneInput.value = verifiedPhone;
                phoneInput.setAttribute('data-current-phone', verifiedPhone);
                phoneInput.setAttribute('data-pending-phone', '');
                setPendingPhoneState('');
                closePhoneOtpModal();
                openPhoneChangeModal(data.message || 'Phone number has been updated successfully.', false);
            })
            .catch(function(error) {
                setPhoneOtpError(error.message || 'Failed to verify OTP.');
            })
            .finally(function() {
                phoneOtpVerifyBtn.disabled = false;
                phoneOtpVerifyBtn.textContent = submitBtnText;
            });
        });
    }

    if (isEditingOwnProfile && profileForm && emailInput && phoneInput) {
        profileForm.addEventListener('submit', function(e) {
            var currentEmail = (emailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var pendingEmail = (emailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            var newEmail = emailInput.value.toLowerCase().trim();
            var currentPhone = (phoneInput.getAttribute('data-current-phone') || '').trim();
            var pendingPhone = (phoneInput.getAttribute('data-pending-phone') || '').trim();
            var newPhone = phoneInput.value.trim();
            var normalizedCurrentPhone = normalizePhoneNumber(currentPhone);
            var normalizedPendingPhone = normalizePhoneNumber(pendingPhone);
            var normalizedNewPhone = normalizePhoneNumber(newPhone);

            if (newEmail !== '' && newEmail !== currentEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('new_email', newEmail);

                fetch('/family/change-email', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    if (data.message && data.old_email && data.new_email) {
                        var oldEmail = (data.old_email || currentEmail).toLowerCase().trim();
                        var requestedEmail = data.new_email.toLowerCase().trim();
                        emailInput.value = oldEmail;
                        emailInput.setAttribute('data-current-email', oldEmail);
                        emailInput.setAttribute('data-pending-email', requestedEmail);
                        setPendingEmailState(requestedEmail);
                        openEmailChangeModal('Change pending. Open the link sent to you at ' + data.new_email + '.', false);
                    } else {
                        openEmailChangeModal(data.message || 'An unexpected response was returned.', true);
                    }
                })
                .catch(function(error) {
                    openEmailChangeModal(error.message || 'An error occurred while sending confirmation. Please try again later.', true);
                });

                return;
            }

            if (pendingEmail !== '' && newEmail === pendingEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();
                emailInput.value = currentEmail;
                openEmailChangeModal('Change pending. Open the link sent to you at ' + pendingEmail + '.', false);
                return;
            }

            if (newPhone !== '' && normalizedNewPhone !== '' && normalizedNewPhone !== normalizedCurrentPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var phoneFormData = new FormData();
                phoneFormData.append('_token', csrfToken);
                phoneFormData.append('new_phone', newPhone);

                fetch('/family/change-phone/send-otp', {
                    method: 'POST',
                    body: phoneFormData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    var oldPhone = (data.old_phone || currentPhone).trim();
                    var requestedPhone = (data.new_phone || newPhone).trim();
                    phoneInput.value = oldPhone;
                    phoneInput.setAttribute('data-current-phone', oldPhone);
                    phoneInput.setAttribute('data-pending-phone', requestedPhone);
                    setPendingPhoneState(requestedPhone);
                    openPhoneChangeModal(data.message || 'OTP has been sent to your WhatsApp number.', false);
                })
                .catch(function(error) {
                    openPhoneChangeModal(error.message || 'An error occurred while sending OTP. Please try again later.', true);
                });

                return;
            }

            if (pendingPhone !== '' && normalizedPendingPhone !== '' && normalizedNewPhone === normalizedPendingPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();
                phoneInput.value = currentPhone;
                openPhoneChangeModal('Change pending. Click "Enter OTP code" to complete verification.', false);
            }
        });
    }

    if (emailChangeModalOkBtn) {
        emailChangeModalOkBtn.addEventListener('click', function() {
            closeEmailChangeModal();
        });
    }

    if (phoneChangeModalOkBtn) {
        phoneChangeModalOkBtn.addEventListener('click', function() {
            closePhoneChangeModal();
        });
    }

    if (phoneOtpCancelBtn) {
        phoneOtpCancelBtn.addEventListener('click', function() {
            closePhoneOtpModal();
        });
    }

    if (accountPasswordForm) {
        accountPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var submitButton = accountPasswordForm.querySelector('button[type="submit"]');
            var submitButtonText = submitButton ? submitButton.textContent : '';
            var formData = new FormData(accountPasswordForm);

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            }

            fetch('/account/password', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                accountPasswordForm.reset();
                passwordToggleButtons.forEach(function(toggleButton) {
                    var targetId = toggleButton.getAttribute('data-target') || '';
                    var targetInput = targetId ? document.getElementById(targetId) : null;
                    if (targetInput) {
                        targetInput.type = 'password';
                    }
                    setPasswordToggleState(toggleButton, false);
                });
                openPasswordChangeModal(data.message || 'Password updated successfully.', false);
            })
            .catch(function(error) {
                openPasswordChangeModal(error.message || 'Failed to update password.', true);
            })
            .finally(function() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = submitButtonText || 'Save Password';
                }
            });
        });
    }

    if (passwordChangeModalOkBtn) {
        passwordChangeModalOkBtn.addEventListener('click', function() {
            closePasswordChangeModal();
        });
    }

    if (emailChangeFeedbackModal) {
        emailChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === emailChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeEmailChangeModal();
            }
        });
    }

    if (phoneChangeFeedbackModal) {
        phoneChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === phoneChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closePhoneChangeModal();
            }
        });
    }

    if (phoneOtpModal) {
        phoneOtpModal.addEventListener('click', function(e) {
            if (e.target === phoneOtpModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closePhoneOtpModal();
            }
        });
    }

    if (passwordChangeFeedbackModal) {
        passwordChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === passwordChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closePasswordChangeModal();
            }
        });
    }
})();
</script>
<script>
(function() {
    var adminProfileForm = document.getElementById('adminProfileForm');
    if (!adminProfileForm) {
        return;
    }

    var adminEmailInput = document.getElementById('accountAdminEmail');
    var adminEmailInputWrap = document.getElementById('adminEmailInputWrap');
    var adminEmailPendingState = document.getElementById('adminEmailPendingState');
    var adminEmailChangeCancelBtn = document.getElementById('adminEmailChangeCancelBtn');
    var adminPhoneInput = document.getElementById('accountAdminPhone');
    var adminPhoneInputWrap = document.getElementById('adminPhoneInputWrap');
    var adminPhonePendingState = document.getElementById('adminPhonePendingState');
    var adminPhoneChangeCancelBtn = document.getElementById('adminPhoneChangeCancelBtn');
    var adminPhoneChangeVerifyLink = document.getElementById('adminPhoneChangeVerifyLink');
    var adminEmailChangeFeedbackModal = document.getElementById('adminEmailChangeFeedbackModal');
    var adminEmailChangeModalCard = document.querySelector('#adminEmailChangeFeedbackModal .message-modal-card');
    var adminEmailChangeModalText = document.getElementById('adminEmailChangeModalText');
    var adminEmailChangeModalOkBtn = document.getElementById('adminEmailChangeModalOkBtn');
    var adminPhoneChangeFeedbackModal = document.getElementById('adminPhoneChangeFeedbackModal');
    var adminPhoneChangeModalCard = document.querySelector('#adminPhoneChangeFeedbackModal .message-modal-card');
    var adminPhoneChangeModalText = document.getElementById('adminPhoneChangeModalText');
    var adminPhoneChangeModalOkBtn = document.getElementById('adminPhoneChangeModalOkBtn');
    var adminPhoneOtpModal = document.getElementById('adminPhoneOtpModal');
    var adminPhoneOtpModalDescription = document.getElementById('adminPhoneOtpModalDescription');
    var adminPhoneOtpInput = document.getElementById('adminPhoneOtpInput');
    var adminPhoneOtpErrorText = document.getElementById('adminPhoneOtpErrorText');
    var adminPhoneOtpCancelBtn = document.getElementById('adminPhoneOtpCancelBtn');
    var adminPhoneOtpVerifyBtn = document.getElementById('adminPhoneOtpVerifyBtn');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var adminEmailModalCloseTimer = null;
    var adminPhoneModalCloseTimer = null;
    var adminPhoneOtpModalCloseTimer = null;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizePhoneNumber(value) {
        var digits = String(value || '').replace(/\D+/g, '');
        if (!digits) {
            return '';
        }
        if (digits.indexOf('0') === 0) {
            return '62' + digits.slice(1);
        }
        if (digits.indexOf('8') === 0) {
            return '62' + digits;
        }
        return digits;
    }

    function parseJsonResponse(response) {
        return response.text().then(function(text) {
            var data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                throw new Error(text || 'Invalid server response');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        });
    }

    function openAdminEmailChangeModal(message, isError) {
        if (!adminEmailChangeFeedbackModal || !adminEmailChangeModalText) {
            return;
        }

        if (adminEmailModalCloseTimer !== null) {
            window.clearTimeout(adminEmailModalCloseTimer);
            adminEmailModalCloseTimer = null;
        }

        if (adminEmailChangeModalCard) {
            adminEmailChangeModalCard.classList.remove('is-success', 'is-error');
            adminEmailChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        adminEmailChangeModalText.classList.remove('is-success', 'is-error');
        adminEmailChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        adminEmailChangeModalText.textContent = message || '';

        adminEmailChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void adminEmailChangeFeedbackModal.offsetWidth;
        adminEmailChangeFeedbackModal.classList.add('is-open');
    }

    function closeAdminEmailChangeModal() {
        if (!adminEmailChangeFeedbackModal || adminEmailChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        adminEmailChangeFeedbackModal.classList.remove('is-open');
        adminEmailChangeFeedbackModal.classList.add('is-closing');

        if (adminEmailModalCloseTimer !== null) {
            window.clearTimeout(adminEmailModalCloseTimer);
        }

        adminEmailModalCloseTimer = window.setTimeout(function() {
            adminEmailChangeFeedbackModal.classList.add('hidden');
            adminEmailChangeFeedbackModal.classList.remove('is-closing');
            adminEmailModalCloseTimer = null;
        }, 220);
    }

    function openAdminPhoneChangeModal(message, isError) {
        if (!adminPhoneChangeFeedbackModal || !adminPhoneChangeModalText) {
            return;
        }

        if (adminPhoneModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneModalCloseTimer);
            adminPhoneModalCloseTimer = null;
        }

        if (adminPhoneChangeModalCard) {
            adminPhoneChangeModalCard.classList.remove('is-success', 'is-error');
            adminPhoneChangeModalCard.classList.add(isError ? 'is-error' : 'is-success');
        }

        adminPhoneChangeModalText.classList.remove('is-success', 'is-error');
        adminPhoneChangeModalText.classList.add(isError ? 'is-error' : 'is-success');
        adminPhoneChangeModalText.textContent = message || '';

        adminPhoneChangeFeedbackModal.classList.remove('hidden', 'is-closing');
        void adminPhoneChangeFeedbackModal.offsetWidth;
        adminPhoneChangeFeedbackModal.classList.add('is-open');
    }

    function closeAdminPhoneChangeModal() {
        if (!adminPhoneChangeFeedbackModal || adminPhoneChangeFeedbackModal.classList.contains('hidden')) {
            return;
        }

        adminPhoneChangeFeedbackModal.classList.remove('is-open');
        adminPhoneChangeFeedbackModal.classList.add('is-closing');

        if (adminPhoneModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneModalCloseTimer);
        }

        adminPhoneModalCloseTimer = window.setTimeout(function() {
            adminPhoneChangeFeedbackModal.classList.add('hidden');
            adminPhoneChangeFeedbackModal.classList.remove('is-closing');
            adminPhoneModalCloseTimer = null;
        }, 220);
    }

    function setAdminPendingEmailState(pendingEmail) {
        if (!adminEmailPendingState) {
            return;
        }

        if (!pendingEmail) {
            if (adminEmailInputWrap) {
                adminEmailInputWrap.classList.remove('hidden');
            }
            adminEmailPendingState.classList.add('hidden');
            adminEmailPendingState.innerHTML = '';
            return;
        }

        if (adminEmailInputWrap) {
            adminEmailInputWrap.classList.add('hidden');
        }

        adminEmailPendingState.classList.remove('hidden');
        adminEmailPendingState.innerHTML =
            '<span>Change pending. Open the link sent to you at ' + escapeHtml(pendingEmail) + '.</span>' +
            '<button id="adminEmailChangeCancelBtn" type="button" class="email-cancel-btn">Cancel email change</button>';

        adminEmailChangeCancelBtn = document.getElementById('adminEmailChangeCancelBtn');
        bindAdminCancelEmailButton();
    }

    function setAdminPendingPhoneState(pendingPhone) {
        if (!adminPhonePendingState) {
            return;
        }

        if (!pendingPhone) {
            if (adminPhoneInputWrap) {
                adminPhoneInputWrap.classList.remove('hidden');
            }
            adminPhonePendingState.classList.add('hidden');
            adminPhonePendingState.innerHTML = '';
            return;
        }

        if (adminPhoneInputWrap) {
            adminPhoneInputWrap.classList.add('hidden');
        }

        adminPhonePendingState.classList.remove('hidden');
        adminPhonePendingState.innerHTML =
            '<span>Change pending. Enter the OTP code sent to WhatsApp number ' + escapeHtml(pendingPhone) + '.</span>' +
            '<a id="adminPhoneChangeVerifyLink" href="#" class="email-cancel-btn">Enter OTP code</a>' +
            '<button id="adminPhoneChangeCancelBtn" type="button" class="email-cancel-btn">Cancel phone change</button>';

        adminPhoneChangeVerifyLink = document.getElementById('adminPhoneChangeVerifyLink');
        adminPhoneChangeCancelBtn = document.getElementById('adminPhoneChangeCancelBtn');
        bindAdminPhonePendingActions();
    }

    function bindAdminCancelEmailButton() {
        if (!adminEmailChangeCancelBtn || !adminEmailInput) {
            return;
        }

        if (adminEmailChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        adminEmailChangeCancelBtn.setAttribute('data-bound', '1');
        adminEmailChangeCancelBtn.addEventListener('click', function() {
            var rollbackEmail = (adminEmailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var previousPendingEmail = (adminEmailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            adminEmailInput.value = rollbackEmail;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/employer/change-email/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentEmail = (data.current_email || adminEmailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
                adminEmailInput.value = currentEmail;
                adminEmailInput.setAttribute('data-current-email', currentEmail);
                adminEmailInput.setAttribute('data-pending-email', '');
                setAdminPendingEmailState('');
                openAdminEmailChangeModal(data.message || 'Email change request has been canceled.', false);
            })
            .catch(function(error) {
                adminEmailInput.setAttribute('data-pending-email', previousPendingEmail);
                setAdminPendingEmailState(previousPendingEmail);
                openAdminEmailChangeModal(error.message || 'Failed to cancel email change request.', true);
            });
        });
    }

    function setAdminPhoneOtpError(message) {
        if (!adminPhoneOtpErrorText) {
            return;
        }

        if (!message) {
            adminPhoneOtpErrorText.textContent = '';
            adminPhoneOtpErrorText.classList.add('hidden');
            return;
        }

        adminPhoneOtpErrorText.textContent = message;
        adminPhoneOtpErrorText.classList.remove('hidden');
    }

    function openAdminPhoneOtpModal() {
        if (!adminPhoneOtpModal || !adminPhoneInput) {
            return;
        }

        var pendingPhone = (adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
        if (pendingPhone === '') {
            openAdminPhoneChangeModal('There is no pending phone change request.', true);
            return;
        }

        if (adminPhoneOtpModalDescription) {
            adminPhoneOtpModalDescription.classList.remove('is-error');
            adminPhoneOtpModalDescription.classList.add('is-success');
            adminPhoneOtpModalDescription.textContent = 'Enter the 6-digit OTP sent to WhatsApp number ' + pendingPhone + '.';
        }

        if (adminPhoneOtpInput) {
            adminPhoneOtpInput.value = '';
        }
        setAdminPhoneOtpError('');

        if (adminPhoneOtpModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneOtpModalCloseTimer);
            adminPhoneOtpModalCloseTimer = null;
        }

        adminPhoneOtpModal.classList.remove('hidden', 'is-closing');
        void adminPhoneOtpModal.offsetWidth;
        adminPhoneOtpModal.classList.add('is-open');

        if (adminPhoneOtpInput) {
            window.setTimeout(function() {
                adminPhoneOtpInput.focus();
            }, 40);
        }
    }

    function closeAdminPhoneOtpModal() {
        if (!adminPhoneOtpModal || adminPhoneOtpModal.classList.contains('hidden')) {
            return;
        }

        adminPhoneOtpModal.classList.remove('is-open');
        adminPhoneOtpModal.classList.add('is-closing');

        if (adminPhoneOtpModalCloseTimer !== null) {
            window.clearTimeout(adminPhoneOtpModalCloseTimer);
        }

        adminPhoneOtpModalCloseTimer = window.setTimeout(function() {
            adminPhoneOtpModal.classList.add('hidden');
            adminPhoneOtpModal.classList.remove('is-closing');
            adminPhoneOtpModalCloseTimer = null;
        }, 220);
    }

    function bindAdminPhonePendingActions() {
        if (adminPhoneChangeVerifyLink && adminPhoneChangeVerifyLink.getAttribute('data-bound') !== '1') {
            adminPhoneChangeVerifyLink.setAttribute('data-bound', '1');
            adminPhoneChangeVerifyLink.addEventListener('click', function(e) {
                e.preventDefault();
                openAdminPhoneOtpModal();
            });
        }

        if (!adminPhoneChangeCancelBtn || !adminPhoneInput) {
            return;
        }

        if (adminPhoneChangeCancelBtn.getAttribute('data-bound') === '1') {
            return;
        }

        adminPhoneChangeCancelBtn.setAttribute('data-bound', '1');
        adminPhoneChangeCancelBtn.addEventListener('click', function() {
            var rollbackPhone = (adminPhoneInput.getAttribute('data-current-phone') || '').trim();
            var previousPendingPhone = (adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
            adminPhoneInput.value = rollbackPhone;

            var formData = new FormData();
            formData.append('_token', csrfToken);

            fetch('/employer/change-phone/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var currentPhone = (data.current_phone || adminPhoneInput.getAttribute('data-current-phone') || '').trim();
                adminPhoneInput.value = currentPhone;
                adminPhoneInput.setAttribute('data-current-phone', currentPhone);
                adminPhoneInput.setAttribute('data-pending-phone', '');
                setAdminPendingPhoneState('');
                closeAdminPhoneOtpModal();
                openAdminPhoneChangeModal(data.message || 'Phone change request has been canceled.', false);
            })
            .catch(function(error) {
                adminPhoneInput.setAttribute('data-pending-phone', previousPendingPhone);
                setAdminPendingPhoneState(previousPendingPhone);
                openAdminPhoneChangeModal(error.message || 'Failed to cancel phone change request.', true);
            });
        });
    }

    bindAdminCancelEmailButton();
    bindAdminPhonePendingActions();

    if (adminPhoneOtpInput) {
        adminPhoneOtpInput.addEventListener('input', function() {
            var digits = adminPhoneOtpInput.value.replace(/\D+/g, '').slice(0, 6);
            if (adminPhoneOtpInput.value !== digits) {
                adminPhoneOtpInput.value = digits;
            }
            if (digits.length > 0) {
                setAdminPhoneOtpError('');
            }
        });
    }

    if (adminPhoneOtpVerifyBtn && adminPhoneInput) {
        adminPhoneOtpVerifyBtn.addEventListener('click', function() {
            var otp = adminPhoneOtpInput ? adminPhoneOtpInput.value.replace(/\D+/g, '') : '';
            if (otp.length !== 6) {
                setAdminPhoneOtpError('Please enter a valid 6-digit OTP.');
                return;
            }

            var submitBtnText = adminPhoneOtpVerifyBtn.textContent;
            adminPhoneOtpVerifyBtn.disabled = true;
            adminPhoneOtpVerifyBtn.textContent = 'Verifying...';
            setAdminPhoneOtpError('');

            var formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('otp', otp);

            fetch('/employer/change-phone/verify-otp', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(parseJsonResponse)
            .then(function(data) {
                var verifiedPhone = (data.phone_number || adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
                adminPhoneInput.value = verifiedPhone;
                adminPhoneInput.setAttribute('data-current-phone', verifiedPhone);
                adminPhoneInput.setAttribute('data-pending-phone', '');
                setAdminPendingPhoneState('');
                closeAdminPhoneOtpModal();
                openAdminPhoneChangeModal(data.message || 'Phone number has been updated successfully.', false);
            })
            .catch(function(error) {
                setAdminPhoneOtpError(error.message || 'Failed to verify OTP.');
            })
            .finally(function() {
                adminPhoneOtpVerifyBtn.disabled = false;
                adminPhoneOtpVerifyBtn.textContent = submitBtnText;
            });
        });
    }

    if (adminProfileForm && adminEmailInput && adminPhoneInput) {
        adminProfileForm.addEventListener('submit', function(e) {
            var currentEmail = (adminEmailInput.getAttribute('data-current-email') || '').toLowerCase().trim();
            var pendingEmail = (adminEmailInput.getAttribute('data-pending-email') || '').toLowerCase().trim();
            var newEmail = adminEmailInput.value.toLowerCase().trim();
            var currentPhone = (adminPhoneInput.getAttribute('data-current-phone') || '').trim();
            var pendingPhone = (adminPhoneInput.getAttribute('data-pending-phone') || '').trim();
            var newPhone = adminPhoneInput.value.trim();
            var normalizedCurrentPhone = normalizePhoneNumber(currentPhone);
            var normalizedPendingPhone = normalizePhoneNumber(pendingPhone);
            var normalizedNewPhone = normalizePhoneNumber(newPhone);

            if (newEmail !== '' && newEmail !== currentEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var emailFormData = new FormData();
                emailFormData.append('_token', csrfToken);
                emailFormData.append('new_email', newEmail);

                fetch('/employer/change-email', {
                    method: 'POST',
                    body: emailFormData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    var oldEmail = (data.old_email || currentEmail).toLowerCase().trim();
                    var requestedEmail = (data.new_email || newEmail).toLowerCase().trim();
                    adminEmailInput.value = oldEmail;
                    adminEmailInput.setAttribute('data-current-email', oldEmail);
                    adminEmailInput.setAttribute('data-pending-email', requestedEmail);
                    setAdminPendingEmailState(requestedEmail);
                    openAdminEmailChangeModal('Change pending. Open the link sent to you at ' + requestedEmail + '.', false);
                })
                .catch(function(error) {
                    openAdminEmailChangeModal(error.message || 'An error occurred while sending confirmation. Please try again later.', true);
                });

                return;
            }

            if (pendingEmail !== '' && newEmail === pendingEmail) {
                e.preventDefault();
                e.stopImmediatePropagation();
                adminEmailInput.value = currentEmail;
                openAdminEmailChangeModal('Change pending. Open the link sent to you at ' + pendingEmail + '.', false);
                return;
            }

            if (newPhone !== '' && normalizedNewPhone !== '' && normalizedNewPhone !== normalizedCurrentPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var phoneFormData = new FormData();
                phoneFormData.append('_token', csrfToken);
                phoneFormData.append('new_phone', newPhone);

                fetch('/employer/change-phone/send-otp', {
                    method: 'POST',
                    body: phoneFormData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(parseJsonResponse)
                .then(function(data) {
                    var oldPhone = (data.old_phone || currentPhone).trim();
                    var requestedPhone = (data.new_phone || newPhone).trim();
                    adminPhoneInput.value = oldPhone;
                    adminPhoneInput.setAttribute('data-current-phone', oldPhone);
                    adminPhoneInput.setAttribute('data-pending-phone', requestedPhone);
                    setAdminPendingPhoneState(requestedPhone);
                    openAdminPhoneChangeModal(data.message || 'OTP has been sent to your WhatsApp number.', false);
                })
                .catch(function(error) {
                    openAdminPhoneChangeModal(error.message || 'An error occurred while sending OTP. Please try again later.', true);
                });

                return;
            }

            if (pendingPhone !== '' && normalizedPendingPhone !== '' && normalizedNewPhone === normalizedPendingPhone) {
                e.preventDefault();
                e.stopImmediatePropagation();
                adminPhoneInput.value = currentPhone;
                openAdminPhoneChangeModal('Change pending. Click "Enter OTP code" to complete verification.', false);
            }
        });
    }

    if (adminEmailChangeModalOkBtn) {
        adminEmailChangeModalOkBtn.addEventListener('click', function() {
            closeAdminEmailChangeModal();
        });
    }

    if (adminPhoneChangeModalOkBtn) {
        adminPhoneChangeModalOkBtn.addEventListener('click', function() {
            closeAdminPhoneChangeModal();
        });
    }

    if (adminPhoneOtpCancelBtn) {
        adminPhoneOtpCancelBtn.addEventListener('click', function() {
            closeAdminPhoneOtpModal();
        });
    }

    if (adminEmailChangeFeedbackModal) {
        adminEmailChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === adminEmailChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeAdminEmailChangeModal();
            }
        });
    }

    if (adminPhoneChangeFeedbackModal) {
        adminPhoneChangeFeedbackModal.addEventListener('click', function(e) {
            if (e.target === adminPhoneChangeFeedbackModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeAdminPhoneChangeModal();
            }
        });
    }

    if (adminPhoneOtpModal) {
        adminPhoneOtpModal.addEventListener('click', function(e) {
            if (e.target === adminPhoneOtpModal || (e.target.classList && e.target.classList.contains('message-modal-backdrop'))) {
                closeAdminPhoneOtpModal();
            }
        });
    }
})();
</script>
@endsection
