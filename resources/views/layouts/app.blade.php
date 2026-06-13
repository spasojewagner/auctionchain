<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AuctionChain - Aukciona platforma')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-gavel me-2"></i>AuctionChain
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('auctions.index') }}">Aukcije</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auctions.create') }}">
                                <i class="fas fa-plus me-1"></i>Nova aukcija
                            </a>
                        </li>
                    @endauth
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-cog me-1"></i>Admin
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav align-items-center">
                    @auth
                        <li class="nav-item me-3">
                            <span class="navbar-balance">
                                <i class="fas fa-wallet me-1"></i>
                                <span id="navbar-balance">{{ number_format((float) auth()->user()->balance, 2) }}</span> RSD
                                @if((float) auth()->user()->locked_balance > 0)
                                    <span class="locked d-block">
                                        <i class="fas fa-lock"></i>
                                        <span id="navbar-locked">{{ number_format((float) auth()->user()->locked_balance, 2) }}</span> zaključano
                                    </span>
                                @endif
                            </span>
                        </li>

                        <li class="nav-item position-relative me-2">
                            <a class="nav-link" href="{{ route('profile.notifications') }}">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notification-badge"
                                      style="{{ ($unreadNotificationsCount ?? 0) > 0 ? '' : 'display:none' }}">
                                    {{ $unreadNotificationsCount ?? 0 }}
                                </span>
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Moj profil</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.auctions') }}">Moje aukcije</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.bids') }}">Moje ponude</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Odjava</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Prijava</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light btn-sm ms-2" href="{{ route('register') }}">Registracija</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @include('partials.flash')

    <main>
        @yield('content')
    </main>

    <footer class="py-4 mt-5 bg-dark text-white-50">
        <div class="container text-center">
            <p class="mb-0">AuctionChain &copy; {{ date('Y') }} | Seminarski rad - Internet programiranje, FTN Čačak</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
