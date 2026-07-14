
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', \App\Models\SystemSetting::get('seo_description', 'Your comprehensive Nigerian identity verification and legal services platform.')); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', \App\Models\SystemSetting::get('seo_keywords', 'verification, identity, legal hub, NIN, BVN')); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', \App\Models\SystemSetting::get('seo_title', \App\Models\SystemSetting::get('site_name', config('app.name')))); ?></title>
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', trim($__env->yieldContent('title')) ?: \App\Models\SystemSetting::get('seo_title', \App\Models\SystemSetting::get('site_name', config('app.name')))); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', trim($__env->yieldContent('meta_description')) ?: \App\Models\SystemSetting::get('seo_description', '')); ?>">
    <meta property="og:type" content="<?php echo $__env->yieldContent('og_type', 'website'); ?>">
    <meta property="og:url" content="<?php echo e(request()->fullUrl()); ?>">
    <?php $ogImg = \App\Models\SystemSetting::get('seo_default_image_url') ?: \App\Models\SystemSetting::get('site_logo_url'); ?>
    <?php if($ogImg): ?>
        <meta property="og:image" content="<?php echo e($ogImg); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('og_title', trim($__env->yieldContent('title')) ?: \App\Models\SystemSetting::get('seo_title', \App\Models\SystemSetting::get('site_name', config('app.name')))); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('og_description', trim($__env->yieldContent('meta_description')) ?: \App\Models\SystemSetting::get('seo_description', '')); ?>">
    <?php if($ogImg): ?>
        <meta name="twitter:image" content="<?php echo e($ogImg); ?>">
    <?php endif; ?>
    
    <?php $favUrl = \App\Models\SystemSetting::get('site_favicon_url'); ?>
    <?php if($favUrl): ?>
        <link rel="icon" type="image/png" href="<?php echo e($favUrl); ?>">
    <?php endif; ?>

    <?php
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
    ?>
    <script type="application/ld+json" nonce="<?php echo e($cspNonce ?? ''); ?>"><?php echo e(json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <?php
        $assetPrefix = rtrim(preg_replace('#/index\.php$#', '', request()->getBaseUrl()), '/');
    ?>

    <!-- Fuwa.NG Styles -->
    <link rel="stylesheet" href="<?php echo e($assetPrefix . '/assets/nexus/css/nexus.css?v=' . @filemtime(public_path('assets/nexus/css/nexus.css'))); ?>">
    <link rel="stylesheet" href="<?php echo e($assetPrefix . '/assets/nexus/css/payment-modal.css?v=' . @filemtime(public_path('assets/nexus/css/payment-modal.css'))); ?>">
    <!-- Payment Gateway Scripts -->
    <script src="https://js.paystack.co/v1/inline.js" defer></script>
    <script src="https://checkout.flutterwave.com/v3.js" defer></script>
    <script src="https://sdk.monnify.com/plugin/monnify.js" defer></script>
    <meta name="auth-user-email" content="<?php echo e(Auth::check() ? Auth::user()->email : ''); ?>">
    <meta name="auth-user-name" content="<?php echo e(Auth::check() ? (Auth::user()->fullname ?? Auth::user()->username) : ''); ?>">
    <script nonce="<?php echo e($cspNonce ?? ''); ?>">
        const authEmailMeta = document.querySelector('meta[name="auth-user-email"]');
        const authNameMeta = document.querySelector('meta[name="auth-user-name"]');
        window.authUserEmail = authEmailMeta ? authEmailMeta.getAttribute('content') : null;
        window.authUserName = authNameMeta ? authNameMeta.getAttribute('content') : null;
    </script>
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/vendor.js', 'resources/js/app.js', 'resources/js/layout.js']); ?>
    
    <style>
        :root {
            --sidebar-w: 260px;
            --clr-primary: <?php echo e(\App\Models\SystemSetting::get('theme_primary', '#3b82f6')); ?>;
            --clr-primary-hover: <?php echo e(\App\Models\SystemSetting::get('theme_primary_hover', '#2563eb')); ?>;
            --clr-accent-1: <?php echo e(\App\Models\SystemSetting::get('theme_accent_1', '#10b981')); ?>;
            --clr-accent-2: <?php echo e(\App\Models\SystemSetting::get('theme_accent_2', '#8b5cf6')); ?>;
            --clr-accent-3: <?php echo e(\App\Models\SystemSetting::get('theme_accent_3', '#f59e0b')); ?>;
            --select-surface: #111827;
            --select-surface-hover: #172033;
            --select-surface-disabled: #0b1220;
            --select-border: rgba(148, 163, 184, 0.38);
            --select-text: #f8fafc;
            --select-muted: #94a3b8;
            --option-surface: #ffffff;
            --option-surface-hover: #f8fafc;
            --option-selected: #bfdbfe;
            --option-text: #111827;
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
        select.form-control {
            background-color: var(--select-surface) !important;
            color: var(--select-text) !important;
            border-color: var(--select-border) !important;
        }
        select.form-control:hover { background-color: var(--select-surface-hover) !important; }
        select.form-control:focus {
            background-color: var(--select-surface-hover) !important;
            border-color: var(--clr-primary) !important;
            color: var(--select-text) !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.18) !important;
        }
        select.form-control:disabled {
            background-color: var(--select-surface-disabled) !important;
            color: var(--select-muted) !important;
            border-color: rgba(148, 163, 184, 0.22) !important;
            opacity: 1;
            cursor: not-allowed;
        }
        select.form-control option {
            color: var(--option-text);
            background: var(--option-surface);
        }
        select.form-control option:hover,
        select.form-control option:focus {
            color: var(--option-text);
            background: var(--option-surface-hover);
        }
        select.form-control option:checked,
        select.form-control option[selected] {
            color: var(--option-text);
            background: var(--option-selected);
        }
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
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<?php
    $webUser = Auth::user();
    $adminUser = Auth::guard('admin')->user();
    $authUser = $webUser ?: $adminUser;
    $isAuthed = (bool) $authUser;
    $adminPath = trim((string) config('app.admin_path', 'admin'), '/');
    $onAdminArea = $adminPath !== '' && (request()->is($adminPath) || request()->is($adminPath . '/*'));
    $routeName = request()->route()?->getName();
    if (! $onAdminArea && is_string($routeName) && str_starts_with($routeName, 'admin.')) {
        $onAdminArea = true;
    }
    // Full admin chrome only on admin URLs — both guards can be logged in at once (same session).
    $showFullAdminSidebar = (bool) $adminUser && $onAdminArea;
    $displayUser = ($onAdminArea && $adminUser) ? $adminUser : ($webUser ?? $adminUser);
    $webUnreadCount = $webUser ? (int) $webUser->unreadNotifications()->count() : 0;
    $isAuthPage = request()->routeIs('login', 'register', 'password.*', 'admin.login', 'admin.2fa.*');
    $showDashboardChrome = $isAuthed && ! $isAuthPage;
?>
<body class="<?php echo e($showDashboardChrome ? 'dashboard-body' : ''); ?>">
    <!-- Background Elements -->
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <?php 
        $hideNav = trim($__env->yieldContent('hide_nav')) === 'true';
    ?>

    <?php if($showDashboardChrome): ?>
    <!-- Top Horizontal Navigation -->
    <aside class="sidebar" id="sidebar" aria-label="Main Navigation" role="navigation">
        <div class="sidebar-header">
            <a href="<?php echo e(url('/')); ?>" class="sidebar-logo text-decoration-none">
                <?php $logoUrl = \App\Models\SystemSetting::get('site_logo_url'); ?>
                <?php if($logoUrl): ?>
                    <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e(\App\Models\SystemSetting::get('site_name', 'Logo')); ?>" loading="lazy" decoding="async" style="max-height: 32px; margin-right: 8px;">
                <?php else: ?>
                    <i class="fa-solid fa-bolt"></i>
                <?php endif; ?>
                <span><?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?></span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="<?php echo e(url('/')); ?>"><i class="fa-solid fa-house"></i> <span class="nav-text">Home</span></a>
            </div>
            <?php if($showFullAdminSidebar): ?>
            <div class="nav-section">Admin</div>
            <div class="nav-item <?php echo e(Request::routeIs('admin.dashboard') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="fa-solid fa-gauge-high"></i> <span class="nav-text">Dashboard</span></a>
            </div>
            
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-layer-group"></i> <span class="nav-text">Catalog</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('admin.services.index')); ?>" class="<?php echo e(Request::routeIs('admin.services.*') ? 'active' : ''); ?>">Services</a>
                    <a href="<?php echo e(route('admin.custom_apis.index')); ?>" class="<?php echo e(Request::routeIs('admin.custom_apis.*') ? 'active' : ''); ?>">Custom APIs</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-users"></i> <span class="nav-text">Users & Access</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="<?php echo e(Request::routeIs('admin.users.*') ? 'active' : ''); ?>">Users</a>
                    <?php if(Auth::guard('admin')->user()?->hasPermission('manage_admins')): ?>
                    <a href="<?php echo e(route('admin.admins.index')); ?>" class="<?php echo e(Request::routeIs('admin.admins.*') ? 'active' : ''); ?>">System Admins</a>
                    <?php endif; ?>
                    <?php if(Auth::guard('admin')->user()?->hasPermission('manage_roles')): ?>
                    <a href="<?php echo e(route('admin.roles.index')); ?>" class="<?php echo e(Request::routeIs('admin.roles.*') ? 'active' : ''); ?>">Roles & Permissions</a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.delivery-agents.index')); ?>" class="<?php echo e(Request::routeIs('admin.delivery-agents.*') ? 'active' : ''); ?>">Delivery Agents</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-chart-line"></i> <span class="nav-text">Monitoring</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('admin.transactions.index')); ?>" class="<?php echo e(Request::routeIs('admin.transactions.*') ? 'active' : ''); ?>">Transactions</a>
                    <a href="<?php echo e(route('admin.verifications.index')); ?>" class="<?php echo e(Request::routeIs('admin.verifications.index') ? 'active' : ''); ?>">Verification Vault</a>
                    <a href="<?php echo e(route('admin.verifications.nin_modifications.index')); ?>" class="<?php echo e(Request::routeIs('admin.verifications.nin_modifications.*') ? 'active' : ''); ?>">NIN Modifications</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-bullhorn"></i> <span class="nav-text">Engagement</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('admin.broadcasts.index')); ?>" class="<?php echo e(Request::routeIs('admin.broadcasts.*') ? 'active' : ''); ?>">Broadcasts</a>
                    <?php if(Auth::guard('admin')->user()?->is_super_admin): ?>
                    <a href="<?php echo e(route('admin.direct_messages.index')); ?>" class="<?php echo e(Request::routeIs('admin.direct_messages.*') ? 'active' : ''); ?>">Direct Messages</a>
                    <a href="<?php echo e(route('admin.email_campaigns.index')); ?>" class="<?php echo e(Request::routeIs('admin.email_campaigns.*') ? 'active' : ''); ?>">Email Campaigns</a>
                    <a href="<?php echo e(route('admin.sms_campaigns.index')); ?>" class="<?php echo e(Request::routeIs('admin.sms_campaigns.*') ? 'active' : ''); ?>">SMS Campaigns</a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.tickets')); ?>" class="<?php echo e(Request::routeIs('admin.tickets*') ? 'active' : ''); ?>">Support Tickets</a>
                </div>
            </div>

            <?php
                $contentAdmin = Auth::guard('admin')->user();
                $canManageBlog = $contentAdmin && $contentAdmin->hasPermission('manage_blog');
                $canManagePages = $contentAdmin && $contentAdmin->hasPermission('manage_pages');
            ?>
            <?php if($canManageBlog || $canManagePages): ?>
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-globe"></i> <span class="nav-text">Content</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <?php if($canManageBlog): ?>
                    <a href="<?php echo e(route('admin.posts.index')); ?>" class="<?php echo e(Request::routeIs('admin.posts.*') ? 'active' : ''); ?>">Blog Posts</a>
                    <?php endif; ?>
                    <?php if($canManagePages): ?>
                    <a href="<?php echo e(route('admin.pages.index')); ?>" class="<?php echo e(Request::routeIs('admin.pages.*') ? 'active' : ''); ?>">Pages</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-sitemap"></i> <span class="nav-text">Operations</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('admin.operations.invoices')); ?>" class="<?php echo e(Request::routeIs('admin.operations.invoices') ? 'active' : ''); ?>">Invoices</a>
                    <a href="<?php echo e(route('admin.operations.logistics')); ?>" class="<?php echo e(Request::routeIs('admin.operations.logistics') ? 'active' : ''); ?>">Logistics</a>
                    <a href="<?php echo e(route('admin.operations.notary')); ?>" class="<?php echo e(Request::routeIs('admin.operations.notary') ? 'active' : ''); ?>">Notary</a>
                    <?php if(\Illuminate\Support\Facades\Route::has('admin.shipping-providers.index')): ?>
                        <a href="<?php echo e(route('admin.shipping-providers.index')); ?>" class="<?php echo e(Request::routeIs('admin.shipping-providers.*') ? 'active' : ''); ?>">Shipping</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-wrench"></i> <span class="nav-text">System</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('admin.settings.index')); ?>" class="<?php echo e(Request::routeIs('admin.settings.*') ? 'active' : ''); ?>">Settings</a>
                    <a href="<?php echo e(route('admin.sandbox.index')); ?>" class="<?php echo e(Request::routeIs('admin.sandbox.*') ? 'active' : ''); ?>">Sandbox</a>
                    <?php if(Auth::guard('admin')->user()?->is_super_admin): ?>
                    <a href="<?php echo e(route('admin.developer_api.index')); ?>" class="<?php echo e(Request::routeIs('admin.developer_api.*') ? 'active' : ''); ?>">Developer API</a>
                    <a href="<?php echo e(route('admin.audit_logs.index')); ?>" class="<?php echo e(Request::routeIs('admin.audit_logs.*') ? 'active' : ''); ?>">Audit Logs</a>
                    <a href="<?php echo e(route('admin.queue.index')); ?>" class="<?php echo e(Request::routeIs('admin.queue.*') ? 'active' : ''); ?>">Queue Monitor</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php elseif($webUser): ?>
            <div class="nav-section">Main Menu</div>
            <div class="nav-item <?php echo e(Request::routeIs('dashboard') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('dashboard')); ?>"><i class="fa-solid fa-house"></i> <span class="nav-text">Overview</span></a>
            </div>
            <div class="nav-item <?php echo e(Request::routeIs('notifications.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('notifications.index')); ?>"><i class="fa-solid fa-bell"></i> <span class="nav-text">Notifications
                    <?php if($webUnreadCount > 0): ?>
                        <span class="badge badge-primary badge-pill ml-auto px-2" style="font-size: 0.65rem;"><?php echo e($webUnreadCount); ?></span>
                    <?php endif; ?>
                </span></a>
            </div>
            <div class="nav-item <?php echo e(Request::routeIs('history') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('history')); ?>"><i class="fa-solid fa-clock-rotate-left"></i> <span class="nav-text">History</span></a>
            </div>
            <div class="nav-item <?php echo e(Request::routeIs('services.price_list') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('services.price_list')); ?>"><i class="fa-solid fa-tags"></i> <span class="nav-text">Price List</span></a>
            </div>
            <div class="nav-item <?php echo e(Request::routeIs('developer.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('developer.portal')); ?>"><i class="fa-solid fa-code"></i> <span class="nav-text">Developer</span></a>
            </div>

            <div class="nav-section">Identity Services</div>
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-id-card"></i> <span class="nav-text">NIN Services</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <?php if(\App\Models\SystemSetting::get('nin_service_enabled', 'true') === 'true'): ?>
                        <a href="<?php echo e(route('services.nin.suite')); ?>" class="<?php echo e(Request::routeIs('services.nin.suite') || Request::routeIs('services.nin') || Request::routeIs('services.validation') || Request::routeIs('services.clearance') || Request::routeIs('services.personalization') ? 'active' : ''); ?>">NIN Suite</a>
                        <a href="<?php echo e(route('services.nin_face')); ?>" class="<?php echo e(Request::routeIs('services.nin_face') ? 'active' : ''); ?>">NIN Face</a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('services.nin.modification')); ?>" class="<?php echo e(Request::routeIs('services.nin.modification') ? 'active' : ''); ?>">NIN Modification</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-building-columns"></i> <span class="nav-text">BVN Services</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <?php if(\App\Models\SystemSetting::get('bvn_service_enabled', 'true') === 'true'): ?>
                        <a href="<?php echo e(route('services.bvn')); ?>" class="<?php echo e(Request::routeIs('services.bvn') ? 'active' : ''); ?>">BVN Suite</a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('services.bvn')); ?>">Print BVN Slip</a>
                    <a href="<?php echo e(route('services.bvn')); ?>">BVN Modification</a>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-shield-halved"></i> <span class="nav-text">Verification Hub</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('services.drivers_license')); ?>" class="<?php echo e(Request::routeIs('services.drivers_license') ? 'active' : ''); ?>">DL Verify</a>
                    <?php if(\Illuminate\Support\Facades\Route::has('services.biometric_verify')): ?>
                        <a href="<?php echo e(route('services.biometric_verify')); ?>" class="<?php echo e(Request::routeIs('services.biometric_verify') ? 'active' : ''); ?>">Bio Verify</a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('services.cac_verify')); ?>" class="<?php echo e(Request::routeIs('services.cac_verify') ? 'active' : ''); ?>">CAC Verify</a>
                    <a href="<?php echo e(route('services.tin_verify')); ?>" class="<?php echo e(Request::routeIs('services.tin_verify') ? 'active' : ''); ?>">TIN Verify</a>
                    <a href="<?php echo e(route('services.passport')); ?>" class="<?php echo e(Request::routeIs('services.passport') ? 'active' : ''); ?>">Passport Verify</a>
                    <a href="<?php echo e(route('services.voters_card')); ?>" class="<?php echo e(Request::routeIs('services.voters_card') ? 'active' : ''); ?>">Voters Card</a>
                    <a href="<?php echo e(route('services.address_verify')); ?>" class="<?php echo e(Request::routeIs('services.address_verify') ? 'active' : ''); ?>">Address Verify</a>
                    <a href="<?php echo e(route('services.plate_number')); ?>" class="<?php echo e(Request::routeIs('services.plate_number') ? 'active' : ''); ?>">Plate Verify</a>
                </div>
            </div>

            <div class="nav-section"><?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?> Ecosystem</div>
            <?php if(\App\Models\SystemSetting::get('airtime_data_enabled', 'true') === 'true'): ?>
            <div class="nav-item <?php echo e((Request::routeIs('services.vtu.*') || Request::routeIs('services.airtime*') || Request::routeIs('services.data*')) ? 'active' : ''); ?>">
                <a href="<?php echo e(route('services.vtu.hub')); ?>"><i class="fa-solid fa-mobile-screen-button"></i> <span class="nav-text">VTU Hub</span></a>
            </div>
            <?php endif; ?>

            <?php if(\App\Models\SystemSetting::get('legal_service_enabled', 'true') === 'true'): ?>
            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-gavel text-indigo"></i> <span class="nav-text">Legal Services</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('services.legal')); ?>" class="<?php echo e(Request::routeIs('services.legal') ? 'active' : ''); ?>">Legal Platform</a>
                    <a href="<?php echo e(route('services.legal-hub')); ?>" class="<?php echo e(Request::routeIs('services.legal-hub') ? 'active' : ''); ?>">AI Legal Hub</a>
                    <a href="<?php echo e(route('services.notary')); ?>" class="<?php echo e(Request::routeIs('services.notary') ? 'active' : ''); ?>">Notary Services</a>
                    <a href="<?php echo e(route('services.stamp_duty')); ?>" class="<?php echo e(Request::routeIs('services.stamp_duty') ? 'active' : ''); ?>">Stamp Duty</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-store text-info"></i> <span class="nav-text">Commerce & Payments</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <a href="<?php echo e(route('services.agency')); ?>" class="<?php echo e(Request::routeIs('services.agency') ? 'active' : ''); ?>">Agency Banking</a>
                    <a href="<?php echo e(route('services.virtual_card')); ?>" class="<?php echo e(Request::routeIs('services.virtual_card') ? 'active' : ''); ?>">Virtual Cards</a>
                    <a href="<?php echo e(route('services.fx')); ?>" class="<?php echo e(Request::routeIs('services.fx') ? 'active' : ''); ?>">FX Exchange</a>
                    <a href="<?php echo e(route('services.invoicing')); ?>" class="<?php echo e(Request::routeIs('services.invoicing') ? 'active' : ''); ?>">Invoicing</a>
                    <a href="<?php echo e(route('services.ticketing')); ?>" class="<?php echo e(Request::routeIs('services.ticketing') ? 'active' : ''); ?>">Ticketing</a>
                    <?php if(\App\Models\SystemSetting::get('auction_service_enabled', 'true') === 'true'): ?>
                        <a href="<?php echo e(route('public.auctions.index')); ?>" target="_blank" class="<?php echo e(Request::routeIs('public.auctions.index') ? 'active' : ''); ?>">Auctions <i class="fa-solid fa-up-right-from-square ml-1 small opacity-50"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-item has-submenu">
                <button type="button" class="submenu-toggle" aria-expanded="false"><i class="fa-solid fa-truck text-danger"></i> <span class="nav-text">Logistics & Utilities</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>
                <div class="submenu" role="region">
                    <?php if(\App\Models\SystemSetting::get('post_office_service_enabled', 'true') === 'true'): ?>
                        <a href="<?php echo e(route('logistics.home')); ?>" target="_blank"
                           class="<?php echo e(request()->routeIs('logistics.home') ? 'active' : ''); ?>">Logistics Hub</a>
                    <?php endif; ?>
                    <?php if(\App\Models\SystemSetting::get('education_service_enabled', 'true') === 'true'): ?>
                        <a href="<?php echo e(route('services.education.waec')); ?>" class="<?php echo e(Request::routeIs('services.education.waec') ? 'active' : ''); ?>">WAEC Result</a>
                        <a href="<?php echo e(route('services.education.waec_registration')); ?>" class="<?php echo e(Request::routeIs('services.education.waec_registration') ? 'active' : ''); ?>">WAEC Reg. PIN</a>
                    <?php endif; ?>
                    <?php if(\App\Models\SystemSetting::get('insurance_service_enabled', 'true') === 'true'): ?>
                        <a href="<?php echo e(route('services.insurance.motor')); ?>" class="<?php echo e(Request::routeIs('services.insurance.motor') ? 'active' : ''); ?>">Motor Insurance</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(($webUser?->role ?? null) === 'admin'): ?>
            <div class="nav-section">Admin Management</div>
            
            <?php if(Auth::guard('admin')->user()?->is_super_admin): ?>
            <div class="nav-item <?php echo e(Request::routeIs('admin.self_funding.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.self_funding.index')); ?>"><i class="fa-solid fa-vault text-primary"></i> <span class="nav-text">Self-Funding</span></a>
            </div>
            <?php endif; ?>

            <?php if(Auth::guard('admin')->user()?->hasPermission('manage_admins')): ?>
            <div class="nav-item <?php echo e(Request::routeIs('admin.admins.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.admins.index')); ?>"><i class="fa-solid fa-users-gear text-danger"></i> <span class="nav-text">System Admins</span></a>
            </div>
            <?php endif; ?>
            <?php if(Auth::guard('admin')->user()?->hasPermission('manage_roles')): ?>
            <div class="nav-item <?php echo e(Request::routeIs('admin.roles.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.roles.index')); ?>"><i class="fa-solid fa-user-shield text-warning"></i> <span class="nav-text">Roles & Permissions</span></a>
            </div>
            <?php endif; ?>

            <?php if(Auth::guard('admin')->user()?->is_super_admin): ?>
            <div class="nav-item <?php echo e(Request::routeIs('admin.logistics-staff.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.logistics-staff.index')); ?>"><i class="fa-solid fa-truck-fast text-danger"></i> <span class="nav-text">Logistics Staff</span></a>
            </div>
            <?php endif; ?>

            <div class="nav-item <?php echo e(Request::routeIs('admin.users.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.users.index')); ?>"><i class="fa-solid fa-users"></i> <span class="nav-text">Users Directory</span></a>
            </div>
            <div class="nav-item <?php echo e(Request::routeIs('admin.custom_apis.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.custom_apis.index')); ?>"><i class="fa-solid fa-code-merge text-primary"></i> <span class="nav-text">Control Tower</span></a>
            </div>
            <div class="nav-item <?php echo e(Request::routeIs('admin.tickets.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.tickets')); ?>"><i class="fa-solid fa-headset text-success"></i> <span class="nav-text">Support Center</span></a>
            </div>
            <div class="nav-item">
                <a href="<?php echo e(route('admin.settings.index')); ?>"><i class="fa-solid fa-gear"></i> <span class="nav-text">System Settings</span></a>
            </div>
            <?php endif; ?>

            <div class="nav-section">Account Settings</div>
            <div class="nav-item <?php echo e(Request::routeIs('profile') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('profile')); ?>"><i class="fa-solid fa-user-gear"></i> <span class="nav-text">Profile</span></a>
            </div>
            <?php elseif($adminUser): ?>
            <div class="nav-section">Admin</div>
            <div class="nav-item <?php echo e(Request::routeIs('admin.dashboard') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="fa-solid fa-gauge-high"></i> <span class="nav-text">Admin Dashboard</span></a>
            </div>
            <div class="nav-section">Account</div>
            <div class="nav-item <?php echo e(Request::routeIs('admin.profile.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('admin.profile.edit')); ?>"><i class="fa-solid fa-user-gear"></i> <span class="nav-text">Admin Profile</span></a>
            </div>
            <?php endif; ?>
        
            <div class="nav-item mt-auto pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                <button type="button" id="minimizeSidebarBtn" aria-label="Minimize Sidebar">
                    <i class="fa-solid fa-angle-left collapse-icon" style="transition: transform 0.3s;"></i>
                    <span class="nav-text">Collapse Menu</span>
                </button>
            </div>
        </nav>

        <div class="sidebar-footer">
            <a
                href="<?php echo e($showFullAdminSidebar ? route('admin.profile.edit') : route('profile')); ?>"
                class="sidebar-user-link mr-3"
                aria-label="<?php echo e($showFullAdminSidebar ? 'Edit admin profile' : 'Edit profile'); ?>"
            >
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?php
                            $sidebarAvatar = $showFullAdminSidebar ? ($adminUser?->avatar ?? null) : null;
                            $sidebarName = $showFullAdminSidebar
                                ? ($adminUser?->fullname ?? $adminUser?->username ?? 'Admin')
                                : ($webUser?->fullname ?? $webUser?->username ?? 'User');
                            $sidebarRole = $showFullAdminSidebar ? 'Admin' : ucfirst($webUser?->role ?? 'User');
                        ?>
                        <?php if($sidebarAvatar): ?>
                            <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($sidebarAvatar)); ?>" alt="<?php echo e($sidebarName); ?>" class="sidebar-user-avatar-img">
                        <?php else: ?>
                            <span class="sidebar-user-avatar-fallback"><?php echo e(strtoupper(substr($sidebarName, 0, 1))); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="sidebar-user-info">
                        <div class="su-name"><?php echo e($sidebarName); ?></div>
                        <div class="su-role text-muted small"><?php echo e($sidebarRole); ?></div>
                    </div>
                </div>
            </a>
            <form action="<?php echo e($showFullAdminSidebar ? route('admin.logout') : route('logout')); ?>" method="POST" id="logout-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>
    <?php endif; ?>
    <?php if(!$showDashboardChrome && !$hideNav): ?>
        <!-- Public Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top public-nav py-3" style="background: rgba(3, 7, 18, 0.95); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(255,255,255,0.05); z-index: 1050;">
            <div class="container">
                <a class="navbar-brand font-weight-bold d-flex align-items-center" href="<?php echo e(url('/')); ?>">
                    <div class="brand-icon mr-2" style="background: var(--clr-primary); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-bolt text-white" style="font-size: 14px;"></i></div>
                    <span style="letter-spacing: 1px;"><?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?></span>
                </a>
                <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto d-flex flex-row">
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(url('/')); ?>#services">Services</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(route('public.auctions.index')); ?>">Auctions</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(route('logistics.home')); ?>">Logistics</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(url('/explore/notary-services')); ?>">Notary</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(url('/')); ?>#legal-hub">AI Legal Hub</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(route('services.price_list')); ?>">Pricing</a></li>
                        <li class="nav-item mx-2"><a class="nav-link text-white small font-weight-bold" href="<?php echo e(route('blog.index')); ?>">Blog</a></li>
                    </ul>
                    <div class="nav-actions d-flex align-items-center gap-3">
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-link text-white text-decoration-none font-weight-bold small">Login</a>
                        <a href="<?php echo e(route('register')); ?>" class="btn btn-primary px-4 py-2 small font-weight-bold" style="border-radius: 10px;">Get Started</a>
                    </div>
                </div>
            </div>
        </nav>
        <div style="height: 80px;"></div> <!-- Spacer for fixed nav -->
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="<?php echo e($showDashboardChrome ? 'main-content' : 'main m-0'); ?>">
        <?php if($showDashboardChrome): ?>
        <header class="top-header">
            <!-- Mobile Toggle -->
            <button class="mobile-toggle d-lg-none mr-3" id="sidebarToggle" type="button" aria-label="Toggle Navigation Sidebar" aria-expanded="false" aria-controls="sidebar" onclick="return window.__toggleNexusSidebar(event);">
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
                Welcome back, <span><?php echo e(explode(' ', $displayUser->fullname ?? $displayUser->username ?? 'User')[0]); ?></span>
            </div>
            <div class="header-actions">
                <button class="action-btn text-decoration-none" id="highContrastToggle" title="Toggle High Contrast Mode" aria-label="High Contrast">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>
                <?php if($webUser): ?>
                <div class="wallet-balance d-none d-md-block">
                    <span class="text-muted small">Balance:</span>
                    <h5 class="m-0">₦<?php echo e(number_format($webUser->balance?->user_balance ?? 0, 2)); ?></h5>
                </div>
                <?php endif; ?>
                <?php if($webUser && ! $showFullAdminSidebar): ?>
                <a class="action-btn text-decoration-none" href="<?php echo e(route('notifications.index')); ?>" aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <?php if($webUnreadCount > 0): ?>
                        <span class="notification-dot"></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            </div>
        </header>
        <?php endif; ?>

        <?php $publicWrapperClass = trim($__env->yieldContent('public_wrapper_class')); ?>
        <?php if($showDashboardChrome): ?>
            <div class="dashboard-content fade-in">
                <?php if(session('error')): ?>
                    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fecaca;">
                        <?php echo session('error'); ?>

                    </div>
                <?php endif; ?>
                <?php if(session('success')): ?>
                    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #bbf7d0;">
                        <?php echo session('success'); ?>

                    </div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        <?php else: ?>
            <?php if($publicWrapperClass === 'none'): ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fecaca;">
                        <?php echo session('error'); ?>

                    </div>
                <?php endif; ?>
                <?php if(session('success')): ?>
                    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #bbf7d0;">
                        <?php echo session('success'); ?>

                    </div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            <?php else: ?>
                <div class="<?php echo e($publicWrapperClass ?: 'container py-5'); ?> fade-in">
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fecaca;">
                            <?php echo session('error'); ?>

                        </div>
                    <?php endif; ?>
                    <?php if(session('success')): ?>
                        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.25); color: #bbf7d0;">
                            <?php echo session('success'); ?>

                        </div>
                    <?php endif; ?>
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <footer class="py-4 <?php echo e($showDashboardChrome ? 'px-4' : ''); ?>" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02);">
            <div class="<?php echo e($showDashboardChrome ? '' : 'container'); ?>">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="gap: 12px;">
                    <div class="text-white-50 small">
                        © <?php echo e(date('Y')); ?> <?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?>. All rights reserved.
                    </div>
                    <div class="d-flex align-items-center" style="gap: 14px;">
                        <a class="text-white-50 small" href="<?php echo e(url('/')); ?>">Home</a>
                        <a class="text-white-50 small" href="<?php echo e(route('services.price_list')); ?>">Pricing</a>
                        <a class="text-white-50 small" href="<?php echo e(route('blog.index')); ?>">Blog</a>
                        <?php if($webUser): ?>
                            <a class="text-white-50 small" href="<?php echo e(route('tickets.index')); ?>">Support</a>
                        <?php elseif($showFullAdminSidebar): ?>
                            <a class="text-white-50 small" href="<?php echo e(route('admin.tickets')); ?>">Support</a>
                        <?php else: ?>
                            <a class="text-white-50 small" href="<?php echo e(route('login')); ?>">Support</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </footer>
        
        <!-- Dynamic WhatsApp Widget handled by whatsappWidget.js -->
        <meta name="auth-check" content="<?php echo e(Auth::check() ? 'true' : 'false'); ?>">

        <?php if($webUser): ?>
        <!-- AI Assistant FAB & Chat -->
        <button class="ai-fab shadow-glow" id="aiFabToggle" type="button" title="<?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?> AI Assistant" aria-label="Open AI Assistant Chat" aria-expanded="false" aria-controls="aiChatCard">
            <i class="fa-solid fa-robot" aria-hidden="true"></i>
        </button>

        <div class="ai-chat-card" id="aiChatCard" role="dialog" aria-label="AI Assistant Chat" hidden>
            <div class="ai-chat-header">
                <div class="d-flex align-items-center">
                    <div class="ai-avatar mr-2"><i class="fa-solid fa-robot"></i></div>
                    <div>
                        <h6 class="mb-0 font-weight-bold"><?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?> AI</h6>
                        <span class="small ai-status">Online Assistant</span>
                    </div>
                </div>
                <button class="ai-close" id="aiChatClose" type="button" aria-label="Close AI Assistant Chat"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="ai-chat-body" id="aiChatBody" role="log" aria-live="polite" aria-relevant="additions text">
                <div class="ai-msg ai-msg-bot">
                    <div class="ai-msg-content">Hello <?php echo e(explode(' ', $webUser->fullname)[0] ?? 'there'); ?>! I am your <?php echo e(\App\Models\SystemSetting::get('site_name', 'Fuwa.NG')); ?> AI assistant. How can I help you navigate the platform today?</div>
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
        <?php endif; ?>
    </main>

    <!-- Toast Container -->
    <div class="nexus-toast-container" id="nexusToastContainer"></div>

    <!-- Scripts -->
    <script src="<?php echo e($assetPrefix . '/assets/nexus/js/csrf-fetch.js?v=' . @filemtime(public_path('assets/nexus/js/csrf-fetch.js'))); ?>"></script>
    <script src="<?php echo e($assetPrefix . '/assets/nexus/js/nexus.js?v=' . @filemtime(public_path('assets/nexus/js/nexus.js'))); ?>"></script>
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
    <?php if($showDashboardChrome): ?>
        <script src="<?php echo e($assetPrefix . '/assets/nexus/js/payment-modal.js?v=' . @filemtime(public_path('assets/nexus/js/payment-modal.js'))); ?>"></script>
        <script src="<?php echo e($assetPrefix . '/assets/nexus/js/nexus-ai.js?v=' . @filemtime(public_path('assets/nexus/js/nexus-ai.js'))); ?>"></script>
    <?php endif; ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script nonce="<?php echo e($cspNonce ?? ''); ?>">
        window.__toggleNexusSidebar = function (event) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }
            var sidebar = document.getElementById('sidebar');
            var mobileToggle = document.getElementById('sidebarToggle');
            if (!sidebar || !mobileToggle) {
                return false;
            }
            if (window.matchMedia('(max-width: 991.98px)').matches) {
                sidebar.classList.remove('minimized');
            }
            var isOpen = sidebar.classList.toggle('open');
            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            return false;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const sidebarHandledByExternalJs = !!window.__NEXUS_SIDEBAR_HANDLED;

            // Public mobile navbar toggle fallback (independent of Bootstrap JS)
            const publicToggle = document.querySelector('.public-nav .navbar-toggler[data-target="#navbarNav"]');
            const publicNav = document.getElementById('navbarNav');
            if (publicToggle && publicNav) {
                publicToggle.addEventListener('click', () => {
                    const isOpen = publicNav.classList.toggle('show');
                    publicToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            // Sidebar Navigation Logic
            const sidebar = document.getElementById('sidebar');
            const minBtn = document.getElementById('minimizeSidebarBtn');
            
            if (sidebar && minBtn && !sidebarHandledByExternalJs) {
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
            if (mobileToggle && sidebar && !sidebarHandledByExternalJs) {
                mobileToggle.addEventListener('click', () => {
                    window.__toggleNexusSidebar();
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
                { title: 'NIN Suite', cat: 'Identity', url: '<?php echo e(route("services.nin.suite")); ?>', icon: 'fa-id-card-clip' },
                { title: 'BVN Suite', cat: 'Identity', url: '<?php echo e(route("services.bvn")); ?>', icon: 'fa-building-columns' },
                { title: 'VTU Hub', cat: 'Ecosystem', url: '<?php echo e(route("services.vtu.hub")); ?>', icon: 'fa-mobile-screen-button' },
                { title: 'Legal Hub', cat: 'Ecosystem', url: '<?php echo e(route("services.legal-hub")); ?>', icon: 'fa-gavel' },
                { title: 'Notary', cat: 'Ecosystem', url: '<?php echo e(route("services.notary")); ?>', icon: 'fa-file-signature' },
                { title: 'Wallet Funding', cat: 'Wallet', url: '<?php echo e(route("wallet.fund")); ?>', icon: 'fa-wallet' },
                { title: 'Transactions', cat: 'Wallet', url: '<?php echo e(route("history")); ?>', icon: 'fa-receipt' },
                { title: 'Profile Settings', cat: 'Account', url: '<?php echo e(route("profile")); ?>', icon: 'fa-user-gear' }
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
<?php /**PATH /var/www/fuwa.ng/html/resources/views/layouts/nexus.blade.php ENDPATH**/ ?>