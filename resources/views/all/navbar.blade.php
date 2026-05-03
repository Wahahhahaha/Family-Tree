<?php $settings = $systemSettings ?? ['website_name' => 'Family Tree System', 'logo_path' => '']; ?>
<?php
    $storedLogoPath = trim((string) ($settings['logo_path'] ?? ''));
    $logoUrl = trim((string) ($settings['logo_url'] ?? ''));
    if ($logoUrl === '' && $storedLogoPath !== '') {
        $logoUrl = (preg_match('#^https?://#i', $storedLogoPath) || str_starts_with($storedLogoPath, 'data:'))
            ? $storedLogoPath
            : asset(ltrim($storedLogoPath, '/'));
    }
?>
<?php $currentRoleId = (int) (session('authenticated_user.roleid') ?? 0); ?>
<?php
    $currentUserId = (int) (session('authenticated_user.userid') ?? 0);
    $welcomeName = '';
    $permissionMenuOptions = [
        'user_management' => '/management/users',
        'activity_log' => '/management/activity-log',
        'recycle_bin' => '/management/recycle-bin',
        'backup_database' => '/management/backup-database',
        'validation' => '/management/validation',
        'permission' => '/management/permission',
        'data_master' => '/management/data-master',
        'setting' => '/management/setting',
    ];
    $permissionRoleKey = match ($currentRoleId) {
        1 => 'superadmin',
        2 => 'admin',
        3, 4 => 'family_member',
        default => '',
    };
    $permissionDefaults = [
        'superadmin' => [
            'data_master' => true,
            'user_management' => true,
            'activity_log' => true,
            'recycle_bin' => true,
            'backup_database' => true,
            'validation' => true,
            'permission' => true,
            'setting' => true,
        ],
        'admin' => [
            'data_master' => true,
            'user_management' => true,
            'activity_log' => true,
            'recycle_bin' => false,
            'backup_database' => false,
            'validation' => true,
            'permission' => false,
            'setting' => false,
        ],
        'family_member' => [
            'data_master' => false,
            'user_management' => true,
            'activity_log' => false,
            'recycle_bin' => false,
            'backup_database' => false,
            'validation' => false,
            'permission' => false,
            'setting' => false,
        ],
    ];
    $rolePermissions = $permissionRoleKey !== '' ? ($permissionDefaults[$permissionRoleKey] ?? []) : [];
    $permissionPath = storage_path('app/role_permissions.json');
    if (\Illuminate\Support\Facades\File::exists($permissionPath)) {
        $decodedPermission = json_decode((string) \Illuminate\Support\Facades\File::get($permissionPath), true);
        if (
            is_array($decodedPermission)
            && isset($decodedPermission['roles'][$permissionRoleKey])
            && is_array($decodedPermission['roles'][$permissionRoleKey])
        ) {
            foreach (array_keys($permissionMenuOptions) as $menuKey) {
                if (array_key_exists($menuKey, $decodedPermission['roles'][$permissionRoleKey])) {
                    $rolePermissions[$menuKey] = filter_var(
                        $decodedPermission['roles'][$permissionRoleKey][$menuKey],
                        FILTER_VALIDATE_BOOLEAN
                    );
                }
            }
        }
    }
    $managementLinks = [];
    foreach ($permissionMenuOptions as $menuKey => $menuUrl) {
        if (!empty($rolePermissions[$menuKey])) {
            $managementLinks[$menuKey] = $menuUrl;
        }
    }
    $canAccessManagement = count($managementLinks) > 0;

    if ($currentUserId !== 0) {
        $employerName = \Illuminate\Support\Facades\DB::table('employer')
            ->where('userid', $currentUserId)
            ->value('name');
        $familyName = \Illuminate\Support\Facades\DB::table('family_member')
            ->where('userid', $currentUserId)
            ->value('name');

        $welcomeName = trim((string) ($employerName ?? $familyName ?? ''));
    }

    if ($welcomeName === '') {
        $welcomeName = (string) (session('authenticated_user.username') ?? '');
    }
?>
<header class="topbar">
    <div class="brand">
        <div class="brand-mark <?php echo e($logoUrl !== '' ? 'has-logo' : ''); ?>">
            <a href="/">
                <?php if ($logoUrl !== ''): ?>
                    <img class="brand-logo" src="<?php echo e($logoUrl); ?>" alt="Logo">
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
        <a href="/" style="text-decoration:none;"><button class="btn btn-ghost" style="display: flex; align-items: center; gap: 8px; margin-right: 10px;">
            <i data-lucide="house" style="width: 18px;"></i> <span><?php echo e(__('common.home')); ?></span>
        </button></a>

        <div class="menu-dropdown family-dropdown">
            <button class="btn btn-soft dropdown-toggle" type="button" data-dropdown-toggle aria-expanded="false">
                <?php echo e(__('common.family_navigation')); ?>
            </button>
            <div class="dropdown-menu" data-dropdown-menu>
                <div class="dropdown-header">
                    <span><?php echo e(__('common.family_navigation')); ?></span>
                    <small><?php echo e(__('common.quick_access_family_features')); ?></small>
                </div>
                <a href="/tree" class="dropdown-item<?php echo request()->is('tree*') ? ' is-active' : ''; ?>"><i data-lucide="network"></i><span><?php echo e(__('common.tree')); ?></span></a>
                <a href="/wiki" class="dropdown-item<?php echo request()->is('wiki*') ? ' is-active' : ''; ?>"><i data-lucide="book-open"></i><span><?php echo e(__('common.wiki')); ?></span></a>
                <a href="/letters" class="dropdown-item<?php echo request()->is('letters*') ? ' is-active' : ''; ?>"><i data-lucide="mail"></i><span><?php echo e(__('common.letters')); ?></span></a>
                <a href="/gallery" class="dropdown-item<?php echo request()->is('gallery*') ? ' is-active' : ''; ?>"><i data-lucide="images"></i><span><?php echo e(__('common.gallery')); ?></span></a>
                <a href="/events" class="dropdown-item<?php echo request()->is('events*') ? ' is-active' : ''; ?>"><i data-lucide="calendar-check"></i><span><?php echo e(__('common.events')); ?></span></a>
                <a href="/calendar" class="dropdown-item<?php echo request()->is('calendar*') ? ' is-active' : ''; ?>"><i data-lucide="calendar"></i><span><?php echo e(__('common.calendar')); ?></span></a>
                <a href="/live-location" class="dropdown-item<?php echo request()->is('live-location*') ? ' is-active' : ''; ?>"><i data-lucide="map-pin"></i><span><?php echo e(__('common.live_location')); ?></span></a>
            </div>
        </div>

        <?php if (in_array($currentRoleId, [1, 2], true)): ?>
            <a href="/leader-succession" style="text-decoration:none;"><button class="btn btn-soft" style="display: flex; align-items: center; gap: 8px; margin-right: 10px;">
                <i data-lucide="crown" style="width: 18px;"></i> <span><?php echo e(__('common.leader_setting')); ?></span>
            </button></a>
        <?php endif; ?>

        <?php if ($canAccessManagement): ?>
            <div class="menu-dropdown family-dropdown">
                <button class="btn btn-soft dropdown-toggle" type="button" data-dropdown-toggle aria-expanded="false">
                    <?php echo e(__('common.management')); ?>
                </button>

                <div class="dropdown-menu" data-dropdown-menu>
                <div class="dropdown-header">
                    <span><?php echo e(__('common.management')); ?></span>
                    <small><?php echo e(__('common.quick_access_family_features')); ?></small>
                </div>
                    <?php if (!empty($managementLinks['data_master'])): ?>
                        <a href="<?php echo e($managementLinks['data_master']); ?>" class="dropdown-item"><i data-lucide="database"></i><span><?php echo e(__('common.data_master')); ?></span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['user_management'])): ?>
                        <a href="<?php echo e($managementLinks['user_management']); ?>" class="dropdown-item"><i data-lucide="users"></i><span><?php echo e(__('common.user_management')); ?></span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['activity_log'])): ?>
                        <a href="<?php echo e($managementLinks['activity_log']); ?>" class="dropdown-item"><i data-lucide="clipboard-list"></i><span><?php echo e(__('common.activity_log')); ?></span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['recycle_bin'])): ?>
                        <a href="<?php echo e($managementLinks['recycle_bin']); ?>" class="dropdown-item"><i data-lucide="trash-2"></i><span><?php echo e(__('common.recycle_bin')); ?></span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['backup_database'])): ?>
                        <a href="<?php echo e($managementLinks['backup_database']); ?>" class="dropdown-item"><i data-lucide="server"></i><span><?php echo e(__('common.backup_database')); ?></span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['validation'])): ?>
                        <a href="<?php echo e($managementLinks['validation']); ?>" class="dropdown-item"><i data-lucide="badge-check"></i><span><?php echo e(__('common.validation')); ?></span></a>
                    <?php endif; ?>
                    <?php if ($currentRoleId === 1): ?>
                        <a href="/management/console" class="dropdown-item"><i data-lucide="terminal"></i><span>Web Console</span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['permission'])): ?>
                        <a href="<?php echo e($managementLinks['permission']); ?>" class="dropdown-item"><i data-lucide="shield-check"></i><span><?php echo e(__('common.permission')); ?></span></a>
                    <?php endif; ?>
                    <?php if (!empty($managementLinks['setting']) || $currentRoleId === 2): ?>
                        <a href="<?php echo e($managementLinks['setting'] ?? '/management/setting'); ?>" class="dropdown-item"><i data-lucide="settings"></i><span><?php echo e(__('common.settings')); ?></span></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="menu-dropdown profile-dropdown">
            <button class="btn btn-ghost dropdown-toggle profile-toggle" type="button" data-dropdown-toggle aria-expanded="false">
                <span class="welcome-label"><?php echo e(__('common.welcome')); ?></span>
                <strong id="navbarWelcomeName"><?php echo e($welcomeName); ?></strong>
            </button>
            <div class="dropdown-menu dropdown-menu-right" data-dropdown-menu>
                <a href="/account" class="dropdown-item"><i data-lucide="user"></i><span><?php echo e(__('common.account')); ?></span></a>
                <form class="dropdown-form" method="POST" action="/logout">
                    <?php echo csrf_field(); ?>
                    <button class="dropdown-item dropdown-submit" type="submit"><i data-lucide="log-out" style="width: 16px; margin-right: 10px;"></i> <?php echo e(__('common.logout')); ?></button>
                </form>
            </div>
        </div>
    </div>
</header>
