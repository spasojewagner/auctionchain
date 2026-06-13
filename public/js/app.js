// === GLOBAL AJAX SETUP ===
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// === AUKCIJA - REAL-TIME LICITIRANJE ===
class AuctionLive {
    constructor(auctionId) {
        this.auctionId = auctionId;
        this.pollInterval = 3000;
        this.pollTimer = null;
        this.lastBidCount = 0;
        this.init();
    }

    init() {
        // Pokreni polling
        this.startPolling();

        // Hook bid form
        const bidForm = document.getElementById('bid-form');
        if (bidForm) {
            bidForm.addEventListener('submit', (e) => this.handleBidSubmit(e));
        }

        // Stop polling kad korisnik napusti stranicu
        window.addEventListener('beforeunload', () => this.stopPolling());
    }

    startPolling() {
        this.poll();
        this.pollTimer = setInterval(() => this.poll(), this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    async poll() {
        try {
            const response = await fetch(`/auctions/${this.auctionId}/poll`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) return;
            const data = await response.json();
            this.updateUI(data);
        } catch (error) {
            console.error('Polling error:', error);
        }
    }

    updateUI(data) {
        // Cena
        const priceEl = document.getElementById('current-price');
        if (priceEl) priceEl.textContent = data.current_price + ' RSD';

        // Broj ponuda
        const bidCountEl = document.getElementById('bid-count');
        if (bidCountEl) bidCountEl.textContent = data.bid_count;

        // Vreme preostalo
        const timeEl = document.getElementById('time-remaining');
        if (timeEl) timeEl.textContent = data.time_remaining;

        // Lista ponuda
        this.renderBidHistory(data.bids);

        // Ako se aukcija završila, zaustavi polling i osvezi stranicu
        if (!data.is_active && this.lastBidCount > 0) {
            this.stopPolling();
            setTimeout(() => location.reload(), 1500);
        }

        this.lastBidCount = data.bid_count;
    }

    renderBidHistory(bids) {
        const container = document.getElementById('bid-history');
        if (!container) return;

        if (bids.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4">Još nema ponuda. Budite prvi!</div>';
            return;
        }

        container.innerHTML = bids.map(bid => `
            <div class="bid-item ${bid.is_mine ? 'mine' : ''}">
                <div class="bid-item-info">
                    <span class="bid-item-user">${this.escapeHtml(bid.user_name)} ${bid.is_mine ? '(Vi)' : ''}</span>
                    <span class="bid-item-time">${bid.created_at}</span>
                </div>
                <span class="bid-item-amount">${bid.amount} RSD</span>
            </div>
        `).join('');
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
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ amount: parseFloat(amountInput.value) })
            });

            const data = await response.json();

            if (data.success) {
                this.showFlash('success', data.message);
                amountInput.value = '';
                this.poll(); // odmah refresh

                // Update navbar balans
                this.updateNavbarBalance(data.user);
            } else {
                if (errorEl) errorEl.textContent = data.message;
                else this.showFlash('danger', data.message);
            }
        } catch (error) {
            if (errorEl) errorEl.textContent = 'Greška pri slanju ponude. Pokušajte ponovo.';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Licitiraj';
        }
    }

    updateNavbarBalance(userData) {
        const balanceEl = document.getElementById('navbar-balance');
        const lockedEl = document.getElementById('navbar-locked');
        if (balanceEl) balanceEl.textContent = userData.balance + ' RSD';
        if (lockedEl) lockedEl.textContent = userData.locked_balance + ' RSD';
    }

    showFlash(type, message) {
        const flash = document.createElement('div');
        flash.className = `alert alert-${type} alert-flash alert-dismissible fade show`;
        flash.innerHTML = `${this.escapeHtml(message)} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(flash);
        setTimeout(() => flash.remove(), 4000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// === NOTIFIKACIJE - POLLING ZA BROJAC ===
class NotificationCounter {
    constructor() {
        this.pollInterval = 30000; // 30 sekundi
        this.init();
    }

    init() {
        if (!document.getElementById('notification-badge')) return;
        this.poll();
        setInterval(() => this.poll(), this.pollInterval);
    }

    async poll() {
        try {
            const response = await fetch('/profile/notifications/count');
            if (!response.ok) return;
            const data = await response.json();
            this.updateBadge(data.count);
        } catch (error) {
            // Tiho
        }
    }

    updateBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
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
            if (url) {
                mainImage.style.backgroundImage = `url('${url}')`;
            }
        });
    });
}

// === INIT ===
document.addEventListener('DOMContentLoaded', () => {
    // Aukcija - real-time
    const auctionContainer = document.getElementById('auction-live');
    if (auctionContainer) {
        new AuctionLive(parseInt(auctionContainer.dataset.auctionId));
    }

    // Notifikacije
    new NotificationCounter();

    // Galerija
    setupImageGallery();

    // Auto-dismiss flash poruka
    document.querySelectorAll('.alert-auto-dismiss').forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    });
});
