@extends('layouts.app')

@section('title', 'Uredi slike - ' . $auction->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <nav class="mb-3">
                <a href="{{ route('auctions.show', $auction) }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Nazad na aukciju
                </a>
            </nav>

            <div class="form-card mb-4" data-aos="fade-up">
                <h2 class="mb-1"><i class="fas fa-images me-2"></i>Slike aukcije</h2>
                <p class="text-muted">{{ $auction->title }}</p>

                <div class="row g-3 mt-1">
                    @foreach($auction->images as $img)
                        <div class="col-md-3 col-6">
                            <div class="position-relative" style="border:2px solid {{ $img->is_primary ? 'var(--primary)' : 'transparent' }}; border-radius:10px; overflow:hidden;">
                                <div style="height:140px; background:url('{{ asset('uploads/' . $img->path) }}') center/cover;"></div>

                                @if($img->is_primary)
                                    <span class="badge bg-primary" style="position:absolute; top:6px; left:6px;">Glavna</span>
                                @endif

                                <div class="d-flex gap-1 p-2 bg-light">
                                    @if(!$img->is_primary)
                                        <form action="{{ route('auctions.images.primary', [$auction, $img]) }}" method="POST" class="flex-fill">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary w-100" title="Postavi kao glavnu">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('auctions.images.destroy', [$auction, $img]) }}" method="POST" class="flex-fill"
                                          onsubmit="return confirm('Obrisati ovu sliku?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger w-100" title="Obriši">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($auction->images->count() < 5)
                <div class="form-card" data-aos="fade-up">
                    <h5 class="mb-3"><i class="fas fa-plus me-2"></i>Dodaj slike</h5>
                    <p class="text-muted small">Možeš imati ukupno 5 slika. Trenutno: {{ $auction->images->count() }}.</p>
                    <form action="{{ route('auctions.images.store', $auction) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="images[]" multiple accept="image/*" class="form-control" required>
                            <button type="submit" class="btn btn-primary-custom btn-ripple">
                                <i class="fas fa-upload me-1"></i>Dodaj
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-info">Dostigli ste maksimum od 5 slika. Obriši neku da bi dodao novu.</div>
            @endif
        </div>
    </div>
</div>
@endsection
