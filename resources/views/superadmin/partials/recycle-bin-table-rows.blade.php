<?php foreach ($users as $user): ?>
    <tr>
        <td><?php echo e($user->username); ?></td>
        <td><?php echo e($user->fullname); ?></td>
        <td><?php echo e($user->levelname ?? '-'); ?></td>
        <td><?php echo e($user->rolename ?? '-'); ?></td>
        <td><?php echo e($user->email); ?></td>
        <td><?php echo e($user->phone); ?></td>
        <td><?php echo e($user->deleted_at ?: '-'); ?></td>
        <td>
            <div class="action-group">
                <form method="POST" action="/management/users/<?php echo e($user->userid); ?>/restore">
                    <?php echo csrf_field(); ?>
                    <button class="action-btn action-restore" type="submit" title="Restore user">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 5v14M5 12l7-7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
                <form method="POST" action="/management/users/<?php echo e($user->userid); ?>/force-delete" onsubmit="return confirm('Permanently delete this user? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <button class="action-btn action-delete" type="submit" title="Permanently delete user">
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
