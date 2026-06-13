@php
    $hoursLeft = $auction->ends_at->diffInHours(now(), absolute: true);
    $urgent = $hoursLeft < 6 && $auction->isActive();
@endphp

<div class="auction-card">
    <a href="{{ route('auctions.show', $auction) }}" style="text-decoration: none;">
        <div class="auction-card-image">
            <i class="fas fa-image"></i>
            <span class="badge-status {{ $auction->isActive() ? '' : 'ended' }}">
                {{ $auction->isActive() ? 'AKTIVNA' : strtoupper($auction->status) }}
            </span>
        </div>
    </a>
    <div class="auction-card-body">
        <div class="auction-card-category">{{ $auction->category->name ?? '' }}</div>
        <a href="{{ route('auctions.show', $auction) }}" class="auction-card-title">
            {{ $auction->title }}
        </a>
        <div class="auction-card-meta">
            <div>
                <div class="auction-card-price">{{ number_format((float) $auction->current_price, 0) }} RSD</div>
                <small class="text-muted">{{ $auction->bids_count ?? $auction->bids()->count() }} ponuda</small>
            </div>
            <div class="text-end">
                <div class="auction-card-time {{ $urgent ? 'urgent' : '' }}">
                    <i class="fas fa-clock"></i> {{ $auction->timeRemaining() }}
                </div>
            </div>
        </div>
    </div>
</div>
