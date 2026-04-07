<?php foreach ($users as $user): ?>
    <tr>
        <td><?php echo e($user->username); ?></td>
        <td><?php echo e($user->fullname); ?></td>
        <td><?php echo e($user->levelname ?? '-'); ?></td>
        <td><?php echo e($user->rolename ?? '-'); ?></td>
        <td><?php echo e($user->email); ?></td>
        <td><?php echo e($user->phone); ?></td>
        <td><?php echo e($user->source); ?></td>
        <td>
            <div class="action-group">
                <form method="POST" action="/management/users/<?php echo e($user->userid); ?>/reset-password">
                    <?php echo csrf_field(); ?>
                    <button class="action-btn action-reset" type="submit" title="Reset password to default (username)">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 5a7 7 0 1 1-6.57 4.6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M4 4v5h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
                <form method="POST" action="/management/users/<?php echo e($user->userid); ?>/delete" onsubmit="return confirm('Delete this user?');">
                    <?php echo csrf_field(); ?>
                    <button class="action-btn action-delete" type="submit" title="Delete user">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 7V5h6v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M7 7l1 12h8l1-12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 11v5M14 11v5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
