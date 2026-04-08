
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

    @php
        $assetPrefix = rtrim(preg_replace('#/index\.php$#', '', request()->getBaseUrl()), '/');
    @endphp

    <!-- Fuwa.NG Styles -->
    <link rel="stylesheet" href="{{ $assetPrefix . '/assets/nexus/css/nexus.css?v=' . @filemtime(public_path('assets/nexus/css/nexus.css')) }}">
    <link rel="stylesheet" href="{{ $assetPrefix . '/assets/nexus/css/payment-modal.css?v=' . @filemtime(public_path('assets/nexus/css/payment-modal.css')) }}">
    <!-- Payment Gateway Scripts -->
    <script src="https://js.paystack.co/v1/inline.js" defer></script>
    <script src="https://checkout.flutterwave.com/v3.js" defer></script>
    <script src="https://sdk.monnify.com/plugin/monnify.js" defer></script>
    <script nonce="{{ $cspNonce ?? '' }}">
        window.authUserEmail = @json(Auth::check() ? Auth::user()->email : null);
        window.authUserName = @json(Auth::check() ? (Auth::user()->fullname ?? Auth::user()->username) : null);
    </script>
    
    @vite(['resources/js/vendor.js', 'resources/js/app.js', 'resources/js/layout.js'])
    
    <style>
        :root {
            --sidebar-w: 260px;
            --clr-primary: {{ \App\Models\SystemSetting::get('theme_primary', '#3b82f6') }};
            --clr-primary-hover: {{ \App\Models\SystemSetting::get('theme_primary_hover', '#2563eb') }};
            --clr-accent-1: {{ \App\Models\SystemSetting::get('theme_accent_1', '#10b981') }};
            --clr-accent-2: {{ \App\Models\SystemSetting::get('theme_accent_2', '#8b5cf6') }};
            --clr-accent-3: {{ \App\Models\SystemSetting::get('theme_accent_3', '#f59e0b') }};
        }
        
        body {
            background-color: var(--clr-bg);
            color: var(--clr-text-main);
            font-family: 'Outfit', sans-serif;
        }

        .topbar-greeting { white-space: nowrap; }

        .search-hub-wrapper { position: relative; max-width: 400px; }
        .search-hub-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--clr-text-muted); pointer-events: none; }
        .search-hub-wrapper input { width: 100%; background: rgba(255,255,255,0.05); border: var(--border-glass); border-radius: 12px; padding: 10px 15px 10px 45px; color: #fff; font-size: 0.9rem; transition: all 0.3s ease; }
        .search-hub-wrapper input:focus { background: rgba(255,255,255,0.08); border-color: var(--clr-primary); box-shadow: 0 0 15px rgba(59, 130, 246, 0.2); outline: none; }
        .search-results-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: var(--clr-bg-nav); border: var(--border-glass); border-radius: 12px; margin-top: 10px; z-index: 1000; max-height: 300px; overflow-y: auto; box-shadow: 0 15px 30px rgba(0,0,0,0.5); }
        .search-result-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--clr-text-muted); text-decoration: none; transition: all 0.2s ease; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .search-result-item:last-child { border-bottom: none; }
        .search-result-item:hover { background: rgba(255,255,255,0.05); color: #fff; text-decoration: none; }
        .search-result-item i { font-size: 1.1rem; width: 20px; text-align: center; }
        .search-result-info { flex: 1; }
        .search-result-title { display: block; font-weight: 600; font-size: 0.9rem; color: #fff; }
        .search-result-category { display: block; font-size: 0.75rem; color: var(--clr-text-muted); }

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

        /* High Contrast Support Global */
        body.high-contrast {
            --clr-bg: #000;
            --clr-bg-card: #111;
            --clr-bg-nav: #111;
            --border-glass: 1px solid #444;
            --clr-text-main: #fff;
            --clr-text-muted: #ccc;
            --clr-primary: #fff;
            --clr-primary-hover: #eee;
            background-color: #000 !important;
            color: #fff !important;
        }
        body.high-contrast .glass-card, 
        body.high-contrast .card,
        body.high-contrast .sidebar,
        body.high-contrast .dropdown-menu,
        body.high-contrast .modal-content,
        body.high-contrast .panel-card {
            background: #111 !important;
            border: 1px solid #666 !important;
            backdrop-filter: none !important;
            box-shadow: none !important;
        }
        body.high-contrast .bg-glow,
        body.high-contrast .hero-bg-accent,
        body.high-contrast .hw-glow {
            display: none !important;
        }
        body.high-contrast .text-primary,
        body.high-contrast .text-muted,
        body.high-contrast .text-white-50 {
            color: #fff !important;
        }
        body.high-contrast .btn-primary {
            background: #fff !important;
            color: #000 !important;
            border: 2px solid #fff !important;
        }

        /* Micro-Animations */
        .fade-up { animation: fadeUp 0.5s ease forwards; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }

        /* Toast UI */
        .nexus-toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .nexus-toast { background: var(--clr-bg-card); backdrop-filter: blur(15px); border: var(--border-glass); border-radius: 12px; padding: 12px 20px; min-width: 280px; display: flex; align-items: center; gap: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-left: 4px solid var(--clr-primary); transform: translateX(120%); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .nexus-toast.show { transform: translateX(0); }
        .nexus-toast.success { border-left-color: var(--clr-accent-1); }
        .nexus-toast.error { border-left-color: #ef4444; }
        .nexus-toast-icon { font-size: 1.2rem; }
        .nexus-toast-msg { flex: 1; font-size: 0.9rem; }

        /* Mobile Navbar Fixes */
        @media (max-width: 991.98px) {
            .public-nav .navbar-collapse {
                background: var(--clr-bg); /* Solid background to prevent content bleed */
                padding: 1.5rem;
                border-radius: 1rem;
                margin-top: 1rem;
                border: var(--border-glass);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
                max-height: calc(100vh - 100px);
                overflow-y: auto;
                position: relative;
                z-index: 1050;
            }
            .public-nav .navbar-nav .nav-item {
                padding: 0.5rem 0;
                border-bottom: 1px solid rgba(255,255,255,0.05);
            }
            .public-nav .navbar-nav .nav-item:last-child {
                border-bottom: none;
            }
            .public-nav .nav-actions {
                margin-top: 1.5rem;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 1rem !important;
            }
            .public-nav .nav-actions .btn {
                width: 100%;
                text-align: center;
                margin: 0;
            }
        }
        
        /* Desktop Navbar Fixes */
        @media (min-width: 992px) {
            .public-nav {
                z-index: 1050 !important;
                background: rgba(3, 7, 18, 0.95) !important; /* Slightly more opaque for better contrast */
            }
            .public-nav .navbar-collapse {
                background: transparent;
            }
            .main, .home-sales {
                position: relative;
                z-index: 1; /* Ensure content stays below the fixed navbar */
            }
        }
        .sidebar-nav::-webkit-scrollbar {
            height: 4px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
        @media (max-width: 992px) {
            .sidebar-nav::-webkit-scrollbar {
                width: 4px;
                height: auto;
            }
            .public-nav .navbar-nav {
                flex-direction: column !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
@php
    $webUser = Auth::user();
    $adminUser = Auth::guard('admin')->user();
    $authUser = $webUser ?: $adminUser;
    $isAuthed = (bool) $authUser;
    $isAdmin = (bool) $adminUser;
@endphp
<body class="{{ $isAuthed ? 'dashboard-body' : '' }}">
    <!-- Background Elements -->
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    @php 
        $hideNav = trim($__env->yieldContent('hide_nav')) === 'true';
    @endphp

    @if($isAuthed)
    <!-- Top Horizontal Navigation -->
    <aside class="sidebar" id="sidebar" aria-label="Main Navigation" role="navigation">
        <div class="sidebar-header">
            <a href="{{ url('/') }}" class="sidebar-logo text-decoration-none">
                @php $logoUrl = \App\Models\SystemSetting::get('site_logo_url'); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\SystemSetting::get('site_name', 'Logo') }}" loading="lazy" decoding="async" style="max-height: 32px; margin-right: 8px;">
                @else
                    <i class="fa-solid fa-bolt"></i>
                @endif
                <span>{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> <span class="nav-text">Home</span></a>
            </div>
            @if($isAdmin)
            <div class="nav-section">Admin</div>
            <div class="nav-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high"></i> <span class="nav-text">Dashboard</span></a>
            </div>
            
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-layer-group"></i> <span class="nav-text">Catalog</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.services.index') }}" class="{{ Request::routeIs('admin.services.*') ? 'active' : '' }}">Services</a>
                    <a href="{{ route('admin.custom_apis.index') }}" class="{{ Request::routeIs('admin.custom_apis.*') ? 'active' : '' }}">Custom APIs</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-users"></i> <span class="nav-text">Users & Access</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.users.index') }}" class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
                    @if(Auth::guard('admin')->user()?->hasPermission('manage_admins'))
                    <a href="{{ route('admin.admins.index') }}" class="{{ Request::routeIs('admin.admins.*') ? 'active' : '' }}">System Admins</a>
                    @endif
                    @if(Auth::guard('admin')->user()?->hasPermission('manage_roles'))
                    <a href="{{ route('admin.roles.index') }}" class="{{ Request::routeIs('admin.roles.*') ? 'active' : '' }}">Roles & Permissions</a>
                    @endif
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-chart-line"></i> <span class="nav-text">Monitoring</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.transactions.index') }}" class="{{ Request::routeIs('admin.transactions.*') ? 'active' : '' }}">Transactions</a>
                    <a href="{{ route('admin.verifications.index') }}" class="{{ Request::routeIs('admin.verifications.index') ? 'active' : '' }}">Verification Vault</a>
                    <a href="{{ route('admin.verifications.nin_modifications.index') }}" class="{{ Request::routeIs('admin.verifications.nin_modifications.*') ? 'active' : '' }}">NIN Modifications</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-bullhorn"></i> <span class="nav-text">Engagement</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.broadcasts.index') }}" class="{{ Request::routeIs('admin.broadcasts.*') ? 'active' : '' }}">Broadcasts</a>
                    @if(Auth::guard('admin')->user()?->is_super_admin)
                    <a href="{{ route('admin.direct_messages.index') }}" class="{{ Request::routeIs('admin.direct_messages.*') ? 'active' : '' }}">Direct Messages</a>
                    <a href="{{ route('admin.email_campaigns.index') }}" class="{{ Request::routeIs('admin.email_campaigns.*') ? 'active' : '' }}">Email Campaigns</a>
                    <a href="{{ route('admin.sms_campaigns.index') }}" class="{{ Request::routeIs('admin.sms_campaigns.*') ? 'active' : '' }}">SMS Campaigns</a>
                    @endif
                    <a href="{{ route('admin.tickets') }}" class="{{ Request::routeIs('admin.tickets*') ? 'active' : '' }}">Support Tickets</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-globe"></i> <span class="nav-text">Content</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.posts.index') }}" class="{{ Request::routeIs('admin.posts.*') ? 'active' : '' }}">Blog Posts</a>
                    <a href="{{ route('admin.pages.index') }}" class="{{ Request::routeIs('admin.pages.*') ? 'active' : '' }}">Pages</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-sitemap"></i> <span class="nav-text">Operations</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.operations.invoices') }}" class="{{ Request::routeIs('admin.operations.invoices') ? 'active' : '' }}">Invoices</a>
                    <a href="{{ route('admin.operations.logistics') }}" class="{{ Request::routeIs('admin.operations.logistics') ? 'active' : '' }}">Logistics</a>
                    <a href="{{ route('admin.operations.notary') }}" class="{{ Request::routeIs('admin.operations.notary') ? 'active' : '' }}">Notary</a>
                    <a href="{{ route('admin.shipping-providers.index') }}" class="{{ Request::routeIs('admin.shipping-providers.*') ? 'active' : '' }}">Shipping</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-wrench"></i> <span class="nav-text">System</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('admin.settings.index') }}" class="{{ Request::routeIs('admin.settings.*') ? 'active' : '' }}">Settings</a>
                    <a href="{{ route('admin.sandbox.index') }}" class="{{ Request::routeIs('admin.sandbox.*') ? 'active' : '' }}">Sandbox</a>
                    @if(Auth::guard('admin')->user()?->is_super_admin)
                    <a href="{{ route('admin.audit_logs.index') }}" class="{{ Request::routeIs('admin.audit_logs.*') ? 'active' : '' }}">Audit Logs</a>
                    <a href="{{ route('admin.queue.index') }}" class="{{ Request::routeIs('admin.queue.*') ? 'active' : '' }}">Queue Monitor</a>
                    @endif
                </div>
            </div>
            @else
            <div class="nav-section">Main Menu</div>
            <div class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-house"></i> <span class="nav-text">Overview</span></a>
            </div>
            <div class="nav-item {{ Request::routeIs('notifications.*') ? 'active' : '' }}">
                <a href="{{ route('notifications.index') }}"><i class="fa-solid fa-bell"></i> <span class="nav-text">Notifications
                    @php 
                        $unread = Auth::user()->unreadNotifications->count(); 
                    @endphp
                    @if($unread > 0)
                        <span class="badge badge-primary badge-pill ml-auto px-2" style="font-size: 0.65rem;">{{ $unread }}</span>
                    @endif
                </span></a>
            </div>
            <div class="nav-item {{ Request::routeIs('history') ? 'active' : '' }}">
                <a href="{{ route('history') }}"><i class="fa-solid fa-clock-rotate-left"></i> <span class="nav-text">History</span></a>
            </div>
            <div class="nav-item {{ Request::routeIs('services.price_list') ? 'active' : '' }}">
                <a href="{{ route('services.price_list') }}"><i class="fa-solid fa-tags"></i> <span class="nav-text">Price List</span></a>
            </div>
            <div class="nav-item {{ Request::routeIs('developer.*') ? 'active' : '' }}">
                <a href="{{ route('developer.portal') }}"><i class="fa-solid fa-code"></i> <span class="nav-text">Developer</span></a>
            </div>

            <div class="nav-section">Identity Services</div>
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-id-card"></i> <span class="nav-text">NIN Services</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    @if(\App\Models\SystemSetting::get('nin_service_enabled', 'true') === 'true')
                        <a href="{{ route('services.nin.suite') }}" class="{{ Request::routeIs('services.nin.suite') || Request::routeIs('services.nin') || Request::routeIs('services.validation') || Request::routeIs('services.clearance') || Request::routeIs('services.personalization') ? 'active' : '' }}">NIN Suite</a>
                        <a href="{{ route('services.nin_face') }}" class="{{ Request::routeIs('services.nin_face') ? 'active' : '' }}">NIN Face</a>
                    @endif
                    <a href="{{ route('services.nin.modification') }}" class="{{ Request::routeIs('services.nin.modification') ? 'active' : '' }}">NIN Modification</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-building-columns"></i> <span class="nav-text">BVN Services</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    @if(\App\Models\SystemSetting::get('bvn_service_enabled', 'true') === 'true')
                        <a href="{{ route('services.bvn') }}" class="{{ Request::routeIs('services.bvn') ? 'active' : '' }}">BVN Suite</a>
                    @endif
                    <a href="{{ route('services.bvn') }}">Print BVN Slip</a>
                    <a href="{{ route('services.bvn') }}">BVN Modification</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-shield-halved"></i> <span class="nav-text">Verification Hub</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('services.drivers_license') }}" class="{{ Request::routeIs('services.drivers_license') ? 'active' : '' }}">DL Verify</a>
                    @if(\Illuminate\Support\Facades\Route::has('services.biometric_verify'))
                        <a href="{{ route('services.biometric_verify') }}" class="{{ Request::routeIs('services.biometric_verify') ? 'active' : '' }}">Bio Verify</a>
                    @endif
                    <a href="{{ route('services.cac_verify') }}" class="{{ Request::routeIs('services.cac_verify') ? 'active' : '' }}">CAC Verify</a>
                    <a href="{{ route('services.tin_verify') }}" class="{{ Request::routeIs('services.tin_verify') ? 'active' : '' }}">TIN Verify</a>
                    <a href="{{ route('services.passport') }}" class="{{ Request::routeIs('services.passport') ? 'active' : '' }}">Passport Verify</a>
                    <a href="{{ route('services.voters_card') }}" class="{{ Request::routeIs('services.voters_card') ? 'active' : '' }}">Voters Card</a>
                    <a href="{{ route('services.address_verify') }}" class="{{ Request::routeIs('services.address_verify') ? 'active' : '' }}">Address Verify</a>
                    <a href="{{ route('services.plate_number') }}" class="{{ Request::routeIs('services.plate_number') ? 'active' : '' }}">Plate Verify</a>
                </div>
            </div>

            <div class="nav-section">{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} Ecosystem</div>
            @if(\App\Models\SystemSetting::get('airtime_data_enabled', 'true') === 'true')
            <div class="nav-item {{ (Request::routeIs('services.vtu.*') || Request::routeIs('services.airtime*') || Request::routeIs('services.data*')) ? 'active' : '' }}">
                <a href="{{ route('services.vtu.hub') }}"><i class="fa-solid fa-mobile-screen-button"></i> <span class="nav-text">VTU Hub</span></a>
            </div>
            @endif

            @if(\App\Models\SystemSetting::get('legal_service_enabled', 'true') === 'true')
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-gavel text-indigo"></i> <span class="nav-text">Legal Services</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('services.legal') }}" class="{{ Request::routeIs('services.legal') ? 'active' : '' }}">Legal Platform</a>
                    <a href="{{ route('services.legal-hub') }}" class="{{ Request::routeIs('services.legal-hub') ? 'active' : '' }}">AI Legal Hub</a>
                    <a href="{{ route('services.notary') }}" class="{{ Request::routeIs('services.notary') ? 'active' : '' }}">Notary Services</a>
                    <a href="{{ route('services.stamp_duty') }}" class="{{ Request::routeIs('services.stamp_duty') ? 'active' : '' }}">Stamp Duty</a>
                </div>
            </div>
            @endif

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-store text-info"></i> <span class="nav-text">Commerce & Payments</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="{{ route('services.agency') }}" class="{{ Request::routeIs('services.agency') ? 'active' : '' }}">Agency Banking</a>
                    <a href="{{ route('services.virtual_card') }}" class="{{ Request::routeIs('services.virtual_card') ? 'active' : '' }}">Virtual Cards</a>
                    <a href="{{ route('services.fx') }}" class="{{ Request::routeIs('services.fx') ? 'active' : '' }}">FX Exchange</a>
                    <a href="{{ route('services.invoicing') }}" class="{{ Request::routeIs('services.invoicing') ? 'active' : '' }}">Invoicing</a>
                    <a href="{{ route('services.ticketing') }}" class="{{ Request::routeIs('services.ticketing') ? 'active' : '' }}">Ticketing</a>
                    @if(\App\Models\SystemSetting::get('auction_service_enabled', 'true') === 'true')
                        <a href="{{ route('public.auctions.index') }}" target="_blank" class="{{ Request::routeIs('public.auctions.index') ? 'active' : '' }}">Auctions <i class="fa-solid fa-up-right-from-square ml-1 small opacity-50"></i></a>
                    @endif
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-truck text-danger"></i> <span class="nav-text">Logistics & Utilities</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    @if(\App\Models\SystemSetting::get('post_office_service_enabled', 'true') === 'true')
                        <a href="{{ route('public.logistics.index') }}" target="_blank"
                           class="{{ request()->routeIs('public.logistics.index') ? 'active' : '' }}">Logistics Hub</a>
                    @endif
                    @if(\App\Models\SystemSetting::get('education_service_enabled', 'true') === 'true')
                        <a href="{{ route('services.education.waec') }}" class="{{ Request::routeIs('services.education.waec') ? 'active' : '' }}">WAEC Result</a>
                        <a href="{{ route('services.education.waec_registration') }}" class="{{ Request::routeIs('services.education.waec_registration') ? 'active' : '' }}">WAEC Reg. PIN</a>
                    @endif
                    @if(\App\Models\SystemSetting::get('insurance_service_enabled', 'true') === 'true')
                        <a href="{{ route('services.insurance.motor') }}" class="{{ Request::routeIs('services.insurance.motor') ? 'active' : '' }}">Motor Insurance</a>
                    @endif
                </div>
            </div>

            @if(($webUser?->role ?? null) === 'admin' || $adminUser)
            <div class="nav-section">Admin Management</div>
            
            @if(Auth::guard('admin')->user()?->is_super_admin)
            <div class="nav-item {{ Request::routeIs('admin.self_funding.*') ? 'active' : '' }}">
                <a href="{{ route('admin.self_funding.index') }}"><i class="fa-solid fa-vault text-primary"></i> <span class="nav-text">Self-Funding</span></a>
            </div>
            @endif

            @if(Auth::guard('admin')->user()?->hasPermission('manage_admins'))
            <div class="nav-item {{ Request::routeIs('admin.admins.*') ? 'active' : '' }}">
                <a href="{{ route('admin.admins.index') }}"><i class="fa-solid fa-users-gear text-danger"></i> <span class="nav-text">System Admins</span></a>
            </div>
            @endif
            @if(Auth::guard('admin')->user()?->hasPermission('manage_roles'))
            <div class="nav-item {{ Request::routeIs('admin.roles.*') ? 'active' : '' }}">
                <a href="{{ route('admin.roles.index') }}"><i class="fa-solid fa-user-shield text-warning"></i> <span class="nav-text">Roles & Permissions</span></a>
            </div>
            @endif

            <div class="nav-item {{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}"><i class="fa-solid fa-users"></i> <span class="nav-text">Users Directory</span></a>
            </div>
            <div class="nav-item {{ Request::routeIs('admin.custom_apis.*') ? 'active' : '' }}">
                <a href="{{ route('admin.custom_apis.index') }}"><i class="fa-solid fa-code-merge text-primary"></i> <span class="nav-text">Control Tower</span></a>
            </div>
            <div class="nav-item {{ Request::routeIs('admin.tickets.*') ? 'active' : '' }}">
                <a href="{{ route('admin.tickets') }}"><i class="fa-solid fa-headset text-success"></i> <span class="nav-text">Support Center</span></a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.settings.index') }}"><i class="fa-solid fa-gear"></i> <span class="nav-text">System Settings</span></a>
            </div>
            @endif

            <div class="nav-section">Account Settings</div>
            @if($isAdmin)
            <div class="nav-item {{ Request::routeIs('admin.profile.*') ? 'active' : '' }}">
                <a href="{{ route('admin.profile.edit') }}"><i class="fa-solid fa-user-gear"></i> <span class="nav-text">Profile Settings</span></a>
            </div>
            @else
            <div class="nav-item {{ Request::routeIs('profile') ? 'active' : '' }}">
                <a href="{{ route('profile') }}"><i class="fa-solid fa-user-gear"></i> <span class="nav-text">Profile</span></a>
            </div>
            @endif
            @endif
        
            <div class="nav-item mt-auto pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                <button type="button" id="minimizeSidebarBtn" aria-label="Minimize Sidebar">
                    <i class="fa-solid fa-angle-left collapse-icon" style="transition: transform 0.3s;"></i>
                    <span class="nav-text">Collapse Menu</span>
                </button>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a
                href="{{ $isAdmin ? route('admin.profile.edit') : route('profile') }}"
                class="sidebar-user-link mr-3"
                aria-label="{{ $isAdmin ? 'Edit admin profile' : 'Edit profile' }}"
            >
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        @php
                            $sidebarAvatar = $isAdmin ? ($adminUser?->avatar ?? null) : null;
                            $sidebarName = $webUser?->fullname ?? $webUser?->username ?? $adminUser?->fullname ?? $adminUser?->username ?? 'Admin';
                            $sidebarRole = $isAdmin ? 'Admin' : ucfirst($webUser?->role ?? 'User');
                        @endphp
                        @if($sidebarAvatar)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($sidebarAvatar) }}" alt="{{ $sidebarName }}" class="sidebar-user-avatar-img">
                        @else
                            <span class="sidebar-user-avatar-fallback">{{ strtoupper(substr($sidebarName, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="sidebar-user-info">
                        <div class="su-name">{{ $sidebarName }}</div>
                        <div class="su-role text-muted small">{{ $sidebarRole }}</div>
                    </div>
                </div>
            </a>
            <form action="{{ $isAdmin ? route('admin.logout') : route('logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>
    @endif
    @if(!$isAuthed && !$hideNav)
        <!-- Public Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top public-nav py-3" style="background: rgba(3, 7, 18, 0.95); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(255,255,255,0.05); z-index: 1050;">
            <div class="container">
                <a class="navbar-brand font-weight-bold d-flex align-items-center" href="{{ url('/') }}">
                    <div class="brand-icon mr-2" style="background: var(--clr-primary); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-bolt text-white" style="font-size: 14px;"></i></div>
                    <span style="letter-spacing: 1px;">{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}</span>
                </a>
                <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto d-flex flex-row">
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ url('/') }}#services">Services</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ route('public.auctions.index') }}">Auctions</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ route('public.logistics.index') }}">Logistics</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ url('/explore/notary-services') }}">Notary</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ url('/') }}#legal-hub">AI Legal Hub</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ route('services.price_list') }}">Pricing</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="{{ route('blog.index') }}">Blog</a></li>
                    </ul>
                    <div class="nav-actions d-flex align-items-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-link text-white text-decoration-none font-weight-bold small">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary px-4 py-2 small font-weight-bold" style="border-radius: 10px;">Get Started</a>
                    </div>
                </div>
            </div>
        </nav>
        <div style="height: 80px;"></div> <!-- Spacer for fixed nav -->
    @endif

    <!-- Main Content Area -->
    <main class="{{ $isAuthed ? 'main-content' : 'main m-0' }}">
        @if($isAuthed)
        <header class="top-header">
            <!-- Mobile Toggle -->
            <button class="mobile-toggle d-lg-none mr-3" id="sidebarToggle" type="button" aria-label="Toggle Navigation Sidebar" aria-expanded="false" aria-controls="sidebar">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <div class="topbar-search d-none d-lg-block flex-grow-1 mx-4">
                <div class="search-hub-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="globalSearchHub" placeholder="Search services (e.g. NIN, VTU, Legal)..." autocomplete="off">
                    <div id="searchHubResults" class="search-results-dropdown" style="display: none;"></div>
                </div>
            </div>
            <div class="topbar-greeting d-none d-sm-block">
                Welcome back, <span>{{ explode(' ', $authUser->fullname ?? $authUser->username ?? 'Admin')[0] }}</span>
            </div>
            <div class="header-actions">
                <button class="action-btn text-decoration-none" id="highContrastToggle" title="Toggle High Contrast Mode" aria-label="High Contrast">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>
                @if($webUser)
                <div class="wallet-balance d-none d-md-block">
                    <span class="text-muted small">Balance:</span>
                    <h5 class="m-0">₦{{ number_format($webUser->balance?->user_balance ?? 0, 2) }}</h5>
                </div>
                @endif
                @if($webUser)
                <a class="action-btn text-decoration-none" href="{{ route('notifications.index') }}" aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    @php 
                        $unreadCount = $webUser->unreadNotifications->count(); 
                    @endphp
                    @if($unreadCount > 0)
                        <span class="notification-dot"></span>
                    @endif
                </a>
                @endif
            </div>
        </header>
        @endif

        @php $publicWrapperClass = trim($__env->yieldContent('public_wrapper_class')); @endphp
        @if($isAuthed)
            <div class="dashboard-content fade-in">
                @if(session('error'))
                    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fecaca;">
                        {!! session('error') !!}
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #bbf7d0;">
                        {!! session('success') !!}
                    </div>
                @endif
                @yield('content')
            </div>
        @else
            @if($publicWrapperClass === 'none')
                @if(session('error'))
                    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fecaca;">
                        {!! session('error') !!}
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #bbf7d0;">
                        {!! session('success') !!}
                    </div>
                @endif
                @yield('content')
            @else
                <div class="{{ $publicWrapperClass ?: 'container py-5' }} fade-in">
                    @if(session('error'))
                        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fecaca;">
                            {!! session('error') !!}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #bbf7d0;">
                            {!! session('success') !!}
                        </div>
                    @endif
                    @yield('content')
                </div>
            @endif
        @endif

        <footer class="py-4 {{ $isAuthed ? 'px-4' : '' }}" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02);">
            <div class="{{ $isAuthed ? '' : 'container' }}">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="gap: 12px;">
                    <div class="text-white-50 small">
                        © {{ date('Y') }} {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}. All rights reserved.
                    </div>
                    <div class="d-flex align-items-center" style="gap: 14px;">
                        <a class="text-white-50 small" href="{{ url('/') }}">Home</a>
                        <a class="text-white-50 small" href="{{ route('services.price_list') }}">Pricing</a>
                        <a class="text-white-50 small" href="{{ route('blog.index') }}">Blog</a>
                        @if($webUser)
                            <a class="text-white-50 small" href="{{ route('tickets.index') }}">Support</a>
                        @elseif($isAdmin)
                            <a class="text-white-50 small" href="{{ route('admin.tickets') }}">Support</a>
                        @else
                            <a class="text-white-50 small" href="{{ route('login') }}">Support</a>
                        @endif
                    </div>
                </div>
            </div>
        </footer>
        
        <!-- Dynamic WhatsApp Widget handled by whatsappWidget.js -->
        <meta name="auth-check" content="{{ Auth::check() ? 'true' : 'false' }}">

        @if($webUser)
        <!-- AI Assistant FAB & Chat -->
        <button class="ai-fab shadow-glow" id="aiFabToggle" type="button" title="{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} AI Assistant" aria-label="Open AI Assistant Chat" aria-expanded="false" aria-controls="aiChatCard">
            <i class="fa-solid fa-robot" aria-hidden="true"></i>
        </button>

        <div class="ai-chat-card" id="aiChatCard" role="dialog" aria-label="AI Assistant Chat" hidden>
            <div class="ai-chat-header">
                <div class="d-flex align-items-center">
                    <div class="ai-avatar mr-2"><i class="fa-solid fa-robot"></i></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold">{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} AI</h6>
                        <span class="small ai-status">Online Assistant</span>
                    </div>
                </div>
                <button class="ai-close" id="aiChatClose" type="button" aria-label="Close AI Assistant Chat"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="ai-chat-body" id="aiChatBody" role="log" aria-live="polite" aria-relevant="additions text">
                <div class="ai-msg ai-msg-bot">
                    <div class="ai-msg-content">Hello {{ explode(' ', $webUser->fullname)[0] ?? 'there' }}! I am your {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} AI assistant. How can I help you navigate the platform today?</div>
                </div>
            </div>
            <div class="ai-chat-footer">
                <form id="aiChatForm" class="d-flex align-items-center">
                    <input type="text" id="aiChatInput" class="ai-input mr-2" placeholder="Ask AI something..." autocomplete="off" required>
                    <button type="submit" class="ai-send" aria-label="Send message">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        @endif
    </main>

    <!-- Toast Container -->
    <div class="nexus-toast-container" id="nexusToastContainer"></div>

    <!-- Scripts -->
    <script src="{{ $assetPrefix . '/assets/nexus/js/csrf-fetch.js' }}"></script>
    <script src="{{ $assetPrefix . '/assets/nexus/js/nexus.js' }}"></script>
    <script>
        // High Contrast Logic
        const hcToggle = document.getElementById('highContrastToggle');
        if (hcToggle) {
            if (localStorage.getItem('high-contrast') === 'true') {
                document.body.classList.add('high-contrast');
            }
            hcToggle.addEventListener('click', () => {
                const isHC = document.body.classList.toggle('high-contrast');
                localStorage.setItem('high-contrast', isHC);
            });
        }

        // Global Toast System
        window.nexusToast = function(msg, type = 'success', duration = 4000) {
            const container = document.getElementById('nexusToastContainer');
            if (!container) return;
            
            const toast = document.createElement('div');
            toast.className = `nexus-toast ${type}`;
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-triangle-exclamation';
            toast.innerHTML = `
                <div class="nexus-toast-icon"><i class="fa-solid ${icon}"></i></div>
                <div class="nexus-toast-msg">${msg}</div>
            `;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, duration);
        }
    </script>
    @if($isAuthed)
        <script src="{{ $assetPrefix . '/assets/nexus/js/payment-modal.js' }}"></script>
        <script src="{{ $assetPrefix . '/assets/nexus/js/nexus-ai.js' }}"></script>
    @endif
    @stack('scripts')
    <script nonce="{{ $cspNonce ?? '' }}">
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Navigation Logic
            const sidebar = document.getElementById('sidebar');
            const minBtn = document.getElementById('minimizeSidebarBtn');
            
            if (sidebar && minBtn) {
                // Restore minimize state
                if (localStorage.getItem('sidebar-minimized') === 'true') {
                    sidebar.classList.add('minimized');
                    minBtn.querySelector('.collapse-icon').style.transform = 'rotate(180deg)';
                }
                
                minBtn.addEventListener('click', () => {
                    const isMin = sidebar.classList.toggle('minimized');
                    localStorage.setItem('sidebar-minimized', isMin);
                    minBtn.querySelector('.collapse-icon').style.transform = isMin ? 'rotate(180deg)' : 'rotate(0)';
                    
                    // close all submenus if minimized
                    if (isMin) {
                        document.querySelectorAll('.nav-item.has-submenu').forEach(el => {
                            el.classList.remove('open');
                            const btn = el.querySelector('.submenu-toggle');
                            if(btn) btn.setAttribute('aria-expanded', 'false');
                        });
                    }
                });
            }
            
            // Submenu Toggles
            document.querySelectorAll('.submenu-toggle').forEach((toggle, index) => {
                const parent = toggle.closest('.nav-item');
                // Give unique IDs to submenus for aria-controls
                const submenu = parent.querySelector('.submenu');
                if (submenu) {
                    const subId = 'submenu-' + index;
                    submenu.id = subId;
                    toggle.setAttribute('aria-controls', subId);
                }
                
                // Check if active item is inside to keep it open initially
                if (parent.querySelector('.submenu a.active')) {
                    parent.classList.add('open');
                    toggle.setAttribute('aria-expanded', 'true');
                }
                
                // Restore expanded state from localStorage
                const stateKey = 'submenu-open-' + index;
                if (localStorage.getItem(stateKey) === 'true' && (!sidebar || !sidebar.classList.contains('minimized'))) {
                    parent.classList.add('open');
                    toggle.setAttribute('aria-expanded', 'true');
                }
                
                toggle.addEventListener('click', (e) => {
                    // If sidebar is minimized, don't toggle click unless we want it to expand?
                    if (sidebar && sidebar.classList.contains('minimized') && window.innerWidth >= 992) {
                        e.preventDefault();
                        return;
                    }
                    
                    const isOpen = parent.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', isOpen);
                    localStorage.setItem(stateKey, isOpen);
                });
            });

            // Mobile Sidebar Toggle
            const mobileToggle = document.getElementById('sidebarToggle');
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', () => {
                    const isOpen = sidebar.classList.toggle('open');
                    mobileToggle.setAttribute('aria-expanded', isOpen);
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', (e) => {
                    if (window.innerWidth < 992 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== mobileToggle && !mobileToggle.contains(e.target)) {
                        sidebar.classList.remove('open');
                        mobileToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            const input = document.getElementById('globalSearchHub');
            const dropdown = document.getElementById('searchHubResults');
            if (!input || !dropdown) return;

            const services = [
                { title: 'NIN Suite', cat: 'Identity', url: '{{ route("services.nin.suite") }}', icon: 'fa-id-card-clip' },
                { title: 'BVN Suite', cat: 'Identity', url: '{{ route("services.bvn") }}', icon: 'fa-building-columns' },
                { title: 'VTU Hub', cat: 'Ecosystem', url: '{{ route("services.vtu.hub") }}', icon: 'fa-mobile-screen-button' },
                { title: 'Legal Hub', cat: 'Ecosystem', url: '{{ route("services.legal-hub") }}', icon: 'fa-gavel' },
                { title: 'Notary', cat: 'Ecosystem', url: '{{ route("services.notary") }}', icon: 'fa-file-signature' },
                { title: 'Wallet Funding', cat: 'Wallet', url: '{{ route("wallet.fund") }}', icon: 'fa-wallet' },
                { title: 'Transactions', cat: 'Wallet', url: '{{ route("history") }}', icon: 'fa-receipt' },
                { title: 'Profile Settings', cat: 'Account', url: '{{ route("profile") }}', icon: 'fa-user-gear' }
            ];

            const hideDropdown = () => {
                dropdown.style.display = 'none';
            };

            const showDropdown = () => {
                dropdown.style.display = 'block';
            };

            input.addEventListener('input', () => {
                const query = (input.value || '').toLowerCase().trim();

                if (!query) {
                    hideDropdown();
                    return;
                }

                const filtered = services.filter((s) =>
                    s.title.toLowerCase().includes(query) ||
                    s.cat.toLowerCase().includes(query)
                );

                if (filtered.length > 0) {
                    dropdown.innerHTML = filtered.map((s) => {
                        return `
                            <a href="${s.url}" class="search-result-item">
                                <i class="fa-solid ${s.icon} text-primary"></i>
                                <div class="search-result-info">
                                    <span class="search-result-title">${s.title}</span>
                                    <span class="search-result-category">${s.cat}</span>
                                </div>
                            </a>
                        `;
                    }).join('');
                    showDropdown();
                } else {
                    dropdown.innerHTML = '<div class="p-3 text-center text-muted small">No services found.</div>';
                    showDropdown();
                }
            });

            document.addEventListener('click', (e) => {
                const target = e.target;
                if (!(target instanceof Element)) return;
                if (!target.closest('.search-hub-wrapper')) {
                    hideDropdown();
                }
            });
        });
    </script>
</body>
</html>
