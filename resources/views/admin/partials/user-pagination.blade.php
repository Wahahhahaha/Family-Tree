<?php if ($users->lastPage() > 1): ?>
    <div class="user-pagination">
        <?php if ($users->onFirstPage()): ?>
            <span class="page-link disabled">Prev</span>
        <?php else: ?>
            <a class="page-link" href="<?php echo e($users->previousPageUrl()); ?>">Prev</a>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $users->lastPage(); $page++): ?>
            <?php if ($page === $users->currentPage()): ?>
                <span class="page-link active"><?php echo e($page); ?></span>
            <?php else: ?>
                <a class="page-link" href="<?php echo e($users->url($page)); ?>"><?php echo e($page); ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($users->hasMorePages()): ?>
            <a class="page-link" href="<?php echo e($users->nextPageUrl()); ?>">Next</a>
        <?php else: ?>
            <span class="page-link disabled">Next</span>
        <?php endif; ?>
    </div>
<?php endif; ?>
