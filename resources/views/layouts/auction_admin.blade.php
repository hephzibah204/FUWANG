<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auction Admin') | {{ config('app.name', 'Fuwa.NG') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/nexus/css/nexus.css') }}">
    @vite(['resources/js/vendor.js','resources/js/layout.js'])

    <style>
        :root {
            --auction-primary: #f59e0b;
            --auction-secondary: #3b82f6;
            --sidebar-w: 260px;
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
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .auction-sidebar {
            width: var(--sidebar-w);
            background: rgba(15, 23, 42, 0.55);
            border-right: 1px solid rgba(255,255,255,0.05);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 999;
            padding-top: 80px;
            overflow-y: auto;
            transition: 0.3s ease;
        }

        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: 90px;
            min-height: 100vh;
        }

        .nav-item {
            padding: 10px 18px;
            margin: 4px 14px;
            border-radius: 12px;
            color: rgba(255,255,255,0.7);
            transition: 0.25s;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .nav-item:hover {
            background: rgba(245, 158, 11, 0.12);
            color: #fff;
            text-decoration: none;
        }

        .nav-item.active {
            background: rgba(245, 158, 11, 0.22);
            color: #fff;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }

        .glass-card {
            background: var(--auction-glass);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--auction-border);
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
        }

        @media (max-width: 991px) {
            .auction-sidebar { transform: translateX(-100%); }
            .auction-sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $auctionAdmin = auth()->guard('auction_admin')->user();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top auction-navbar px-4">
        <a class="navbar-brand font-weight-bold" href="{{ route('auction.admin.dashboard') }}" style="color: #fff;">
            <i class="fa-solid fa-gavel mr-2" style="color: var(--auction-primary);"></i> Auction Admin
        </a>

        <button class="navbar-toggler" type="button" onclick="document.getElementById('auctionAdminSidebar').classList.toggle('open')">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="ml-auto d-flex align-items-center" style="gap: 12px;">
            <a href="{{ route('public.auctions.index') }}" class="btn btn-outline-light btn-sm">View Auction Hub</a>
            <div class="text-right d-none d-md-block">
                <div class="small text-white-50">Signed in as</div>
                <div class="font-weight-bold">{{ $auctionAdmin?->fullname ?? $auctionAdmin?->email }}</div>
            </div>
            <form action="{{ route('auction.admin.logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
            </form>
        </div>
    </nav>

    <aside class="auction-sidebar" id="auctionAdminSidebar">
        <nav>
            <a href="{{ route('auction.admin.dashboard') }}" class="nav-item {{ request()->routeIs('auction.admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <div class="px-4 py-2 small text-white-50 mt-3">MANAGE</div>
            <a href="{{ route('auction.admin.auctions.index') }}" class="nav-item {{ request()->routeIs('auction.admin.auctions.*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked"></i> Lots
            </a>
            <a href="{{ route('auction.admin.sellers.index') }}" class="nav-item {{ request()->routeIs('auction.admin.sellers.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-check"></i> Sellers
            </a>
        </nav>
    </aside>

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

    @stack('scripts')
</body>
</html>

