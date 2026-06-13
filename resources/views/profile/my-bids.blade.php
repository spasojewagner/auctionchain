@extends('layouts.app')

@section('title', 'Moje ponude')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-gavel me-2"></i>Moje ponude</h2>

    @if($wonAuctions->count() > 0)
        <h5 class="mb-3"><i class="fas fa-trophy text-warning me-2"></i>Pobedničke aukcije</h5>
        <div class="row g-3 mb-4">
            @foreach($wonAuctions as $auction)
                <div class="col-md-4">
                    <div class="form-card">
                        <span class="badge badge-{{ $auction->status }} mb-2">{{ strtoupper($auction->status) }}</span>
                        <h6><a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none">{{ Str::limit($auction->title, 50) }}</a></h6>
                        <p class="mb-1"><strong>{{ number_format((float) $auction->current_price, 2) }} RSD</strong></p>
                        @if($auction->status === 'ended')
                            <div class="d-flex gap-2 mt-2">
                                <form action="{{ route('auctions.confirm', $auction) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Potvrdi prijem
                                    </button>
                                </form>
                                <a href="{{ route('disputes.create', $auction) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h5 class="mb-3">Sve moje ponude</h5>
    @if($myBids->count() > 0)
        <div class="form-card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Aukcija</th>
                            <th>Moja ponuda</th>
                            <th>Trenutna cena</th>
                            <th>Status aukcije</th>
                            <th>Kraj</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myBids as $bid)
                            <tr>
                                <td>
                                    <a href="{{ route('auctions.show', $bid->auction) }}" class="text-decoration-none">
                                        {{ Str::limit($bid->auction->title, 40) }}
                                    </a>
                                </td>
                                <td><strong>{{ number_format((float) $bid->amount, 2) }} RSD</strong></td>
                                <td>{{ number_format((float) $bid->auction->current_price, 2) }} RSD</td>
                                <td><span class="badge badge-{{ $bid->auction->status }}">{{ strtoupper($bid->auction->status) }}</span></td>
                                <td><small>{{ $bid->auction->ends_at->format('d.m.Y H:i') }}</small></td>
                                <td>
                                    @if((float) $bid->amount == (float) $bid->auction->current_price)
                                        <span class="badge bg-success"><i class="fas fa-crown"></i> Najviša</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $myBids->links() }}</div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-gavel fa-3x mb-3 text-muted"></i>
            <h5>Još niste licitirali</h5>
            <a href="{{ route('auctions.index') }}" class="btn btn-primary-custom mt-2">Pregledaj aukcije</a>
        </div>
    @endif
</div>
@endsection
