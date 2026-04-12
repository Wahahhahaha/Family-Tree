<div class="wrapper">

    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <section class="settings-card">
        <div class="settings-head">
            <h2>Recycle Bin</h2>
            <p>Deleted data archive. This page is only accessible by superadmin.</p>
        </div>

        <?php if (session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="alert-success">
            Recycle Bin view is ready. You can continue with restore/permanent delete features next.
        </div>
    </section>
</div>
