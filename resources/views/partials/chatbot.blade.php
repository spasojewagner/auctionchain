{{-- AuctionChain chatbot widget (samostalan: stil + markup + skripta) --}}
<style>
    .acb-toggle {
        position: fixed; bottom: 22px; right: 22px; z-index: 10000;
        width: 60px; height: 60px; border-radius: 50%; border: none;
        background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff;
        font-size: 1.5rem; cursor: pointer; box-shadow: 0 8px 24px rgba(79,70,229,0.45);
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .acb-toggle:hover { transform: scale(1.08); box-shadow: 0 10px 28px rgba(79,70,229,0.6); }
    .acb-toggle .acb-icon-close { display: none; }
    .acb-open .acb-toggle .acb-icon-open { display: none; }
    .acb-open .acb-toggle .acb-icon-close { display: inline; }

    .acb-panel {
        position: fixed; bottom: 94px; right: 22px; z-index: 10000;
        width: 380px; max-width: calc(100vw - 32px); height: 540px; max-height: calc(100vh - 130px);
        background: #fff; border-radius: 16px; overflow: hidden;
        box-shadow: 0 16px 48px rgba(0,0,0,0.25);
        display: flex; flex-direction: column;
        opacity: 0; transform: translateY(20px) scale(0.98); pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    .acb-open .acb-panel { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

    .acb-header { background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; padding: 1rem 1.2rem; }
    .acb-header h6 { margin: 0; font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem; }
    .acb-header p { margin: 0.15rem 0 0; font-size: 0.8rem; opacity: 0.85; }

    .acb-body { flex: 1; overflow-y: auto; padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.6rem; }
    .acb-msg { max-width: 85%; padding: 0.65rem 0.9rem; border-radius: 14px; font-size: 0.92rem; line-height: 1.45; white-space: pre-wrap; word-wrap: break-word; }
    .acb-msg-bot { align-self: flex-start; background: #fff; color: #0f172a; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; }
    .acb-msg-user { align-self: flex-end; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border-bottom-right-radius: 4px; }

    .acb-quick { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.2rem; }
    .acb-quick button {
        background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; border-radius: 16px;
        padding: 0.4rem 0.7rem; font-size: 0.82rem; cursor: pointer; transition: background 0.15s;
    }
    .acb-quick button:hover { background: #e0e7ff; }

    .acb-typing { align-self: flex-start; display: flex; gap: 4px; padding: 0.7rem 0.9rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; }
    .acb-typing span { width: 7px; height: 7px; border-radius: 50%; background: #94a3b8; animation: acbBounce 1.2s infinite ease-in-out; }
    .acb-typing span:nth-child(2) { animation-delay: 0.2s; }
    .acb-typing span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes acbBounce { 0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; } 40% { transform: scale(1); opacity: 1; } }

    .acb-footer { padding: 0.7rem; border-top: 1px solid #e2e8f0; background: #fff; display: flex; gap: 0.5rem; }
    .acb-footer input { flex: 1; border: 1px solid #cbd5e1; border-radius: 10px; padding: 0.6rem 0.8rem; outline: none; font-size: 0.92rem; }
    .acb-footer input:focus { border-color: #6366f1; }
    .acb-footer button { background: #6366f1; border: none; color: #fff; width: 42px; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
    .acb-footer button:hover { background: #4f46e5; }
    .acb-footer button:disabled { opacity: 0.5; cursor: not-allowed; }

    @media (max-width: 480px) {
        .acb-panel { right: 16px; left: 16px; width: auto; bottom: 88px; }
        .acb-toggle { bottom: 16px; right: 16px; }
    }
</style>

<div id="acb-root">
    <div class="acb-panel" id="acb-panel" role="dialog" aria-label="Pomoć chatbot">
        <div class="acb-header">
            <h6><i class="fas fa-robot"></i> AuctionChain Pomoć</h6>
            <p>Pitaj me bilo šta o platformi</p>
        </div>
        <div class="acb-body" id="acb-body"></div>
        <div class="acb-footer">
            <input type="text" id="acb-input" placeholder="Napiši poruku..." autocomplete="off" maxlength="2000">
            <button id="acb-send" aria-label="Pošalji"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <button class="acb-toggle" id="acb-toggle" aria-label="Otvori pomoć">
        <i class="fas fa-comment-dots acb-icon-open"></i>
        <i class="fas fa-times acb-icon-close"></i>
    </button>
</div>

<script>
(function () {
    const root = document.getElementById('acb-root');
    const toggle = document.getElementById('acb-toggle');
    const body = document.getElementById('acb-body');
    const input = document.getElementById('acb-input');
    const sendBtn = document.getElementById('acb-send');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const endpoint = "{{ route('chat') }}";

    const history = [];
    let greeted = false;

    const quickQuestions = [
        'Kako da licitiram?',
        'Kako da uplatim novac?',
        'Šta je escrow?',
        'Kako da prodam predmet?',
    ];

    function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    function addMsg(role, text) {
        const div = document.createElement('div');
        div.className = 'acb-msg ' + (role === 'user' ? 'acb-msg-user' : 'acb-msg-bot');
        div.textContent = text;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
        return div;
    }

    function addQuick() {
        const wrap = document.createElement('div');
        wrap.className = 'acb-quick';
        quickQuestions.forEach(q => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = q;
            b.addEventListener('click', () => { send(q); wrap.remove(); });
            wrap.appendChild(b);
        });
        body.appendChild(wrap);
        body.scrollTop = body.scrollHeight;
    }

    let typingEl = null;
    function typing(on) {
        if (on) {
            typingEl = document.createElement('div');
            typingEl.className = 'acb-typing';
            typingEl.innerHTML = '<span></span><span></span><span></span>';
            body.appendChild(typingEl);
            body.scrollTop = body.scrollHeight;
        } else if (typingEl) {
            typingEl.remove(); typingEl = null;
        }
    }

    function greet() {
        if (greeted) return;
        greeted = true;
        addMsg('bot', 'Zdravo! Ja sam AuctionChain asistent. Kako mogu da pomognem? Možeš me pitati bilo šta ili izabrati:');
        addQuick();
    }

    async function send(text) {
        text = (text || '').trim();
        if (!text) return;
        addMsg('user', text);
        history.push({ role: 'user', content: text });
        input.value = '';
        sendBtn.disabled = true;
        typing(true);

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ messages: history.slice(-12) })
            });
            const data = await res.json();
            typing(false);
            if (data.success) {
                addMsg('bot', data.reply);
                history.push({ role: 'assistant', content: data.reply });
            } else {
                addMsg('bot', data.message || 'Izvini, došlo je do greške. Pokušaj ponovo.');
            }
        } catch (e) {
            typing(false);
            addMsg('bot', 'Greška u konekciji. Pokušaj ponovo.');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    }

    toggle.addEventListener('click', () => {
        document.body.classList.toggle('acb-open');
        if (document.body.classList.contains('acb-open')) {
            greet();
            setTimeout(() => input.focus(), 200);
        }
    });

    sendBtn.addEventListener('click', () => send(input.value));
    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); send(input.value); } });
})();
</script>
