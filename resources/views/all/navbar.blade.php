<?php $settings = $systemSettings ?? ['website_name' => 'Family Tree System', 'logo_path' => '']; ?>
<?php $currentRoleId = (int) (session('authenticated_user.roleid') ?? 0); ?>
<?php $canAccessManagement = in_array($currentRoleId, [1, 2, 3], true); ?>
<header class="topbar">
    <div class="brand">

        <div class="brand-mark <?php echo e(!empty($settings['logo_path']) ? 'has-logo' : ''); ?>">
            <a href="/">
                <?php if (!empty($settings['logo_path'])): ?>
                    <img class="brand-logo" src="<?php echo e($settings['logo_path']); ?>" alt="Logo">
                <?php else: ?>
                    FT
                <?php endif; ?>
            </a>
        </div>
        <div>
            <h1><?php echo e($settings['website_name'] ?? 'Family Tree System'); ?></h1>
        </div>
    </div>

    <div class="actions">
        <?php if ($canAccessManagement): ?>
            <div class="menu-dropdown">
                <button class="btn btn-soft dropdown-toggle" type="button" data-dropdown-toggle aria-expanded="false">
                    Management
                </button>
                <div class="dropdown-menu" data-dropdown-menu>
                    <a href="/management/users" class="dropdown-item">User Management</a>
                    <?php if ($currentRoleId === 1): ?>
                        <a href="/setting" class="dropdown-item">Setting</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="menu-dropdown profile-dropdown">
            <button class="btn btn-ghost dropdown-toggle profile-toggle" type="button" data-dropdown-toggle aria-expanded="false">
                <span class="welcome-label">Welcome,</span>
                <strong><?php echo e(session('authenticated_user.username')); ?></strong>
            </button>
            <div class="dropdown-menu dropdown-menu-right" data-dropdown-menu>
                <a href="/account" class="dropdown-item">Account</a>
                <form class="dropdown-form" method="POST" action="/logout">
                    <?php echo csrf_field(); ?>
                    <button class="dropdown-item dropdown-submit" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
