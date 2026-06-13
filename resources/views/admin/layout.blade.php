@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- SIDEBAR --}}
        <div class="col-md-2 admin-sidebar p-0">
            <div class="px-3 mb-3">
                <h5 class="text-white mt-3"><i class="fas fa-cog me-2"></i>Admin panel</h5>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-chart-line me-2"></i>Pregled
                </a>
                <a class="nav-link {{ request()->routeIs('admin.auctions.*') ? 'active' : '' }}" href="{{ route('admin.auctions.index') }}">
                    <i class="fas fa-tag me-2"></i>Aukcije
                </a>
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="fas fa-folder me-2"></i>Kategorije
                </a>
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users me-2"></i>Korisnici
                </a>
                <a class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}" href="{{ route('admin.disputes.index') }}">
                    <i class="fas fa-balance-scale me-2"></i>Sporovi
                </a>
                <hr class="text-white-50 mx-3">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-arrow-left me-2"></i>Nazad na sajt
                </a>
            </nav>
        </div>

        {{-- SADRŽAJ --}}
        <div class="col-md-10 py-4 px-4">
            @yield('admin-content')
        </div>
    </div>
</div>
@endsection
