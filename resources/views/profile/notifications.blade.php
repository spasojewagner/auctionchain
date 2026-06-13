@extends('layouts.app')

@section('title', 'Notifikacije')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-bell me-2"></i>Notifikacije</h2>

            <div class="form-card p-0" style="overflow: hidden;">
                @forelse($notifications as $notification)
                    <div class="notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                @php
                                    $icons = [
                                        'outbid' => 'fa-arrow-trend-up text-warning',
                                        'auction_won' => 'fa-trophy text-success',
                                        'auction_ended_seller' => 'fa-flag-checkered text-primary',
                                        'item_received' => 'fa-box text-info',
                                        'dispute_opened' => 'fa-exclamation-triangle text-danger',
                                        'dispute_resolved' => 'fa-gavel text-secondary',
                                    ];
                                @endphp
                                <i class="fas {{ $icons[$notification->type] ?? 'fa-bell' }} me-2"></i>
                                {{ $notification->message }}
                                @if($notification->auction)
                                    <a href="{{ route('auctions.show', $notification->auction) }}" class="ms-2 small">Vidi aukciju →</a>
                                @endif
                            </div>
                            <small class="text-muted text-nowrap ms-3">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-bell-slash fa-3x mb-3"></i>
                        <p>Nemate notifikacija.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-3">{{ $notifications->links() }}</div>
        </div>
    </div>
</div>
@endsection
