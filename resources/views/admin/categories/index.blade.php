@extends('admin.layout')

@section('title', 'Admin - Kategorije')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Kategorije</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary-custom">
        <i class="fas fa-plus me-2"></i>Nova kategorija
    </a>
</div>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Naziv</th>
                    <th>Slug</th>
                    <th>Opis</th>
                    <th>Broj aukcija</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td><small class="text-muted">{{ Str::limit($category->description, 50) }}</small></td>
                        <td><span class="badge bg-secondary">{{ $category->auctions_count }}</span></td>
                        <td>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($category->auctions_count === 0)
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Obrisati kategoriju?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Nema kategorija.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $categories->links() }}
</div>
@endsection
