<?php if (isset($activityLogs) && method_exists($activityLogs, 'links')): ?>
    <div class="pagination-wrap">
        <?php echo e($activityLogs->links()); ?>
    </div>
<?php endif; ?>
