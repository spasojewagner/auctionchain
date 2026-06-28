@extends('layouts.app')

@section('title', 'Registracija')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="form-card" data-aos="fade-up">
                <h2 class="text-center mb-1">Registracija</h2>
                <p class="text-center text-muted mb-4">Kreiraj nalog i počni da licitiraš</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Ime i prezime</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lozinka (min 6 karaktera)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Potvrdi lozinku</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 btn-ripple">
                        <i class="fas fa-user-plus me-2"></i>Registruj se
                    </button>
                </form>

                <hr class="my-4">
                <p class="text-center mb-0">
                    Već imate nalog? <a href="{{ route('login') }}">Prijavite se</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
