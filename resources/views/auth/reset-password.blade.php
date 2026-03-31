@extends('layouts.nexus')

@section('title', 'Reset Password | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-container">
    <div class="auth-brand">
        <a href="/" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span></a>
        <h2>Set a new password</h2>
        <p>Use a strong password to keep your account secure.</p>
        <div class="brand-features">
            <div class="brand-feat"><i class="fa-solid fa-shield-halved"></i> Security first</div>
            <div class="brand-feat"><i class="fa-solid fa-lock"></i> Encrypted credentials</div>
            <div class="brand-feat"><i class="fa-solid fa-headset"></i> Need help? Contact support</div>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Reset password</h2>
            <p class="auth-sub">Enter your email and choose a new password.</p>

            @if(session('status'))
                <div class="auth-alert auth-alert-success" role="status">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="auth-alert auth-alert-error" role="alert">{{ $errors->first() }}</div>
            @endif

            @if(empty($token) || empty($email))
                <div class="auth-alert auth-alert-error" role="alert">This reset link is invalid or incomplete.</div>
                <div class="auth-actions" style="justify-content:flex-start;">
                    <a href="{{ route('password.request') }}" class="label-link">Request a new link</a>
                </div>
            @else
                <form action="{{ route('password.update') }}" method="POST" id="resetForm" class="auth-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}" />

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" placeholder="you@example.com" required readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                            <button type="button" class="toggle-pw" aria-label="Toggle password visibility" onclick="togglePassword('password', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="help-text">Use at least 8 characters.</div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter your new password" required>
                            <button type="button" class="toggle-pw" aria-label="Toggle password visibility" onclick="togglePassword('password_confirmation', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="update-btn">
                        <i class="fa-solid fa-check mr-2"></i> Update password
                    </button>

                    <div class="auth-actions">
                        <a href="{{ route('login') }}" class="label-link">Back to sign in</a>
                        <a href="{{ route('password.request') }}" class="label-link">Request new link</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (!input || !icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }

    (function() {
        var form = document.getElementById('resetForm');
        var btn = document.getElementById('update-btn');
        if (!form || !btn) return;
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...';
        });
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
    .input-wrap input { width: 100%; padding: 14px 45px 14px 45px !important; border-radius: 14px; transition: all 0.3s ease; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: #fff; }
    .input-wrap input[readonly] { opacity: 0.9; }
    .input-wrap input:focus { background: rgba(255,255,255,0.06); border-color: var(--clr-primary); box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); outline: none; }
    .toggle-pw { position: absolute; right: 15px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; transition: color 0.3s ease; }
    .toggle-pw:hover { color: #fff; }
    .btn-full { width: 100%; padding: 16px !important; font-size: 1.05rem; border-radius: 14px; font-weight: 700; letter-spacing: 0.5px; margin-top: 10px; transition: all 0.3s ease; }
    .label-link { font-size: 0.85rem; color: var(--clr-primary); transition: color 0.3s ease; font-weight: 500; }
    .label-link:hover { color: var(--clr-primary-hover); text-decoration: underline; }
    .auth-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
    .auth-alert { border-radius: 14px; padding: 12px 14px; margin: 0 0 14px; font-size: 0.92rem; }
    .auth-alert-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25); color: rgba(187,247,208,0.95); }
    .auth-alert-error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); color: rgba(254,202,202,0.95); }
    .help-text { margin-top: 8px; font-size: 0.85rem; color: rgba(229,231,235,0.7); }
</style>
@endpush

