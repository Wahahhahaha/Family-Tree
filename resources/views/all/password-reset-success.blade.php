<main class="login-shell auth-shell">
    <section class="login-panel">
        <h2 class="panel-title">Reset Password Berhasil</h2>
        <p class="panel-subtitle">Password akun Anda sudah diperbarui dan siap digunakan.</p>

        <div class="alert-box alert-success-soft">
            <p>Password baru berhasil disimpan.</p>
            <p>Anda akan diarahkan ke halaman login dalam <span id="redirectSeconds">3</span> detik.</p>
        </div>

        <a class="btn-login inline-btn" href="/login">Masuk Sekarang</a>
        <p class="panel-note">Jika tidak teralihkan otomatis, klik tombol di atas.</p>
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
