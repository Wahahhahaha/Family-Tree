<?php if (session()->has('authenticated_user') && request()->path() !== 'chatbot'): ?>
    <a href="/chatbot" class="floating-chat-btn" aria-label="Open chatbot">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4 5.5C4 4.12 5.12 3 6.5 3h11C18.88 3 20 4.12 20 5.5v8c0 1.38-1.12 2.5-2.5 2.5H9.41l-3.7 3.7c-.63.63-1.71.18-1.71-.71V5.5zm2.5-.5a.5.5 0 0 0-.5.5v11.79l2.29-2.29c.19-.19.44-.29.71-.29h8.5a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-11z" fill="currentColor"/>
            <circle cx="9" cy="9.5" r="1" fill="currentColor"/>
            <circle cx="12" cy="9.5" r="1" fill="currentColor"/>
            <circle cx="15" cy="9.5" r="1" fill="currentColor"/>
        </svg>
    </a>
<?php endif; ?>
<script src="/js/activity-log-geo.js"></script>
<script src="/js/script.js"></script>
</body>
</html>
