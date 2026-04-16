<div class="wrapper">

    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <section class="management-card">
        <div class="management-head">
            <div>
                <h2>Recycle Bin</h2>
                <p>Deleted user archive. This page is only accessible by superadmin.</p>
            </div>
            <div class="management-tools">
                <span id="recycleBinCount" class="table-count">Total: <?php echo e($deletedUsers->total()); ?> users</span>

            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Deleted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($deletedUsers->isEmpty()): ?>
                        <tr>
                            <td colspan="8" class="text-center">No deleted users in the recycle bin.</td>
                        </tr>
                    <?php else: ?>
                        <?php echo view('superadmin.partials.recycle-bin-table-rows', ['users' => $deletedUsers]); ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="recycleBinPagination">
            <?php echo view('admin.partials.user-pagination', ['users' => $deletedUsers]); ?>
        </div>
    </section>
</div>
