<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auctions') | {{ config('app.name') }}</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php
        $assetPrefix = rtrim(preg_replace('#/index\.php$#', '', request()->getBaseUrl()), '/');
    @endphp
    <link rel="stylesheet" href="{{ $assetPrefix . '/assets/nexus/css/nexus.css' }}">
    
    @vite(['resources/js/vendor.js', 'resources/js/layout.js'])
    
    <style>
        :root {
            --auction-primary: #f59e0b; /* Amber/Gold for Auctions */
            --auction-secondary: #3b82f6;
            --sidebar-w: 240px;
            --auction-bg: #080b12;
            --auction-glass: rgba(255, 255, 255, 0.03);
            --auction-border: rgba(255, 255, 255, 0.08);
        }
        
        body {
            background-color: var(--auction-bg);
            color: #fff;
            font-family: 'Outfit', sans-serif;
        }

        .auction-navbar {
            background: rgba(10, 15, 25, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            z-index: 1050;
        }

        .auction-sidebar {
            width: var(--sidebar-w);
            background: rgba(15, 23, 42, 0.5);
            border-right: 1px solid rgba(255,255,255,0.05);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 80px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: 80px;
            min-height: 100vh;
        }

        .auction-public-content {
            padding-top: 90px;
            padding-bottom: 30px;
            min-height: 100vh;
        }

        .nav-item {
            padding: 10px 20px;
            margin: 4px 15px;
            border-radius: 12px;
            color: rgba(255,255,255,0.6);
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(245, 158, 11, 0.15);
            color: var(--auction-primary);
            text-decoration: none;
            transform: translateX(5px);
        }

        .nav-item i { width: 24px; margin-right: 10px; }

        @media (max-width: 991px) {
            .auction-sidebar { transform: translateX(-100%); }
            .auction-sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }

        .badge-winning { background: #10b981; color: #fff; }
        .badge-outbid { background: #ef4444; color: #fff; }

        .glass-card {
            background: var(--auction-glass);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--auction-border);
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
        }

        .tracking-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--auction-border);
            color: #fff;
            border-radius: 15px;
            padding: 1rem;
            font-size: 1rem;
        }

        .tracking-input:focus {
            background: rgba(255,255,255,0.08);
            border-color: var(--auction-primary);
            color: #fff;
            box-shadow: none;
        }

        .btn-auction-primary {
            background: var(--auction-primary);
            color: #000;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            transition: 0.3s;
        }

        .btn-auction-primary:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);
            color: #000;
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $user = auth()->user();
        $isAuthed = (bool) $user;
        $auctionsDashboardRoute = \Illuminate\Support\Facades\Route::has('auction.dashboard')
            ? 'auction.dashboard'
            : (\Illuminate\Support\Facades\Route::has('services.auctions.dashboard') ? 'services.auctions.dashboard' : null);

        $auctionsDashboardUrl = $auctionsDashboardRoute
            ? route($auctionsDashboardRoute)
            : route('public.auctions.index');
        $auctionsBidsUrl = $auctionsDashboardRoute
            ? route($auctionsDashboardRoute, ['tab' => 'bids'])
            : route('public.auctions.index');
        $auctionsWatchlistUrl = $auctionsDashboardRoute
            ? route($auctionsDashboardRoute, ['tab' => 'watchlist'])
            : route('public.auctions.index');
    @endphp

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top auction-navbar py-3">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand font-weight-bold d-flex align-items-center" href="{{ route('public.auctions.index') }}">
                <div class="mr-2" style="background: var(--auction-primary); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-gavel text-white" style="font-size: 14px;"></i>
                </div>
                <span>Auction Hub</span>
            </a>
            
            <button class="navbar-toggler border-0 d-lg-none" type="button" id="sidebarToggle">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="mx-auto my-2 my-lg-0" action="{{ route('public.auctions.index') }}" method="GET" style="max-width: 500px; width: 100%;">
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute" style="left: 15px; top: 12px; color: rgba(255,255,255,0.4);"></i>
                        <input class="form-control bg-dark border-0 text-white pl-5" type="search" name="q" placeholder="Search for items, categories..." style="border-radius: 20px;" value="{{ request('q') }}">
                    </div>
                </form>

                <div class="d-flex align-items-center" style="gap: 15px;">
                    @if($isAuthed)
                        <div class="text-right d-none d-md-block mr-3">
                            <div class="small text-white-50">Wallet Balance</div>
                            <div class="font-weight-bold text-success">₦{{ number_format($user->balance?->user_balance ?? 0, 2) }}</div>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm" title="Back to Fuwa.NG">Main Dashboard</a>
                        <form action="{{ route('auction.logout') }}" method="POST" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('auction.login') }}" class="btn btn-link text-white text-decoration-none small">Login</a>
                        <a href="{{ route('auction.register') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 10px; background: var(--auction-primary); border:none;">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    @if($isAuthed)
        <aside class="auction-sidebar" id="sidebar">
            <nav>
                <a href="{{ route('public.auctions.index') }}" class="nav-item {{ Request::routeIs('public.auctions.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Browse All
                </a>
                <div class="px-4 py-2 small text-white-50 mt-3">CATEGORIES</div>
                <a href="{{ route('public.auctions.index', ['category' => 'Electronics']) }}" class="nav-item {{ request('category') === 'Electronics' ? 'active' : '' }}">
                    <i class="fa-solid fa-laptop"></i> Electronics
                </a>
                <a href="{{ route('public.auctions.index', ['category' => 'Vehicles']) }}" class="nav-item {{ request('category') === 'Vehicles' ? 'active' : '' }}">
                    <i class="fa-solid fa-car"></i> Vehicles
                </a>
                <a href="{{ route('public.auctions.index', ['category' => 'Real Estate']) }}" class="nav-item {{ request('category') === 'Real Estate' ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i> Real Estate
                </a>

                <div class="px-4 py-2 small text-white-50 mt-3">MY ACTIVITY</div>
                <a href="{{ $auctionsDashboardUrl }}" class="nav-item {{ Request::routeIs('auction.dashboard') || Request::routeIs('services.auctions.dashboard') || Request::routeIs('auctions.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i> My Dashboard
                </a>
                <a href="{{ $auctionsBidsUrl }}" class="nav-item">
                    <i class="fa-solid fa-gavel"></i> My Bids
                </a>
                <a href="{{ $auctionsWatchlistUrl }}" class="nav-item">
                    <i class="fa-solid fa-bookmark"></i> Watchlist
                </a>

                <div class="px-4 py-2 small text-white-50 mt-3">HELP</div>
                <a href="{{ url('/help/auctions') }}" class="nav-item">
                    <i class="fa-solid fa-circle-question"></i> How it Works
                </a>
            </nav>
        </aside>
    @endif

    <!-- Main Content -->
    @if($isAuthed)
        <main class="main-content">
            <div class="container-fluid px-lg-5 py-4">
                @if(session('success'))
                    <div class="alert alert-success bg-success text-white border-0">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger bg-danger text-white border-0">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    @else
        <main class="auction-public-content">
            <div class="container px-lg-5">
                @yield('content')
            </div>
        </main>
    @endif

    <script nonce="{{ $cspNonce ?? '' }}">
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('sidebar');
            var sidebarToggle = document.getElementById('sidebarToggle');
            var navbarCollapse = document.getElementById('navbarNav');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                    if (navbarCollapse) {
                        navbarCollapse.classList.toggle('show');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
