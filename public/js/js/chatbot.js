(function () {
    var app = document.getElementById("chatbotApp");
    if (!app) {
        return;
    }

    var form = document.getElementById("chatbotForm");
    var input = document.getElementById("chatbotInput");
    var messages = document.getElementById("chatbotMessages");
    var submitButton = form ? form.querySelector('button[type="submit"]') : null;
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
    var quickButtons = Array.prototype.slice.call(
        document.querySelectorAll(".chatbot-quick-btn")
    );
    var conversation = [];
    var isSending = false;

    if (!form || !input || !messages) {
        return;
    }

    function nowLabel() {
        var now = new Date();
        return now.toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatMessageText(text) {
        return escapeHtml(text).replace(/\n/g, "<br>");
    }

    function sanitizeBotText(text) {
        return String(text || "").replace(/\*\*/g, "").trim();
    }

    function appendMessage(type, text, timeText) {
        var msg = document.createElement("div");
        msg.className = "chatbot-msg " + (type === "user" ? "chatbot-msg-user" : "chatbot-msg-bot");
        msg.innerHTML =
            "<p>" + formatMessageText(text) + "</p>" +
            "<span>" + escapeHtml(timeText || nowLabel()) + "</span>";
        messages.appendChild(msg);
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
        removeTyping();

        var wrap = document.createElement("div");
        wrap.className = "chatbot-msg chatbot-msg-bot";
        wrap.id = "chatbotTypingIndicator";
        wrap.innerHTML =
            '<p class="chatbot-typing"><i></i><i></i><i></i></p>' +
            "<span>Typing...</span>";
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        var typing = document.getElementById("chatbotTypingIndicator");
        if (typing) {
            typing.remove();
        }
    }

    function setSendingState(nextState) {
        isSending = !!nextState;
        input.disabled = isSending;

        if (submitButton) {
            submitButton.disabled = isSending;
        }

        quickButtons.forEach(function (btn) {
            btn.disabled = isSending;
        });
    }

    function normalizedHistory() {
        return conversation.slice(-10).map(function (item) {
            return {
                role: item.role,
                content: item.content,
            };
        });
    }

    function parseJsonSafe(response) {
        return response
            .json()
            .catch(function () {
                return {};
            })
            .then(function (payload) {
                return {
                    ok: response.ok,
                    status: response.status,
                    payload: payload || {},
                };
            });
    }

    function requestAiReply(userText) {
        return fetch("/chatbot/ask", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                message: userText,
                history: normalizedHistory()
            })
        })
            .then(parseJsonSafe)
            .then(function (result) {
                if (!result.ok) {
                    throw new Error(
                        String(result.payload.message || "Failed to get AI response.")
                    );
                }

                var reply = String(result.payload.reply || "").trim();
                if (!reply) {
                    throw new Error("AI returned an empty response.");
                }

                return reply;
            });
    }

    function sendMessage(text) {
        var normalized = String(text || "").trim();
        if (!normalized || isSending) {
            return;
        }

        appendMessage("user", normalized, nowLabel());
        showTyping();
        setSendingState(true);

        requestAiReply(normalized)
            .then(function (reply) {
                var cleanReply = sanitizeBotText(reply);
                removeTyping();
                appendMessage("bot", cleanReply, nowLabel());

                conversation.push({
                    role: "user",
                    content: normalized
                });
                conversation.push({
                    role: "assistant",
                    content: cleanReply
                });

                if (conversation.length > 10) {
                    conversation = conversation.slice(-10);
                }
            })
            .catch(function (error) {
                var errorText = sanitizeBotText(
                    error && error.message
                        ? error.message
                        : "I cannot respond right now. Please try again."
                );
                removeTyping();
                appendMessage(
                    "bot",
                    errorText,
                    nowLabel()
                );
            })
            .then(function () {
                setSendingState(false);
                input.focus();
            });
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        sendMessage(input.value);
        input.value = "";
    });

    quickButtons.forEach(function (btn) {
        btn.addEventListener("click", function () {
            var question = btn.getAttribute("data-question") || "";
            sendMessage(question);
        });
    });

    var quickHeads = Array.prototype.slice.call(
        document.querySelectorAll(".chatbot-quick-head")
    );

    quickHeads.forEach(function (head) {
        head.addEventListener("click", function () {
            var wrap = head.closest(".chatbot-quick-wrap");
            if (!wrap) {
                return;
            }

            wrap.classList.toggle("is-collapsed");
            var expanded = !wrap.classList.contains("is-collapsed");
            head.setAttribute("aria-expanded", expanded ? "true" : "false");
        });
    });
})();
