@extends('layouts.app')

@section('title', 'Forgot Password')
@section('body-class', 'page-login')

@section('content')
<?php
    $requestedMethod = strtolower((string) request()->query('method', ''));
    $hasPhoneErrors = $errors->has('phone_number') || $errors->has('otp');
    $activeMethod = 'email';

    if (in_array($requestedMethod, ['email', 'phone'], true)) {
        $activeMethod = $requestedMethod;
    } elseif ($hasPhoneErrors || session('phone_status') || $showPhoneOtpForm || $showOtpModal) {
        $activeMethod = 'phone';
    }
?>

<main class="login-shell auth-shell">
    <section class="login-panel">
        <h2 class="panel-title">Forgot Password</h2>
        <p class="panel-subtitle">Choose your account recovery method: email or phone number.</p>

        <?php if (session('status')): ?>
            <div class="alert-box alert-success-soft">
                <p><?php echo e(session('status')); ?></p>
            </div>
        <?php endif; ?>

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

        <div class="method-switch" role="tablist" aria-label="Recovery method">
            <button
                type="button"
                class="method-btn <?php echo e($activeMethod === 'email' ? 'is-active' : ''); ?>"
                data-method-btn="email"
                role="tab"
                aria-selected="<?php echo e($activeMethod === 'email' ? 'true' : 'false'); ?>"
            >
                By Email
            </button>
            <button
                type="button"
                class="method-btn <?php echo e($activeMethod === 'phone' ? 'is-active' : ''); ?>"
                data-method-btn="phone"
                role="tab"
                aria-selected="<?php echo e($activeMethod === 'phone' ? 'true' : 'false'); ?>"
            >
                By Phone Number
            </button>
        </div>

        <div class="auth-panel <?php echo e($activeMethod === 'email' ? 'is-active' : ''); ?>" data-method-panel="email" <?php echo e($activeMethod === 'email' ? '' : 'hidden'); ?>>
            <form method="POST" action="/forgot-password">
                <?php echo csrf_field(); ?>

                <div class="field">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="Enter your registered email" value="<?php echo e(old('email')); ?>" required>
                </div>

                <button class="btn-login" type="submit">Send Reset Link</button>
            </form>
        </div>

        <div class="auth-panel <?php echo e($activeMethod === 'phone' ? 'is-active' : ''); ?>" data-method-panel="phone" <?php echo e($activeMethod === 'phone' ? '' : 'hidden'); ?>>
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
                    OTP has been sent to <strong><?php echo e($phoneDisplayNumber); ?></strong>
                </p>
                <form method="POST" action="/forgot-password/phone/verify-otp" class="phone-otp-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="phone_number" value="<?php echo e($phoneInputValue); ?>">
                    <div class="field">
                        <label for="otp">OTP Code (6 digits)</label>
                        <input id="otp" type="text" name="otp" inputmode="numeric" pattern="\d{6}" minlength="6" maxlength="6" placeholder="Enter 6-digit OTP" value="<?php echo e(old('otp')); ?>" required>
                    </div>
                    <button class="btn-login" type="submit">Verify OTP</button>
                </form>
                <div class="row auth-row-spaced">
                    <a class="forgot" href="/forgot-password/phone?method=phone">Use phone number page</a>
                </div>
            <?php endif; ?>
        </div>

        <p class="panel-note">Remember your password? <a class="forgot" href="/login">Back to Sign In</a></p>
    </section>
</main>

<div id="otpSentModal" class="otp-modal <?php echo e($showOtpModal ? 'is-visible' : ''); ?>" aria-hidden="<?php echo e($showOtpModal ? 'false' : 'true'); ?>">
    <div class="otp-modal-backdrop" data-close-modal="true"></div>
    <div class="otp-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="otpSentModalTitle">
        <h3 id="otpSentModalTitle">OTP Sent</h3>
        <p>A 6-digit OTP has been sent to your WhatsApp. Continue verification to reset your password.</p>
        <button type="button" class="btn-login inline-btn" id="closeOtpModal">Continue</button>
    </div>
</div>

<script>
    (function () {
        var methodButtons = document.querySelectorAll('[data-method-btn]');
        var methodPanels = document.querySelectorAll('[data-method-panel]');

        var setActiveMethod = function (method) {
            methodButtons.forEach(function (button) {
                var isActive = button.getAttribute('data-method-btn') === method;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            methodPanels.forEach(function (panel) {
                var isActive = panel.getAttribute('data-method-panel') === method;
                panel.classList.toggle('is-active', isActive);
                if (isActive) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', 'hidden');
                }
            });
        };

        methodButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setActiveMethod(button.getAttribute('data-method-btn'));
            });
        });

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
@endsection
