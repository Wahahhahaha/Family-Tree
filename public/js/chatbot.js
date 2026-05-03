(function () {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') || '' : '';

    function getTimeLabel() {
        return new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function initShell(shell) {
        var form = shell.querySelector('#chatbotForm');
        var input = shell.querySelector('#chatbotInput');
        var messages = shell.querySelector('#chatbotMessages');
        var sendButton = form ? form.querySelector('button[type="submit"]') : null;

        if (!form || !input || !messages) {
            return;
        }

        var history = [];
        var isSending = false;
        var quickWrap = shell.querySelector('.chatbot-quick-wrap');
        var quickHead = shell.querySelector('.chatbot-quick-head');
        var quickButtons = Array.prototype.slice.call(shell.querySelectorAll('.chatbot-quick-btn'));

        function scrollToBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        function appendMessage(role, text, timeText, extraClass) {
            var node = document.createElement('div');
            node.className = 'chatbot-msg chatbot-msg-' + role + (extraClass ? ' ' + extraClass : '');

            var paragraph = document.createElement('p');
            paragraph.textContent = text;
            paragraph.style.whiteSpace = 'pre-wrap';
            paragraph.style.wordBreak = 'break-word';
            node.appendChild(paragraph);

            if (timeText) {
                var time = document.createElement('span');
                time.textContent = timeText;
                node.appendChild(time);
            }

            messages.appendChild(node);
            scrollToBottom();

            history.push({
                role: role === 'user' ? 'user' : 'assistant',
                content: text
            });
        }

        function renderTyping() {
            var typing = document.createElement('div');
            typing.className = 'chatbot-msg chatbot-msg-bot chatbot-typing';

            var bubble = document.createElement('p');
            bubble.setAttribute('aria-label', 'Chatbot is typing');

            [0, 1, 2].forEach(function () {
                bubble.appendChild(document.createElement('i'));
            });

            typing.appendChild(bubble);
            messages.appendChild(typing);
            scrollToBottom();

            return typing;
        }

        function extractChatbotText(rawText) {
            var text = (rawText || '').trim();
            if (!text) {
                return '';
            }

            text = text
                .replace(/^```(?:json)?\s*/i, '')
                .replace(/\s*```$/i, '')
                .trim();

            function findStringValue(value) {
                if (typeof value === 'string') {
                    var trimmed = value.trim();
                    return trimmed !== '' ? trimmed : '';
                }

                if (Array.isArray(value)) {
                    for (var i = 0; i < value.length; i += 1) {
                        var arrayResult = findStringValue(value[i]);
                        if (arrayResult) {
                            return arrayResult;
                        }
                    }
                }

                if (value && typeof value === 'object') {
                    var preferredKeys = ['reply', 'message', 'error', 'content', 'text'];
                    for (var k = 0; k < preferredKeys.length; k += 1) {
                        var preferred = preferredKeys[k];
                        if (Object.prototype.hasOwnProperty.call(value, preferred)) {
                            var preferredResult = findStringValue(value[preferred]);
                            if (preferredResult) {
                                return preferredResult;
                            }
                        }
                    }

                    var keys = Object.keys(value);
                    for (var j = 0; j < keys.length; j += 1) {
                        var objectResult = findStringValue(value[keys[j]]);
                        if (objectResult) {
                            return objectResult;
                        }
                    }
                }

                return '';
            }

            if (text.charAt(0) === '{' || text.charAt(0) === '[' || text.charAt(0) === '"') {
                try {
                    var parsed = JSON.parse(text);
                    var extracted = findStringValue(parsed);
                    if (extracted) {
                        return extracted
                            .replace(/^```(?:json)?\s*/i, '')
                            .replace(/\s*```$/i, '')
                            .trim();
                    }
                } catch (error) {
                    return text;
                }
            }

            return text;
        }

        function setControlsDisabled(disabled) {
            isSending = disabled;
            input.disabled = disabled;
            if (sendButton) {
                sendButton.disabled = disabled;
            }
            quickButtons.forEach(function (button) {
                button.disabled = disabled;
            });
        }

        function sendMessage(rawMessage) {
            var message = (rawMessage || '').trim();
            if (!message || isSending) {
                return;
            }

            setControlsDisabled(true);
            appendMessage('user', message, getTimeLabel());
            input.value = '';
            input.focus();

            var typing = renderTyping();
            var formData = new FormData(form);

            formData.set('message', message);
            formData.set('prefer_groq', '1');
            formData.delete('history');

            history.slice(-10).forEach(function (item, index) {
                formData.append('history[' + index + '][role]', item.role);
                formData.append('history[' + index + '][content]', item.content);
            });

            fetch('/chatbot/ask', {
                method: 'POST',
                headers: {
                    'Accept': 'text/plain',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(function (response) {
                return response.text().then(function (text) {
                    return {
                        ok: response.ok,
                        status: response.status,
                        text: text
                    };
                });
            })
            .then(function (result) {
                typing.remove();

                var responseText = extractChatbotText(result.text);
                if (result.ok) {
                    appendMessage('bot', responseText || 'I did not understand that.', getTimeLabel());
                } else {
                    appendMessage('bot', responseText || 'Sorry, something went wrong.', getTimeLabel());
                }

                setControlsDisabled(false);
            })
            .catch(function () {
                typing.remove();
                appendMessage('bot', 'Connection failed. Please try again.', getTimeLabel());
                setControlsDisabled(false);
            });
        }

        if (quickHead && quickWrap) {
            quickHead.addEventListener('click', function () {
                var collapsed = quickWrap.classList.toggle('is-collapsed');
                quickHead.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            });
        }

        quickButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var question = button.getAttribute('data-question') || button.textContent || '';
                if (!question.trim()) {
                    return;
                }

                input.value = question;
                sendMessage(question);
            });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage(input.value);
        });

        scrollToBottom();
    }

    Array.prototype.slice.call(document.querySelectorAll('[data-chatbot-shell]')).forEach(initShell);
})();
