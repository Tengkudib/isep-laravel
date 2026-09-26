@php
    // Kesan sama ada halaman semasa ialah halaman bab, supaya bot boleh rujuk nota bab tersebut
    $__chatbot_chapter_id = (int) ($chatbotChapterId ?? 0);
@endphp
<style>
    .chatbot-fab {
        position: fixed; right: 24px; bottom: 24px; z-index: 400;
        width: 58px; height: 58px; border-radius: 50%; border: none;
        background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary));
        color: white; font-size: 1.4rem; display: flex; align-items: center; justify-content: center;
        box-shadow: var(--isep-shadow-lg); cursor: pointer;
        transition: transform var(--isep-duration) var(--isep-ease), box-shadow var(--isep-duration) var(--isep-ease);
    }
    .chatbot-fab:hover { transform: scale(1.08) translateY(-2px); }
    .chatbot-fab .fa-xmark { display: none; }
    .chatbot-fab.open .fa-robot { display: none; }
    .chatbot-fab.open .fa-xmark { display: inline; }

    .chatbot-panel {
        position: fixed; right: 24px; bottom: 92px; z-index: 400;
        width: 360px; max-width: calc(100vw - 32px); height: 480px; max-height: calc(100vh - 140px);
        background: var(--isep-card-bg); border-radius: var(--isep-r-xl);
        box-shadow: var(--isep-shadow-xl); display: none; flex-direction: column; overflow: hidden;
        opacity: 0; transform: translateY(12px) scale(0.98);
        transition: opacity 180ms var(--isep-ease), transform 180ms var(--isep-ease);
        border: 1px solid rgba(123,104,238,0.12);
    }
    .chatbot-panel.open { display: flex; opacity: 1; transform: translateY(0) scale(1); }
    .chatbot-header {
        background: linear-gradient(135deg, var(--isep-primary-dark), var(--isep-primary), var(--isep-secondary));
        color: white; padding: 14px 18px; display: flex; align-items: center; gap: 10px; flex-shrink: 0;
    }
    .chatbot-header .avatar {
        width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .chatbot-header .name { font-weight: 700; font-size: 0.95rem; margin-bottom: 0; }
    .chatbot-header .status { font-size: 0.72rem; opacity: 0.85; }
    .chatbot-body { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; background: var(--isep-bg); }
    .chatbot-msg { max-width: 85%; padding: 10px 14px; border-radius: var(--isep-r-lg); font-size: 0.88rem; line-height: 1.45; word-wrap: break-word; }
    .chatbot-msg.user { align-self: flex-end; background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary)); color: white; border-bottom-right-radius: var(--isep-r-sm); }
    .chatbot-msg.bot { align-self: flex-start; background: var(--isep-card-bg); color: var(--isep-text); border: 1px solid rgba(123,104,238,0.1); border-bottom-left-radius: var(--isep-r-sm); }
    .chatbot-msg.bot.error { background: rgba(255,193,7,0.12); border-color: rgba(255,193,7,0.3); color: var(--isep-text); }
    .chatbot-msg .chatbot-code { background: rgba(0,0,0,0.08); padding: 8px 10px; border-radius: var(--isep-r-sm); font-family: 'Consolas', monospace; font-size: 0.8rem; white-space: pre-wrap; margin: 6px 0; overflow-x: auto; }
    [data-theme="dark"] .chatbot-msg .chatbot-code { background: rgba(255,255,255,0.08); }
    .chatbot-typing { align-self: flex-start; display: flex; gap: 4px; padding: 12px 14px; }
    .chatbot-typing span { width: 7px; height: 7px; border-radius: 50%; background: var(--isep-text-faint, #a0a8b8); animation: chatbotBounce 1.2s infinite ease-in-out; }
    .chatbot-typing span:nth-child(2) { animation-delay: 0.15s; }
    .chatbot-typing span:nth-child(3) { animation-delay: 0.3s; }
    @keyframes chatbotBounce { 0%, 60%, 100% { transform: translateY(0); opacity: 0.5; } 30% { transform: translateY(-5px); opacity: 1; } }
    .chatbot-context-pill {
        margin: 10px 16px 0; padding: 6px 12px; border-radius: var(--isep-r-lg); background: rgba(123,104,238,0.1);
        color: var(--isep-primary); font-size: 0.72rem; font-weight: 600; text-align: center; flex-shrink: 0;
    }
    .chatbot-input-row { display: flex; gap: 8px; padding: 12px; border-top: 1px solid rgba(123,104,238,0.1); flex-shrink: 0; background: var(--isep-card-bg); }
    .chatbot-input-row textarea {
        flex: 1; resize: none; border: 1px solid var(--isep-border, #dee2e6); border-radius: var(--isep-r-lg);
        padding: 9px 12px; font-size: 0.85rem; font-family: inherit; background: var(--isep-bg); color: var(--isep-text);
        max-height: 80px;
    }
    .chatbot-input-row textarea:focus { outline: none; border-color: var(--isep-secondary); box-shadow: 0 0 0 3px rgba(32,178,170,0.15); }
    .chatbot-input-row button {
        width: 40px; height: 40px; border-radius: 50%; border: none; flex-shrink: 0;
        background: linear-gradient(135deg, var(--isep-primary), var(--isep-secondary)); color: white;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: transform var(--isep-duration) var(--isep-ease);
    }
    .chatbot-input-row button:hover { transform: scale(1.08); }
    .chatbot-input-row button:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    @media (prefers-reduced-motion: reduce) { .chatbot-typing span { animation: none; opacity: 0.8; } }
    @media (max-width: 480px) {
        .chatbot-panel { right: 16px; bottom: 84px; }
        .chatbot-fab { right: 16px; bottom: 16px; }
    }
</style>

<button type="button" class="chatbot-fab" id="chatbotFab" onclick="toggleChatbot()" title="{{ t('iSEP Tutor - Tanya AI', 'iSEP Tutor - Ask AI') }}">
    <i class="fas fa-robot"></i>
    <i class="fas fa-xmark"></i>
</button>

<div class="chatbot-panel" id="chatbotPanel">
    <div class="chatbot-header">
        <div class="avatar"><i class="fas fa-robot"></i></div>
        <div>
            <div class="name">iSEP Tutor</div>
            <div class="status">{{ t('Pembantu belajar AI anda', 'Your AI learning assistant') }}</div>
        </div>
    </div>
    @if ($__chatbot_chapter_id > 0)
    <div class="chatbot-context-pill"><i class="fas fa-book-open me-1"></i> {{ t('Tahu tentang bab yang sedang anda belajar', 'Aware of the chapter you are studying') }}</div>
    @endif
    <div class="chatbot-body" id="chatbotBody">
        <div class="chatbot-msg bot">{{ t('Hai! Saya iSEP Tutor 🤖. Ada apa-apa soalan pengaturcaraan yang boleh saya bantu?', 'Hi! I\'m iSEP Tutor 🤖. Got any programming questions I can help with?') }}</div>
    </div>
    <div class="chatbot-input-row">
        <textarea id="chatbotInput" rows="1" placeholder="{{ t('Taip soalan anda...', 'Type your question...') }}" onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault();sendChatbotMessage();}"></textarea>
        <button type="button" id="chatbotSendBtn" onclick="sendChatbotMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
(function () {
    const CHATBOT_CSRF = {!! json_encode(csrf_token()) !!};
    const CHATBOT_CHAPTER_ID = {{ (int) $__chatbot_chapter_id }};
    let chatbotHistory = [];
    let chatbotBusy = false;

    window.toggleChatbot = function () {
        const panel = document.getElementById('chatbotPanel');
        const fab = document.getElementById('chatbotFab');
        const willOpen = !panel.classList.contains('open');
        panel.classList.toggle('open', willOpen);
        fab.classList.toggle('open', willOpen);
        if (willOpen) document.getElementById('chatbotInput').focus();
    };

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderMessageHtml(text) {
        const escaped = escapeHtml(text);
        return escaped
            .replace(/```([\s\S]*?)```/g, function (_, code) { return '<pre class="chatbot-code">' + code.trim() + '</pre>'; })
            .replace(/\n/g, '<br>');
    }

    function appendMessage(role, text, isError) {
        const body = document.getElementById('chatbotBody');
        const div = document.createElement('div');
        div.className = 'chatbot-msg ' + (role === 'user' ? 'user' : 'bot') + (isError ? ' error' : '');
        div.innerHTML = renderMessageHtml(text);
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
    }

    function showTyping() {
        const body = document.getElementById('chatbotBody');
        const div = document.createElement('div');
        div.className = 'chatbot-typing';
        div.id = 'chatbotTypingIndicator';
        div.innerHTML = '<span></span><span></span><span></span>';
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
    }

    function hideTyping() {
        const el = document.getElementById('chatbotTypingIndicator');
        if (el) el.remove();
    }

    window.sendChatbotMessage = function () {
        if (chatbotBusy) return;
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        if (!message) return;

        appendMessage('user', message);
        chatbotBusy = true;
        document.getElementById('chatbotSendBtn').disabled = true;
        input.value = '';
        showTyping();

        const body = new URLSearchParams({
            message: message,
            chapter_id: String(CHATBOT_CHAPTER_ID),
            history: JSON.stringify(chatbotHistory),
            _token: CHATBOT_CSRF,
        });

        fetch({!! json_encode(route('student.chatbot')) !!}, { method: 'POST', body, headers: { 'Accept': 'application/json' } })
            .then(function (r) {
                return r.json().catch(function () {
                    // Respons bukan JSON (cth. halaman ralat 500 / timeout proksi) - tunjuk kod status untuk diagnosis
                    return { error: {!! json_encode(t('Ralat pelayan. Sila cuba lagi.', 'Server error. Please try again.')) !!} + ' (HTTP ' + r.status + ')' };
                });
            })
            .then(function (data) {
                hideTyping();
                if (data.error) {
                    appendMessage('bot', data.error, true);
                } else {
                    appendMessage('bot', data.reply);
                    chatbotHistory.push({ role: 'user', content: message });
                    chatbotHistory.push({ role: 'assistant', content: data.reply });
                }
            })
            .catch(function () {
                hideTyping();
                appendMessage('bot', {!! json_encode(t('Ralat sambungan. Sila cuba lagi.', 'Connection error. Please try again.')) !!}, true);
            })
            .finally(function () {
                chatbotBusy = false;
                document.getElementById('chatbotSendBtn').disabled = false;
            });
    };
})();
</script>
