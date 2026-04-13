<div class="wrapper">
    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <section class="chatbot-page">
        <article class="chatbot-card chatbot-app" id="chatbotApp">
            <div class="chatbot-head">
                <h2>Family Assistant</h2>
                <p>Ask anything about how to use the Family Tree System.</p>
            </div>

            <div class="chatbot-quick-wrap">
                <p>Quick Questions</p>
                <div class="chatbot-quick-list">
                    <button type="button" class="chatbot-quick-btn" data-question="How to add child?">How to add child</button>
                    <button type="button" class="chatbot-quick-btn" data-question="How to add partner?">How to add partner</button>
                    <button type="button" class="chatbot-quick-btn" data-question="How to edit profile?">How to edit profile</button>
                    <button type="button" class="chatbot-quick-btn" data-question="I forgot my password, what should I do?">Forgot password</button>
                </div>
            </div>

            <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
                <div class="chatbot-msg chatbot-msg-bot">
                    <p>Hello, I am ready to help. Pick a quick question or type your own.</p>
                    <span>Just now</span>
                </div>
            </div>

            <form id="chatbotForm" class="chatbot-form" autocomplete="off">
                <input id="chatbotInput" type="text" placeholder="Type your message..." maxlength="300" required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </article>
    </section>
</div>
<script src="/js/chatbot.js"></script>
