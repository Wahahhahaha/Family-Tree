<div class="chatbot-widget__body" data-chatbot-body>
    <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
        <div class="chatbot-msg chatbot-msg-bot">
            <p>{{ __('common.hello_help') }}</p>
            <span>{{ __('common.quick_questions') }}</span>
        </div>
        <div class="chatbot-msg chatbot-msg-user chatbot-msg-quick">
            <div class="chatbot-quick-wrap">
                <button type="button" class="chatbot-quick-head" aria-expanded="true">
                    <span class="chatbot-quick-title"><b>+</b><strong>{{ __('common.quick_questions') }}</strong></span>
                    <i>v</i>
                </button>
                <div class="chatbot-quick-list">
                    <button type="button" class="chatbot-quick-btn" data-question="{{ __('chatbot.how_add_child') }}">{{ __('chatbot.how_add_child') }}</button>
                    <button type="button" class="chatbot-quick-btn" data-question="{{ __('chatbot.how_add_partner') }}">{{ __('chatbot.how_add_partner') }}</button>
                    <button type="button" class="chatbot-quick-btn" data-question="{{ __('chatbot.how_edit_profile') }}">{{ __('chatbot.how_edit_profile') }}</button>
                    <button type="button" class="chatbot-quick-btn" data-question="{{ __('chatbot.forgot_password') }}">{{ __('chatbot.forgot_password') }}</button>
                </div>
            </div>
        </div>
    </div>

    <form id="chatbotForm" class="chatbot-form" action="/chatbot/ask" method="post" autocomplete="off">
        @csrf
        <input id="chatbotInput" name="message" type="text" placeholder="{{ __('common.type_message') }}" maxlength="300" required>
        <input type="hidden" name="prefer_groq" value="1">
        <button type="submit" class="btn btn-primary">{{ __('common.send') }}</button>
    </form>
</div>
