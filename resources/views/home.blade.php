@extends('layouts.app')

@section('title', 'AuctionChain - Pronađi najbolje ponude')

@section('content')
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1>Licitiraj. Pobedi. Sačuvaj.</h1>
                <p>Bezbedna onlajn aukcija sa zaštitom kupca i prodavca. Postavi svoj predmet ili pronađi unikatne aukcije u realnom vremenu.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('auctions.index') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-search me-2"></i>Pregledaj aukcije
                    </a>
                    @auth
                        <a href="{{ route('auctions.create') }}" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-plus me-2"></i>Postavi aukciju
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Registruj se</a>
                    @endauth
                </div>
            </div>
            <div class="col-md-5 d-none d-md-block text-center">
                <i class="fas fa-gavel" style="font-size: 12rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</section>

<div class="container">
    {{-- KATEGORIJE --}}
    <section class="mb-5">
        <h2 class="mb-4">Kategorije</h2>
        <div class="row g-3">
            @foreach($categories as $category)
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('auctions.index', ['category' => $category->id]) }}"
                       class="card text-decoration-none p-3 text-center h-100"
                       style="border: 1px solid #e2e8f0; transition: all 0.2s;">
                        <div class="text-primary mb-2" style="font-size: 1.8rem;">
                            <i class="fas fa-tag"></i>
                        </div>
                        <h5 class="mb-1">{{ $category->name }}</h5>
                        <small class="text-muted">{{ $category->auctions_count }} aktivnih</small>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ISTAKNUTE AUKCIJE --}}
    @if($featuredAuctions->count() > 0)
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-fire text-danger me-2"></i>Trending aukcije</h2>
            <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary btn-sm">Vidi sve</a>
        </div>
        <div class="row g-4">
            @foreach($featuredAuctions as $auction)
                <div class="col-md-4 col-lg-4">
                    @include('partials.auction-card', ['auction' => $auction])
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- USKORO SE ZAVRŠAVAJU --}}
    @if($endingSoon->count() > 0)
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-hourglass-half text-warning me-2"></i>Uskoro se završavaju</h2>
        </div>
        <div class="row g-4">
            @foreach($endingSoon as $auction)
                <div class="col-md-3">
                    @include('partials.auction-card', ['auction' => $auction])
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- KAKO RADI --}}
    <section class="mb-5 py-5" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <div class="text-center mb-5">
            <h2>Kako radi AuctionChain?</h2>
            <p class="text-muted">Jednostavno, sigurno i brzo</p>
        </div>
        <div class="row text-center g-4 px-4">
            <div class="col-md-4">
                <div class="mb-3" style="font-size: 3rem; color: #6366f1;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h5>1. Registruj se</h5>
                <p class="text-muted">Kreiraj nalog i dobij 10.000 RSD virtuelnog balansa za licitiranje.</p>
            </div>
            <div class="col-md-4">
                <div class="mb-3" style="font-size: 3rem; color: #10b981;">
                    <i class="fas fa-gavel"></i>
                </div>
                <h5>2. Licitiraj</h5>
                <p class="text-muted">Sredstva se sigurno zaključavaju u escrow-u dok aukcija ne završi.</p>
            </div>
            <div class="col-md-4">
                <div class="mb-3" style="font-size: 3rem; color: #f59e0b;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5>3. Potvrdi prijem</h5>
                <p class="text-muted">Nakon prijema robe, novac se oslobađa prodavcu. Sve zaštićeno.</p>
            </div>
        </div>
    </section>
</div>
@endsection
