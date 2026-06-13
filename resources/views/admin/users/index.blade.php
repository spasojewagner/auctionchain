@extends('admin.layout')

@section('title', 'Admin - Korisnici')

@section('admin-content')
<h2 class="mb-4">Korisnici</h2>

<div class="form-card mb-3">
    <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Pretraga po imenu ili email-u..."
               value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary-custom">Traži</button>
    </form>
</div>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Ime</th>
                    <th>Email</th>
                    <th>Uloga</th>
                    <th>Balans</th>
                    <th>Zaključano</th>
                    <th>Aukcije</th>
                    <th>Status</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td><small>{{ $user->email }}</small></td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge bg-dark">ADMIN</span>
                            @else
                                <span class="badge bg-secondary">KORISNIK</span>
                            @endif
                        </td>
                        <td>{{ number_format((float) $user->balance, 0) }} RSD</td>
                        <td><small class="text-warning">{{ number_format((float) $user->locked_balance, 0) }}</small></td>
                        <td>{{ $user->auctions_count }}</td>
                        <td>
                            @if($user->is_suspended)
                                <span class="badge bg-danger">SUSPENDOVAN</span>
                            @else
                                <span class="badge bg-success">AKTIVAN</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                {{-- Suspenzija --}}
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $user->is_suspended ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                            title="{{ $user->is_suspended ? 'Ukloni suspenziju' : 'Suspenduj' }}">
                                        <i class="fas {{ $user->is_suspended ? 'fa-unlock' : 'fa-ban' }}"></i>
                                    </button>
                                </form>
                                {{-- Dodaj balans --}}
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#balanceModal{{ $user->id }}" title="Dodaj balans">
                                    <i class="fas fa-coins"></i>
                                </button>
                            </div>

                            {{-- Modal za dodavanje balansa --}}
                            <div class="modal fade" id="balanceModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.users.balance', $user) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Dodaj balans - {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label">Iznos (RSD)</label>
                                                    <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Napomena</label>
                                                    <input type="text" name="note" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                                                <button type="submit" class="btn btn-primary-custom">Dodaj</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
