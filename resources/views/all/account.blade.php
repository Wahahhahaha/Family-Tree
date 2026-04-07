<div class="wrapper">
    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <section class="account-card">
        <h2>Account</h2>
        <p>Ringkasan akun yang sedang login.</p>

        <ul class="account-list">
            <li>
                <span>Username</span>
                <strong><?php echo e(session('authenticated_user.username')); ?></strong>
            </li>
            <li>
                <span>Level</span>
                <strong><?php echo e(session('authenticated_user.levelname') ?? '-'); ?></strong>
            </li>
            <li>
                <span>Role</span>
                <strong><?php echo e(session('authenticated_user.rolename') ?? '-'); ?></strong>
            </li>
        </ul>
    </section>
</div>
