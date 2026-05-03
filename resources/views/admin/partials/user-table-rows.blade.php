<?php if ($users->isEmpty()): ?>
    <tr>
        <td colspan="8" style="text-align: center;"><?php echo e(__('management_users.no_users_found')); ?></td>
    </tr>
<?php else: ?>
    <?php foreach ($users as $user): ?>
        <tr data-user-row="1" data-userid="<?php echo e($user->userid); ?>">
            <td class="bulk-select-col">
                <input
                    id="bulkUserCheckbox<?php echo e($user->userid); ?>"
                    class="bulk-select-checkbox"
                    data-bulk-checkbox
                    type="checkbox"
                    value="<?php echo e($user->userid); ?>"
                    aria-label="<?php echo e(__('management_users.select_user', ['username' => $user->username])); ?>"
                >
            </td>
            <td><?php echo e($user->username); ?></td>
            <td><?php echo e($user->fullname); ?></td>
            <td><?php echo e($user->levelname ?? '-'); ?></td>
            <td><?php echo e($user->rolename ?? '-'); ?></td>
            <td><?php echo e($user->email); ?></td>
            <td><?php echo e($user->phone); ?></td>
            <td>
                <div class="action-group">
                    <form method="POST" action="/management/users/<?php echo e($user->userid); ?>/reset-password">
                        <?php echo csrf_field(); ?>
                        <button
                            class="action-btn action-reset js-open-reset-user-modal"
                            type="button"
                            title="<?php echo e(__('management_users.reset_password_action')); ?>"
                            onclick="return window.openResetUserModalFromButton && window.openResetUserModalFromButton(this);"
                            data-reset-action="/management/users/<?php echo e($user->userid); ?>/reset-password"
                            data-username="<?php echo e($user->username); ?>"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 5a7 7 0 1 1-6.57 4.6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M4 4v5h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                    <button
                        class="action-btn action-reset js-open-edit-user-modal"
                        type="button"
                            title="<?php echo e(__('management_users.edit_action')); ?>"
                        onclick="return window.openEditUserModalFromButton && window.openEditUserModalFromButton(this);"
                        data-userid="<?php echo e($user->userid); ?>"
                        data-username="<?php echo e($user->username); ?>"
                        data-fullname="<?php echo e($user->fullname); ?>"
                        data-email="<?php echo e($user->email === '-' ? '' : $user->email); ?>"
                        data-phone="<?php echo e($user->phone === '-' ? '' : $user->phone); ?>"
                        data-levelid="<?php echo e((string) ($user->levelid ?? '')); ?>"
                        data-levelname="<?php echo e((string) ($user->levelname ?? '')); ?>"
                        data-roleid="<?php echo e((string) ($user->roleid ?? '')); ?>"
                        data-rolename="<?php echo e((string) ($user->rolename ?? '')); ?>"
                        data-source="<?php echo e(strtolower((string) ($user->source ?? ''))); ?>"
                        data-bloodtype="<?php echo e((string) ($user->bloodtype ?? '')); ?>"
                        data-job="<?php echo e((string) ($user->job ?? '')); ?>"
                        data-education-status="<?php echo e((string) ($user->education_status ?? '')); ?>"
                        data-address="<?php echo e((string) ($user->address ?? '')); ?>"
                        data-gender="<?php echo e((string) ($user->gender ?? '')); ?>"
                        data-birthdate="<?php echo e((string) ($user->birthdate ?? '')); ?>"
                        data-birthplace="<?php echo e((string) ($user->birthplace ?? '')); ?>"
                        data-marital-status="<?php echo e((string) ($user->marital_status ?? '')); ?>"
                        data-life-status="<?php echo e((string) ($user->life_status ?? '')); ?>"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 20h4l10-10-4-4L4 16v4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M13 7l4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
