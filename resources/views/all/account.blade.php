<div class="wrapper">
    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <?php if ($canEditAdminProfile): ?>
        <div class="account-grid">
            <section class="account-card account-edit-card">
                <h2>Profile</h2>
                <p>Update your own account identity details.</p>

                <div id="accountAdminAjaxAlert" class="hidden"></div>

                <form id="adminProfileForm" method="POST" action="/account/profile" class="settings-form account-form">
                    <?php echo csrf_field(); ?>

                    <div class="settings-field">
                        <label for="accountUsernameReadonly">Username</label>
                        <input
                            id="accountUsernameReadonly"
                            type="text"
                            value="<?php echo e(session('authenticated_user.username')); ?>"
                            readonly
                            disabled
                        >
                        <small>Username cannot be changed.</small>
                    </div>

                    <div class="settings-field">
                        <label for="accountAdminName">Name</label>
                        <input
                            id="accountAdminName"
                            type="text"
                            name="name"
                            value="<?php echo e(old('name', $currentEmployerProfile->name ?? '')); ?>"
                            required
                        >
                    </div>

                    <div class="settings-field">
                        <label for="accountAdminEmail">Email</label>
                        <input
                            id="accountAdminEmail"
                            type="email"
                            name="email"
                            value="<?php echo e(old('email', $currentEmployerProfile->email ?? '')); ?>"
                            required
                        >
                    </div>

                    <div class="settings-field">
                        <label for="accountAdminPhone">Phone Number</label>
                        <input
                            id="accountAdminPhone"
                            type="text"
                            name="phonenumber"
                            value="<?php echo e(old('phonenumber', $currentEmployerProfile->phonenumber ?? '')); ?>"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </section>
        </div>
    <?php endif; ?>

    <?php if ($canEditOwnProfile): ?>
        <section class="account-card account-edit-card account-family-profile-card">
            <h2>Profile</h2>
            <p>Manage your personal profile details from this page.</p>

            <?php if (session('success')): ?>
                <div class="alert-success">
                    <div><?php echo e(session('success')); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($errors->any()): ?>
                <div class="alert-error">
                    <?php foreach ($errors->all() as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
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
                <small id="detailPhotoHint">Click photo to choose new profile picture.</small>
            </div>

            <form id="profileForm" method="POST" action="/family/profile" class="settings-form account-form account-form-grid" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="/account">
                <input id="profilePictureInput" type="file" name="picture" accept="image/*" class="hidden">

                <div class="settings-field">
                    <label for="accountFamilyUsername">Username</label>
                    <input id="accountFamilyUsername" type="text" value="<?php echo e(session('authenticated_user.username')); ?>" disabled>
                    <small>Username cannot be edited.</small>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyName">Name</label>
                    <input id="accountFamilyName" type="text" name="name" value="<?php echo e(old('name', $currentFamilyProfile->name ?? '')); ?>" placeholder="Enter your full name">
                </div>

                <div class="settings-field">
                    <label for="accountFamilyEmail">Email</label>
                    <input id="accountFamilyEmail" type="email" name="email" value="<?php echo e(old('email', $currentFamilyProfile->email ?? '')); ?>" placeholder="Enter your email">
                </div>

                <div class="settings-field">
                    <label for="accountFamilyPhone">Phone Number</label>
                    <input id="accountFamilyPhone" type="text" name="phonenumber" value="<?php echo e(old('phonenumber', $currentFamilyProfile->phonenumber ?? '')); ?>" placeholder="Enter your phone number">
                </div>

                <div class="settings-field">
                    <label for="accountFamilyGender">Gender</label>
                    <select id="accountFamilyGender" disabled>
                        <option value="">-</option>
                        <option value="male" <?php echo e(old('gender', $currentFamilyProfile->gender ?? '') === 'male' ? 'selected' : ''); ?>>Male</option>
                        <option value="female" <?php echo e(old('gender', $currentFamilyProfile->gender ?? '') === 'female' ? 'selected' : ''); ?>>Female</option>
                    </select>
                    <small>Gender cannot be edited.</small>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyBirthdate">Date of Birth</label>
                    <input id="accountFamilyBirthdate" type="date" value="<?php echo e(old('birthdate', $currentFamilyProfile->birthdate ?? '')); ?>" disabled>
                    <small>Date of birth cannot be edited.</small>
                </div>

                <div class="settings-field">
                    <label for="accountFamilyBirthplace">Birthplace</label>
                    <input id="accountFamilyBirthplace" type="text" value="<?php echo e(old('birthplace', $currentFamilyProfile->birthplace ?? '')); ?>" disabled>
                    <small>Birthplace cannot be edited.</small>
                </div>

                <div class="settings-field">
                    <label for="accountJob">Job</label>
                    <input id="accountJob" type="text" name="job" value="<?php echo e(old('job', $currentFamilyProfile->job ?? '')); ?>" placeholder="Example: Software Engineer">
                </div>

                <div class="settings-field">
                    <label for="accountAddress">Address</label>
                    <input id="accountAddress" type="text" name="address" value="<?php echo e(old('address', $currentFamilyProfile->address ?? '')); ?>" placeholder="Enter your full address">
                </div>

                <div class="settings-field">
                    <label for="accountEducation">Education</label>
                    <input id="accountEducation" type="text" name="education_status" value="<?php echo e(old('education_status', $currentFamilyProfile->education_status ?? '')); ?>" placeholder="Example: Bachelor Degree">
                </div>

                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>
        </section>

        <div id="photoCropModal" class="photo-crop-modal hidden" role="dialog" aria-modal="true" aria-labelledby="photoCropTitle">
            <div class="photo-crop-backdrop"></div>
            <div class="photo-crop-card">
                <h4 id="photoCropTitle">Crop Profile Photo</h4>
                <p>Move and zoom to adjust your photo.</p>
                <div class="photo-crop-stage-wrap">
                    <canvas id="photoCropCanvas" class="photo-crop-canvas" width="320" height="320"></canvas>
                </div>
                <div class="photo-crop-zoom">
                    <label for="photoCropZoom">Zoom</label>
                    <input id="photoCropZoom" type="range" min="1" max="3" step="0.01" value="1">
                </div>
                <div class="photo-crop-actions">
                    <button id="photoCropCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                    <button id="photoCropApplyBtn" type="button" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
