@extends('layouts.app')

@section('title', 'Nova aukcija')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card" data-aos="fade-up">
                <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Postavi novu aukciju</h2>

                <form method="POST" action="{{ route('auctions.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Naziv predmeta</label>
                        <input type="text" name="title" id="auction-title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Opis</label>
                            <button type="button" id="ai-describe-btn" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-wand-magic-sparkles me-1"></i>Generiši opis (AI)
                            </button>
                        </div>
                        <textarea name="description" id="auction-description" rows="5"
                                  class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategorija</label>
                            <select name="category_id" id="auction-category" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Izaberi --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" data-name="{{ $cat->name }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Početna cena (RSD)</label>
                            <input type="number" name="starting_price" min="1" step="0.01"
                                   class="form-control @error('starting_price') is-invalid @enderror"
                                   value="{{ old('starting_price') }}" required>
                            @error('starting_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Trajanje (sati)</label>
                            <select name="duration_hours" class="form-select" required>
                                <option value="6">6 sati</option>
                                <option value="24" selected>1 dan</option>
                                <option value="72">3 dana</option>
                                <option value="168">7 dana</option>
                                <option value="336">14 dana</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">"Kupi odmah" cena (RSD) — opciono</label>
                        <input type="number" name="buy_now_price" min="1" step="0.01"
                               class="form-control @error('buy_now_price') is-invalid @enderror"
                               value="{{ old('buy_now_price') }}" placeholder="Ostavi prazno ako ne želiš opciju">
                        <small class="text-muted">Mora biti veća od početne cene. Kupac može momentalno kupiti predmet po ovoj ceni.</small>
                        @error('buy_now_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slike (1-5, max 5MB svaka)</label>
                        <input type="file" name="images[]" id="auction-images" multiple accept="image/*"
                               class="form-control @error('images') is-invalid @enderror" required>
                        <small class="text-muted">Prva slika će biti glavna (thumbnail).</small>
                        @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom btn-ripple">
                            <i class="fas fa-check me-2"></i>Postavi aukciju
                        </button>
                        <a href="{{ route('auctions.index') }}" class="btn btn-outline-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('ai-describe-btn')?.addEventListener('click', async function () {
    const btn = this;
    const title = document.getElementById('auction-title').value.trim();
    const catSelect = document.getElementById('auction-category');
    const category = catSelect.options[catSelect.selectedIndex]?.dataset.name || '';
    const descEl = document.getElementById('auction-description');
    const fileInput = document.getElementById('auction-images');
    const hasImage = fileInput.files && fileInput.files.length > 0;

    if (!title && !hasImage) {
        showToast('danger', 'Unesite naziv ili izaberite sliku.');
        return;
    }

    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (hasImage ? 'Analiziram sliku...' : 'Generišem...');

    try {
        let res;
        if (hasImage) {
            const fd = new FormData();
            fd.append('image', fileInput.files[0]);
            fd.append('title', title);
            fd.append('category', category);
            res = await fetch('{{ route("ai.describe-image") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: fd
            });
        } else {
            res = await fetch('{{ route("ai.describe") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ title, category })
            });
        }

        const data = await res.json();
        if (data.success) {
            descEl.value = data.description;
            showToast('success', 'Opis generisan!');
        } else {
            showToast('danger', data.message || 'AI nije uspeo da generiše opis.');
        }
    } catch (e) {
        showToast('danger', 'Greška pri pozivu AI servisa.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = original;
    }
});
</script>
@endsection
