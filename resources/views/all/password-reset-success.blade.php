<main class="login-shell auth-shell">
    <section class="login-brand">
        <span class="brand-badge"><?php echo e($systemSettings['website_name'] ?? 'Family Tree System'); ?></span>
        <h1 class="brand-title">Password Reset Successful</h1>
        <p class="brand-subtitle">
            Your password has been updated successfully. You can now sign in using your new password.
        </p>
        <ul class="brand-points">
            <li><span>01</span> Your account credentials are now updated</li>
            <li><span>02</span> Keep your new password secure</li>
            <li><span>03</span> Redirecting to sign-in page shortly</li>
        </ul>
    </section>

    <section class="login-panel">
        <h2 class="panel-title">Success</h2>
        <p class="panel-subtitle">Password reset completed.</p>

        <div class="alert-box alert-success-soft">
            <p>Your password has been reset successfully.</p>
            <p>You will be redirected to the login page in <span id="redirectSeconds">3</span> seconds.</p>
        </div>

        <a class="btn-login inline-btn" href="/login">Go to Login Now</a>
    </section>
</main>

<script>
    (function () {
        var seconds = 3;
        var secondsNode = document.getElementById('redirectSeconds');

        var timer = window.setInterval(function () {
            seconds -= 1;
            if (secondsNode) {
                secondsNode.textContent = String(Math.max(seconds, 0));
            }

            if (seconds <= 0) {
                window.clearInterval(timer);
                window.location.href = '/login';
            }
        }, 1000);
    })();
</script>
