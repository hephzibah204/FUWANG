<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Logistics Hub'); ?> | <?php echo e(config('app.name', 'Fuwa.NG')); ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/vendor.js', 'resources/js/layout.js']); ?>

    <style>
        :root {
            --po-primary: #f59e0b; /* Amber/Orange for Logistics */
            --po-secondary: #1e293b;
            --po-accent: #3b82f6;
            --bg-dark: #0f172a;
            --glass-white: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.08);
            --po-select-surface: #111827;
            --po-select-surface-hover: #172033;
            --po-select-surface-disabled: #0b1220;
            --po-select-border: rgba(148, 163, 184, 0.38);
            --po-select-text: #f8fafc;
            --po-select-muted: #94a3b8;
            --po-option-surface: #ffffff;
            --po-option-surface-hover: #f8fafc;
            --po-option-selected: #fde68a;
            --po-option-text: #111827;
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
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php
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
    ?>

    <nav class="navbar navbar-expand-lg navbar-po">
        <a class="navbar-brand font-weight-bold d-flex align-items-center" href="<?php echo e(route('logistics.home')); ?>">
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
                <li class="nav-item"><a class="nav-link" href="<?php echo e(route('logistics.home')); ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
            </ul>

            <div class="d-flex align-items-center">
                <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('logistics.login')); ?>" class="text-white-50 mr-3 text-decoration-none small">Login</a>
                    <a href="<?php echo e(route('logistics.register')); ?>" class="btn btn-po-primary btn-sm px-4">Create Account</a>
                <?php else: ?>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-toggle="dropdown">
                            <div class="mr-2 text-right d-none d-md-block">
                                <small class="d-block text-white-50">Wallet Balance</small>
                                <span class="font-weight-bold" style="color:var(--po-primary)">₦<?php echo e(number_format((float) (auth()->user()->balance?->user_balance ?? 0), 2)); ?></span>
                            </div>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-user"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right bg-dark border-glass shadow-lg">
                            <a class="dropdown-item text-white" href="<?php echo e($logisticsDashboardUrl); ?>"><i class="fa fa-th-large mr-2"></i> Dashboard</a>
                            <div class="dropdown-divider border-white-10"></div>
                            <form action="<?php echo e(route('logout')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button class="dropdown-item text-danger"><i class="fa fa-power-off mr-2"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if(auth()->guard()->check()): ?>
        <aside class="po-sidebar">
            <div class="mb-4">
                <small class="text-white-50 text-uppercase tracking-wider font-weight-bold" style="font-size: 0.7rem;">Main Menu</small>
            </div>
            <a href="<?php echo e($logisticsDashboardUrl); ?>" class="po-menu-item <?php echo e(request()->routeIs('services.user.logistics.dashboard') || request()->routeIs('user.logistics.dashboard') ? 'active' : ''); ?>">
                <i class="fa fa-th-large"></i> Overview
            </a>
            <a href="<?php echo e($logisticsBookUrl); ?>" class="po-menu-item <?php echo e(request()->routeIs('services.user.logistics.book') || request()->routeIs('user.logistics.book') ? 'active' : ''); ?>">
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
            <a href="<?php echo e($helpCenterUrl); ?>" class="po-menu-item">
                <i class="fa fa-headset"></i> Help Center
            </a>
        </aside>
        <main class="po-main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    <?php else: ?>
        <main class="po-public-content">
            <div class="container">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    <?php endif; ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/fuwa.ng/html/resources/views/layouts/postoffice.blade.php ENDPATH**/ ?>