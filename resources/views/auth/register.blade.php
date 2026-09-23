@extends('layouts.nexus')

@section('title', 'Create Account | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-wrapper py-4 py-lg-5">
    <div class="container">
        <div class="row align-items-center justify-content-center min-vh-80 g-4 g-lg-5">
            <!-- Left Branding Showcase Column -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="auth-brand-card p-4 p-xl-5 rounded-4 position-relative overflow-hidden">
                    <div class="brand-badge mb-4">
                        <span class="badge-pill-glow"><i class="fa-solid fa-bolt text-warning mr-1"></i> Unified Financial & Identity Platform</span>
                    </div>

                    <a href="/" class="auth-logo-text d-inline-flex align-items-center text-decoration-none mb-4">
                        <img src="{{ \App\Models\SystemSetting::get('site_logo_url', '/images/logo.png') }}" alt="{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}" style="height: 42px; width: 42px; object-fit: contain; margin-right: 12px;">
                        <span class="logo-title">Fuwa<span class="text-primary">.NG</span></span>
                    </a>

                    <h1 class="brand-heading mb-3">Powering your daily digital transactions.</h1>
                    <p class="brand-lead text-white-50 mb-4">Instant bill payments, identity verification, logistics, and agency services — all in one secure platform.</p>

                    <div class="brand-perks d-flex flex-column gap-3 mb-4">
                        <div class="perk-item d-flex align-items-center gap-3">
                            <div class="perk-icon"><i class="fa-solid fa-lock text-success"></i></div>
                            <div>
                                <h6 class="mb-0 text-white font-weight-bold">Bank-Grade Security</h6>
                                <small class="text-white-50">256-bit SSL encryption & 2FA protection</small>
                            </div>
                        </div>
                        <div class="perk-item d-flex align-items-center gap-3">
                            <div class="perk-icon"><i class="fa-solid fa-bolt text-warning"></i></div>
                            <div>
                                <h6 class="mb-0 text-white font-weight-bold">Instant Automated Settlement</h6>
                                <small class="text-white-50">Sub-second execution for all transactions</small>
                            </div>
                        </div>
                        <div class="perk-item d-flex align-items-center gap-3">
                            <div class="perk-icon"><i class="fa-solid fa-headset text-info"></i></div>
                            <div>
                                <h6 class="mb-0 text-white font-weight-bold">24/7 Dedicated Support</h6>
                                <small class="text-white-50">Our team is available round-the-clock</small>
                            </div>
                        </div>
                    </div>

                    <div class="social-proof pt-3 border-top border-secondary-subtle d-flex align-items-center gap-3">
                        <div class="avatar-group d-flex">
                            <span class="avatar-sm rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center font-weight-bold">F</span>
                            <span class="avatar-sm rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center font-weight-bold ms-n2">U</span>
                            <span class="avatar-sm rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center font-weight-bold ms-n2">W</span>
                        </div>
                        <small class="text-white-50">Joined by <strong class="text-white">50,000+</strong> users across Nigeria</small>
                    </div>
                </div>
            </div>

            <!-- Right Registration Form Column -->
            <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                <div class="auth-card-glass p-4 p-sm-5 rounded-4 border border-white-10 shadow-lg position-relative">
                    <div class="auth-card-header text-center text-sm-start mb-4">
                        <div class="d-lg-none mb-3">
                            <a href="/" class="auth-logo-text d-inline-block text-decoration-none">
                                <span class="logo-icon"><i class="fa-solid fa-shield-halved"></i></span>
                                <span class="logo-title">Fuwa<span class="text-primary">.NG</span></span>
                            </a>
                        </div>
                        <h2 class="auth-title text-white font-weight-bold mb-1">Create your Account</h2>
                        <p class="auth-subtitle text-white-50 mb-0">Fill in your information to get started in seconds</p>
                    </div>

                    <form action="{{ url('register') }}" method="POST" id="registerForm" class="auth-form-modern" autocomplete="off" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="alert alert-danger py-2.5 px-3 small rounded-3 mb-3 d-none" id="errorMsgContainer">
                            <i class="fa-solid fa-circle-exclamation mr-2"></i><span id="errorMsg"></span>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2.5 px-3 small rounded-3 mb-3">
                                <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ $errors->first() }}
                            </div>
                        @endif

                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-12 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="fullname" class="form-label-custom">Full Name</label>
                                    <div class="input-wrap-modern">
                                        <i class="fa-regular fa-user input-icon"></i>
                                        <input type="text" id="fullname" name="fullname" class="form-control-modern" placeholder="e.g. John Doe" value="{{ old('fullname') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="col-12 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="username" class="form-label-custom">Username</label>
                                    <div class="input-wrap-modern">
                                        <i class="fa-solid fa-at input-icon"></i>
                                        <input type="text" id="username" name="username" class="form-control-modern" maxlength="20" placeholder="johndoe" value="{{ old('username') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="email" class="form-label-custom">Email Address</label>
                                    <div class="input-wrap-modern">
                                        <i class="fa-regular fa-envelope input-icon"></i>
                                        <input type="email" id="email" name="email" class="form-control-modern" placeholder="name@example.com" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-12 col-sm-7">
                                <div class="form-group mb-0">
                                    <label for="password" class="form-label-custom">Password</label>
                                    <div class="input-wrap-modern">
                                        <i class="fa-solid fa-lock input-icon"></i>
                                        <input type="password" id="password" name="password" class="form-control-modern pr-5" placeholder="Min. 8 characters" required>
                                        <button type="button" class="btn-input-action toggle-password" data-target="password" tabindex="-1" aria-label="Toggle password visibility">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction PIN -->
                            <div class="col-12 col-sm-5">
                                <div class="form-group mb-0">
                                    <label for="transaction_pin" class="form-label-custom d-flex justify-content-between align-items-center">
                                        <span>4-Digit PIN</span>
                                        <i class="fa-solid fa-circle-info text-white-50 small-icon" data-bs-toggle="tooltip" title="Used to authorize quick transactions"></i>
                                    </label>
                                    <div class="input-wrap-modern">
                                        <i class="fa-solid fa-key input-icon"></i>
                                        <input type="password" id="transaction_pin" name="transaction_pin" class="form-control-modern text-center font-monospace tracking-widest" placeholder="••••" value="{{ old('transaction_pin') }}" required maxlength="4" pattern="[0-9]{4}" inputmode="numeric">
                                        <button type="button" class="btn-input-action toggle-password" data-target="transaction_pin" tabindex="-1" aria-label="Toggle PIN visibility">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Referral / Invite Code -->
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="referral_code" class="form-label-custom d-flex justify-content-between">
                                        <span>Invite Code</span>
                                        <span class="text-white-50 fw-normal small">(Optional)</span>
                                    </label>
                                    <div class="input-wrap-modern">
                                        <i class="fa-solid fa-gift input-icon"></i>
                                        <input type="text" id="referral_code" name="referral_code" class="form-control-modern" placeholder="Enter referral code if you have one" value="{{ old('referral_code', request('ref') ?? request('referral_code')) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="reseller_id" value="default">

                        <!-- Submit Button -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-hero-submit w-100" id="register-btn">
                                <span>Create Account</span>
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Divider & Social SSO -->
                    <div class="auth-divider-modern my-4">
                        <span>OR CONTINUE WITH</span>
                    </div>

                    <div class="oauth-buttons-modern">
                        <a class="btn-google-sso w-100 text-decoration-none" href="{{ route('auth.google.redirect') }}">
                            <svg class="google-icon mr-2" width="18" height="18" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                            </svg>
                            <span>Sign up with Google</span>
                        </a>
                    </div>

                    <!-- Footer Link -->
                    <p class="auth-switch-modern text-center mt-4 mb-0">
                        Already have an account? <a href="{{ route('login') }}" class="text-primary font-weight-bold text-decoration-none hover-underline">Sign in instead</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle password & PIN visibility
        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                var icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });

    function registerFormWhenJQueryReady(fn) {
        function tryBind() {
            if (window.jQuery) {
                window.jQuery(fn);
                return true;
            }
            return false;
        }
        if (tryBind()) {
            return;
        }
        var attempts = 0;
        var id = setInterval(function () {
            attempts += 1;
            if (tryBind() || attempts > 200) {
                clearInterval(id);
            }
        }, 50);
    }

    registerFormWhenJQueryReady(function($) {
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
            $('#errorMsgContainer').removeClass('d-none');
            $('html, body').animate({ scrollTop: $('#registerForm').offset().top - 100 }, 300);
        }

        function hideError() {
            $('#errorMsgContainer').addClass('d-none');
            $('#errorMsg').text('');
        }

        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            hideError();

            var form = $(this);
            var btn = $('#register-btn');
            var originalBtnHtml = btn.html();

            // Auto-detect referral from URL if not already set
            const urlParams = new URLSearchParams(window.location.search);
            const ref = urlParams.get('ref') || urlParams.get('referral_code');
            if (ref) {
                const existing = form.find('input[name="referral_code"]');
                if (existing.length) {
                    if (!String(existing.val() || '').trim()) {
                        existing.val(ref);
                    }
                } else {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'referral_code',
                        value: ref
                    }).appendTo(form);
                }
            }

            btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating Account...');
            btn.prop('disabled', true);

            var formEl = form[0];
            var payload = new FormData(formEl);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: payload,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 120000,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    btn.html(originalBtnHtml);
                    btn.prop('disabled', false);
                    try {
                        if (response && response.status === 'success') {
                            const target = normalizeRedirect(response.redirect) || (window.location.origin + '/dashboard');
                            if (window.Swal && typeof window.Swal.fire === 'function') {
                                window.Swal.fire({
                                    title: 'Account Created!',
                                    text: response.message || 'Registration successful. Welcome to Fuwa.NG!',
                                    icon: 'success',
                                    background: '#0a0a0f',
                                    color: '#fff',
                                    confirmButtonColor: '#3b82f6'
                                }).then(function () {
                                    window.location.assign(target);
                                });
                            } else {
                                window.location.assign(target);
                            }
                        } else {
                            showError((response && response.message) ? response.message : 'Registration could not be completed.');
                        }
                    } catch (e) {
                        showError('Something went wrong. Please try again.');
                    }
                },
                error: function(xhr) {
                    var message = 'An error occurred. Please try again.';
                    try {
                        var body = xhr.responseJSON;
                        if (!body && xhr.responseText) {
                            try {
                                body = JSON.parse(xhr.responseText);
                            } catch (parseErr) {
                                body = null;
                            }
                        }

                        if (xhr.status === 422 && body && body.errors) {
                            var flat = Object.values(body.errors).flat();
                            if (flat.length) {
                                message = flat[0];
                            }
                        } else if (xhr.status === 413) {
                            message = 'Upload is too large. Please use a smaller file and try again.';
                        } else if (xhr.status === 419) {
                            message = 'Your session expired. Refresh this page and try again.';
                        } else if (body && body.message) {
                            message = body.message;
                        } else if (xhr.status === 0 || xhr.statusText === 'timeout') {
                            message = 'Request timed out. Check your connection and try again.';
                        } else if (xhr.status >= 500) {
                            message = 'Server error while creating your account. Please try again in a moment.';
                        }
                    } catch (e) {
                        /* keep default message */
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
    .auth-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        background: radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 85% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
    }

    .auth-brand-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    .badge-pill-glow {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 50rem;
        background: rgba(59, 130, 246, 0.12);
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: #93c5fd;
        font-size: 0.825rem;
        font-weight: 600;
    }

    .auth-logo-text .logo-icon {
        font-size: 1.5rem;
        color: var(--po-primary, #3b82f6);
        margin-right: 8px;
    }

    .auth-logo-text .logo-title {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #fff;
    }

    .brand-heading {
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.5px;
        color: #fff;
    }

    .perk-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
        border: 2px solid #0f172a;
    }

    .auth-card-glass {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6);
    }

    .form-label-custom {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .input-wrap-modern {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrap-modern .input-icon {
        position: absolute;
        left: 16px;
        color: rgba(255, 255, 255, 0.4);
        font-size: 1rem;
        pointer-events: none;
        transition: color 0.25s ease;
    }

    .input-wrap-modern:focus-within .input-icon {
        color: #3b82f6;
    }

    .form-control-modern {
        width: 100%;
        height: 50px;
        padding: 10px 16px 10px 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #ffffff;
        font-size: 0.95rem;
        transition: all 0.25s ease;
    }

    .form-control-modern:focus {
        background: rgba(255, 255, 255, 0.07);
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        outline: none;
        color: #fff;
    }

    .form-control-modern::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .btn-input-action {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.4);
        padding: 8px;
        cursor: pointer;
        transition: color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-input-action:hover {
        color: #ffffff;
    }

    .btn-hero-submit {
        height: 52px;
        border-radius: 14px;
        font-size: 1.05rem;
        font-weight: 700;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.5);
        transition: all 0.3s ease;
    }

    .btn-hero-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px -5px rgba(59, 130, 246, 0.6);
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
    }

    .auth-divider-modern {
        position: relative;
        text-align: center;
    }

    .auth-divider-modern::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
    }

    .auth-divider-modern span {
        position: relative;
        background: #0f172a;
        padding: 0 14px;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.4);
        font-weight: 700;
        letter-spacing: 1px;
    }

    .btn-google-sso {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.25s ease;
    }

    .btn-google-sso:hover {
        background: rgba(255, 255, 255, 0.09);
        border-color: rgba(255, 255, 255, 0.25);
        color: #fff;
        transform: translateY(-1px);
    }

    .hover-underline:hover {
        text-decoration: underline !important;
    }

    .tracking-widest {
        letter-spacing: 0.25em;
    }
</style>
@endpush
