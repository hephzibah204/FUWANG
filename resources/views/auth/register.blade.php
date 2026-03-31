@extends('layouts.nexus')

@section('title', 'Create Account | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="auth-container">
    <!-- Branding Side -->
    <div class="auth-brand">
        <a href="/" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>.NG</span></a>
        <h2>Join Fuwa.NG for Digital Payments</h2>
        <p>Get instant access to a unified ecosystem of verification and financial services.</p>
        <div class="brand-features">
            <div class="brand-feat"><i class="fa-solid fa-shield-halved"></i> Bank-grade encryption</div>
            <div class="brand-feat"><i class="fa-solid fa-bolt"></i> Real-time processing</div>
            <div class="brand-feat"><i class="fa-solid fa-headset"></i> 24/7 support</div>
        </div>
    </div>

    <!-- Form Side -->
    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="auth-sub">Enter your details to get started</p>

            <form action="{{ url('register') }}" method="POST" id="registerForm" class="auth-form">
                @csrf
                <p class="text-danger small text-center" id="errorMsg"></p>

                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-at"></i>
                        <input type="text" id="username" name="username" maxlength="20" placeholder="johndoe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="transaction_pin">Transaction PIN (4 Digits)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-key"></i>
                        <input type="tel" id="transaction_pin" name="transaction_pin" placeholder="1234" required maxlength="4">
                    </div>
                </div>

                <input type="hidden" name="reseller_id" value="default">

                <button type="submit" class="btn btn-primary btn-full" id="register-btn">
                    <i class="fa-solid fa-user-plus mr-2"></i> Register Now
                </button>
            </form>

            <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#register-btn');
            var originalBtnText = btn.html();

            // Auto-detect referral from URL if not already set
            const urlParams = new URLSearchParams(window.location.search);
            const ref = urlParams.get('ref');
            if (ref && !form.find('input[name="referral_code"]').length) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'referral_code',
                    value: ref
                }).appendTo(form);
            }

            btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...');
            btn.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: { 'Accept': 'application/json' },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            background: '#0a0a0f',
                            color: '#fff',
                            confirmButtonColor: '#3b82f6'
                        }).then(() => {
                            const target = normalizeRedirect(response.redirect) || (window.location.origin + '/dashboard');
                            try {
                                window.location.assign(target);
                            } catch (e) {
                                window.location.href = target;
                            }
                        });
                    } else {
                        $('#errorMsg').text(response.message);
                        btn.html(originalBtnText);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    var message = 'An error occurred. Please try again.';
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat()[0];
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
    .btn-full { width: 100%; padding: 16px !important; font-size: 1.05rem; border-radius: 14px; font-weight: 700; letter-spacing: 0.5px; margin-top: 15px; transition: all 0.3s ease; }
    .auth-switch { text-align: center; margin-top: 25px; font-size: 0.95rem; color: var(--clr-text-muted); }
    .auth-switch a { color: var(--clr-primary); font-weight: 600; transition: color 0.3s ease; }
    .auth-switch a:hover { color: var(--clr-primary-hover); text-decoration: underline; }
</style>
@endpush
