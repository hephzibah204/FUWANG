@extends('layouts.nexus')

@section('title', 'Verify email | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-container">
    <div class="auth-brand">
        <a href="/" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span></a>
        <h2>Confirm your email</h2>
        <p>We sent a verification link to <strong class="text-white">{{ Auth::user()->email }}</strong>. Click the link in that email to unlock your dashboard and full account access.</p>
        <div class="brand-features">
            <div class="brand-feat"><i class="fa-solid fa-envelope-circle-check"></i> Check inbox &amp; spam</div>
            <div class="brand-feat"><i class="fa-solid fa-link"></i> Link expires for your security</div>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Didn’t get the email?</h2>
            <p class="auth-sub">Request a new verification link below.</p>

            @if (session('status'))
                <p class="text-success small text-center mb-3">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
                @csrf
                <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Resend verification email
                </button>
            </form>

            <p class="text-white-50 small text-center mt-4 mb-0">
                Need to use a different email?
                <a href="{{ route('logout') }}" class="text-primary font-weight-bold" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                and register again, or contact support to update your address.
            </p>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>
@endsection
