@extends('layouts.app')

@section('title', 'Email Updated')
@section('body-class', 'page-login')

@section('content')
<main class="login-shell auth-shell email-change-success-shell">
    <section class="login-panel email-change-success-panel">
        <div class="email-change-success-icon" aria-hidden="true">OK</div>
        <h2 class="panel-title">Email Updated Successfully</h2>
        <p class="panel-subtitle">Your account email has been verified and updated.</p>

        <div class="email-change-success-summary">
            <div class="email-change-success-row">
                <span>Previous email</span>
                <strong><?php echo e($oldEmail); ?></strong>
            </div>
            <div class="email-change-success-row">
                <span>New email</span>
                <strong><?php echo e($newEmail); ?></strong>
            </div>
            <p class="email-change-success-countdown">
                Redirecting to your account in <span id="redirectSeconds">3</span> seconds.
            </p>
        </div>

        <a class="btn-login inline-btn email-change-success-btn" href="<?php echo e($redirectTo); ?>">Go to Account Now</a>
        <p class="panel-note">If redirection does not happen automatically, click the button above.</p>
    </section>
</main>
@endsection

@section('scripts')
<script>
    (function () {
        var redirectUrl = '<?php echo e($redirectTo); ?>';
        var seconds = 3;
        var secondsNode = document.getElementById('redirectSeconds');

        var timer = window.setInterval(function () {
            seconds -= 1;
            if (secondsNode) {
                secondsNode.textContent = String(Math.max(seconds, 0));
            }

            if (seconds <= 0) {
                window.clearInterval(timer);
                window.location.href = redirectUrl;
            }
        }, 1000);
    })();
</script>
@endsection
