@extends('layouts.app')

@section('title', __('auth.login_title'))
@section('body-class', 'page-login')

@section('content')
@php
    $showOtpSection = $showOtpSection ?? false;
    $showEmailOtpForm = $showEmailOtpForm ?? false;
    $showOtpModal = $showOtpModal ?? false;
    $savedEmailAddress = $savedEmailAddress ?? '';
    $loginModalMessages = $loginModalMessages ?? [];

    $isOtpMode = (bool) $showOtpSection;
    $isOtpFormStep = $isOtpMode && !empty($showEmailOtpForm);
    $savedEmail = old('email', $savedEmailAddress);
    $usernameValue = old('username');
    $websiteName = trim((string) ($systemSettings['website_name'] ?? 'Family Tree System'));
    $logoPath = trim((string) ($systemSettings['logo_path'] ?? ''));
    $websiteLogoUrl = trim((string) ($systemSettings['logo_url'] ?? ''));
    if ($websiteLogoUrl === '' && $logoPath !== '') {
        $websiteLogoUrl = preg_match('#^https?://#i', $logoPath) || str_starts_with($logoPath, 'data:')
            ? $logoPath
            : asset(ltrim($logoPath, '/'));
    }
    $websiteInitial = strtoupper(substr($websiteName, 0, 1));
    $loginMessages = [];
    $emailDisplayAddress = $emailDisplayAddress ?? ($savedEmailAddress ?: $savedEmail);

    if (!empty($loginModalMessages) && is_array($loginModalMessages)) {
        $loginMessages = $loginModalMessages;
    }

    if (session('error')) {
        $loginMessages[] = (string) session('error');
    }

    $loginMessages = array_values(array_unique(array_filter($loginMessages, function ($message) {
        return trim((string) $message) !== '';
    })));
@endphp

<div class="login-shell">
    <section class="login-card">
        <div class="login-card-body">
            <div class="login-brand">
                <div class="login-brand-logo">
                    @if(!empty($websiteLogoUrl))
                        <img
                            src="{{ $websiteLogoUrl }}"
                            alt="{{ $websiteName }} logo"
                            loading="lazy"
                            onerror="this.style.display='none';var fallback=this.nextElementSibling;if(fallback){fallback.style.display='grid';}"
                        >
                    @endif
                    <div class="login-brand-initial" @if(!empty($websiteLogoUrl)) style="display:none;" @endif>{{ $websiteInitial }}</div>
                </div>

                <div class="login-brand-copy">
                    <p>{{ __('auth.welcome_to') }}</p>
                    <h1>{{ $websiteName }}</h1>
                </div>
            </div>

            <div class="login-panel-head">
                <p class="login-panel-kicker">{{ __('auth.secure_access') }}</p>
                <h2>{{ __('auth.welcome_back') }}</h2>
                <p class="login-panel-subtitle">{{ __('auth.login_subtitle') }}</p>
            </div>

            @if(!empty($loginMessages))
                <div class="login-alerts" role="alert" aria-live="polite">
                    <div class="login-alert is-error">
                        <p>{{ __('auth.review_messages') }}</p>
                        <ul class="login-alert-list">
                            @foreach($loginMessages as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(!empty($showOtpModal))
                <div class="login-alerts" aria-live="polite">
                    <div class="login-alert is-success">
                        <p>{{ __('auth.otp_sent') }}</p>
                    </div>
                </div>
            @endif

            @if($isOtpMode)
                <div class="login-otp-stack">
                    @if(!$isOtpFormStep)
                        <p class="login-otp-note">{{ __('auth.otp_note') }}</p>

                        <form method="POST" action="/login/otp/send" class="login-form">
                            @csrf
                            <div class="login-field">
                                <label for="email">{{ __('auth.email_address') }}</label>
                                <input
                                    id="email"
                                    class="login-input"
                                    type="email"
                                    name="email"
                                    placeholder="name@example.com"
                                    value="{{ $savedEmail }}"
                                    required
                                >
                            </div>

                            <button class="btn-login" type="submit">{{ __('auth.send_otp') }}</button>
                        </form>
                    @else
                        <p class="login-otp-note">
                            {!! str_replace(':email', '<strong>' . e($emailDisplayAddress) . '</strong>', e(__('auth.otp_sent_to', ['email' => $emailDisplayAddress]))) !!}
                        </p>

                        <form method="POST" action="/login/otp/verify" class="login-form">
                            @csrf
                            <input type="hidden" name="email" value="{{ $savedEmail }}">

                            <div class="login-field">
                                <label for="otp">{{ __('auth.otp_code') }}</label>
                                <input
                                    id="otp"
                                    class="login-input"
                                    type="text"
                                    name="otp"
                                    inputmode="numeric"
                                    pattern="\d{6}"
                                    minlength="6"
                                    maxlength="6"
                                    placeholder="Enter 6-digit code"
                                    value="{{ old('otp') }}"
                                    required
                                >
                            </div>

                            <button class="btn-login" type="submit">{{ __('auth.verify_otp_sign_in') }}</button>
                        </form>

                        <div class="login-utility-row">
                            <a class="login-link-inline" href="/login/otp/resend">{{ __('auth.resend_otp') }}</a>
                            <a class="login-back-link" href="/login">{{ __('auth.back_to_password') }}</a>
                        </div>
                    @endif

                    <div class="login-divider"><span>{{ __('auth.or') }}</span></div>

                    <div class="login-shortcuts">
                        <a class="login-shortcut" href="/login">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                                <path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                            </svg>
                            {{ __('auth.username_login') }}
                        </a>
                        <a class="login-shortcut" href="/login/google">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path fill="#4285F4" d="M21.58 12.24c0-.76-.07-1.49-.19-2.2H12v4.16h5.38a4.61 4.61 0 0 1-2 3.03v2.52h3.23c1.89-1.74 2.97-4.31 2.97-7.51z"></path>
                                <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.45l-3.23-2.52c-.9.6-2.06.95-3.38.95-2.6 0-4.8-1.76-5.58-4.12H3.08v2.6A9.99 9.99 0 0 0 12 22z"></path>
                                <path fill="#FBBC05" d="M6.42 13.86A5.98 5.98 0 0 1 6.1 12c0-.64.11-1.27.32-1.86v-2.6H3.08A9.99 9.99 0 0 0 2 12c0 1.61.39 3.14 1.08 4.46l3.34-2.6z"></path>
                                <path fill="#EA4335" d="M12 6.03c1.47 0 2.78.5 3.82 1.5l2.86-2.86C16.95 3.06 14.69 2 12 2A9.99 9.99 0 0 0 3.08 7.54l3.34 2.6C7.2 7.79 9.4 6.03 12 6.03z"></path>
                            </svg>
                            {{ __('auth.sign_in_with_google') }}
                        </a>
                    </div>
                </div>
            @else
                <form method="POST" action="/login" class="login-form" id="loginForm">
                    @csrf

                    <div class="login-field">
                        <label for="username">{{ __('auth.username') }}</label>
                        <input
                            id="username"
                            class="login-input"
                            type="text"
                            name="username"
                            placeholder="{{ __('auth.enter_username') }}"
                            value="{{ $usernameValue }}"
                            required
                            autofocus
                        >
                    </div>

                    <div class="login-field login-password-field">
                        <label for="password">{{ __('auth.password') }}</label>
                        <input
                            id="password"
                            class="login-input"
                            type="password"
                            name="password"
                            placeholder="{{ __('auth.enter_password') }}"
                            required
                        >
                        <button class="login-password-toggle" type="button" id="passwordToggle" aria-label="{{ __('auth.show_password') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.8"></circle>
                            </svg>
                        </button>
                    </div>

                    @if(!empty($showRecaptcha))
                        <div class="login-captcha">
                            <input type="hidden" id="loginCaptchaMode" name="captcha_mode" value="{{ old('captcha_mode', 'offline') }}">
                            <p id="loginCaptchaStatus" class="captcha-mode-status"></p>

                            <div id="captcha-online-section" class="captcha-mode-panel hidden">
                                <div class="field captcha-field">
                                    <div id="loginRecaptchaWidget" class="captcha-widget"></div>
                                </div>
                            </div>

                            <div id="captcha-offline-section" class="captcha-mode-panel hidden">
                                <div class="offline-captcha-box">
                                    <span class="offline-captcha-question">{{ $offlineCaptchaQuestion }}</span>
                                </div>

                                <div class="login-field" style="margin-bottom: 0;">
                                    <label for="offline_captcha_answer">{{ __('auth.captcha_answer') }}</label>
                                    <input
                                        id="offline_captcha_answer"
                                        class="login-input"
                                        type="text"
                                        name="offline_captcha_answer"
                                        placeholder="{{ __('auth.enter_calculation') }}"
                                        value="{{ old('offline_captcha_answer') }}"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="login-utility-row" style="justify-content: flex-end;">
                        <a class="login-link-inline" href="/forgot-password">{{ __('auth.forgot_password') }}</a>
                    </div>

                    <button class="btn-login" type="submit">{{ __('auth.sign_in') }}</button>
                </form>

                <div class="login-divider"><span>{{ __('auth.or') }}</span></div>

                <div class="login-shortcuts">
                    <a class="login-shortcut" href="/login?method=otp">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 8h10a2 2 0 0 1 2 2v8l-4-2H7a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                            <path d="M9 8V6a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                        {{ __('auth.login_by_otp') }}
                    </a>
                    <a class="login-shortcut" href="/login/google">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="#4285F4" d="M21.58 12.24c0-.76-.07-1.49-.19-2.2H12v4.16h5.38a4.61 4.61 0 0 1-2 3.03v2.52h3.23c1.89-1.74 2.97-4.31 2.97-7.51z"></path>
                            <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.45l-3.23-2.52c-.9.6-2.06.95-3.38.95-2.6 0-4.8-1.76-5.58-4.12H3.08v2.6A9.99 9.99 0 0 0 12 22z"></path>
                            <path fill="#FBBC05" d="M6.42 13.86A5.98 5.98 0 0 1 6.1 12c0-.64.11-1.27.32-1.86v-2.6H3.08A9.99 9.99 0 0 0 2 12c0 1.61.39 3.14 1.08 4.46l3.34-2.6z"></path>
                            <path fill="#EA4335" d="M12 6.03c1.47 0 2.78.5 3.82 1.5l2.86-2.86C16.95 3.06 14.69 2 12 2A9.99 9.99 0 0 0 3.08 7.54l3.34 2.6C7.2 7.79 9.4 6.03 12 6.03z"></path>
                        </svg>
                        {{ __('auth.sign_in_with_google') }}
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
