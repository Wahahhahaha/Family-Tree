<div class="wrapper">

    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <section class="settings-card">
        <div class="settings-head">
            <h2>System Setting</h2>
            <p>Update website name and logo for the whole system.</p>
        </div>

        <?php if (session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if ($errors->any()): ?>
            <div class="alert-error">
                <?php foreach ($errors->all() as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div id="settingsAjaxAlert" class="alert-success hidden"></div>

        <form id="systemSettingsForm" method="POST" action="/setting" enctype="multipart/form-data" class="settings-form">
            <?php echo csrf_field(); ?>

            <div class="settings-field">
                <label for="websiteName">Website Name</label>
                <input
                    id="websiteName"
                    type="text"
                    name="website_name"
                    value="<?php echo e(old('website_name', $systemSettings['website_name'] ?? 'Family Tree System')); ?>"
                    placeholder="Enter website name"
                    required
                >
            </div>

            <div class="settings-field">
                <label for="systemLogoInput">System Logo</label>
                <input id="systemLogoInput" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg">
                <small>Allowed: jpg, jpeg, png, webp, svg (max 2MB)</small>
            </div>

            <div class="settings-preview">
                <span>Current Logo Preview</span>
                <div class="settings-logo-box">
                    <img
                        id="systemLogoPreview"
                        src="<?php echo e($systemSettings['logo_path'] ?? ''); ?>"
                        alt="System logo preview"
                        class="<?php echo e(empty($systemSettings['logo_path']) ? 'hidden' : ''); ?>"
                    >
                    <div id="systemLogoPlaceholder" class="logo-placeholder <?php echo e(!empty($systemSettings['logo_path']) ? 'hidden' : ''); ?>">
                        No logo uploaded
                    </div>
                </div>
            </div>

            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">Save Setting</button>
            </div>
        </form>
    </section>
</div>
