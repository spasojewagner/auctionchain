{{-- AuctionChain chatbot widget (vođeni meni + AI rezerva, samostalan fajl) --}}
<style>
    .acb-toggle { position: fixed; bottom: 22px; right: 22px; z-index: 10000; width: 64px; height: 64px; border-radius: 50%; border: none; padding: 0; overflow: hidden; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; font-size: 1.5rem; cursor: pointer; box-shadow: 0 8px 24px rgba(79,70,229,0.45); display: flex; align-items: center; justify-content: center; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .acb-toggle:hover { transform: scale(1.08); box-shadow: 0 10px 28px rgba(79,70,229,0.6); }
    .acb-toggle .acb-logo { width: 100%; height: 100%; object-fit: cover; }
    .acb-toggle .acb-icon-close { display: none; }
    .acb-open .acb-toggle .acb-icon-open { display: none; }
    .acb-open .acb-toggle .acb-icon-close { display: inline; }

    .acb-panel { position: fixed; bottom: 96px; right: 22px; z-index: 10000; width: 380px; max-width: calc(100vw - 32px); height: 560px; max-height: calc(100vh - 130px); background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 16px 48px rgba(0,0,0,0.25); display: flex; flex-direction: column; opacity: 0; transform: translateY(20px) scale(0.98); pointer-events: none; transition: opacity 0.25s ease, transform 0.25s ease; }
    .acb-open .acb-panel { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

    .acb-header { background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; padding: 1rem 1.2rem; display: flex; justify-content: space-between; align-items: flex-start; }
    .acb-header h6 { margin: 0; font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 0.55rem; }
    .acb-head-logo { width: 32px; height: 32px; }
    .acb-header p { margin: 0.15rem 0 0; font-size: 0.8rem; opacity: 0.85; }
    .acb-home-btn { background: rgba(255,255,255,0.18); border: none; color: #fff; border-radius: 8px; padding: 0.3rem 0.6rem; font-size: 0.78rem; cursor: pointer; transition: background 0.15s; }
    .acb-home-btn:hover { background: rgba(255,255,255,0.3); }

    .acb-body { flex: 1; overflow-y: auto; padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.55rem; }

    /* red sa avatarom (poruke bota) */
    .acb-row { display: flex; align-items: flex-end; gap: 0.5rem; align-self: flex-start; max-width: 92%; }
    .acb-avatar { width: 32px; height: 32px; flex-shrink: 0; }

    .acb-msg { max-width: 86%; padding: 0.65rem 0.9rem; border-radius: 14px; font-size: 0.92rem; line-height: 1.45; white-space: pre-wrap; word-wrap: break-word; animation: acbIn 0.25s ease; }
    @keyframes acbIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .acb-msg-bot { background: #fff; color: #0f172a; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; }
    .acb-row .acb-msg-bot { max-width: 100%; }
    .acb-msg-user { align-self: flex-end; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border-bottom-right-radius: 4px; }

    .acb-opts { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.15rem; align-self: flex-start; }
    .acb-opts button { background: #fff; color: #4f46e5; border: 1.5px solid #c7d2fe; border-radius: 18px; padding: 0.45rem 0.85rem; font-size: 0.85rem; cursor: pointer; transition: background 0.15s, border-color 0.15s; }
    .acb-opts button:hover { background: #eef2ff; border-color: #6366f1; }
    .acb-opts button.acb-back { color: #64748b; border-color: #e2e8f0; }

    .acb-typing { display: flex; gap: 4px; padding: 0.7rem 0.9rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; }
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
        .acb-panel { right: 16px; left: 16px; width: auto; bottom: 90px; }
        .acb-toggle { bottom: 16px; right: 16px; }
    }
</style>

<div id="acb-root">
    <div class="acb-panel" id="acb-panel" role="dialog" aria-label="Pomoć">
        <div class="acb-header">
            <div>
                <h6><img src="{{ asset('images/chatbot.png') }}" alt="" class="acb-head-logo"> AuctionChain Pomoć</h6>
                <p>Izaberi temu ili napiši pitanje</p>
            </div>
            <button class="acb-home-btn" id="acb-home" title="Glavni meni"><i class="fas fa-home"></i></button>
        </div>
        <div class="acb-body" id="acb-body"></div>
        <div class="acb-footer">
            <input type="text" id="acb-input" placeholder="Napiši pitanje..." autocomplete="off" maxlength="2000">
            <button id="acb-send" aria-label="Pošalji"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <button class="acb-toggle" id="acb-toggle" aria-label="Otvori pomoć">
        <img src="{{ asset('images/chatbot.png') }}" alt="AuctionChain bot" class="acb-logo acb-icon-open">
        <i class="fas fa-times acb-icon-close"></i>
    </button>
</div>

<script>
(function () {
    const toggle = document.getElementById('acb-toggle');
    const homeBtn = document.getElementById('acb-home');
    const body = document.getElementById('acb-body');
    const input = document.getElementById('acb-input');
    const sendBtn = document.getElementById('acb-send');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const endpoint = "{{ route('chat') }}";
    const BOT_AVATAR = "{{ asset('images/chatbot.png') }}";

    const BACK = { label: '← Glavni meni', next: 'root', back: true };

    const tree = {
        root: {
            messages: ['Zdravo! 👋 Ja sam AuctionChain asistent. Izaberi temu ili napiši svoje pitanje:'],
            options: [
                { label: 'Licitiranje', next: 'bidding' },
                { label: 'Plaćanje i uplata', next: 'payments' },
                { label: 'Kupi odmah', next: 'buynow' },
                { label: 'Prodaja predmeta', next: 'selling' },
                { label: 'Escrow zaštita', next: 'escrow' },
                { label: 'Ocenjivanje', next: 'ratings' },
                { label: 'Nalog i balans', next: 'account' },
                { label: 'Sporovi', next: 'disputes' },
            ]
        },
        bidding: {
            messages: ['Šta te zanima o licitiranju?'],
            options: [
                { label: 'Kako da licitiram', next: 'bid_how' },
                { label: 'Ako me nadlicitiraju', next: 'bid_outbid' },
                BACK,
            ]
        },
        bid_how: {
            messages: ['Otvori aukciju koja te zanima.', 'Unesi iznos veći od trenutne cene i klikni „Licitiraj". Tvoj iznos se odmah zaključava u escrow-u dok traje aukcija.'],
            options: [{ label: '← Licitiranje', next: 'bidding' }, BACK]
        },
        bid_outbid: {
            messages: ['Ako te neko nadlicitira, tvoj zaključani iznos ti se automatski vraća na dostupan balans.', 'Dobiješ i obaveštenje na zvonce i email.'],
            options: [{ label: '← Licitiranje', next: 'bidding' }, BACK]
        },
        payments: {
            messages: ['Koji način uplate te zanima?'],
            options: [
                { label: 'Karticom (Stripe)', next: 'pay_stripe' },
                { label: 'MetaMask', next: 'pay_metamask' },
                { label: 'Gde uplaćujem?', next: 'pay_where' },
                BACK,
            ]
        },
        pay_stripe: {
            messages: ['Uplata karticom ide preko Stripe-a (siguran sistem za plaćanje).', 'Idi na Profil → „Uplata na balans", unesi iznos i plati karticom. Za test koristi karticu 4242 4242 4242 4242, bilo koji budući datum i bilo koji CVC.'],
            options: [{ label: '← Plaćanje', next: 'payments' }, BACK]
        },
        pay_metamask: {
            messages: ['Možeš povezati MetaMask Web3 novčanik na svom profilu (sekcija „Web3 novčanik" → Connect MetaMask).', 'Adresa novčanika se sačuva uz tvoj nalog.'],
            options: [{ label: '← Plaćanje', next: 'payments' }, BACK]
        },
        pay_where: {
            messages: ['Sva uplata sredstava se radi na stranici Profil → „Uplata na balans".'],
            options: [{ label: '← Plaćanje', next: 'payments' }, BACK]
        },
        buynow: {
            messages: ['„Kupi odmah" je fiksna cena koju neke aukcije imaju.', 'Klikom na „Kupi odmah" momentalno kupuješ predmet i preskačeš licitaciju — aukcija se odmah završava u tvoju korist, a iznos se zaključava u escrow-u dok ne potvrdiš prijem.'],
            options: [{ label: 'Kako se plaća?', next: 'payments' }, BACK]
        },
        selling: {
            messages: ['Šta te zanima o prodaji?'],
            options: [
                { label: 'Kako da postavim aukciju', next: 'sell_how' },
                { label: 'AI opis predmeta', next: 'sell_ai' },
                { label: 'Uređivanje slika', next: 'sell_images' },
                BACK,
            ]
        },
        sell_how: {
            messages: ['Klikni „Nova aukcija" u meniju.', 'Unesi naziv, opis, 1–5 slika, početnu cenu, opciono „Kupi odmah" cenu i trajanje, pa klikni „Postavi aukciju".'],
            options: [{ label: '← Prodaja', next: 'selling' }, BACK]
        },
        sell_ai: {
            messages: ['Pri kreiranju aukcije klikni dugme „Generiši opis (AI)".', 'Ako si izabrao sliku, AI će je pogledati i opisati predmet; ako nisi, piše opis na osnovu naziva. Tekst uvek možeš sam da doradiš.'],
            options: [{ label: '← Prodaja', next: 'selling' }, BACK]
        },
        sell_images: {
            messages: ['Na stranici svoje aukcije klikni „Uredi slike".', 'Tu možeš dodati nove slike, obrisati postojeće ili izabrati koja je glavna (thumbnail).'],
            options: [{ label: '← Prodaja', next: 'selling' }, BACK]
        },
        escrow: {
            messages: ['Escrow je sistem zaštite novca za obe strane.', 'Kad licitiraš, iznos ti se zaključava. Ako te nadlicitiraju — vraća se. Ako pobediš — ostaje zaključan dok ne potvrdiš prijem robe, pa tek onda ide prodavcu.', 'Tako su i kupac i prodavac zaštićeni.'],
            options: [{ label: 'Ako roba ne stigne?', next: 'disputes' }, BACK]
        },
        ratings: {
            messages: ['Nakon završene transakcije (kada kupac potvrdi prijem robe), kupac i prodavac mogu da ocene jedan drugog.', 'Na stranici te aukcije se pojavi forma: 1–5 zvezdica + opcioni komentar. Jedna ocena po aukciji — ponovnim slanjem menjaš svoju postojeću.', 'Prosečna ocena prodavca (★ i broj ocena) se vidi pored njegovog imena na svakoj njegovoj aukciji.'],
            options: [
                { label: 'Kako se završava transakcija?', next: 'rate_complete' },
                BACK,
            ]
        },
        rate_complete: {
            messages: ['Kada pobediš aukciju (ili kupiš preko „Kupi odmah"), na stranici aukcije klikneš „Potvrdi prijem" kad ti roba stigne.', 'Tada novac iz escrow-a ide prodavcu, aukcija dobija status „završena" — i tek tada se otključava ocenjivanje.'],
            options: [{ label: '← Ocenjivanje', next: 'ratings' }, BACK]
        },
        account: {
            messages: ['Šta te zanima o nalogu?'],
            options: [
                { label: 'Registracija', next: 'acc_register' },
                { label: 'Moj balans', next: 'acc_balance' },
                { label: 'Notifikacije', next: 'acc_notif' },
                BACK,
            ]
        },
        acc_register: {
            messages: ['Klikni „Registracija" i unesi ime, email i lozinku.', 'Novi nalozi počinju sa balansom 0 — sredstva uplaćuješ preko Stripe-a ili MetaMask-a.'],
            options: [{ label: '← Nalog', next: 'account' }, BACK]
        },
        acc_balance: {
            messages: ['Balans vidiš gore u meniju.', 'Dostupan balans je za licitiranje, a „zaključano" je novac trenutno vezan u aktivnim licitacijama ili escrow-u.'],
            options: [{ label: '← Nalog', next: 'account' }, BACK]
        },
        acc_notif: {
            messages: ['Obaveštenja dobiješ na zvonce u meniju i na email — kad te nadlicitiraju, kad pobediš, o sporovima itd.'],
            options: [{ label: '← Nalog', next: 'account' }, BACK]
        },
        disputes: {
            messages: ['Ako roba ne stigne ili nije kako je opisana, otvori spor.', 'Na završenoj aukciji koju si dobio klikni „Otvori spor". Administrator će ga pregledati i rešiti — vraćanje novca kupcu ili isplata prodavcu.'],
            options: [BACK]
        },
    };

    const aiHistory = [];
    let started = false;
    let currentOpts = null;
    let busy = false;

    function addMsg(role, text) {
        if (role === 'bot') {
            const row = document.createElement('div');
            row.className = 'acb-row';
            const av = document.createElement('img');
            av.className = 'acb-avatar';
            av.src = BOT_AVATAR;
            av.alt = '';
            const div = document.createElement('div');
            div.className = 'acb-msg acb-msg-bot';
            div.textContent = text;
            row.appendChild(av);
            row.appendChild(div);
            body.appendChild(row);
        } else {
            const div = document.createElement('div');
            div.className = 'acb-msg acb-msg-user';
            div.textContent = text;
            body.appendChild(div);
        }
        body.scrollTop = body.scrollHeight;
    }

    function clearOpts() { if (currentOpts) { currentOpts.remove(); currentOpts = null; } }

    function showOpts(options) {
        clearOpts();
        const wrap = document.createElement('div');
        wrap.className = 'acb-opts';
        options.forEach(opt => {
            const b = document.createElement('button');
            b.type = 'button';
            if (opt.back) b.classList.add('acb-back');
            b.textContent = opt.label;
            b.addEventListener('click', () => onPick(opt));
            wrap.appendChild(b);
        });
        body.appendChild(wrap);
        body.scrollTop = body.scrollHeight;
        currentOpts = wrap;
    }

    let typingEl = null;
    function typing(on) {
        if (on && !typingEl) {
            typingEl = document.createElement('div');
            typingEl.className = 'acb-row';
            typingEl.innerHTML = '<img class="acb-avatar" src="' + BOT_AVATAR + '" alt=""><div class="acb-typing"><span></span><span></span><span></span></div>';
            body.appendChild(typingEl);
            body.scrollTop = body.scrollHeight;
        } else if (!on && typingEl) { typingEl.remove(); typingEl = null; }
    }

    function botSay(messages, options) {
        busy = true;
        let i = 0;
        (function next() {
            if (i < messages.length) {
                typing(true);
                setTimeout(() => {
                    typing(false);
                    addMsg('bot', messages[i]);
                    i++;
                    setTimeout(next, 220);
                }, 650);
            } else {
                busy = false;
                if (options && options.length) showOpts(options);
            }
        })();
    }

    function goNode(id) {
        const node = tree[id];
        if (!node) return;
        clearOpts();
        botSay(node.messages, node.options);
    }

    function onPick(opt) {
        if (busy) return;
        clearOpts();
        addMsg('user', opt.label.replace(/^←\s*/, ''));
        goNode(opt.next);
    }

    async function askAI(text) {
        text = (text || '').trim();
        if (!text || busy) return;
        clearOpts();
        addMsg('user', text);
        aiHistory.push({ role: 'user', content: text });
        busy = true; sendBtn.disabled = true;
        typing(true);
        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ messages: aiHistory.slice(-10) })
            });
            const data = await res.json();
            typing(false);
            if (data.success) {
                addMsg('bot', data.reply);
                aiHistory.push({ role: 'assistant', content: data.reply });
            } else {
                addMsg('bot', data.message || 'Izvini, došlo je do greške.');
            }
        } catch (e) {
            typing(false);
            addMsg('bot', 'Greška u konekciji. Pokušaj ponovo.');
        } finally {
            busy = false; sendBtn.disabled = false;
            showOpts([{ label: '← Glavni meni', next: 'root', back: true }]);
            input.focus();
        }
    }

    function start() {
        if (started) return;
        started = true;
        goNode('root');
    }

    toggle.addEventListener('click', () => {
        document.body.classList.toggle('acb-open');
        if (document.body.classList.contains('acb-open')) {
            start();
            setTimeout(() => input.focus(), 250);
        }
    });
    homeBtn.addEventListener('click', () => { if (!busy) { addMsg('user', 'Glavni meni'); goNode('root'); } });
    sendBtn.addEventListener('click', () => { const v = input.value; input.value = ''; askAI(v); });
    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); const v = input.value; input.value = ''; askAI(v); } });
})();
</script>
