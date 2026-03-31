document.addEventListener('DOMContentLoaded', function() {
    const aiFab =
        document.getElementById('aiFabToggle') ||
        document.getElementById('nexusAiFab');
    const aiChat =
        document.getElementById('aiChatCard') ||
        document.getElementById('nexusAiChat');
    const aiClose =
        document.getElementById('aiChatClose') ||
        document.getElementById('nexusAiClose');
    const aiForm = document.getElementById('aiChatForm');
    const aiInput = document.getElementById('aiChatInput');
    const aiBody = document.getElementById('aiChatBody');

    if (!aiFab || !aiChat) return;

    const openClass = 'is-open';

    function setExpanded(expanded) {
        aiFab.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function openChat() {
        aiChat.hidden = false;
        requestAnimationFrame(() => {
            aiChat.classList.add(openClass);
            setExpanded(true);
            if (aiInput) aiInput.focus();
            scrollToBottom();
        });
    }

    function closeChat() {
        aiChat.classList.remove(openClass);
        setExpanded(false);
        window.setTimeout(() => {
            aiChat.hidden = true;
        }, 220);
    }

    function toggleChat() {
        const isOpen = !aiChat.hidden && aiChat.classList.contains(openClass);
        if (isOpen) {
            closeChat();
        } else {
            openChat();
        }
    }

    aiFab.addEventListener('click', toggleChat);
    if (aiClose) {
        aiClose.addEventListener('click', toggleChat);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !aiChat.hidden) {
            closeChat();
        }
    });

    // Form submission
    if (aiForm) {
        aiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = aiInput.value.trim();
            if (!message) return;

            // 1. Append User Message
            appendMessage('user', message);
            aiInput.value = '';

            // 2. Append Loading Indicator
            const loadingId = appendLoading();
            scrollToBottom();

            // 3. Send AJAX Request to Server
            setFormDisabled(true);
            const send = (window.csrfFetch
                ? window.csrfFetch('/ai/chat', { data: { message: message } })
                : fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                }).then(response => response.json()));

            send
            .then(data => {
                removeLoading(loadingId);
                if (data.success) {
                    appendMessage('ai', data.response);
                } else {
                    appendMessage('ai', "I'm sorry, I encountered an error. Please try again.");
                }
                scrollToBottom();
                setFormDisabled(false);
            })
            .catch(error => {
                console.error("AI Chat Error:", error);
                removeLoading(loadingId);
                appendMessage('ai', "I'm having trouble connecting right now. Please check your connection.");
                scrollToBottom();
                setFormDisabled(false);
            });
        });
    }

    function appendMessage(sender, text) {
        const msgWrapper = document.createElement('div');
        msgWrapper.className = sender === 'user' ? 'ai-msg ai-msg-user' : 'ai-msg ai-msg-bot';

        const bubble = document.createElement('div');
        bubble.className = 'ai-msg-content';
        
        if (sender === 'user') {
            bubble.textContent = text;
        } else {
            bubble.innerHTML = safeFormat(text);
        }

        msgWrapper.appendChild(bubble);
        aiBody.appendChild(msgWrapper);
    }

    function appendLoading() {
        const id = 'loading-' + Date.now();
        const msgWrapper = document.createElement('div');
        msgWrapper.id = id;
        msgWrapper.className = 'ai-msg ai-msg-bot';
        
        const bubble = document.createElement('div');
        bubble.className = 'ai-msg-content ai-msg-loading';
        bubble.setAttribute('aria-label', 'Assistant is typing');
        bubble.innerHTML = '<span class="ai-typing"><span class="ai-dot"></span><span class="ai-dot"></span><span class="ai-dot"></span></span>';
        
        msgWrapper.appendChild(bubble);
        aiBody.appendChild(msgWrapper);
        return id;
    }

    function removeLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function setFormDisabled(disabled) {
        if (aiInput) aiInput.disabled = disabled;
        const btn = aiForm ? aiForm.querySelector('button[type="submit"]') : null;
        if (btn) btn.disabled = disabled;
    }

    function scrollToBottom() {
        aiBody.scrollTop = aiBody.scrollHeight;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function safeFormat(value) {
        const escaped = escapeHtml(value);
        const withLinks = escaped.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, function(_m, text, url) {
            return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + text + '</a>';
        });
        return withLinks.replace(/\n/g, '<br>');
    }
});
