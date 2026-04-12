<div class="wrapper">
    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <section class="management-card">
        <div class="management-head">
            <div>
                <h2>Activity Log</h2>
                <p>System activity records (latest first). Access is limited to superadmin.</p>
            </div>
            <div class="management-tools">
                <span id="activityLogTableCount" class="table-count">Total: <?php echo e($activityLogs->total()); ?> records</span>
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
                        <th>Time</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>IP</th>
                        <th>Longitude</th>
                        <th>Latitude</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="activityLogTableBody">
                    <?php echo view('admin.partials.activity-log-table-rows', ['activityLogs' => $activityLogs]); ?>
                </tbody>
            </table>
        </div>

        <div id="activityLogPagination">
            <?php echo view('admin.partials.activity-log-pagination', ['activityLogs' => $activityLogs]); ?>
        </div>
    </section>
</div>
