<?php if ($activityLogs->lastPage() > 1): ?>
    <div class="user-pagination">
        <?php if ($activityLogs->onFirstPage()): ?>
            <span class="page-link disabled">Prev</span>
        <?php else: ?>
            <a class="page-link" href="<?php echo e($activityLogs->previousPageUrl()); ?>">Prev</a>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $activityLogs->lastPage(); $page++): ?>
            <?php if ($page === $activityLogs->currentPage()): ?>
                <span class="page-link active"><?php echo e($page); ?></span>
            <?php else: ?>
                <a class="page-link" href="<?php echo e($activityLogs->url($page)); ?>"><?php echo e($page); ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($activityLogs->hasMorePages()): ?>
            <a class="page-link" href="<?php echo e($activityLogs->nextPageUrl()); ?>">Next</a>
        <?php else: ?>
            <span class="page-link disabled">Next</span>
        <?php endif; ?>
    </div>
<?php endif; ?>
