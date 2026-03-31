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
            --po-primary: #f59e0b; /* Amber/Orange for Logistics */
            --po-secondary: #1e293b;
            --po-accent: #3b82f6;
            --bg-dark: #0f172a;
            --glass-white: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: #fff;
            margin: 0;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(245, 158, 11, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 40%);
            background-attachment: fixed;
        }

        .navbar-po {
            background: rgba(10, 15, 25, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }

        .nav-link {
            color: rgba(255,255,255,0.7) !important;
            font-weight: 500;
            transition: 0.3s;
            padding: 0.5rem 1rem !important;
        }

        .nav-link:hover {
            color: var(--po-primary) !important;
        }

        .po-sidebar {
            width: 280px;
            height: calc(100vh - 70px);
            background: rgba(255,255,255,0.01);
            border-right: 1px solid var(--border-glass);
            padding: 2rem 1.5rem;
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
            padding: 2.5rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .glass-card {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.08) !important;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
        }
        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255,255,255,0.15) !important;
            box-shadow: 0 25px 50px -10px rgba(0,0,0,0.5);
        }

        .btn-po-primary {
            background: var(--po-primary);
            color: #000;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            transition: 0.3s;
        }

        .btn-po-primary:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);
        }

        .po-menu-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            color: rgba(255,255,255,0.6);
            text-decoration: none !important;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: 0.3s;
        }

        .po-menu-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .po-menu-item:hover, .po-menu-item.active {
            background: rgba(245, 158, 11, 0.15);
            color: var(--po-primary);
            transform: translateX(5px);
        }

        .tracking-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border-glass);
            color: #fff;
            border-radius: 15px;
            padding: 1.2rem;
            font-size: 1.1rem;
        }

        .tracking-input:focus {
            background: rgba(255,255,255,0.08);
            border-color: var(--po-primary);
            color: #fff;
            box-shadow: none;
        }

        @media (max-width: 991px) {
            .po-sidebar { display: none; }
            .po-main-content { margin-left: 0; padding: 1.5rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-po">
        <a class="navbar-brand font-weight-bold d-flex align-items-center" href="{{ route('public.logistics.index') }}">
            <div class="rounded-lg d-flex align-items-center justify-content-center mr-2" style="width: 35px; height: 35px; background: var(--po-primary);">
                <i class="fa fa-box-open text-dark"></i>
            </div>
            <span class="text-white">Fuwa<span style="color:var(--po-primary)">Post</span></span>
        </a>

        <button class="navbar-toggler text-white" type="button" data-toggle="collapse" data-target="#poNav">
            <i class="fa fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="poNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('public.logistics.index') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
            </ul>

            <div class="d-flex align-items-center">
                @guest
                    <a href="{{ route('login') }}" class="text-white-50 mr-3 text-decoration-none small">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-po-primary btn-sm px-4">Get Started</a>
                @else
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-toggle="dropdown">
                            <div class="mr-2 text-right d-none d-md-block">
                                <small class="d-block text-white-50">Wallet Balance</small>
                                <span class="font-weight-bold" style="color:var(--po-primary)">₦{{ number_format(auth()->user()->balance ?? 0, 2) }}</span>
                            </div>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-user"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right bg-dark border-glass shadow-lg">
                            <a class="dropdown-item text-white" href="{{ route('user.logistics.dashboard') }}"><i class="fa fa-th-large mr-2"></i> Dashboard</a>
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
            <a href="{{ route('user.logistics.dashboard') }}" class="po-menu-item {{ request()->routeIs('user.logistics.dashboard') ? 'active' : '' }}">
                <i class="fa fa-th-large"></i> Overview
            </a>
            <a href="{{ route('user.logistics.book') }}" class="po-menu-item {{ request()->routeIs('user.logistics.book') ? 'active' : '' }}">
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
            <a href="{{ route('user.tickets') }}" class="po-menu-item">
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
