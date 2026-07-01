@extends('layouts.app')

@section('title', $auction->title)

@section('content')
<div class="container py-4"@if($auction->isActive()) id="auction-live" data-auction-id="{{ $auction->id }}"@endif>
    @php
        $primaryImg = $auction->primaryImage ?? $auction->images->first();
        $mainImgUrl = $primaryImg ? asset('uploads/' . $primaryImg->path) : asset('uploads/placeholder.jpg');
        $isOwner = auth()->check() && (auth()->id() === $auction->seller_id || auth()->user()->isAdmin());
    @endphp

    <nav class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('auctions.index') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Sve aukcije
        </a>
        @if($isOwner)
            <a href="{{ route('auctions.edit', $auction) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-images me-1"></i>Uredi slike
            </a>
        @endif
    </nav>

    <div class="row">
        {{-- SLIKE I OPIS --}}
        <div class="col-md-7">
            <div class="auction-detail mb-4" data-aos="fade-right">
                <div class="auction-main-wrap">
                    <div class="auction-main-blur" style="background-image: url('{{ $mainImgUrl }}');"></div>
                    <div id="main-auction-image" class="auction-main-image" style="background-image: url('{{ $mainImgUrl }}');"></div>
                </div>
                @if($auction->images->count() > 1)
                    <div class="auction-thumbnails">
                        @foreach($auction->images as $idx => $img)
                            <div class="auction-thumbnail {{ $idx === 0 ? 'active' : '' }}"
                                 data-url="{{ asset('uploads/' . $img->path) }}"
                                 style="background-image: url('{{ asset('uploads/' . $img->path) }}')">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="form-card" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="auction-card-category">{{ $auction->category->name }}</span>
                        <h1 class="mt-2">{{ $auction->title }}</h1>
                    </div>
                    <span class="badge badge-{{ $auction->status }} fs-6">
                        {{ strtoupper($auction->status) }}
                    </span>
                </div>

                <hr>

                <h5>Opis</h5>
                <p style="white-space: pre-line;">{{ $auction->description }}</p>

                <hr>

                <div class="row text-muted small">
                    <div class="col-md-6"><strong>Prodavac:</strong> {{ $auction->seller->name }}</div>
                    <div class="col-md-6"><strong>Postavljeno:</strong> {{ $auction->starts_at->format('d.m.Y H:i') }}</div>
                    <div class="col-md-6 mt-2"><strong>Početna cena:</strong> {{ number_format((float) $auction->starting_price, 2) }} RSD</div>
                    <div class="col-md-6 mt-2"><strong>Završetak:</strong> {{ $auction->ends_at->format('d.m.Y H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- LICITACIJA I PONUDE --}}
        <div class="col-md-5">
            <div class="bid-form mb-4" data-aos="fade-left">
                <h5><i class="fas fa-gavel me-2"></i>Trenutno stanje</h5>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Trenutna cena</small>
                        <div class="current-price"><span id="current-price">{{ number_format((float) $auction->current_price, 2) }}</span> RSD</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Broj ponuda</small>
                        <div class="current-price"><span id="bid-count">{{ $auction->bids()->count() }}</span></div>
                    </div>
                </div>

                <div class="time-remaining">
                    <i class="fas fa-clock me-2"></i>
                    <span id="time-remaining">{{ $auction->timeRemaining() }}</span>
                </div>

                @if($auction->isActive())
                    @auth
                        @if(auth()->id() === $auction->seller_id)
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Ovo je vaša aukcija. Ne možete licitirati.
                            </div>
                        @else
                            <form id="bid-form">
                                @csrf
                                <label class="form-label">Vaša ponuda (RSD)</label>
                                <div class="input-group mb-2">
                                    <input type="number" name="amount" class="form-control form-control-lg"
                                           min="{{ (float) $auction->current_price + 0.01 }}" step="0.01"
                                           placeholder="Min. {{ number_format((float) $auction->current_price + 1, 2) }}" required>
                                    <button type="submit" class="btn btn-primary-custom btn-ripple">Licitiraj</button>
                                </div>
                                <div id="bid-error" class="text-danger small"></div>
                                <small class="text-muted">
                                    Dostupan balans: {{ number_format((float) auth()->user()->balance, 2) }} RSD
                                </small>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary-custom w-100 btn-ripple">
                            <i class="fas fa-sign-in-alt me-2"></i>Prijavite se za licitiranje
                        </a>
                    @endauth
                @elseif($auction->status === 'ended' && auth()->check() && auth()->id() === $auction->winner_id)
                    <div class="alert alert-success">
                        <h6><i class="fas fa-trophy me-2"></i>Pobedili ste!</h6>
                        <p class="mb-2 small">Iznos {{ number_format((float) $auction->current_price, 2) }} RSD je zaključan u escrow-u.</p>
                        <form action="{{ route('auctions.confirm', $auction) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check me-1"></i>Potvrdi prijem
                            </button>
                            <a href="{{ route('disputes.create', $auction) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-exclamation-triangle me-1"></i>Otvori spor
                            </a>
                        </form>
                    </div>
                @elseif(!$auction->isActive())
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-flag-checkered me-2"></i>Aukcija je završena.
                        @if($auction->winner)
                            <br><small>Pobednik: {{ $auction->winner->name }}</small>
                        @endif
                    </div>
                @endif
            </div>

            {{-- KUPI ODMAH --}}
            @if($auction->buyNowAvailable() && auth()->check() && auth()->id() !== $auction->seller_id)
                <div class="buy-now-box mb-4" data-aos="fade-left">
                    <div class="mb-1"><i class="fas fa-bolt text-warning me-1"></i> <strong>Kupi odmah</strong></div>
                    <div class="price mb-2">{{ number_format((float) $auction->buy_now_price, 2) }} RSD</div>
                    <p class="small text-muted mb-3">Preskoči licitaciju i kupi predmet odmah po fiksnoj ceni.</p>
                    <form action="{{ route('auctions.buynow', $auction) }}" method="POST"
                          onsubmit="return confirm('Kupiti predmet odmah za {{ number_format((float) $auction->buy_now_price, 2) }} RSD?');">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100 btn-ripple fw-bold">
                            <i class="fas fa-bolt me-1"></i>Kupi odmah
                        </button>
                    </form>
                </div>
            @elseif($auction->buyNowAvailable() && !auth()->check())
                <div class="buy-now-box mb-4">
                    <div class="mb-1"><i class="fas fa-bolt text-warning me-1"></i> <strong>Kupi odmah</strong></div>
                    <div class="price mb-2">{{ number_format((float) $auction->buy_now_price, 2) }} RSD</div>
                    <a href="{{ route('login') }}" class="btn btn-warning w-100">Prijavite se za kupovinu</a>
                </div>
            @endif

            <div class="form-card" data-aos="fade-left" data-aos-delay="100">
                <h5 class="mb-3"><i class="fas fa-list me-2"></i>Istorija ponuda</h5>
                <div id="bid-history" class="bid-history">
                    @forelse($auction->bids->take(10) as $bid)
                        <div class="bid-item {{ auth()->check() && $bid->user_id === auth()->id() ? 'mine' : '' }}">
                            <div class="bid-item-info">
                                <span class="bid-item-user">
                                    {{ $bid->user->name }}
                                    @if(auth()->check() && $bid->user_id === auth()->id()) (Vi) @endif
                                </span>
                                <span class="bid-item-time">{{ $bid->created_at->diffForHumans() }}</span>
                            </div>
                            <span class="bid-item-amount">{{ number_format((float) $bid->amount, 2) }} RSD</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Još nema ponuda. Budite prvi!</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
