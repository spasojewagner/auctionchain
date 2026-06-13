@extends('admin.layout')

@section('title', 'Admin - Pregled')

@section('admin-content')
<h2 class="mb-4">Pregled platforme</h2>

{{-- STATISTIKE --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="stat-card-label">Ukupno korisnika</div>
            <div class="stat-card-value">{{ $stats['total_users'] }}</div>
            <small class="text-muted">{{ $stats['active_users'] }} aktivnih</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-card-label">Aktivne aukcije</div>
            <div class="stat-card-value">{{ $stats['active_auctions'] }}</div>
            <small class="text-muted">{{ $stats['total_auctions'] }} ukupno</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-card-label">Ukupno ponuda</div>
            <div class="stat-card-value">{{ $stats['total_bids'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="stat-card-label">Otvoreni sporovi</div>
            <div class="stat-card-value">{{ $stats['open_disputes'] }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-card-label">Završene aukcije (completed)</div>
            <div class="stat-card-value">{{ $stats['completed_auctions'] }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card success">
            <div class="stat-card-label">Ukupan promet (completed)</div>
            <div class="stat-card-value">{{ number_format((float) $stats['total_volume'], 0) }} RSD</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- TOP KATEGORIJE --}}
    <div class="col-md-5">
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Top kategorije</h5>
            @forelse($topCategories as $cat)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span>{{ $cat->name }}</span>
                    <span class="badge bg-primary">{{ $cat->auction_count }} aukcija</span>
                </div>
            @empty
                <p class="text-muted">Nema podataka.</p>
            @endforelse
        </div>
    </div>

    {{-- NAJNOVIJE AUKCIJE --}}
    <div class="col-md-7">
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Najnovije aukcije</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Naslov</th>
                            <th>Prodavac</th>
                            <th>Cena</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAuctions as $auction)
                            <tr>
                                <td><a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none">{{ Str::limit($auction->title, 25) }}</a></td>
                                <td><small>{{ $auction->seller->name }}</small></td>
                                <td><small>{{ number_format((float) $auction->current_price, 0) }}</small></td>
                                <td><span class="badge badge-{{ $auction->status }}">{{ strtoupper($auction->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
