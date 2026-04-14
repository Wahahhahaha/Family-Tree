(function () {
    var app = document.getElementById("chatbotApp");
    if (!app) {
        return;
    }

    var form = document.getElementById("chatbotForm");
    var input = document.getElementById("chatbotInput");
    var messages = document.getElementById("chatbotMessages");
    var quickButtons = Array.prototype.slice.call(
        document.querySelectorAll(".chatbot-quick-btn")
    );

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

    function appendMessage(type, text, timeText) {
        var msg = document.createElement("div");
        msg.className = "chatbot-msg " + (type === "user" ? "chatbot-msg-user" : "chatbot-msg-bot");
        msg.innerHTML =
            "<p>" + escapeHtml(text) + "</p>" +
            "<span>" + escapeHtml(timeText || nowLabel()) + "</span>";
        messages.appendChild(msg);
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
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

    function generateReply(userTextRaw) {
        var userText = String(userTextRaw || "")
            .toLowerCase()
            .replace(/[’]/g, "'");

        var hasAny = function (keywords) {
            return keywords.some(function (keyword) {
                return userText.includes(keyword);
            });
        };

        if (hasAny(["why can't i add a sibling", "why cant i add a sibling", "cannot add a sibling", "can't add a sibling"])) {
            return "You cannot add a sibling directly in this system. The available options are Add Child or Add Partner from your own (Me) card.";
        }

        if (hasAny(["why can't i add a child", "why cant i add a child", "cannot add a child", "can't add a child"])) {
            return "Usually this happens because you are not selecting your own card (Me), or your account does not have permission. Open Home, click your Me card, then use Add Member > Add Child.";
        }

        if (hasAny(["why can't i add a partner", "why cant i add a partner", "cannot add a partner", "can't add a partner"])) {
            return "Usually this happens because you are not on your own card (Me), your account role is restricted, or your relation setup does not allow another partner entry.";
        }

        if (hasAny(["why is my data not updated", "my data not updated", "data not updated"])) {
            return "Make sure you click Save Profile after editing and wait for the success message. If changes still do not appear, refresh the page and check your internet connection or permissions.";
        }

        if (hasAny(["who added this family member", "who added this member", "who added this"])) {
            return "You can check who added a member from Activity Log (available for Superadmin). If you do not have access, ask your admin/superadmin to review the log.";
        }

        if (hasAny(["how to add child", "add child", "tambah anak"])) {
            return "To add a child: open Home, click the card with the Me badge, select Add Member, choose Add Child, fill in the form, then submit.";
        }

        if (hasAny(["how to add partner", "add partner", "tambah pasangan"])) {
            return "To add a partner: open Home, click the card with the Me badge, select Add Member, choose Add Partner, fill in the details, then save.";
        }

        if (hasAny(["how to edit profile", "edit profile", "ubah profile", "edit akun"])) {
            return "To edit your profile: open the Account menu, update the required fields, then click Save Profile. You can also change your profile photo there.";
        }

        if (hasAny([
            "how to change my password",
            "change my password",
            "forgot password",
            "forget password",
            "forgat password",
            "forgor password",
            "forget pasword",
            "forgot pasword",
            "forgat pasword",
            "forgor pasword",
            "lupa pasword",
            "lupa password",
            "reset password",
            "foregt pw",
            "forget pw",
            "forgot pw"
        ])) {
            return "If you forgot your password: on the login page click Forgot Password, choose email or phone reset, then complete verification and save the new password.";
        }

        if (hasAny(["how to delete an account", "delete an account", "delete account"])) {
            return "To delete an account: if you are Superadmin/Admin, open Management > User Management and use the Delete action. For family relations, parents can delete linked child/partner from the Home detail panel.";
        }

        if (hasAny(["how to update family member information", "update family member information", "update member information"])) {
            return "Open Account to update your own profile information (job, address, education, and photo). For life status updates (alive/deceased), select the member card and use the Life Status section if your account has access.";
        }

        if (hasAny(["how to update status", "alive or deceased", "update status alive", "update status deceased", "update life status"])) {
            return "To update status (alive/deceased): open Home, select the target member card, find Life Status on the right panel, choose Alive or Deceased, then click Save.";
        }

        if (hasAny(["how to upload a profile photo", "upload profile photo", "change profile photo"])) {
            return "To upload a profile photo: go to Account (or your Me card profile area), click your photo, select an image, adjust crop if needed, then click Save Profile.";
        }

        if (hasAny(["delete child", "hapus anak"])) {
            return "To delete a child: select the child member in the tree, then use the Delete Child button on the right panel (if your account has access).";
        }

        return "I understand. Please be more specific, for example: 'how to add child', 'how to add partner', 'how to edit profile', or 'forgot password'.";
    }

    function sendMessage(text) {
        var normalized = String(text || "").trim();
        if (!normalized) {
            return;
        }

        appendMessage("user", normalized, nowLabel());
        showTyping();

        window.setTimeout(function () {
            removeTyping();
            appendMessage("bot", generateReply(normalized), nowLabel());
        }, 450);
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        sendMessage(input.value);
        input.value = "";
        input.focus();
    });

    quickButtons.forEach(function (btn) {
        btn.addEventListener("click", function () {
            var question = btn.getAttribute("data-question") || "";
            sendMessage(question);
            input.focus();
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
