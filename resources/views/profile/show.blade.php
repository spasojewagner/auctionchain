@extends('layouts.app')

@section('title', 'Moj profil')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="form-card mb-4" data-aos="fade-up">
                <div class="text-center mb-3">
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <h4 class="text-center mb-1">{{ $user->name }}</h4>
                <p class="text-center text-muted">{{ $user->email }}</p>
                <p class="text-center small text-muted">Član od {{ $user->created_at->format('d.m.Y') }}</p>

                <hr>

                <div class="text-center mb-2">
                    <small class="text-muted">Dostupan balans</small>
                    <div class="h3 mb-0 text-primary">{{ number_format((float) $user->balance, 2) }} RSD</div>
                </div>

                @if((float) $user->locked_balance > 0)
                    <div class="text-center">
                        <small class="text-muted">Zaključano u escrow-u</small>
                        <div class="h5 text-warning"><i class="fas fa-lock me-1"></i>{{ number_format((float) $user->locked_balance, 2) }} RSD</div>
                    </div>
                @endif
            </div>

            {{-- UPLATA PREKO STRIPE-A --}}
            <div class="form-card mb-4" data-aos="fade-up">
                <h5><i class="fab fa-stripe-s me-2"></i>Uplata na balans</h5>
                <p class="text-muted small">Plaćanje karticom preko Stripe-a (test režim).</p>
                <form method="POST" action="{{ route('deposit.checkout') }}">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="amount" class="form-control" placeholder="Iznos (min 100)" min="100" step="1" required>
                        <button type="submit" class="btn btn-primary-custom btn-ripple">
                            <i class="fas fa-credit-card me-1"></i>Plati
                        </button>
                    </div>
                </form>
            </div>

            {{-- METAMASK CONNECT --}}
            <div class="form-card" data-aos="fade-up" id="wallet-section">
                <h5><i class="fab fa-ethereum me-2"></i>Web3 novčanik</h5>
                <p class="text-muted small">Poveži MetaMask novčanik sa svojim nalogom.</p>
                <div id="wallet-connected" style="{{ $user->wallet_address ? '' : 'display:none' }}">
                    <span class="badge bg-success mb-2"><i class="fas fa-check me-1"></i>Povezan</span>
                    <div class="wallet-address">
                        <i class="fab fa-ethereum me-1"></i>
                        <span id="wallet-display">{{ $user->wallet_address }}</span>
                    </div>
                </div>
                <button id="connect-wallet" class="btn btn-dark w-100 btn-ripple"
                        style="{{ $user->wallet_address ? 'display:none' : '' }}">
                    <i class="fab fa-ethereum me-2"></i>Connect MetaMask
                </button>
            </div>
        </div>

        <div class="col-md-8">
            <div class="form-card mb-4" data-aos="fade-up">
                <h5 class="mb-3"><i class="fas fa-link me-2"></i>Brzi linkovi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route('profile.auctions') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-tag me-2"></i>Moje aukcije
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('profile.bids') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-gavel me-2"></i>Moje ponude
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('auctions.create') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus me-2"></i>Postavi aukciju
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('profile.notifications') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-bell me-2"></i>Notifikacije
                        </a>
                    </div>
                </div>
            </div>

            <div class="form-card" data-aos="fade-up">
                <h5 class="mb-3"><i class="fas fa-history me-2"></i>Istorija transakcija</h5>
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vreme</th>
                                    <th>Tip</th>
                                    <th>Iznos</th>
                                    <th>Balans</th>
                                    <th>Napomena</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $t)
                                    <tr>
                                        <td><small>{{ $t->created_at->format('d.m.Y H:i') }}</small></td>
                                        <td>
                                            @php
                                                $colors = ['deposit' => 'success', 'lock' => 'warning', 'unlock' => 'info', 'payout' => 'primary', 'refund' => 'secondary'];
                                                $labels = ['deposit' => 'Uplata', 'lock' => 'Zaključano', 'unlock' => 'Oslobođeno', 'payout' => 'Isplata', 'refund' => 'Povraćaj'];
                                            @endphp
                                            <span class="badge bg-{{ $colors[$t->type] ?? 'secondary' }}">{{ $labels[$t->type] ?? $t->type }}</span>
                                        </td>
                                        <td class="{{ $t->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $t->amount >= 0 ? '+' : '' }}{{ number_format((float) $t->amount, 2) }} RSD
                                        </td>
                                        <td><small>{{ number_format((float) $t->balance_after, 2) }} RSD</small></td>
                                        <td><small class="text-muted">{{ $t->note }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">Nema transakcija.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const connectBtn = document.getElementById('connect-wallet');
    if (!connectBtn) return;

    connectBtn.addEventListener('click', async () => {
        if (typeof window.ethereum === 'undefined') {
            showToast('danger', 'MetaMask nije instaliran. Instalirajte MetaMask ekstenziju u browseru.');
            return;
        }

        connectBtn.disabled = true;
        const original = connectBtn.innerHTML;
        connectBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Povezivanje...';

        try {
            const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
            const address = accounts[0];

            const res = await fetch('{{ route("profile.wallet") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ wallet_address: address })
            });
            const data = await res.json();

            if (data.success) {
                showToast('success', data.message);
                document.getElementById('wallet-display').textContent = data.wallet;
                document.getElementById('wallet-connected').style.display = 'block';
                connectBtn.style.display = 'none';
            } else {
                showToast('danger', data.message || 'Greška pri povezivanju.');
                connectBtn.disabled = false;
                connectBtn.innerHTML = original;
            }
        } catch (e) {
            showToast('danger', 'Povezivanje je odbijeno ili otkazano.');
            connectBtn.disabled = false;
            connectBtn.innerHTML = original;
        }
    });
});
</script>
<style>
.wallet-address {
    background: #0f172a;
    color: #818cf8;
    padding: 0.6rem 0.8rem;
    border-radius: 8px;
    font-family: monospace;
    font-size: 0.8rem;
    word-break: break-all;
}
</style>
@endsection
