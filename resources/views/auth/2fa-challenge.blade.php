@extends('layouts.nexus')

@section('title', 'Two-Factor Authentication | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-container">
    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Two-Factor Authentication</h2>
            <p class="auth-sub">Enter the 6-digit code from your authenticator app.</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small mb-3" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.35); color: #ffb3bd;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('2fa.verify') }}" method="POST" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="one_time_password">Authenticator Code</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input type="text" id="one_time_password" name="one_time_password" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fa-solid fa-check mr-2"></i> Verify & Continue
                </button>
            </form>

            <form action="{{ route('2fa.cancel') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-full">
                    Cancel and return to login
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

