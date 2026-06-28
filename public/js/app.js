// === GLOBAL AJAX SETUP ===
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// === TOAST SISTEM ===
function showToast(type, message) {
    const container = document.getElementById('toast-container');
    if (!container) { alert(message); return; }
    const icons = { success: 'fa-check-circle', danger: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = `app-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span></span>`;
    toast.querySelector('span').textContent = message;
    container.appendChild(toast);
    setTimeout(() => { toast.classList.add('hide'); setTimeout(() => toast.remove(), 300); }, 4000);
}

// === RIPPLE ===
function setupRipples() {
    document.querySelectorAll('.btn-ripple').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const circle = document.createElement('span');
            const diameter = Math.max(this.clientWidth, this.clientHeight);
            const rect = this.getBoundingClientRect();
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - rect.left - diameter / 2}px`;
            circle.style.top = `${e.clientY - rect.top - diameter / 2}px`;
            circle.classList.add('ripple');
            const existing = this.querySelector('.ripple');
            if (existing) existing.remove();
            this.appendChild(circle);
            setTimeout(() => circle.remove(), 600);
        });
    });
}

// === REAL-TIME LICITIRANJE ===
class AuctionLive {
    constructor(auctionId) {
        this.auctionId = auctionId;
        this.pollInterval = 3000;
        this.pollTimer = null;
        this.lastBidCount = 0;
        this.lastPrice = null;
        this.init();
    }
    init() {
        this.startPolling();
        const bidForm = document.getElementById('bid-form');
        if (bidForm) bidForm.addEventListener('submit', (e) => this.handleBidSubmit(e));
        window.addEventListener('beforeunload', () => this.stopPolling());
    }
    startPolling() { this.poll(); this.pollTimer = setInterval(() => this.poll(), this.pollInterval); }
    stopPolling() { if (this.pollTimer) { clearInterval(this.pollTimer); this.pollTimer = null; } }
    async poll() {
        try {
            const response = await fetch(`/auctions/${this.auctionId}/poll`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            this.updateUI(data);
        } catch (error) { console.error('Polling error:', error); }
    }
    updateUI(data) {
        const priceEl = document.getElementById('current-price');
        if (priceEl) {
            if (this.lastPrice !== null && this.lastPrice !== data.current_price) {
                priceEl.parentElement.classList.remove('price-bump');
                void priceEl.parentElement.offsetWidth;
                priceEl.parentElement.classList.add('price-bump');
            }
            priceEl.textContent = data.current_price;
            this.lastPrice = data.current_price;
        }
        const bidCountEl = document.getElementById('bid-count');
        if (bidCountEl) bidCountEl.textContent = data.bid_count;
        const timeEl = document.getElementById('time-remaining');
        if (timeEl) timeEl.textContent = data.time_remaining;
        this.renderBidHistory(data.bids);
        if (!data.is_active && this.lastBidCount > 0) {
            this.stopPolling();
            showToast('info', 'Aukcija je završena. Osvežavam stranicu...');
            setTimeout(() => location.reload(), 1500);
        }
        this.lastBidCount = data.bid_count;
    }
    renderBidHistory(bids) {
        const container = document.getElementById('bid-history');
        if (!container) return;
        if (bids.length === 0) { container.innerHTML = '<div class="text-center text-muted py-4">Još nema ponuda. Budite prvi!</div>'; return; }
        container.innerHTML = bids.map(bid => `
            <div class="bid-item ${bid.is_mine ? 'mine' : ''}">
                <div class="bid-item-info">
                    <span class="bid-item-user">${this.escapeHtml(bid.user_name)} ${bid.is_mine ? '(Vi)' : ''}</span>
                    <span class="bid-item-time">${bid.created_at}</span>
                </div>
                <span class="bid-item-amount">${bid.amount} RSD</span>
            </div>`).join('');
    }
    async handleBidSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const amountInput = form.querySelector('input[name="amount"]');
        const submitBtn = form.querySelector('button[type="submit"]');
        const errorEl = document.getElementById('bid-error');
        if (errorEl) errorEl.textContent = '';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Šaljem...';
        try {
            const response = await fetch(`/auctions/${this.auctionId}/bids`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ amount: parseFloat(amountInput.value) })
            });
            const data = await response.json();
            if (data.success) {
                showToast('success', data.message);
                amountInput.value = '';
                this.poll();
                this.updateNavbarBalance(data.user);
            } else {
                if (errorEl) errorEl.textContent = data.message;
                showToast('danger', data.message);
            }
        } catch (error) {
            if (errorEl) errorEl.textContent = 'Greška pri slanju ponude. Pokušajte ponovo.';
            showToast('danger', 'Greška pri slanju ponude.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Licitiraj';
        }
    }
    updateNavbarBalance(userData) {
        const balanceEl = document.getElementById('navbar-balance');
        const lockedEl = document.getElementById('navbar-locked');
        if (balanceEl) balanceEl.textContent = userData.balance;
        if (lockedEl) lockedEl.textContent = userData.locked_balance;
    }
    escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
}

// === NOTIFIKACIJE ===
class NotificationCounter {
    constructor() { this.pollInterval = 30000; this.init(); }
    init() { if (!document.getElementById('notification-badge')) return; this.poll(); setInterval(() => this.poll(), this.pollInterval); }
    async poll() {
        try {
            const response = await fetch('/profile/notifications/count');
            if (!response.ok) return;
            const data = await response.json();
            this.updateBadge(data.count);
        } catch (error) {}
    }
    updateBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (!badge) return;
        if (count > 0) { badge.textContent = count > 99 ? '99+' : count; badge.style.display = 'flex'; }
        else { badge.style.display = 'none'; }
    }
}

// === GALERIJA SLIKA ===
function setupImageGallery() {
    const thumbnails = document.querySelectorAll('.auction-thumbnail');
    const mainImage = document.getElementById('main-auction-image');
    if (!thumbnails.length || !mainImage) return;
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            thumbnails.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            const url = thumb.dataset.url;
            if (url) mainImage.style.backgroundImage = `url('${url}')`;
        });
    });
}

// === INIT ===
document.addEventListener('DOMContentLoaded', () => {
    if (window.AOS) AOS.init({ duration: 650, once: true, offset: 60, easing: 'ease-out-cubic' });
    setupRipples();
    const auctionContainer = document.getElementById('auction-live');
    if (auctionContainer) new AuctionLive(parseInt(auctionContainer.dataset.auctionId));
    new NotificationCounter();
    setupImageGallery();
    document.querySelectorAll('.alert-auto-dismiss').forEach(alert => {
        setTimeout(() => { alert.classList.remove('show'); setTimeout(() => alert.remove(), 300); }, 4000);
    });
});
