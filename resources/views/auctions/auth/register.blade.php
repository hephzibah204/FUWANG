@extends('layouts.auction')

@section('title', 'Create Auction Account')

@section('content')
<div class="row align-items-center" style="min-height: calc(100vh - 180px);">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--auction-primary);">
                Fuwa Auctions
            </span>
        </div>
        <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem; line-height: 1.1;">
            Create your <span style="color:var(--auction-primary)">Auctions</span> account
        </h1>
        <p class="text-white-50 mb-4" style="max-width: 560px;">
            Browse lots, save your watchlist, and bid in real time with a wallet-backed balance.
        </p>
        <div class="glass-card p-3 d-inline-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.18); color: var(--auction-primary);">
                <i class="fa-solid fa-gavel"></i>
            </div>
            <div>
                <div class="font-weight-bold">Faster bidding</div>
                <div class="text-white-50 small">Track your bids and updates in one dashboard.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="font-weight-bold mb-0">Create account</h4>
                <a href="{{ route('auction.login') }}" class="text-white-50 text-decoration-none small">
                    Already have an account?
                    <span style="color:var(--auction-primary); font-weight: 600;">Login</span>
                </a>
            </div>

            <div class="mb-4">
                <div class="text-white-50 small mb-2">Already have a Fuwa.NG account? Continue with your account</div>
                <div class="d-flex flex-wrap">
                    <a href="{{ route('login') }}?service=auctions&redirect={{ urlencode('/auction/dashboard') }}"
                       class="btn btn-outline-light mr-3 mb-2"
                       style="border-radius: 12px;">
                        <i class="fa fa-shield-halved mr-2" style="color: var(--auction-primary);"></i> Continue with Fuwa.NG
                    </a>
                    <a href="{{ route('auth.google.redirect', ['service' => 'auctions', 'redirect' => '/auction/dashboard']) }}"
                       class="btn btn-outline-light mb-2"
                       style="border-radius: 12px;">
                        <i class="fa-brands fa-google mr-2" style="color: var(--auction-primary);"></i> Continue with Google
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 mb-4" style="background: rgba(220, 38, 38, 0.15); color: #fff;">
                    <div class="font-weight-bold mb-2">Please fix the errors below</div>
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('auction.register') }}">
                @csrf
                <input type="hidden" name="service" value="auctions">

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Full name</label>
                    <input type="text" name="fullname" class="form-control tracking-input" value="{{ old('fullname') }}" required>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Username</label>
                    <input type="text" name="username" class="form-control tracking-input" value="{{ old('username') }}" required>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Email</label>
                    <input type="email" name="email" class="form-control tracking-input" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Password</label>
                    <input type="password" name="password" class="form-control tracking-input" required>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control tracking-input" required>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Transaction PIN</label>
                    <input type="password" name="transaction_pin" maxlength="4" class="form-control tracking-input" placeholder="4-digit PIN" required>
                </div>

                <button type="submit" class="btn btn-auction-primary btn-block">
                    <i class="fa fa-user-plus mr-2"></i> Create account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

