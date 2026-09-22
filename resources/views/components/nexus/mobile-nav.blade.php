<nav class="nexus-mobile-bottom-nav d-md-none" aria-label="Mobile Navigation">
    <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a href="{{ Route::has('services.nin.suite') ? route('services.nin.suite') : url('services/nin/suite') }}" class="mobile-nav-link {{ request()->is('services*') ? 'active' : '' }}">
        <i class="fa-solid fa-grid-2"></i>
        <span>Services</span>
    </a>
    <a href="{{ Route::has('wallet.fund') ? route('wallet.fund') : url('wallet/fund') }}" class="mobile-nav-link mobile-nav-highlight {{ request()->is('wallet*') ? 'active' : '' }}">
        <div class="highlight-icon">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <span>Wallet</span>
    </a>
    <a href="{{ Route::has('history') ? route('history') : url('history') }}" class="mobile-nav-link {{ request()->is('history*') ? 'active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>History</span>
    </a>
    <a href="{{ Route::has('profile.edit') ? route('profile.edit') : (Route::has('profile') ? route('profile') : url('profile')) }}" class="mobile-nav-link {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
    </a>
</nav>
