@extends('layouts.nexus')

@section('title', 'Sign In | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-wrapper">
    <!-- Ambient Background Lighting -->
    <div class="auth-glow-blob blob-1"></div>
    <div class="auth-glow-blob blob-2"></div>

    <div class="auth-container">
        <!-- Branding Side -->
        <div class="auth-brand">
            <div class="status-pill">
                <span class="status-dot"></span> All Systems Operational
            </div>

            <a href="/" class="auth-logo">
                <div class="logo-icon-bg">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <span class="logo-text">Fuwa<span class="logo-accent">.NG</span></span>
            </a>

            <h1 class="brand-headline">Nigeria's Most Trusted Digital Services Hub</h1>
            <p class="brand-subtext">Unified verification, VTU billing, agency banking, notary services, and digital auctions — crafted for speed and security.</p>

            <div class="brand-features">
                <div class="brand-feat-card">
                    <div class="feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="feat-info">
                        <strong>Bank-Grade Security</strong>
                        <span>256-bit encryption & multi-layered protection</span>
                    </div>
                </div>

                <div class="brand-feat-card">
                    <div class="feat-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="feat-info">
                        <strong>Real-Time Settlement</strong>
                        <span>Sub-second automated execution for all operations</span>
                    </div>
                </div>

                <div class="brand-feat-card">
                    <div class="feat-icon"><i class="fa-solid fa-headset"></i></div>
                    <div class="feat-info">
                        <strong>24/7 Priority Support</strong>
                        <span>Dedicated customer assistance available anytime</span>
                    </div>
                </div>
            </div>

            <div class="brand-stats-row">
                <div class="stat-item">
                    <span class="stat-val">50K+</span>
                    <span class="stat-lbl">Active Users</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-val">99.9%</span>
                    <span class="stat-lbl">API Uptime</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-val">&lt; 1s</span>
                    <span class="stat-lbl">Response Time</span>
                </div>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Welcome back 👋</h2>
                    <p class="auth-sub">Sign in to access your digital financial workspace</p>
                </div>

                <form action="{{ url('login') }}" method="POST" id="loginForm" class="auth-form">
                    @csrf
                    @if(request('service'))
                        <input type="hidden" name="service" value="{{ request('service') }}">
                    @endif

                    <div id="errorContainer" class="alert-banner alert-banner-danger {{ $errors->any() ? '' : 'd-none' }}">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>
                        <span id="errorMsg">{{ $errors->first() ?? '' }}</span>
                    </div>

                    @if(session('status'))
                        <div id="statusContainer" class="alert-banner alert-banner-success">
                            <i class="fa-solid fa-circle-check mr-2"></i>
                            <span id="statusMsg">{{ session('status') }}</span>
                        </div>
                    @else
                        <div id="statusContainer" class="alert-banner alert-banner-success d-none">
                            <i class="fa-solid fa-circle-check mr-2"></i>
                            <span id="statusMsg"></span>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="label-row">
                            <label for="password" class="form-label mb-0">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="label-link">Forgot Password?</a>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••••••" required class="form-input">
                            <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="custom-checkbox-container">
                            <input type="checkbox" id="remember" name="remember">
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Keep me signed in</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="login-btn">
                        <span>Sign In</span>
                        <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>or continue with</span>
                </div>

                <div class="oauth-buttons">
                    <a class="oauth-btn" href="{{ route('auth.google.redirect') }}">
                        <svg class="google-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px">
                            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                        </svg>
                        <span>Sign in with Google</span>
                    </a>
                </div>

                <p class="auth-switch">
                    Don't have an account? <a href="{{ route('register') }}">Create one free</a>
                </p>

                <div class="mt-3 pt-3 border-top border-secondary text-center">
                    <span class="text-white-50 small">NIN Enrollment Agent?</span>
                    <a href="{{ route('agent.register') }}?type=existing" class="text-primary fw-bold small ms-1 text-decoration-none">
                        <i class="fa-solid fa-id-card-clip me-1"></i>Sign in / Register as Agent
                    </a>
                </div>
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

        function showError(msg) {
            $('#errorMsg').text(msg);
            $('#errorContainer').removeClass('d-none');
        }

        function hideError() {
            $('#errorMsg').text('');
            $('#errorContainer').addClass('d-none');
        }

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            hideError();

            var form = $(this);
            var btn = $('#login-btn');
            var originalBtnHtml = btn.html();

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
                    if ((response.status === 'success' || response.status === '2fa_required') && response.redirect) {
                        const target = normalizeRedirect(response.redirect) || (window.location.origin + '/dashboard');
                        try {
                            window.location.assign(target);
                        } catch (e) {
                            window.location.href = target;
                        }
                    } else {
                        showError(response.message || 'Authentication failed. Please check your credentials.');
                        btn.html(originalBtnHtml);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr, textStatus) {
                    var message = 'An error occurred. Please try again.';
                    if (textStatus === 'timeout') {
                        message = 'Login request timed out. Please check your internet connection and try again.';
                    }
                    if (xhr.status === 419) {
                        message = 'Session expired. Refresh the page and try again.';
                    }
                    if (xhr.status === 422) {
                        message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : message;
                    }
                    showError(message);
                    btn.html(originalBtnHtml);
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Wrapper & Ambient Background */
    .auth-wrapper {
        position: relative;
        min-height: calc(100vh - 140px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        overflow: hidden;
    }

    .auth-glow-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.25;
        pointer-events: none;
        z-index: 0;
        animation: pulseGlow 10s infinite alternate ease-in-out;
    }

    .blob-1 {
        width: 450px;
        height: 450px;
        top: -50px;
        left: -50px;
        background: radial-gradient(circle, var(--clr-primary, #3b82f6) 0%, rgba(59, 130, 246, 0) 70%);
    }

    .blob-2 {
        width: 400px;
        height: 400px;
        bottom: -50px;
        right: -50px;
        background: radial-gradient(circle, #8b5cf6 0%, rgba(139, 92, 246, 0) 70%);
        animation-delay: -5s;
    }

    @keyframes pulseGlow {
        0% { transform: scale(1) translate(0, 0); opacity: 0.2; }
        50% { transform: scale(1.15) translate(20px, -20px); opacity: 0.3; }
        100% { transform: scale(1) translate(0, 0); opacity: 0.2; }
    }

    /* Container Layout */
    .auth-container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1140px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 60px;
        margin: 0 auto;
    }

    /* Branding Side */
    .auth-brand {
        flex: 1.1;
        display: none;
        padding-right: 20px;
    }

    @media (min-width: 992px) {
        .auth-brand {
            display: block;
        }
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #34d399;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 30px;
        margin-bottom: 24px;
        letter-spacing: 0.3px;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 10px #10b981;
        animation: blinkDot 2s infinite ease-in-out;
    }

    @keyframes blinkDot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .auth-logo {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none !important;
        margin-bottom: 20px;
    }

    .logo-icon-bg {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, var(--clr-primary, #3b82f6), #2563eb);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.25rem;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
    }

    .logo-text {
        font-size: 1.85rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.5px;
    }

    .logo-accent {
        color: var(--clr-primary, #3b82f6);
    }

    .brand-headline {
        font-size: 2.35rem;
        font-weight: 800;
        line-height: 1.25;
        color: #f8fafc;
        margin-bottom: 16px;
        letter-spacing: -0.5px;
        background: linear-gradient(180deg, #ffffff 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .brand-subtext {
        font-size: 1.05rem;
        color: #94a3b8;
        line-height: 1.6;
        margin-bottom: 35px;
    }

    .brand-features {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 40px;
    }

    .brand-feat-card {
        display: flex;
        align-items: center;
        gap: 16px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        padding: 14px 18px;
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    .brand-feat-card:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.1);
        transform: translateX(4px);
    }

    .feat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(59, 130, 246, 0.12);
        color: var(--clr-primary, #3b82f6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .feat-info {
        display: flex;
        flex-direction: column;
    }

    .feat-info strong {
        color: #f1f5f9;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .feat-info span {
        color: #64748b;
        font-size: 0.83rem;
    }

    .brand-stats-row {
        display: flex;
        align-items: center;
        gap: 24px;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 18px 24px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
    }

    .stat-item {
        display: flex;
        flex-direction: column;
    }

    .stat-val {
        font-size: 1.35rem;
        font-weight: 800;
        color: #38bdf8;
    }

    .stat-lbl {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .stat-divider {
        width: 1px;
        height: 30px;
        background: rgba(255, 255, 255, 0.1);
    }

    /* Form Side & Auth Card */
    .auth-form-side {
        flex: 1;
        max-width: 460px;
        width: 100%;
        margin: 0 auto;
    }

    .auth-card {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 40px 36px;
        border-radius: 24px;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    .auth-card:hover {
        border-color: rgba(255, 255, 255, 0.16);
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7), 0 0 30px rgba(59, 130, 246, 0.1);
    }

    .auth-header {
        margin-bottom: 28px;
    }

    .auth-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 6px;
        letter-spacing: -0.3px;
    }

    .auth-sub {
        color: #94a3b8;
        font-size: 0.92rem;
        margin: 0;
    }

    /* Form Alert Banners */
    .alert-banner {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.88rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        line-height: 1.4;
    }

    .alert-banner-danger {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #fca5a5;
    }

    .alert-banner-success {
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #6ee7b7;
    }

    /* Form Fields */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 8px;
    }

    .label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .label-link {
        font-size: 0.84rem;
        color: var(--clr-primary, #3b82f6);
        font-weight: 600;
        text-decoration: none !important;
        transition: color 0.2s ease;
    }

    .label-link:hover {
        color: #60a5fa;
        text-decoration: underline !important;
    }

    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 16px;
        color: #64748b;
        font-size: 1rem;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .form-input {
        width: 100%;
        padding: 13px 16px 13px 46px !important;
        background: rgba(15, 23, 42, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 14px !important;
        color: #f8fafc !important;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-input::placeholder {
        color: #475569;
    }

    .form-input:focus {
        background: rgba(15, 23, 42, 0.85) !important;
        border-color: var(--clr-primary, #3b82f6) !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
        outline: none !important;
    }

    .input-wrap:focus-within .input-icon {
        color: var(--clr-primary, #3b82f6);
    }

    .toggle-pw {
        position: absolute;
        right: 14px;
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 4px 8px;
        font-size: 1rem;
        transition: color 0.2s ease;
    }

    .toggle-pw:hover {
        color: #e2e8f0;
    }

    /* Custom Checkbox */
    .form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .custom-checkbox-container {
        display: inline-flex;
        align-items: center;
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        font-size: 0.88rem;
        color: #94a3b8;
        user-select: none;
    }

    .custom-checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        position: absolute;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
        height: 18px;
        width: 18px;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 5px;
        transition: all 0.2s ease;
    }

    .custom-checkbox-container:hover input ~ .checkmark {
        border-color: rgba(255, 255, 255, 0.3);
    }

    .custom-checkbox-container input:checked ~ .checkmark {
        background-color: var(--clr-primary, #3b82f6);
        border-color: var(--clr-primary, #3b82f6);
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
        left: 6px;
        top: 2px;
        width: 4px;
        height: 9px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .custom-checkbox-container input:checked ~ .checkmark:after {
        display: block;
    }

    .checkbox-label {
        font-weight: 500;
    }

    /* Action Button */
    .btn-full {
        width: 100%;
        padding: 14px 20px !important;
        font-size: 1rem;
        border-radius: 14px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--clr-primary, #3b82f6) 0%, #2563eb 100%) !important;
        border: none !important;
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
    }

    .btn-full:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(59, 130, 246, 0.45);
        background: linear-gradient(135deg, #4f46e5 0%, var(--clr-primary, #3b82f6) 100%) !important;
    }

    .btn-full:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Auth Divider */
    .auth-divider {
        position: relative;
        text-align: center;
        margin: 28px 0;
    }

    .auth-divider::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
    }

    .auth-divider span {
        position: relative;
        background: rgba(15, 23, 42, 0.95);
        padding: 0 16px;
        font-size: 0.78rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    /* OAuth Buttons */
    .oauth-buttons {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }

    .oauth-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #f1f5f9;
        padding: 12px 16px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }

    .oauth-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }

    /* Auth Switch */
    .auth-switch {
        text-align: center;
        margin-top: 20px;
        font-size: 0.92rem;
        color: #94a3b8;
    }

    .auth-switch a {
        color: var(--clr-primary, #3b82f6);
        font-weight: 600;
        text-decoration: none !important;
        transition: color 0.2s ease;
    }

    .auth-switch a:hover {
        color: #60a5fa;
        text-decoration: underline !important;
    }

    /* Responsive Polish */
    @media (max-width: 576px) {
        .auth-card {
            padding: 28px 20px;
            border-radius: 20px;
        }

        .auth-wrapper {
            padding: 20px 12px;
        }
    }
</style>
@endpush
