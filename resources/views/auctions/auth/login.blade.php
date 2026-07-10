@extends('layouts.auction')

@section('title', 'Auction Login')

@section('content')
<div class="row align-items-center" style="min-height: calc(100vh - 180px);">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--auction-primary);">
                Fuwa Auctions
            </span>
        </div>
        <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem; line-height: 1.1;">
            Welcome back to <span style="color:var(--auction-primary)">Auctions</span>
        </h1>
        <p class="text-white-50 mb-4" style="max-width: 560px;">
            Sign in to access your auction dashboard, manage your bids, and track your watchlist.
        </p>
        <img src="{{ asset('images/hero_visual.png') }}" alt="Auctions illustration" class="img-fluid" style="max-height: 260px; opacity: 0.95;">
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="font-weight-bold mb-0">Login</h4>
                <a href="{{ route('auction.register') }}" class="text-white-50 text-decoration-none small">
                    New here?
                    <span style="color:var(--auction-primary); font-weight: 600;">Create account</span>
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger border-0 mb-4" style="background: rgba(220, 38, 38, 0.15); color: #fff;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('auction.login') }}" id="loginForm">
                @csrf
                <input type="hidden" name="service" value="auctions">

                <div class="form-group">
                    <label for="email" class="text-white-50 small">Email address</label>
                    <input id="email" name="email" type="email" class="form-control tracking-input" required autofocus value="{{ old('email') }}">
                    @error('email')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="password" class="text-white-50 small d-flex justify-content-between">
                        <span>Password</span>
                        <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: var(--auction-primary);">Forgot?</a>
                    </label>
                    <input id="password" name="password" type="password" class="form-control tracking-input" required>
                    @error('password')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                </div>

                <button type="submit" class="btn btn-auction-primary btn-block">
                    <i class="fa fa-arrow-right-to-bracket mr-2"></i> Sign in
                </button>
            </form>

            <div class="mt-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1" style="height: 1px; background: rgba(255,255,255,0.08);"></div>
                    <div class="px-3 text-white-50 small">or</div>
                    <div class="flex-grow-1" style="height: 1px; background: rgba(255,255,255,0.08);"></div>
                </div>

                <div class="mt-3">
                    <div class="text-white-50 small mb-2">Already have a Fuwa.NG account? Login with your account</div>
                    <a href="{{ route('login') }}?service=auctions&redirect={{ urlencode(request()->get('redirect', '/auction/dashboard')) }}"
                       class="btn btn-outline-light btn-block" style="border-radius: 12px;">
                        <i class="fa fa-shield-halved mr-2" style="color: var(--auction-primary);"></i> Continue with Fuwa.ng
                    </a>

                    <a href="{{ route('auth.google.redirect', ['service' => 'auctions', 'redirect' => request()->get('redirect', '/auction/dashboard')]) }}"
                       class="btn btn-outline-light btn-block mt-2" style="border-radius: 12px;">
                        <i class="fa-brands fa-google mr-2" style="color: var(--auction-primary);"></i> Continue with Google
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

