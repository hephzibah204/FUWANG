<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="@yield('meta_description', \App\Models\SystemSetting::get('seo_description', 'Your comprehensive Nigerian identity verification and legal services platform.'))">
    <meta name="keywords" content="@yield('meta_keywords', \App\Models\SystemSetting::get('seo_keywords', 'verification, identity, legal hub, NIN, BVN'))">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\SystemSetting::get('seo_title', \App\Models\SystemSetting::get('site_name', config('app.name'))))</title>
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title')) ?: \App\Models\SystemSetting::get('seo_title', \App\Models\SystemSetting::get('site_name', config('app.name'))))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description')) ?: \App\Models\SystemSetting::get('seo_description', ''))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    @php $ogImg = \App\Models\SystemSetting::get('seo_default_image_url') ?: \App\Models\SystemSetting::get('site_logo_url'); @endphp
    @if($ogImg)
        <meta property="og:image" content="{{ $ogImg }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', trim($__env->yieldContent('title')) ?: \App\Models\SystemSetting::get('seo_title', \App\Models\SystemSetting::get('site_name', config('app.name'))))">
    <meta name="twitter:description" content="@yield('og_description', trim($__env->yieldContent('meta_description')) ?: \App\Models\SystemSetting::get('seo_description', ''))">
    @if($ogImg)
        <meta name="twitter:image" content="{{ $ogImg }}">
    @endif
    
    @php $favUrl = \App\Models\SystemSetting::get('site_favicon_url'); @endphp
    @if($favUrl)
        <link rel="icon" type="image/png" href="{{ $favUrl }}">
    @endif

    @php
        $siteName = \App\Models\SystemSetting::get('site_name', config('app.name'));
        $siteUrl = url('/');
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
        ];
        if ($ogImg) {
            $schema['image'] = $ogImg;
        }
    @endphp
    <script type="application/ld+json" nonce="{{ $cspNonce ?? '' }}">{{ json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Fuwa.NG Styles -->
    <link rel="stylesheet" href="{{ asset('assets/nexus/css/nexus.css?v=' . @filemtime(public_path('assets/nexus/css/nexus.css')) ) }}">
    <link rel="stylesheet" href="{{ asset('assets/nexus/css/payment-modal.css?v=' . @filemtime(public_path('assets/nexus/css/payment-modal.css')) ) }}">
    <!-- Payment Gateway Scripts -->
    <script src="https://js.paystack.co/v1/inline.js" defer></script>
    <script src="https://checkout.flutterwave.com/v3.js" defer></script>
    <script nonce="{{ $cspNonce ?? '' }}">
        window.authUserEmail = @json(Auth::check() ? Auth::user()->email : null);
    </script>
    
    @vite(['resources/js/vendor.js', 'resources/js/layout.js'])
    <link rel="stylesheet" href="https://unpkg.com/intro.js/minified/introjs.min.css">
    
    <style>
        :root {
            --sidebar-w: 260px;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: var(--clr-bg);
            color: var(--clr-text-main);
            font-family: 'Outfit', sans-serif;
        }

        .container, .container-main, .main, .nexus-main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
        }

        @media(min-width: 900px) {
            .nexus-main {
                margin-left: var(--sidebar-w);
            }
        }
        
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Bootstrap Overrides for Fuwa.NG */
        .modal-content { background: var(--clr-bg-card); backdrop-filter: blur(20px); border: var(--border-glass); border-radius: 20px; }
        .form-control { background: rgba(255,255,255,0.05); border: var(--border-glass); color: #fff; border-radius: 12px; padding: 12px; }
        .form-control:focus { background: rgba(255,255,255,0.08); border-color: var(--clr-primary); color: #fff; box-shadow: none; }
        .btn-primary { background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover)); border: none; border-radius: 12px; padding: 12px 24px; font-weight: 600; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-glow); }
        .dropdown-menu { background: var(--clr-bg-nav); backdrop-filter: blur(15px); border: var(--border-glass); border-radius: 15px; }
        .dropdown-item { color: var(--clr-text-muted); padding: 10px 20px; }
        .dropdown-item:hover { background: rgba(255,255,255,0.05); color: #fff; }
    </style>
    
    @stack('styles')
</head>
<body class="{{ Auth::check() ? 'dashboard-body' : '' }}">
    <!-- Background Elements -->
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    @auth
    <!-- Mobile Toggle -->
    <button class="mobile-toggle d-block d-lg-none" id="sidebarToggle" aria-label="Toggle Navigation Sidebar" aria-expanded="false" aria-controls="sidebar">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                @php $logoUrl = \App\Models\SystemSetting::get('site_logo_url'); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\SystemSetting::get('site_name', 'Logo') }}" loading="lazy" decoding="async" style="max-height: 30px; margin-right: 10px;">
                @else
                    <i class="fa-solid fa-bolt"></i>
                @endif
                {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}
            </div>
            <div class="sidebar-user">
                <div class="sidebar-avatar" style="background: url('{{ asset('images/avatar-placeholder.png') }}') center/cover;"></div>
                <div class="sidebar-user-info">
                    <div class="su-name">{{ Auth::user()->fullname ?? Auth::user()->username }}</div>
                    <div class="su-role">{{ ucfirst(Auth::user()->role ?? 'User') }}</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">Main Menu</div>
            <div class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-house"></i> Overview</a>
            </div>
            <div class="nav-item {{ Request::routeIs('history') ? 'active' : '' }}">
                <a href="{{ route('history') }}"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
            </div>

            <div class="nav-section">Identity & Trust</div>
            <div class="nav-item {{ Request::routeIs('services.nin') ? 'active' : '' }}">
                <a href="{{ route('services.nin') }}"><i class="fa-solid fa-id-card"></i> NIN Verify</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.bvn') ? 'active' : '' }}">
                <a href="{{ route('services.bvn') }}"><i class="fa-solid fa-building-columns"></i> BVN Verify</a>
            </div>

            <div class="nav-item {{ (Request::routeIs('services.vtu.*') || Request::routeIs('services.airtime*') || Request::routeIs('services.data*')) ? 'active' : '' }}">
                <a href="{{ route('services.vtu.hub') }}"><i class="fa-solid fa-mobile-screen-button"></i> VTU Hub</a>
            </div>

            <div class="nav-section">Fuwa.NG Ecosystem</div>
            <div class="nav-item {{ Request::routeIs('services.agency') ? 'active' : '' }}">
                <a href="{{ route('services.agency') }}"><i class="fa-solid fa-store text-info"></i> Agency Banking</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.virtual_card') ? 'active' : '' }}">
                <a href="{{ route('services.virtual_card') }}"><i class="fa-solid fa-credit-card text-primary"></i> Virtual Cards</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.fx') ? 'active' : '' }}">
                <a href="{{ route('services.fx') }}"><i class="fa-solid fa-arrows-rotate text-purple"></i> FX Exchange</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.invoicing') ? 'active' : '' }}">
                <a href="{{ route('services.invoicing') }}"><i class="fa-solid fa-file-invoice-dollar text-warning"></i> Invoicing</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.notary') ? 'active' : '' }}">
                <a href="{{ route('services.notary') }}"><i class="fa-solid fa-file-signature text-secondary"></i> Notary Services</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.postoffice') ? 'active' : '' }}">
                <a href="{{ route('services.postoffice') }}"><i class="fa-solid fa-mail-bulk text-danger"></i> Post Office</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.ticketing') ? 'active' : '' }}">
                <a href="{{ route('services.ticketing') }}"><i class="fa-solid fa-ticket text-accent"></i> Ticketing</a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.auctions') ? 'active' : '' }}">
                <a href="{{ route('services.auctions') }}"><i class="fa-solid fa-gavel text-white-50"></i> Auctions</a>
            </div>

            <div class="nav-section">Settings & Support</div>
            <div class="nav-item {{ Request::routeIs('profile') ? 'active' : '' }}">
                <a href="{{ route('profile') }}"><i class="fa-solid fa-user-gear"></i> Profile</a>
            </div>
            <div class="nav-item {{ Request::routeIs('tickets.*') ? 'active' : '' }}">
                <a href="{{ route('tickets.index') }}"><i class="fa-solid fa-headset text-success"></i> Support Center</a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </aside>
    @endauth

    <!-- Main Content Area -->
    <main class="main {{ Auth::check() ? 'nexus-main' : 'm-0' }}">
        @auth
        <header class="topbar">
            <div class="topbar-greeting">
                Welcome back, <span>{{ explode(' ', Auth::user()->fullname)[0] ?? Auth::user()->username }}</span>
            </div>
            <div class="topbar-right">
                <div class="wallet-balance d-none d-md-block">
                    <span class="text-muted small">Balance:</span>
                    <h5 class="m-0">₦{{ number_format(Auth::user()->balance?->user_balance ?? 0, 2) }}</h5>
                </div>
                <div class="notif-btn">
                    <i class="fa-regular fa-bell"></i>
                    <div class="notif-dot"></div>
                </div>
            </div>
        </header>
        @endauth

        <div class="{{ Auth::check() ? 'dash-content' : 'container py-5' }} fade-in">
            @yield('content')
        </div>

        <footer class="py-4 {{ Auth::check() ? 'px-4' : '' }}" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02);">
            <div class="{{ Auth::check() ? '' : 'container' }}">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="gap: 12px;">
                    <div class="text-white-50 small">
                        © {{ date('Y') }} {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}. All rights reserved.
                    </div>
                    <div class="d-flex align-items-center" style="gap: 14px;">
                        <a class="text-white-50 small" href="{{ url('/') }}">Home</a>
                        <a class="text-white-50 small" href="{{ route('services.price_list') }}">Pricing</a>
                        <a class="text-white-50 small" href="{{ route('blog.index') }}">Blog</a>
                        @auth
                            <a class="text-white-50 small" href="{{ route('tickets.index') }}">Support</a>
                        @else
                            <a class="text-white-50 small" href="{{ route('login') }}">Support</a>
                        @endauth
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="{{ asset('assets/nexus/js/csrf-fetch.js') }}"></script>
    <script src="{{ asset('assets/nexus/js/nexus.js') }}"></script>
    <script src="{{ asset('assets/nexus/js/payment-modal.js') }}"></script>
    <script src="https://unpkg.com/intro.js/minified/intro.min.js"></script>
    @stack('scripts')
</body>
</html>
