<main class="login-shell auth-shell">
    <section class="login-panel">
        <h2 class="panel-title">Reset Password</h2>
        <p class="panel-subtitle">Verifikasi nomor HP berhasil. Silakan buat password baru Anda.</p>

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
                <label for="password">Password Baru</label>
                <div class="password-wrap">
                    <input id="password" type="password" name="password" placeholder="Masukkan password baru" minlength="8" required>
                    <button type="button" class="password-toggle" data-toggle-target="password" aria-label="Show password">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" class="eye-open">
                            <path fill="currentColor" d="M12 5c5.23 0 9.27 3.11 10.95 6.5C21.27 14.89 17.23 18 12 18S2.73 14.89 1.05 11.5C2.73 8.11 6.77 5 12 5m0 2C8.36 7 5.35 9.03 3.75 11.5C5.35 13.97 8.36 16 12 16s6.65-2.03 8.25-4.5C18.65 9.03 15.64 7 12 7m0 1.5A3.5 3.5 0 1 1 8.5 12A3.5 3.5 0 0 1 12 8.5m0 2A1.5 1.5 0 1 0 13.5 12A1.5 1.5 0 0 0 12 10.5"/>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" class="eye-closed hidden">
                            <path fill="currentColor" d="M2.81 2.81L1.39 4.22l3 3A11.85 11.85 0 0 0 1.05 11.5C2.73 14.89 6.77 18 12 18a11.9 11.9 0 0 0 4.71-.92l3.07 3.07l1.41-1.41M7.5 10.31l1.55 1.55a3 3 0 0 0 3.14 3.14l1.55 1.55A5 5 0 0 1 7.5 10.31m4.45-1.79L15.47 12a3 3 0 0 0-3.52-3.48M12 6a11.92 11.92 0 0 1 10.95 5.5a11.8 11.8 0 0 1-3.93 4.42l-1.43-1.43A9.54 9.54 0 0 0 20.25 11.5C18.65 9.03 15.64 7 12 7a9.8 9.8 0 0 0-2.59.35L7.74 5.68A12.4 12.4 0 0 1 12 6"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="password-wrap">
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password baru" minlength="8" required>
                    <button type="button" class="password-toggle" data-toggle-target="password_confirmation" aria-label="Show password confirmation">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" class="eye-open">
                            <path fill="currentColor" d="M12 5c5.23 0 9.27 3.11 10.95 6.5C21.27 14.89 17.23 18 12 18S2.73 14.89 1.05 11.5C2.73 8.11 6.77 5 12 5m0 2C8.36 7 5.35 9.03 3.75 11.5C5.35 13.97 8.36 16 12 16s6.65-2.03 8.25-4.5C18.65 9.03 15.64 7 12 7m0 1.5A3.5 3.5 0 1 1 8.5 12A3.5 3.5 0 0 1 12 8.5m0 2A1.5 1.5 0 1 0 13.5 12A1.5 1.5 0 0 0 12 10.5"/>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" class="eye-closed hidden">
                            <path fill="currentColor" d="M2.81 2.81L1.39 4.22l3 3A11.85 11.85 0 0 0 1.05 11.5C2.73 14.89 6.77 18 12 18a11.9 11.9 0 0 0 4.71-.92l3.07 3.07l1.41-1.41M7.5 10.31l1.55 1.55a3 3 0 0 0 3.14 3.14l1.55 1.55A5 5 0 0 1 7.5 10.31m4.45-1.79L15.47 12a3 3 0 0 0-3.52-3.48M12 6a11.92 11.92 0 0 1 10.95 5.5a11.8 11.8 0 0 1-3.93 4.42l-1.43-1.43A9.54 9.54 0 0 0 20.25 11.5C18.65 9.03 15.64 7 12 7a9.8 9.8 0 0 0-2.59.35L7.74 5.68A12.4 12.4 0 0 1 12 6"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button class="btn-login" type="submit">Simpan Password Baru</button>
        </form>

        <p class="panel-note">Butuh OTP lagi? <a class="forgot" href="/forgot-password?method=phone">Kembali ke verifikasi nomor HP</a></p>
    </section>
</main>

<script>
    (function () {
        var toggleButtons = document.querySelectorAll('[data-toggle-target]');
        if (!toggleButtons.length) {
            return;
        }

        toggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = button.getAttribute('data-toggle-target');
                var input = targetId ? document.getElementById(targetId) : null;
                if (!input) {
                    return;
                }

                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';

                var openEye = button.querySelector('.eye-open');
                var closedEye = button.querySelector('.eye-closed');
                if (openEye && closedEye) {
                    openEye.classList.toggle('hidden', !showing);
                    closedEye.classList.toggle('hidden', showing);
                }

                button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });
    })();
</script>
