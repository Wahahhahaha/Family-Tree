@extends('layouts.app')

@section('title', 'Family Assistant')
<?php
    $chatbotEmbed = request()->boolean('embed');
    $hideNavbar = $chatbotEmbed;
    $hideFooter = $chatbotEmbed;
    $pageClass = 'page-family-tree page-chatbot' . ($chatbotEmbed ? ' page-chatbot-embed' : '');
?>

@section('content')
<div class="chatbot-container">
    <section class="chatbot-page">
        <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
            <div class="chatbot-msg chatbot-msg-bot">
                <p>Hello, I am ready to help. Ask me anything about the Family Tree System.</p>
                <span>Just now</span>
            </div>
            <div class="chatbot-msg chatbot-msg-user chatbot-msg-quick">
                <div class="chatbot-quick-wrap">
                    <button type="button" class="chatbot-quick-head" aria-expanded="true">
                        <span class="chatbot-quick-title"><b>+</b><strong>Quick Questions</strong></span>
                        <i>v</i>
                    </button>
                    <div class="chatbot-quick-list">
                        <button type="button" class="chatbot-quick-btn" data-question="How do I add a child?">How do I add a child?</button>
                        <button type="button" class="chatbot-quick-btn" data-question="How do I add a partner?">How do I add a partner?</button>
                        <button type="button" class="chatbot-quick-btn" data-question="How do I edit my profile?">How do I edit my profile?</button>
                        <button type="button" class="chatbot-quick-btn" data-question="I forgot my password. What should I do?">I forgot my password. What should I do?</button>
                    </div>
                </div>
            </div>
        </div>
        <form id="chatbotForm" class="chatbot-form" action="/chatbot/ask" method="post" autocomplete="off">
            @csrf
            <input id="chatbotInput" name="message" type="text" placeholder="Type your message..." maxlength="300" required>
            <input type="hidden" name="prefer_groq" value="1">
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </section>
</div>
@endsection

@section('scripts')
<script src="/js/chatbot.js"></script>
@endsection
