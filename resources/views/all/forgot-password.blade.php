<main class="login-shell auth-shell">
    <section class="login-brand">
        <span class="brand-badge"><?php echo e($systemSettings['website_name'] ?? 'Family Tree System'); ?></span>
        <h1 class="brand-title">Forgot Your Password?</h1>
        <p class="brand-subtitle">
            Enter your registered email address and we will send you a secure password reset link.
        </p>
        <ul class="brand-points">
            <li><span>01</span> Use the email registered in this system</li>
            <li><span>02</span> Check your inbox for the reset link</li>
            <li><span>03</span> Create a new password in seconds</li>
        </ul>
    </section>

    <section class="login-panel">
        <h2 class="panel-title">Forgot Password</h2>
        <p class="panel-subtitle">Please provide your recovery method.</p>

        <?php if (session('status')): ?>
            <div class="alert-box alert-success-soft">
                <p><?php echo e(session('status')); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($errors->any()): ?>
            <div class="alert-box">
                <?php foreach ($errors->all() as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/forgot-password">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" placeholder="Enter your registered email" value="<?php echo e(old('email')); ?>" required>
            </div>

            <button class="btn-login" type="submit">Send Reset Link</button>
        </form>

        <div class="row" style="margin-top: 14px;">
            <a class="forgot" href="/forgot-password/phone">Forgot password by phone number</a>
        </div>

        <p class="panel-note">Remember your password? <a class="forgot" href="/login">Back to Sign In</a></p>
    </section>
</main>
