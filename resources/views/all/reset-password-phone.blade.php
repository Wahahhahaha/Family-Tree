<main class="login-shell auth-shell">
    <section class="login-brand">
        <span class="brand-badge"><?php echo e($systemSettings['website_name'] ?? 'Family Tree System'); ?></span>
        <h1 class="brand-title">Set a New Password</h1>
        <p class="brand-subtitle">
            Your phone number has been verified successfully. Create your new account password now.
        </p>
        <ul class="brand-points">
            <li><span>01</span> Use at least 8 characters</li>
            <li><span>02</span> Use a strong and unique password</li>
            <li><span>03</span> Keep your credentials private</li>
        </ul>
    </section>

    <section class="login-panel">
        <h2 class="panel-title">Reset Password by Phone</h2>
        <p class="panel-subtitle">Create your new password below.</p>

        <?php if ($errors->any()): ?>
            <div class="alert-box">
                <?php foreach ($errors->all() as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/reset-password/phone">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="password">New Password</label>
                <div class="password-wrap">
                    <input id="password" type="password" name="password" placeholder="Enter a new password" required>
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password">
                        <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M12 5c5.23 0 9.27 3.11 10.95 6.5C21.27 14.89 17.23 18 12 18S2.73 14.89 1.05 11.5C2.73 8.11 6.77 5 12 5m0 2C8.36 7 5.35 9.03 3.75 11.5C5.35 13.97 8.36 16 12 16s6.65-2.03 8.25-4.5C18.65 9.03 15.64 7 12 7m0 1.5A3.5 3.5 0 1 1 8.5 12A3.5 3.5 0 0 1 12 8.5m0 2A1.5 1.5 0 1 0 13.5 12A1.5 1.5 0 0 0 12 10.5"/>
                        </svg>
                        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" class="hidden">
                            <path fill="currentColor" d="M2.81 2.81L1.39 4.22l3 3A11.85 11.85 0 0 0 1.05 11.5C2.73 14.89 6.77 18 12 18a11.9 11.9 0 0 0 4.71-.92l3.07 3.07l1.41-1.41M7.5 10.31l1.55 1.55a3 3 0 0 0 3.14 3.14l1.55 1.55A5 5 0 0 1 7.5 10.31m4.45-1.79L15.47 12a3 3 0 0 0-3.52-3.48M12 6a11.92 11.92 0 0 1 10.95 5.5a11.8 11.8 0 0 1-3.93 4.42l-1.43-1.43A9.54 9.54 0 0 0 20.25 11.5C18.65 9.03 15.64 7 12 7a9.8 9.8 0 0 0-2.59.35L7.74 5.68A12.4 12.4 0 0 1 12 6"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm your new password" required>
            </div>

            <button class="btn-login" type="submit">Save New Password</button>
        </form>
    </section>
</main>

<script>
    (function () {
        var passwordInput = document.getElementById('password');
        var toggleButton = document.getElementById('togglePassword');
        var eyeOpen = document.getElementById('eyeOpen');
        var eyeClosed = document.getElementById('eyeClosed');

        if (!passwordInput || !toggleButton || !eyeOpen || !eyeClosed) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            var showing = passwordInput.type === 'text';
            passwordInput.type = showing ? 'password' : 'text';
            eyeOpen.classList.toggle('hidden', !showing);
            eyeClosed.classList.toggle('hidden', showing);
            toggleButton.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    })();
</script>
