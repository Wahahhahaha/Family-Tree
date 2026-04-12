<?php
    $phoneResetSession = session('phone_password_reset');
    $savedPhoneNumber = is_array($phoneResetSession) ? (string) ($phoneResetSession['phone_number'] ?? '') : '';
    $phoneDisplayNumber = is_array($phoneResetSession) ? (string) ($phoneResetSession['phone_display'] ?? $savedPhoneNumber) : '';
    $phoneInputValue = old('phone_number', $savedPhoneNumber);
    $showPhoneOtpForm = session('show_phone_otp_form', false) || is_array($phoneResetSession);
    $showOtpModal = session('phone_otp_sent', false);
?>

<main class="login-shell auth-shell">
    <section class="login-brand">
        <span class="brand-badge"><?php echo e($systemSettings['website_name'] ?? 'Family Tree System'); ?></span>
        <h1 class="brand-title">Forgot Password by Phone Number</h1>
        <p class="brand-subtitle">
            Enter your registered phone number to receive a 6-digit OTP via WhatsApp.
        </p>
        <ul class="brand-points">
            <li><span>01</span> Use your registered phone number</li>
            <li><span>02</span> Receive OTP in WhatsApp via Fonnte</li>
            <li><span>03</span> Verify OTP and reset your password</li>
        </ul>
    </section>

    <section class="login-panel">
        <h2 class="panel-title">Phone Verification</h2>
        <p class="panel-subtitle">Use WhatsApp OTP to continue password reset.</p>

        <?php if (session('phone_status')): ?>
            <div class="alert-box alert-success-soft">
                <p><?php echo e(session('phone_status')); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($errors->any()): ?>
            <div class="alert-box">
                <?php foreach ($errors->all() as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$showPhoneOtpForm): ?>
            <form method="POST" action="/forgot-password/phone/send-otp" class="phone-send-form">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label for="phone_number">Phone Number</label>
                    <input id="phone_number" type="text" name="phone_number" placeholder="Example: 081234567890" value="<?php echo e($phoneInputValue); ?>" required>
                </div>
                <button class="btn-login" type="submit">Send OTP via WhatsApp</button>
            </form>
        <?php else: ?>
            <p class="otp-note">
                We have sent the OTP to <strong><?php echo e($phoneDisplayNumber); ?></strong>
            </p>
            <form method="POST" action="/forgot-password/phone/verify-otp" class="phone-otp-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="phone_number" value="<?php echo e($phoneInputValue); ?>">
                <div class="field">
                    <label for="otp">6-Digit OTP Code</label>
                    <input id="otp" type="text" name="otp" inputmode="numeric" pattern="\d{6}" minlength="6" maxlength="6" placeholder="Enter 6 digits OTP" value="<?php echo e(old('otp')); ?>" required>
                </div>
                <button class="btn-login" type="submit">Verify OTP</button>
            </form>
            <div class="row" style="margin-top: 10px;">
                <a class="forgot" href="/forgot-password/phone">Use another phone number</a>
            </div>
        <?php endif; ?>

        <p class="panel-note">Prefer email method? <a class="forgot" href="/forgot-password">Reset by Email</a></p>
        <p class="panel-note">Remember your password? <a class="forgot" href="/login">Back to Sign In</a></p>
    </section>
</main>

<div id="otpSentModal" class="otp-modal <?php echo e($showOtpModal ? 'is-visible' : ''); ?>" aria-hidden="<?php echo e($showOtpModal ? 'false' : 'true'); ?>">
    <div class="otp-modal-backdrop" data-close-modal="true"></div>
    <div class="otp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="otpSentModalTitle">
        <h3 id="otpSentModalTitle">OTP Sent Successfully</h3>
        <p>A 6-digit OTP has been sent to your WhatsApp number. Please check your chat and continue verification.</p>
        <button type="button" class="btn-login inline-btn" id="closeOtpModal">Continue</button>
    </div>
</div>

<style>
    .phone-send-form {
        margin-bottom: 12px;
    }
    .phone-otp-form {
        border-top: 1px dashed rgba(107, 114, 128, 0.35);
        padding-top: 12px;
    }
    .otp-note {
        margin: 0 0 10px;
        font-size: 12px;
        color: #4b5563;
    }
    .otp-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }
    .otp-modal.is-visible {
        display: flex;
    }
    .otp-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(17, 24, 39, 0.55);
        opacity: 0;
        animation: otpFadeIn 0.26s ease forwards;
    }
    .otp-modal-dialog {
        position: relative;
        width: min(92%, 420px);
        background: #ffffff;
        border-radius: 16px;
        padding: 22px 20px;
        box-shadow: 0 20px 45px rgba(17, 24, 39, 0.24);
        opacity: 0;
        transform: translateY(10px);
        animation: otpFadeInUp 0.28s ease forwards;
    }
    .otp-modal.fade-out .otp-modal-backdrop {
        animation: otpFadeOut 0.22s ease forwards;
    }
    .otp-modal.fade-out .otp-modal-dialog {
        animation: otpFadeOutDown 0.24s ease forwards;
    }
    .otp-modal-dialog h3 {
        margin: 0 0 8px;
        font-size: 20px;
        color: #111827;
    }
    .otp-modal-dialog p {
        margin: 0 0 16px;
        color: #374151;
        font-size: 14px;
        line-height: 1.45;
    }
    @keyframes otpFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes otpFadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    @keyframes otpFadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes otpFadeOutDown {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(8px); }
    }
</style>

<script>
    (function () {
        var modal = document.getElementById('otpSentModal');
        if (!modal || !modal.classList.contains('is-visible')) {
            return;
        }

        var closeButton = document.getElementById('closeOtpModal');
        var closeBackdrop = modal.querySelector('[data-close-modal="true"]');

        var closeModal = function () {
            if (!modal.classList.contains('is-visible')) {
                return;
            }

            modal.classList.add('fade-out');
            window.setTimeout(function () {
                modal.classList.remove('is-visible');
                modal.classList.remove('fade-out');
                modal.setAttribute('aria-hidden', 'true');
            }, 240);
        };

        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }

        if (closeBackdrop) {
            closeBackdrop.addEventListener('click', closeModal);
        }
    })();
</script>
