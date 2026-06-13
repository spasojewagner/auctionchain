@extends('layouts.app')

@section('title', 'Prijava')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-card">
                <h2 class="mb-4 text-center">Prijava</h2>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lozinka</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Zapamti me</label>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Prijavi se
                    </button>
                </form>

                <hr class="my-4">
                <p class="text-center mb-0">
                    Nemate nalog? <a href="{{ route('register') }}">Registrujte se</a>
                </p>

                <div class="alert alert-info mt-3 small">
                    <strong>Demo nalozi:</strong><br>
                    Admin: admin@auctionchain.test / admin123<br>
                    Korisnik: marko@test.com / password
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
