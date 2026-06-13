@extends('admin.layout')

@section('title', 'Admin - Aukcije')

@section('admin-content')
<h2 class="mb-4">Sve aukcije</h2>

<div class="form-card mb-3">
    <form method="GET" action="{{ route('admin.auctions.index') }}" class="row g-2">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Pretraga po naslovu..."
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">Svi statusi</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktivne</option>
                <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Završene</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Kompletirane</option>
                <option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Sporne</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Otkazane</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary-custom w-100">Filtriraj</button>
        </div>
    </form>
</div>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Naslov</th>
                    <th>Prodavac</th>
                    <th>Kategorija</th>
                    <th>Cena</th>
                    <th>Ponude</th>
                    <th>Status</th>
                    <th>Kraj</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auctions as $auction)
                    <tr>
                        <td><a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none">{{ Str::limit($auction->title, 30) }}</a></td>
                        <td><small>{{ $auction->seller->name }}</small></td>
                        <td><small>{{ $auction->category->name }}</small></td>
                        <td><strong>{{ number_format((float) $auction->current_price, 0) }}</strong></td>
                        <td>{{ $auction->bids_count }}</td>
                        <td><span class="badge badge-{{ $auction->status }}">{{ strtoupper($auction->status) }}</span></td>
                        <td><small>{{ $auction->ends_at->format('d.m.Y') }}</small></td>
                        <td>
                            <form action="{{ route('admin.auctions.destroy', $auction) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Obrisati aukciju? Ovo briše i sve ponude!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $auctions->links() }}
</div>
@endsection
