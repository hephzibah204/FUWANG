@extends('layouts.nexus')

@section('title', 'Sign In | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-container">
    <!-- Branding Side -->
    <div class="auth-brand">
        <a href="/" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span></a>
        <h2>Nigeria's Most Trusted Digital Services Hub</h2>
        <p>Access NIN/BVN Verification, VTU, Agency Banking, Notary, Auctions, and more — all in one place.</p>
        <div class="brand-features">
            <div class="brand-feat"><i class="fa-solid fa-shield-halved"></i> Bank-grade encryption</div>
            <div class="brand-feat"><i class="fa-solid fa-bolt"></i> Real-time processing</div>
            <div class="brand-feat"><i class="fa-solid fa-headset"></i> 24/7 support</div>
        </div>
    </div>

    <!-- Form Side -->
    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Welcome back</h2>
            <p class="auth-sub">Sign in to your account to continue</p>

            <form action="{{ url('login') }}" method="POST" id="loginForm" class="auth-form">
                @csrf
                <p class="text-danger small text-center" id="errorMsg">
                    @if($errors->any())
                        {{ $errors->first() }}
                    @endif
                </p>

                @if(session('status'))
                    <p class="text-success small text-center" id="statusMsg">{{ session('status') }}</p>
                @endif

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password <a href="{{ route('password.request') }}" class="label-link">Forgot?</a></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('password', this)">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" id="remember" name="remember" class="mr-2">
                        <label for="remember">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none font-weight-bold">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="login-btn">
                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Sign In
                </button>
            </form>

            <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Create one free</a></p>

            <div class="auth-divider"><span>or continue with</span></div>
            <div class="oauth-buttons">
                <button class="oauth-btn"><i class="fa-brands fa-google mr-2"></i> Google</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }

    $(document).ready(function() {
        function normalizeRedirect(raw) {
            if (typeof raw !== 'string') {
                return null;
            }
            const value = raw.trim();
            if (!value) {
                return null;
            }
            try {
                const url = new URL(value, window.location.origin);
                if (url.origin !== window.location.origin) {
                    return null;
                }
                return url.href;
            } catch (e) {
                return null;
            }
        }

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#login-btn');
            var originalBtnText = btn.html();

            btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...');
            btn.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: { 'Accept': 'application/json' },
                timeout: 15000,
                success: function(response) {
                    if (response.status === 'success') {
                        const target = normalizeRedirect(response.redirect) || (window.location.origin + '/dashboard');
                        try {
                            window.location.assign(target);
                        } catch (e) {
                            window.location.href = target;
                        }
                    } else {
                        $('#errorMsg').text(response.message);
                        btn.html(originalBtnText);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr, textStatus) {
                    var message = 'An error occurred. Please try again.';
                    if (textStatus === 'timeout') {
                        message = 'Login request timed out. Ensure MySQL is running and try again.';
                    }
                    if (xhr.status === 419) {
                        message = 'Session expired. Refresh the page and try again.';
                    }
                    if (xhr.status === 422) {
                        message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : message;
                    }
                    $('#errorMsg').text(message);
                    btn.html(originalBtnText);
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .auth-container { display: flex; min-height: 80vh; align-items: center; justify-content: space-between; gap: 50px; }
    .auth-brand { flex: 1; display: none; }
    @media(min-width: 992px) { .auth-brand { display: block; } }
    .auth-brand h2 { font-size: 2.5rem; font-weight: 800; margin: 2rem 0 1rem; color: #fff; }
    .auth-brand p { font-size: 1.1rem; color: var(--clr-text-muted); margin-bottom: 2rem; }
    .brand-features { display: flex; flex-direction: column; gap: 15px; }
    .brand-feat { display: flex; align-items: center; gap: 10px; color: var(--clr-primary); font-weight: 500; }
    .auth-form-side { flex: 1; max-width: 480px; margin: 0 auto; }
    .auth-card { background: rgba(17, 24, 39, 0.7); border: 1px solid rgba(255,255,255,0.1); padding: 45px; border-radius: 28px; backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transform: translateY(0); transition: all 0.4s ease; }
    .auth-card:hover { transform: translateY(-5px); box-shadow: 0 30px 60px -12px rgba(0,0,0,0.6); border-color: rgba(255,255,255,0.15); }
    .auth-card h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 8px; color: #fff; }
    .auth-sub { color: var(--clr-text-muted); margin-bottom: 30px; font-size: 0.95rem; }
    .input-wrap { position: relative; display: flex; align-items: center; transition: all 0.3s ease; }
    .input-wrap i { position: absolute; left: 18px; color: var(--clr-text-muted); transition: color 0.3s ease; }
    .input-wrap:focus-within i { color: var(--clr-primary); }
    .input-wrap input { width: 100%; padding: 14px 15px 14px 45px !important; border-radius: 14px; transition: all 0.3s ease; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .input-wrap input:focus { background: rgba(255,255,255,0.06); border-color: var(--clr-primary); box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); outline: none; }
    .toggle-pw { position: absolute; right: 15px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; transition: color 0.3s ease; }
    .toggle-pw:hover { color: #fff; }
    .label-link { font-size: 0.85rem; color: var(--clr-primary); float: right; transition: color 0.3s ease; font-weight: 500; }
    .label-link:hover { color: var(--clr-primary-hover); text-decoration: underline; }
    .btn-full { width: 100%; padding: 16px !important; font-size: 1.05rem; border-radius: 14px; font-weight: 700; letter-spacing: 0.5px; margin-top: 10px; transition: all 0.3s ease; }
    .auth-switch { text-align: center; margin-top: 25px; font-size: 0.95rem; color: var(--clr-text-muted); }
    .auth-switch a { color: var(--clr-primary); font-weight: 600; transition: color 0.3s ease; }
    .auth-switch a:hover { color: var(--clr-primary-hover); text-decoration: underline; }
    .auth-divider { position: relative; text-align: center; margin: 30px 0; }
    .auth-divider::before { content: ""; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: rgba(255,255,255,0.08); }
    .auth-divider span { position: relative; background: #080b12; padding: 0 15px; font-size: 0.8rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 1px; }
    .oauth-buttons { display: flex; gap: 15px; }
    .oauth-btn { flex: 1; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 14px; border-radius: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
    .oauth-btn:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); }
</style>
@endpush
