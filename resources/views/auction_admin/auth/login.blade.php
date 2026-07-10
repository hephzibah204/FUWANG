@extends('layouts.auction')

@section('title', 'Auction Admin Login')

@section('content')
<div class="row align-items-center" style="min-height: calc(100vh - 180px);">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--auction-primary);">
                Auction Admin
            </span>
        </div>
        <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem; line-height: 1.1;">
            Manage <span style="color:var(--auction-primary)">Auctions</span> securely
        </h1>
        <p class="text-white-50 mb-4" style="max-width: 560px;">
            Sign in to create lots, manage sellers, and review bids.
        </p>
        <div class="glass-card p-3 d-inline-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.18); color: var(--auction-primary);">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="font-weight-bold">Restricted access</div>
                <div class="text-white-50 small">Authorized staff only.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="font-weight-bold mb-0">Auction Admin Login</h4>
                <a href="{{ route('public.auctions.index') }}" class="text-white-50 text-decoration-none small">Back to Auction Hub</a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 mb-4" style="background: rgba(220, 38, 38, 0.15); color: #fff;">
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('auction.admin.login') }}">
                @csrf

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Email address</label>
                    <input type="email" name="email" class="form-control tracking-input" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small mb-2">Password</label>
                    <input type="password" name="password" class="form-control tracking-input" required>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label for="remember" class="form-check-label text-white-50 small">Remember this device</label>
                </div>

                <button type="submit" class="btn btn-auction-primary btn-block">
                    <i class="fa-solid fa-shield-check mr-2"></i> Access Admin
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

