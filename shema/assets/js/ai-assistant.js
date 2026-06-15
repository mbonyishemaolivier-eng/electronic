document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('aiChatToggle');
    var panel = document.getElementById('aiChatPanel');
    var closeBtn = document.getElementById('aiChatClose');
    var form = document.getElementById('aiChatForm');
    var input = document.getElementById('aiChatInput');
    var messages = document.getElementById('aiChatMessages');

    if (!toggle || !panel || !form) return;

    function openChat() {
        panel.hidden = false;
        toggle.classList.add('active');
        input.focus();
    }

    function closeChat() {
        panel.hidden = true;
        toggle.classList.remove('active');
    }

    toggle.addEventListener('click', function () {
        if (panel.hidden) openChat();
        else closeChat();
    });

    closeBtn.addEventListener('click', closeChat);

    document.querySelectorAll('.ai-suggestion').forEach(function (btn) {
        btn.addEventListener('click', function () {
            input.value = btn.dataset.msg;
            form.dispatchEvent(new Event('submit'));
        });
    });

    function appendMessage(text, type, products) {
        var div = document.createElement('div');
        div.className = 'ai-msg ai-msg-' + type;

        var p = document.createElement('p');
        p.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
        div.appendChild(p);

        if (products && products.length > 0) {
            var grid = document.createElement('div');
            grid.className = 'ai-product-cards';
            products.forEach(function (prod) {
                var card = document.createElement('a');
                card.href = prod.url;
                card.className = 'ai-product-card';
                card.innerHTML =
                    '<img src="' + prod.image + '" alt="">' +
                    '<div><strong>' + prod.name + '</strong><span>' + prod.price + '</span></div>';
                grid.appendChild(card);
            });
            div.appendChild(grid);
        }

        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function appendTyping() {
        var div = document.createElement('div');
        div.className = 'ai-msg ai-msg-bot ai-typing';
        div.id = 'aiTyping';
        div.innerHTML = '<p><span></span><span></span><span></span></p>';
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        var el = document.getElementById('aiTyping');
        if (el) el.remove();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = input.value.trim();
        if (!msg) return;

        appendMessage(msg, 'user');
        input.value = '';
        input.disabled = true;
        appendTyping();

        fetch('api/ai-chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            removeTyping();
            appendMessage(data.reply || 'Sorry, I could not process that.', 'bot', data.products || []);
        })
        .catch(function () {
            removeTyping();
            appendMessage('Connection error. Please try again.', 'bot');
        })
        .finally(function () {
            input.disabled = false;
            input.focus();
        });
    });
});
