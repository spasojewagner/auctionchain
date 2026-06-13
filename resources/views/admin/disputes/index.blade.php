@extends('admin.layout')

@section('title', 'Admin - Sporovi')

@section('admin-content')
<h2 class="mb-4">Sporovi</h2>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Aukcija</th>
                    <th>Otvorio</th>
                    <th>Iznos</th>
                    <th>Status</th>
                    <th>Datum</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disputes as $dispute)
                    <tr>
                        <td>{{ $dispute->id }}</td>
                        <td>
                            <a href="{{ route('auctions.show', $dispute->auction) }}" class="text-decoration-none">
                                {{ Str::limit($dispute->auction->title, 30) }}
                            </a>
                        </td>
                        <td><small>{{ $dispute->opener->name }}</small></td>
                        <td><strong>{{ number_format((float) $dispute->auction->current_price, 0) }} RSD</strong></td>
                        <td>
                            @if($dispute->status === 'open')
                                <span class="badge bg-warning">OTVOREN</span>
                            @elseif($dispute->status === 'resolved_for_buyer')
                                <span class="badge bg-info">REŠEN ZA KUPCA</span>
                            @else
                                <span class="badge bg-success">REŠEN ZA PRODAVCA</span>
                            @endif
                        </td>
                        <td><small>{{ $dispute->created_at->format('d.m.Y H:i') }}</small></td>
                        <td>
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-sm btn-primary-custom">
                                <i class="fas fa-eye me-1"></i>Detalji
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Nema sporova.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $disputes->links() }}
</div>
@endsection
