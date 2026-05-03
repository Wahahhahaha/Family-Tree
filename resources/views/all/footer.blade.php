<?php
    $footerSettings = $systemSettings ?? [];
    $footerSiteName = trim((string) ($footerSettings['website_name'] ?? 'Family Tree System'));
    if ($footerSiteName === '') {
        $footerSiteName = 'Family Tree System';
    }
?>

</main>

<?php if (!isset($hideFooter) || !$hideFooter): ?>
<footer class="site-footer" aria-label="Copyright">
    <span class="site-footer__copy">&copy; <?php echo e(date('Y')); ?> <?php echo e($footerSiteName); ?>. All Rights Reserved.</span>
</footer>
<?php endif; ?>

<?php if (session()->has('authenticated_user') && request()->path() !== 'chatbot'): ?>
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
<?php endif; ?>
<script src="/js/indonesia-province-city-data.js"></script>
<?php $scriptVersion = @filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/script.js') ?: time(); ?>
<script src="/js/script.js?v=<?php echo e($scriptVersion); ?>"></script>
<script src="/js/chatbot.js"></script>
<script defer src="/js/activity-log-geo.js"></script>
</body>
</html>
