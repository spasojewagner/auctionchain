@extends('admin.layout')

@section('title', 'Admin - Nova kategorija')

@section('admin-content')
<h2 class="mb-4">Nova kategorija</h2>

<div class="row">
    <div class="col-md-6">
        <div class="form-card">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Naziv</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Opis (opciono)</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary-custom">Sačuvaj</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Otkaži</a>
            </form>
        </div>
    </div>
</div>
@endsection
