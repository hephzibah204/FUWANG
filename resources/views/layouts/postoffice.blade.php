<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logistics Hub') | {{ config('app.name', 'Fuwa.NG') }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/js/vendor.js', 'resources/js/layout.js'])

    <style>
        :root {
            --po-primary: #f59e0b; /* Tarmac Amber */
            --po-primary-hover: #d97706;
            --po-secondary: #131926;
            --po-accent: #38bdf8;
            --bg-dark: #090d16;
            --surface-card: #0e1422;
            --surface-card-hover: #131a2b;
            --surface-raised: #182033;
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-glass-subtle: rgba(255, 255, 255, 0.05);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --po-select-surface: #111827;
            --po-select-surface-hover: #141c2e;
            --po-select-surface-disabled: #090d16;
            --po-select-border: rgba(148, 163, 184, 0.25);
            --po-select-text: #f8fafc;
            --po-select-muted: #94a3b8;
            --po-option-surface: #ffffff;
            --po-option-surface-hover: #f8fafc;
            --po-option-selected: #fde68a;
            --po-option-text: #111827;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            margin: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .navbar-po {
            background: #090d16;
            border-bottom: 1px solid var(--border-glass);
            padding: 0.85rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.15s ease;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
        }

        .po-sidebar {
            width: 280px;
            height: calc(100vh - 70px);
            background: #090d16;
            border-right: 1px solid var(--border-glass);
            padding: 1.5rem;
            position: fixed;
            left: 0;
            top: 70px;
            overflow-y: auto;
        }

        .po-main-content {
            margin-left: 280px;
            padding: 2.5rem;
            min-height: calc(100vh - 70px);
        }

        /* Hero / Public Layout */
        .po-public-content {
            padding: 2rem 0 4rem;
            max-width: 1240px;
            margin: 0 auto;
        }

        .glass-card {
            background: var(--surface-card);
            border: 1px solid var(--border-glass) !important;
            border-radius: 16px;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.15) !important;
            background: var(--surface-card-hover);
        }

        .btn-po-primary {
            background: var(--po-primary);
            color: #0b0f19 !important;
            font-weight: 600;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 0.65rem 1.35rem;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.15s ease;
        }

        .btn-po-primary:hover {
            background: var(--po-primary-hover);
            color: #000 !important;
        }

        .po-menu-item {
            display: flex;
            align-items: center;
            padding: 0.65rem 1rem;
            color: var(--text-secondary);
            text-decoration: none !important;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .po-menu-item i {
            width: 20px;
            margin-right: 10px;
            font-size: 1rem;
            color: var(--text-muted);
        }

        .po-menu-item:hover, .po-menu-item.active {
            background: var(--surface-raised);
            color: var(--text-primary);
        }

        .po-menu-item:hover i, .po-menu-item.active i {
            color: var(--po-primary);
        }

        .tracking-input {
            background: var(--surface-raised);
            border: 1px solid var(--border-glass);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .tracking-input:focus {
            background: var(--surface-card);
            border-color: var(--po-primary);
            color: #fff;
            box-shadow: 0 0 0 1px var(--po-primary);
        }

        /* Accessible logistics selects: light text on dark closed control,
           dark text on light option list for readable native dropdown menus. */
        select.form-control,
        select.tracking-input {
            background-color: var(--po-select-surface) !important;
            color: var(--po-select-text) !important;
            border-color: var(--po-select-border) !important;
        }

        select.form-control:hover,
        select.tracking-input:hover {
            background-color: var(--po-select-surface-hover) !important;
        }

        select.form-control:focus,
        select.tracking-input:focus {
            background-color: var(--po-select-surface-hover) !important;
            color: var(--po-select-text) !important;
            border-color: var(--po-primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.18) !important;
        }

        select.form-control:disabled,
        select.tracking-input:disabled {
            background-color: var(--po-select-surface-disabled) !important;
            color: var(--po-select-muted) !important;
            border-color: rgba(148, 163, 184, 0.22) !important;
            opacity: 1;
            cursor: not-allowed;
        }

        select.form-control option,
        select.tracking-input option {
            background-color: var(--po-option-surface);
            color: var(--po-option-text);
        }

        select.form-control option:hover,
        select.form-control option:focus,
        select.tracking-input option:hover,
        select.tracking-input option:focus {
            background-color: var(--po-option-surface-hover);
            color: var(--po-option-text);
        }

        select.form-control option:checked,
        select.form-control option[selected],
        select.tracking-input option:checked,
        select.tracking-input option[selected] {
            background-color: var(--po-option-selected);
            color: var(--po-option-text);
        }

        .dropdown-menu {
            background: #111827;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .dropdown-item {
            color: #e2e8f0 !important;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: rgba(245, 158, 11, 0.16);
            color: #ffffff !important;
        }

        @media (max-width: 991px) {
            .po-sidebar { display: none; }
            .po-main-content { margin-left: 0; padding: 1.5rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $logisticsDashboardRoute = \Illuminate\Support\Facades\Route::has('services.user.logistics.dashboard')
            ? 'services.user.logistics.dashboard'
            : (\Illuminate\Support\Facades\Route::has('user.logistics.dashboard') ? 'user.logistics.dashboard' : null);
        $logisticsBookRoute = \Illuminate\Support\Facades\Route::has('services.user.logistics.book')
            ? 'services.user.logistics.book'
            : (\Illuminate\Support\Facades\Route::has('user.logistics.book') ? 'user.logistics.book' : null);

        $logisticsDashboardUrl = $logisticsDashboardRoute
            ? route($logisticsDashboardRoute)
            : route('logistics.home');
        $logisticsBookUrl = $logisticsBookRoute
            ? route($logisticsBookRoute)
            : route('logistics.home');
        $helpCenterUrl = \Illuminate\Support\Facades\Route::has('tickets.index')
            ? route('tickets.index')
            : '#';
    @endphp

    <nav class="navbar navbar-expand-lg navbar-po">
        <a class="navbar-brand font-weight-bold d-flex align-items-center" href="{{ route('logistics.home') }}">
            <div class="rounded-lg d-flex align-items-center justify-content-center mr-2" style="width: 35px; height: 35px; background: var(--po-primary);">
                <i class="fa fa-box-open text-dark"></i>
            </div>
            <span class="text-white">Fuwa<span style="color:var(--po-primary)">Post</span></span>
        </a>

        <button class="navbar-toggler text-white" type="button" data-toggle="collapse" data-target="#poNav" aria-controls="poNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="poNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('logistics.home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
            </ul>

            <div class="d-flex align-items-center">
                @guest
                    <a href="{{ route('logistics.login') }}" class="text-white-50 mr-3 text-decoration-none small">Login</a>
                    <a href="{{ route('logistics.register') }}" class="btn btn-po-primary btn-sm px-4">Create Account</a>
                @else
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-toggle="dropdown">
                            <div class="mr-2 text-right d-none d-md-block">
                                <small class="d-block text-white-50">Wallet Balance</small>
                                <span class="font-weight-bold" style="color:var(--po-primary)">₦{{ number_format((float) (auth()->user()->balance?->user_balance ?? 0), 2) }}</span>
                            </div>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-user"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right bg-dark border-glass shadow-lg">
                            <a class="dropdown-item text-white" href="{{ $logisticsDashboardUrl }}"><i class="fa fa-th-large mr-2"></i> Dashboard</a>
                            <div class="dropdown-divider border-white-10"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger"><i class="fa fa-power-off mr-2"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </nav>

    @auth
        <aside class="po-sidebar">
            <div class="mb-4">
                <small class="text-white-50 text-uppercase tracking-wider font-weight-bold" style="font-size: 0.7rem;">Main Menu</small>
            </div>
            <a href="{{ $logisticsDashboardUrl }}" class="po-menu-item {{ request()->routeIs('services.user.logistics.dashboard') || request()->routeIs('user.logistics.dashboard') ? 'active' : '' }}">
                <i class="fa fa-th-large"></i> Overview
            </a>
            <a href="{{ $logisticsBookUrl }}" class="po-menu-item {{ request()->routeIs('services.user.logistics.book') || request()->routeIs('user.logistics.book') ? 'active' : '' }}">
                <i class="fa fa-plus-circle"></i> Book Shipment
            </a>
            <a href="#" class="po-menu-item">
                <i class="fa fa-truck-fast"></i> My Shipments
            </a>
            <a href="#" class="po-menu-item">
                <i class="fa fa-address-book"></i> Address Book
            </a>
            <div class="mt-5 mb-4">
                <small class="text-white-50 text-uppercase tracking-wider font-weight-bold" style="font-size: 0.7rem;">Support</small>
            </div>
            <a href="{{ $helpCenterUrl }}" class="po-menu-item">
                <i class="fa fa-headset"></i> Help Center
            </a>
        </aside>
        <main class="po-main-content">
            @yield('content')
        </main>
    @else
        <main class="po-public-content">
            <div class="container">
                @yield('content')
            </div>
        </main>
    @endauth
    @stack('scripts')
</body>
</html>
