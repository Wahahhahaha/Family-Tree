<main class="login-shell">
    <section class="login-panel">
        <h2 class="panel-title">Sign In</h2>
        <p class="panel-subtitle">Enter your username and password to continue.</p>

        <?php if ($errors->any()): ?>
            <div class="alert-box">
                <?php foreach ($errors->all() as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" placeholder="Enter your username" value="<?php echo e(old('username')); ?>" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="row">
                <a class="forgot" href="/forgot-password">Forgot password?</a>
            </div>

            <button class="btn-login" type="submit">Sign In</button>
        </form>

        <p class="panel-note">Don't have an account? Contact the family administrator.</p>
    </section>
</main>
