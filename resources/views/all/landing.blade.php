@extends('layouts.app')

@section('title', $systemSettings['website_name'] ?? 'Family Tree')
@section('body-class', 'page-landing')

@section('content')
@php
    $settings = $systemSettings ?? [];
    $landing = $landingPageSettings ?? [];
    $currentLocale = $currentLocale ?? app()->getLocale() ?? 'en';
    $supportedLocales = $supportedLocales ?? ['en' => 'English', 'id' => 'Bahasa Indonesia'];
    $resolvePhotoUrl = function (string $path): string {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:https?:|data:|blob:)#i', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    };

    $siteLogo = trim((string) ($settings['logo_url'] ?? ''));
    if ($siteLogo === '') {
        $storedLogoPath = trim((string) ($settings['logo_path'] ?? ''));
        if ($storedLogoPath !== '') {
            $siteLogo = (preg_match('#^https?://#i', $storedLogoPath) || str_starts_with($storedLogoPath, 'data:'))
                ? $storedLogoPath
                : asset(ltrim($storedLogoPath, '/'));
        }
    }

    $familyName = trim((string) ($landing['family_name'] ?? ''));
    if ($familyName === '') {
        $familyName = __('landing.family_tree');
    }

    $description = trim((string) ($landing['description'] ?? ''));
    if ($description === '') {
        $description = __('landing.description_fallback');
    }

    $headName = trim((string) ($landing['head_of_family_name'] ?? ''));
    if ($headName === '') {
        $headName = __('landing.family_head');
    }

    $headMessage = trim((string) ($landing['head_of_family_message'] ?? ''));
    if ($headMessage === '') {
        $headMessage = __('landing.head_message_fallback');
    }

    $headPhoto = $resolvePhotoUrl((string) ($landing['head_of_family_photo'] ?? ''));
    $translateApprovalName = function (string $value, string $fallbackKey): string {
        $normalized = trim($value);
        if ($normalized === '') {
            return __('landing.not_set_yet');
        }

        $map = [
            'created by' => __('landing.created_by'),
            'designed by' => __('landing.designed_by'),
            'approved by' => __('landing.approved_by'),
            'acknowledged by' => __('landing.acknowledged_by'),
        ];

        $lookup = strtolower($normalized);
        if (array_key_exists($lookup, $map)) {
            return $map[$lookup];
        }

        $translatedFallback = __($fallbackKey);
        if (strtolower($normalized) === strtolower((string) $translatedFallback)) {
            return $translatedFallback;
        }

        return $normalized;
    };
    $approvalCards = [
        [
            'name' => $translateApprovalName((string) ($landing['created_by_name'] ?? ''), 'landing.created_by'),
            'photo' => $resolvePhotoUrl((string) ($landing['created_by_photo'] ?? '')),
        ],
        [
            'name' => $translateApprovalName((string) ($landing['designed_by_name'] ?? ''), 'landing.designed_by'),
            'photo' => $resolvePhotoUrl((string) ($landing['designed_by_photo'] ?? '')),
        ],
        [
            'name' => $translateApprovalName((string) ($landing['approved_by_name'] ?? ''), 'landing.approved_by'),
            'photo' => $resolvePhotoUrl((string) ($landing['approved_by_photo'] ?? '')),
        ],
        [
            'name' => $translateApprovalName((string) ($landing['acknowledged_by_name'] ?? ''), 'landing.acknowledged_by'),
            'photo' => $resolvePhotoUrl((string) ($landing['acknowledged_by_photo'] ?? '')),
        ],
    ];
    $currentLanguageLabel = $supportedLocales[$currentLocale] ?? strtoupper((string) $currentLocale);
    $currentRequestUri = request()->getRequestUri() ?: '/';
@endphp

<div class="landing-page">
    <div class="landing-bg landing-bg--one"></div>
    <div class="landing-bg landing-bg--two"></div>

    <div class="landing-shell">
        <header class="landing-topbar">
            <a href="/" class="landing-brand" aria-label="{{ __('landing.go_home') }}">
                <span class="landing-brand-mark {{ $siteLogo !== '' ? 'has-logo' : '' }}">
                    @if ($siteLogo !== '')
                        <img src="{{ $siteLogo }}" alt="Site logo">
                    @else
                        FT
                    @endif
                </span>
                <span class="landing-brand-copy">
                    <strong>{{ $settings['website_name'] ?? 'Family Tree System' }}</strong>
                    <span>{{ __('landing.public_family_homepage') }}</span>
                </span>
            </a>

            <div class="landing-topbar-actions">
                <details class="landing-language-switcher">
                    <summary aria-label="{{ __('landing.select_language') }}">
                        <i data-lucide="languages"></i>
                        <span>{{ $currentLanguageLabel }}</span>
                        <i data-lucide="chevron-down"></i>
                    </summary>
                    <div class="landing-language-switcher__menu" role="menu" aria-label="{{ __('landing.select_language') }}">
                        @foreach ($supportedLocales as $localeCode => $localeLabel)
                            <a
                                href="{{ route('language.switch', ['locale' => $localeCode, 'next' => $currentRequestUri]) }}"
                                class="{{ $localeCode === $currentLocale ? 'is-active' : '' }}"
                                data-language-switch="{{ $localeCode }}"
                            >
                                {{ $localeLabel }}
                            </a>
                        @endforeach
                    </div>
                </details>

                <a href="/login" class="landing-topbar-link">
                    <i data-lucide="log-in"></i>
                    <span>{{ __('landing.sign_in') }}</span>
                </a>
            </div>
        </header>

        <section class="landing-hero">
            <div class="landing-hero-copy">
                <h1>{{ $familyName }}</h1>
                <p class="landing-lead">{{ $description }}</p>

                <div class="landing-hero-actions">
                    <a href="#family-board" class="landing-hero-action landing-hero-action--primary">
                        <span>{{ __('landing.learn_more') }}</span>
                        <i data-lucide="arrow-down-right"></i>
                    </a>
                </div>
            </div>

            <article class="landing-hero-card">
                <div class="landing-hero-card__media">
                    <div class="landing-hero-card__photo {{ $headPhoto !== '' ? 'has-photo' : '' }}">
                        @if ($headPhoto !== '')
                            <img src="{{ $headPhoto }}" alt="{{ $headName }}">
                        @else
                            <span>{{ strtoupper(substr($headName !== '' ? $headName : $familyName, 0, 2)) }}</span>
                        @endif
                    </div>
                </div>
                <div class="landing-hero-card__body">
                    <span class="landing-card-label">{{ __('landing.head_of_family') }}</span>
                    <h2>{{ $headName }}</h2>
                    <p>{{ $headMessage }}</p>
                </div>
            </article>
        </section>

        <section id="family-board" class="landing-section">
            <div class="landing-section-head">
                <div>
                    <span class="landing-section-kicker">{{ __('landing.public_board') }}</span>
                    <h2>{{ __('landing.family_acknowledgment') }}</h2>
                </div>
            </div>

            <div class="landing-approval-grid">
                @foreach ($approvalCards as $card)
                    <article class="landing-approval-card">
                        <div class="landing-approval-card__photo {{ $card['photo'] !== '' ? 'has-photo' : '' }}">
                            @if ($card['photo'] !== '')
                                <img src="{{ $card['photo'] }}" alt="Approval photo">
                            @else
                                <span>{{ strtoupper(substr($card['name'], 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="landing-approval-card__body">
                            <h3>{{ $card['name'] }}</h3>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'family_tree_locale';
    const currentLocale = @json($currentLocale);
    const supportedLocales = @json(array_keys($supportedLocales));
    const savedLocale = localStorage.getItem(storageKey);

    if (savedLocale && supportedLocales.includes(savedLocale) && savedLocale !== currentLocale && !window.location.pathname.startsWith('/language/')) {
        const nextPath = window.location.pathname + window.location.search + window.location.hash;
        window.location.replace('/language/' + encodeURIComponent(savedLocale) + '?next=' + encodeURIComponent(nextPath));
        return;
    }

    document.querySelectorAll('[data-language-switch]').forEach(function (link) {
        link.addEventListener('click', function () {
            const locale = this.getAttribute('data-language-switch');
            if (locale) {
                localStorage.setItem(storageKey, locale);
            }
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#') {
                return;
            }

            const target = document.querySelector(targetId);
            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
});
</script>
@endsection
