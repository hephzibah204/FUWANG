@extends('layouts.nexus')

@section('title', 'Forgot Password | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-container">
    <div class="auth-brand">
        <a href="/" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span></a>
        <h2>Recover access to your account</h2>
        <p>Enter your email and we’ll send a secure password reset link.</p>
        <div class="brand-features">
            <div class="brand-feat"><i class="fa-solid fa-shield-halved"></i> Secure recovery</div>
            <div class="brand-feat"><i class="fa-solid fa-envelope"></i> Email delivery</div>
            <div class="brand-feat"><i class="fa-solid fa-headset"></i> 24/7 support</div>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Forgot password</h2>
            <p class="auth-sub">We’ll email you a reset link if an account exists.</p>

            @if(session('status'))
                <div class="auth-alert auth-alert-success" role="status">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="auth-alert auth-alert-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" id="forgotForm" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="send-btn">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Send reset link
                </button>

                <div class="auth-actions">
                    <a href="{{ route('login') }}" class="label-link">Back to sign in</a>
                    <button type="button" class="label-link" id="resend-btn" style="background:none;border:none;padding:0;" disabled>Resend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        var btn = document.getElementById('resend-btn');
        var sendBtn = document.getElementById('send-btn');
        var form = document.getElementById('forgotForm');
        if (!btn || !form || !sendBtn) return;

        var cooldownSeconds = 30;
        var key = 'forgot_pw_cooldown_until';

        function nowMs() { return new Date().getTime(); }
        function setCooldown(seconds) {
            var until = nowMs() + (seconds * 1000);
            try { localStorage.setItem(key, String(until)); } catch (e) {}
            render();
        }
        function getUntil() {
            try {
                var raw = localStorage.getItem(key);
                var parsed = raw ? parseInt(raw, 10) : 0;
                return isNaN(parsed) ? 0 : parsed;
            } catch (e) {
                return 0;
            }
        }
        function render() {
            var until = getUntil();
            var remaining = Math.max(0, Math.ceil((until - nowMs()) / 1000));
            if (remaining > 0) {
                btn.disabled = true;
                btn.textContent = 'Resend in ' + remaining + 's';
            } else {
                btn.disabled = false;
                btn.textContent = 'Resend';
            }
        }

        btn.addEventListener('click', function() {
            if (btn.disabled) return;
            setCooldown(cooldownSeconds);
            form.submit();
        });

        form.addEventListener('submit', function() {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Sending...';
            setCooldown(cooldownSeconds);
        });

        render();
        setInterval(render, 500);
    })();
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
    .auth-card { background: rgba(17, 24, 39, 0.7); border: 1px solid rgba(255,255,255,0.1); padding: 45px; border-radius: 28px; backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transition: all 0.4s ease; }
    .auth-card h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 8px; color: #fff; }
    .auth-sub { color: var(--clr-text-muted); margin-bottom: 18px; font-size: 0.95rem; }
    .input-wrap { position: relative; display: flex; align-items: center; transition: all 0.3s ease; }
    .input-wrap i { position: absolute; left: 18px; color: var(--clr-text-muted); transition: color 0.3s ease; }
    .input-wrap:focus-within i { color: var(--clr-primary); }
    .input-wrap input { width: 100%; padding: 14px 15px 14px 45px !important; border-radius: 14px; transition: all 0.3s ease; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: #fff; }
    .input-wrap input:focus { background: rgba(255,255,255,0.06); border-color: var(--clr-primary); box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); outline: none; }
    .btn-full { width: 100%; padding: 16px !important; font-size: 1.05rem; border-radius: 14px; font-weight: 700; letter-spacing: 0.5px; margin-top: 10px; transition: all 0.3s ease; }
    .label-link { font-size: 0.85rem; color: var(--clr-primary); transition: color 0.3s ease; font-weight: 500; }
    .label-link:hover { color: var(--clr-primary-hover); text-decoration: underline; }
    .auth-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
    .auth-alert { border-radius: 14px; padding: 12px 14px; margin: 0 0 14px; font-size: 0.92rem; }
    .auth-alert-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25); color: rgba(187,247,208,0.95); }
    .auth-alert-error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); color: rgba(254,202,202,0.95); }
    #resend-btn[disabled] { opacity: 0.6; cursor: not-allowed; text-decoration: none; }
</style>
@endpush

