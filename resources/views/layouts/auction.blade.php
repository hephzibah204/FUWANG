<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auctions') | {{ config('app.name') }}</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @php
        $assetPrefix = rtrim(preg_replace('#/index\.php$#', '', request()->getBaseUrl()), '/');
    @endphp
    <link rel="stylesheet" href="{{ $assetPrefix . '/assets/nexus/css/nexus.css' }}">
    
    @vite(['resources/js/vendor.js', 'resources/js/layout.js'])
    
    <style>
        :root {
            --auction-primary: #d97706; /* Refined Institutional Amber */
            --auction-primary-hover: #b45309;
            --auction-secondary: #2563eb;
            --sidebar-w: 240px;
            --auction-bg: #090d16;
            --auction-surface: #0f172a;
            --auction-surface-raised: #1e293b;
            --auction-surface-subtle: rgba(15, 23, 42, 0.6);
            --auction-border: rgba(255, 255, 255, 0.08);
            --auction-border-subtle: rgba(255, 255, 255, 0.04);
            --auction-text-main: #f8fafc;
            --auction-text-muted: #94a3b8;
            --auction-text-soft: #64748b;
        }

        [data-theme="light"],
        html[data-theme="light"],
        body.theme-light {
            --auction-bg: #f8fafc;
            --auction-surface: #ffffff;
            --auction-surface-raised: #f1f5f9;
            --auction-surface-subtle: #f8fafc;
            --auction-border: #e2e8f0;
            --auction-border-subtle: #edf2f7;
            --auction-text-main: #0f172a;
            --auction-text-muted: #475569;
            --auction-text-soft: #64748b;
        }
        
        body {
            background-color: var(--auction-bg);
            color: var(--auction-text-main);
            font-family: 'Outfit', sans-serif;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .auction-navbar {
            background: var(--auction-surface);
            border-bottom: 1px solid var(--auction-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            z-index: 1050;
        }

        .auction-sidebar {
            width: var(--sidebar-w);
            background: var(--auction-surface);
            border-right: 1px solid var(--auction-border);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 80px;
            z-index: 1000;
            transition: transform 0.25s ease;
        }

        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: 80px;
            min-height: 100vh;
        }

        .auction-public-content {
            padding-top: 88px;
            padding-bottom: 36px;
            min-height: 100vh;
        }

        .nav-item {
            padding: 9px 16px;
            margin: 2px 12px;
            border-radius: 8px;
            color: var(--auction-text-muted);
            display: flex;
            align-items: center;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(217, 119, 6, 0.12);
            color: var(--auction-primary);
            text-decoration: none;
        }

        .nav-item i { width: 22px; margin-right: 10px; font-size: 0.95rem; }

        @media (max-width: 991px) {
            .auction-sidebar { transform: translateX(-100%); }
            .auction-sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }

        .badge-winning { background: #10b981; color: #fff; }
        .badge-outbid { background: #ef4444; color: #fff; }

        .glass-card {
            background: var(--auction-surface);
            border: 1px solid var(--auction-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .tracking-input {
            background: var(--auction-surface-subtle);
            border: 1px solid var(--auction-border);
            color: var(--auction-text-main);
            border-radius: 8px;
            padding: 0.65rem 0.85rem;
            font-size: 0.9375rem;
            transition: border-color 0.15s ease;
        }

        .tracking-input:focus {
            background: var(--auction-surface);
            border-color: var(--auction-primary);
            color: var(--auction-text-main);
            box-shadow: none;
        }

        .btn-auction-primary {
            background: var(--auction-primary);
            color: #ffffff !important;
            font-weight: 600;
            border: 1px solid var(--auction-primary);
            border-radius: 8px;
            padding: 0.6rem 1.25rem;
            font-size: 0.9375rem;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .btn-auction-primary:hover {
            background: var(--auction-primary-hover);
            border-color: var(--auction-primary-hover);
            color: #ffffff !important;
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
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top auction-navbar py-2.5">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand font-weight-bold d-flex align-items-center" href="{{ route('public.auctions.index') }}" style="color: var(--auction-text-main);">
                <div class="mr-2" style="background: var(--auction-primary); width: 30px; height: 30px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-gavel text-white" style="font-size: 13px;"></i>
                </div>
                <span>Auction Hub</span>
            </a>
            
            <button class="navbar-toggler border-0 d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars-staggered" style="color: var(--auction-text-main);"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="mx-auto my-2 my-lg-0" action="{{ route('public.auctions.index') }}" method="GET" style="max-width: 440px; width: 100%;">
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute" style="left: 14px; top: 11px; color: var(--auction-text-soft);"></i>
                        <input class="form-control pl-5" type="search" name="q" placeholder="Search lots by title or code..." style="border-radius: 8px; background: var(--auction-surface-subtle); border: 1px solid var(--auction-border); color: var(--auction-text-main); font-size: 0.875rem;" value="{{ request('q') }}">
                    </div>
                </form>

                <div class="d-flex align-items-center" style="gap: 12px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1.5" id="themeToggle" title="Toggle theme" style="border-radius: 8px; border-color: var(--auction-border); color: var(--auction-text-muted);">
                        <i class="fa-regular fa-sun d-none" id="themeIconSun"></i>
                        <i class="fa-regular fa-moon" id="themeIconMoon"></i>
                    </button>

                    @if($isAuthed)
                        <div class="text-right d-none d-md-block mr-2">
                            <div class="small" style="color: var(--auction-text-soft); font-size: 0.75rem;">Wallet Balance</div>
                            <div class="font-weight-bold text-success" style="font-variant-numeric: tabular-nums; font-size: 0.9375rem;">₦{{ number_format($user->balance?->user_balance ?? 0, 2) }}</div>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius: 8px; border-color: var(--auction-border); color: var(--auction-text-main);" title="Back to Fuwa.NG">Main Dashboard</a>
                        <form action="{{ route('auction.logout') }}" method="POST" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm px-3" style="border-radius: 8px;">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('auction.login') }}" class="btn btn-link text-decoration-none small font-weight-semibold" style="color: var(--auction-text-muted);">Sign in</a>
                        <a href="{{ route('auction.register') }}" class="btn btn-auction-primary btn-sm px-3.5">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    @if($isAuthed)
        <aside class="auction-sidebar" id="sidebar">
            <nav class="py-2">
                <a href="{{ route('public.auctions.index') }}" class="nav-item {{ Request::routeIs('public.auctions.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-border-all"></i> Browse All Lots
                </a>
                
                <div class="px-3 pt-3 pb-1.5 small font-weight-semibold" style="color: var(--auction-text-soft); font-size: 0.75rem;">Categories</div>
                <a href="{{ route('public.auctions.index', ['category' => 'Electronics']) }}" class="nav-item {{ request('category') === 'Electronics' ? 'active' : '' }}">
                    <i class="fa-solid fa-laptop"></i> Electronics
                </a>
                <a href="{{ route('public.auctions.index', ['category' => 'Vehicles']) }}" class="nav-item {{ request('category') === 'Vehicles' ? 'active' : '' }}">
                    <i class="fa-solid fa-car"></i> Vehicles
                </a>
                <a href="{{ route('public.auctions.index', ['category' => 'Real Estate']) }}" class="nav-item {{ request('category') === 'Real Estate' ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i> Real Estate
                </a>

                <div class="px-3 pt-3 pb-1.5 small font-weight-semibold" style="color: var(--auction-text-soft); font-size: 0.75rem;">My activity</div>
                <a href="{{ $auctionsDashboardUrl }}" class="nav-item {{ Request::routeIs('auction.dashboard') || Request::routeIs('services.auctions.dashboard') || Request::routeIs('auctions.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i> Console Dashboard
                </a>
                <a href="{{ $auctionsBidsUrl }}" class="nav-item">
                    <i class="fa-solid fa-gavel"></i> My Placed Bids
                </a>
                <a href="{{ $auctionsWatchlistUrl }}" class="nav-item">
                    <i class="fa-solid fa-bookmark"></i> Saved Lots
                </a>

                <div class="px-3 pt-3 pb-1.5 small font-weight-semibold" style="color: var(--auction-text-soft); font-size: 0.75rem;">Support</div>
                <a href="{{ url('/help/auctions') }}" class="nav-item">
                    <i class="fa-solid fa-circle-question"></i> Auction Rules &amp; FAQ
                </a>
            </nav>
        </aside>
    @endif

    <!-- Main Content -->
    @if($isAuthed)
        <main class="main-content">
            <div class="container-fluid px-lg-5 py-4">
                @if(session('success'))
                    <div class="alert alert-success border-0 mb-3" style="border-radius: 8px;">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 mb-3" style="border-radius: 8px;">{{ session('error') }}</div>
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

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    var now = Date.now();
                    if (window.__lastSidebarToggleTs && (now - window.__lastSidebarToggleTs) < 300) {
                        return;
                    }
                    window.__lastSidebarToggleTs = now;

                    if (sidebar) {
                        sidebar.classList.toggle('open');
                    }
                    if (navbarCollapse) {
                        navbarCollapse.classList.toggle('show');
                    }
                });
            }

            // Theme toggle sync
            var themeBtn = document.getElementById('themeToggle');
            var iconSun = document.getElementById('themeIconSun');
            var iconMoon = document.getElementById('themeIconMoon');

            function syncThemeIcons(theme) {
                if (theme === 'light') {
                    if (iconSun) iconSun.classList.remove('d-none');
                    if (iconMoon) iconMoon.classList.add('d-none');
                } else {
                    if (iconSun) iconSun.classList.add('d-none');
                    if (iconMoon) iconMoon.classList.remove('d-none');
                }
            }

            var currentTheme = document.documentElement.getAttribute('data-theme') || (document.body.classList.contains('theme-light') ? 'light' : 'dark');
            syncThemeIcons(currentTheme);

            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    var isLight = document.documentElement.getAttribute('data-theme') === 'light' || document.body.classList.contains('theme-light');
                    var newTheme = isLight ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    document.body.classList.toggle('theme-light', newTheme === 'light');
                    try { localStorage.setItem('nexus_theme', newTheme); } catch(e) {}
                    syncThemeIcons(newTheme);
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
