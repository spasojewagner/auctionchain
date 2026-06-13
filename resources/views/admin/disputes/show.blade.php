@extends('admin.layout')

@section('title', 'Admin - Spor #' . $dispute->id)

@section('admin-content')
<nav class="mb-3">
    <a href="{{ route('admin.disputes.index') }}" class="text-decoration-none">
        <i class="fas fa-arrow-left me-1"></i>Svi sporovi
    </a>
</nav>

<h2 class="mb-4">Spor #{{ $dispute->id }}</h2>

<div class="row">
    <div class="col-md-7">
        <div class="form-card mb-4">
            <h5 class="mb-3">Detalji aukcije</h5>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Aukcija</th>
                    <td><a href="{{ route('auctions.show', $dispute->auction) }}">{{ $dispute->auction->title }}</a></td>
                </tr>
                <tr>
                    <th>Prodavac</th>
                    <td>{{ $dispute->auction->seller->name }} ({{ $dispute->auction->seller->email }})</td>
                </tr>
                <tr>
                    <th>Kupac (pobednik)</th>
                    <td>{{ $dispute->auction->winner?->name ?? '-' }} ({{ $dispute->auction->winner?->email ?? '-' }})</td>
                </tr>
                <tr>
                    <th>Iznos (zaključan)</th>
                    <td><strong>{{ number_format((float) $dispute->auction->current_price, 2) }} RSD</strong></td>
                </tr>
                <tr>
                    <th>Spor otvorio</th>
                    <td>{{ $dispute->opener->name }}</td>
                </tr>
            </table>

            <hr>
            <h6>Razlog spora:</h6>
            <p style="white-space: pre-line;">{{ $dispute->reason }}</p>

            @if($dispute->status !== 'open')
                <hr>
                <h6>Rešenje ({{ $dispute->resolver->name ?? 'Admin' }}):</h6>
                <p style="white-space: pre-line;">{{ $dispute->resolution }}</p>
            @endif
        </div>

        {{-- Istorija ponuda --}}
        <div class="form-card">
            <h5 class="mb-3">Istorija ponuda</h5>
            @foreach($dispute->auction->bids as $bid)
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span>{{ $bid->user->name }}</span>
                    <span><strong>{{ number_format((float) $bid->amount, 2) }} RSD</strong> <small class="text-muted">{{ $bid->created_at->format('d.m H:i') }}</small></span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- REŠAVANJE --}}
    <div class="col-md-5">
        @if($dispute->status === 'open')
            <div class="form-card">
                <h5 class="mb-3"><i class="fas fa-gavel me-2"></i>Reši spor</h5>
                <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Odluka</label>
                        <div class="form-check">
                            <input type="radio" name="decision" value="buyer" class="form-check-input" id="dec-buyer" required>
                            <label class="form-check-label" for="dec-buyer">
                                <strong>U korist kupca</strong> - novac se vraća kupcu, aukcija se otkazuje
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="radio" name="decision" value="seller" class="form-check-input" id="dec-seller" required>
                            <label class="form-check-label" for="dec-seller">
                                <strong>U korist prodavca</strong> - novac se isplaćuje prodavcu
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Obrazloženje</label>
                        <textarea name="resolution" rows="4" class="form-control" required minlength="10"
                                  placeholder="Objašnjenje odluke..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100"
                            onclick="return confirm('Potvrditi rešenje spora? Ova akcija je nepovratna.')">
                        <i class="fas fa-check me-2"></i>Potvrdi rešenje
                    </button>
                </form>
            </div>
        @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>Ovaj spor je već rešen.
            </div>
        @endif
    </div>
</div>
@endsection
