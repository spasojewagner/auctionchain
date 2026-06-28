@extends('layouts.app')

@section('title', 'Moje aukcije')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tag me-2"></i>Moje aukcije</h2>
        <a href="{{ route('auctions.create') }}" class="btn btn-primary-custom btn-ripple">
            <i class="fas fa-plus me-2"></i>Nova aukcija
        </a>
    </div>

    @if($myAuctions->count() > 0)
        <div class="form-card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Naslov</th>
                            <th>Kategorija</th>
                            <th>Trenutna cena</th>
                            <th>Ponude</th>
                            <th>Status</th>
                            <th>Kraj</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myAuctions as $auction)
                            <tr>
                                <td>
                                    <a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none">
                                        {{ Str::limit($auction->title, 40) }}
                                    </a>
                                </td>
                                <td><small>{{ $auction->category->name }}</small></td>
                                <td><strong>{{ number_format((float) $auction->current_price, 2) }} RSD</strong></td>
                                <td>{{ $auction->bids_count }}</td>
                                <td><span class="badge badge-{{ $auction->status }}">{{ strtoupper($auction->status) }}</span></td>
                                <td><small>{{ $auction->ends_at->format('d.m.Y H:i') }}</small></td>
                                <td>
                                    <a href="{{ route('auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary" title="Pogledaj">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('auctions.edit', $auction) }}" class="btn btn-sm btn-outline-secondary" title="Uredi slike">
                                        <i class="fas fa-images"></i>
                                    </a>
                                    @if($auction->bids_count === 0 && $auction->status === 'active')
                                        <form action="{{ route('auctions.destroy', $auction) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Obrisati aukciju?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Obriši">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $myAuctions->links() }}</div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-tag fa-3x mb-3 text-muted"></i>
            <h5>Još niste postavili nijednu aukciju</h5>
            <a href="{{ route('auctions.create') }}" class="btn btn-primary-custom mt-2">Postavi prvu aukciju</a>
        </div>
    @endif
</div>
@endsection
