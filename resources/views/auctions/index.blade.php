@extends('layouts.app')

@section('title', 'Sve aukcije')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Sve aukcije</h1>

    <div class="row">
        {{-- FILTERI --}}
        <div class="col-md-3 mb-4">
           <div class="form-card filter-sticky">
                <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filteri</h5>

                <form method="GET" action="{{ route('auctions.index') }}">
                    <div class="mb-3">
                        <label class="form-label">Pretraga</label>
                        <input type="text" name="search" class="form-control" placeholder="Naslov..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Aktivne</option>
                            <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Završene</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Sve</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategorija</label>
                        <select name="category" class="form-select">
                            <option value="">Sve kategorije</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cena (RSD)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control" placeholder="Od"
                                       value="{{ request('min_price') }}">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control" placeholder="Do"
                                       value="{{ request('max_price') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sortiraj</label>
                        <select name="sort" class="form-select">
                            <option value="ends_soon" {{ request('sort') === 'ends_soon' ? 'selected' : '' }}>Završavaju se uskoro</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Najnovije</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Cena: niska prvo</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Cena: visoka prvo</option>
                            <option value="most_bids" {{ request('sort') === 'most_bids' ? 'selected' : '' }}>Najviše ponuda</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 mb-2">Primeni</button>
                    <a href="{{ route('auctions.index') }}" class="btn btn-outline-secondary w-100">Resetuj</a>
                </form>
            </div>
        </div>

        {{-- LISTA AUKCIJA --}}
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Pronađeno: {{ $auctions->total() }} aukcija</span>
            </div>

            @if($auctions->count() > 0)
                <div class="row g-4">
                    @foreach($auctions as $auction)
                        <div class="col-md-6 col-lg-4">
                            @include('partials.auction-card', ['auction' => $auction])
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $auctions->links() }}
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                    <h4>Nema rezultata</h4>
                    <p class="mb-0">Nije pronađena nijedna aukcija sa zadatim filterima.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
