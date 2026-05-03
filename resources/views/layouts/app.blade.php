<!doctype html>
<html lang="<?php echo e(app()->getLocale() ?: 'en'); ?>">
<head>
    <?php
        $settings = $systemSettings ?? [];
        $storedLogoPath = trim((string) ($settings['logo_path'] ?? ''));
        $faviconUrl = trim((string) ($settings['logo_url'] ?? ''));
        if ($faviconUrl === '' && $storedLogoPath !== '') {
            $faviconUrl = (preg_match('#^https?://#i', $storedLogoPath) || str_starts_with($storedLogoPath, 'data:'))
                ? $storedLogoPath
                : asset(ltrim($storedLogoPath, '/'));
        }
        $stylePath = public_path('css/style.css');
        $styleVersion = is_file($stylePath) ? @filemtime($stylePath) : time();
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>@yield('title', $pageTitle ?? ($systemSettings['website_name'] ?? 'Family Tree'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="<?php echo e($faviconUrl); ?>">
    <link rel="stylesheet" href="/css/style.css?v=<?php echo e($styleVersion); ?>">
    <?php if (request()->is('account')): ?>
        <style>
            body.page-account main,
            body.page-account .wrapper {
                width: 100% !important;
                max-width: none !important;
                box-sizing: border-box;
            }

            body.page-account .wrapper {
                margin: 0;
                padding: 30px 15px 48px;
            }

            body.page-account .account-grid {
                width: 100%;
                max-width: none !important;
                margin-top: 18px;
            }

            body.page-account .account-card,
            body.page-account .account-edit-card,
            body.page-account .account-password-card {
                width: 100%;
                max-width: none !important;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }

            body.page-account .account-password-form {
                width: 100%;
                max-width: none !important;
            }

            @media (max-width: 960px) {
                body.page-account .account-password-form {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    <?php endif; ?>

    <script src="https://unpkg.com/lucide@latest"></script>
    @yield('styles')
</head>
<?php
    $bodyClass = trim((string) ($pageClass ?? 'page-default'));
    if (request()->is('management/data-master')) {
        $bodyClass = trim($bodyClass . ' page-management-data-master');
    }
    if (request()->is('management/users')) {
        $bodyClass = trim($bodyClass . ' page-management-users');
    }
    if (request()->is('management/activity-log')) {
        $bodyClass = trim($bodyClass . ' page-management-activity-log');
    }
    if (request()->is('management/recycle-bin')) {
        $bodyClass = trim($bodyClass . ' page-management-recycle-bin');
    }
    if (request()->is('management/backup-database')) {
        $bodyClass = trim($bodyClass . ' page-management-backup-database');
    }
    if (request()->is('management/console')) {
        $bodyClass = trim($bodyClass . ' page-management-console');
    }
    if (request()->is('management/permission')) {
        $bodyClass = trim($bodyClass . ' page-management-permission');
    }
    if (request()->is('management/setting')) {
        $bodyClass = trim($bodyClass . ' page-management-setting');
    }
    if (request()->is('account')) {
        $bodyClass = trim($bodyClass . ' page-account');
    }
    if (request()->is('live-location') || request()->is('live-location/*')) {
        $bodyClass = trim($bodyClass . ' page-live-location');
    }
?>
<body class="<?php echo e($bodyClass); ?>">
    <script>
        window.appBaseUrl = <?php echo json_encode(url('/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    </script>

    @if(!isset($hideNavbar) || !$hideNavbar)
        <div class="page-navbar-shell">
            @include('all.navbar')
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <?php
        $footerSettings = $systemSettings ?? [];
        $footerSiteName = trim((string) ($footerSettings['website_name'] ?? 'Family Tree System'));
        if ($footerSiteName === '') {
            $footerSiteName = 'Family Tree System';
        }
    ?>
    <?php if (!isset($hideFooter) || !$hideFooter): ?>
        <footer class="site-footer" aria-label="Copyright">
            <span class="site-footer__copy">&copy; <?php echo e(date('Y')); ?> <?php echo e($footerSiteName); ?>. All Rights Reserved.</span>
        </footer>
    <?php endif; ?>

    @if(Auth::check() && request()->path() !== 'chatbot')
        <?php $chatbotToggleId = 'chatbot-toggle-' . uniqid(); ?>
        <div class="chatbot-shell" data-chatbot-shell>
            <input id="<?php echo e($chatbotToggleId); ?>" type="checkbox" class="chatbot-toggle">
            <label for="<?php echo e($chatbotToggleId); ?>" class="floating-chat-btn chatbot-launcher-btn" aria-label="Open chatbot">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M4 5.5C4 4.12 5.12 3 6.5 3h11C18.88 3 20 4.12 20 5.5v8c0 1.38-1.12 2.5-2.5 2.5H9.41l-3.7 3.7c-.63.63-1.71.18-1.71-.71V5.5zm2.5-.5a.5.5 0 0 0-.5.5v11.79l2.29-2.29c.19-.19.44-.29.71-.29h8.5a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-11z" fill="currentColor"/>
                    <circle cx="9" cy="9.5" r="1" fill="currentColor"/>
                    <circle cx="12" cy="9.5" r="1" fill="currentColor"/>
                    <circle cx="15" cy="9.5" r="1" fill="currentColor"/>
                </svg>
            </label>

            <div id="chatbotWidget" class="chatbot-widget" aria-hidden="true" data-chatbot-widget>
                <div class="chatbot-widget__panel">
                    <div class="chatbot-widget__header">
                        <div class="chatbot-widget__title">
                            <p>Family Tree Assistant</p>
                            <h3>Chatbot</h3>
                        </div>
                        <label for="<?php echo e($chatbotToggleId); ?>" class="chatbot-widget__close" aria-label="Close chatbot">
                            &times;
                        </label>
                    </div>
                    @include('all.partials.chatbot-widget-body')
                </div>
            </div>
        </div>
    @endif

    <script src="/js/script.js"></script>
    <script src="/js/chatbot.js"></script>
    <script>
        function initLucide() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            } else {
                setTimeout(initLucide, 100);
            }
        }
        document.addEventListener('DOMContentLoaded', initLucide);
    </script>
    @yield('scripts')
    @if(session()->has('authenticated_user'))
        <script defer src="/js/live-location.js"></script>
    @endif
</body>
</html>
