<?php $__env->startSection('title', 'Create Account | ' . config('app.name')); ?>
<?php $__env->startSection('public_wrapper_class', 'none'); ?>

<?php $__env->startSection('content'); ?>
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

            <form action="<?php echo e(url('register')); ?>" method="POST" id="registerForm" class="auth-form" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <p class="text-danger small text-center" id="errorMsg"></p>
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger py-2 px-3 small" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.35); color: #ffb3bd;">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="fullname" name="fullname" placeholder="John Doe" value="<?php echo e(old('fullname')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-at"></i>
                        <input type="text" id="username" name="username" maxlength="20" placeholder="johndoe" value="<?php echo e(old('username')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo e(old('email')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="referral_code">Invite Code (Optional)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-gift"></i>
                        <input type="text" id="referral_code" name="referral_code" placeholder="Enter invite code" value="<?php echo e(old('referral_code', request('ref') ?? request('referral_code'))); ?>">
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
                        <input type="tel" id="transaction_pin" name="transaction_pin" placeholder="1234" value="<?php echo e(old('transaction_pin')); ?>" required maxlength="4">
                    </div>
                </div>

                <input type="hidden" name="reseller_id" value="default">

                <button type="submit" class="btn btn-primary btn-full" id="register-btn">
                    <i class="fa-solid fa-user-plus mr-2"></i> Register Now
                </button>
            </form>

            <p class="auth-switch">Already have an account? <a href="<?php echo e(route('login')); ?>">Sign In</a></p>
            <div class="auth-divider"><span>or continue with</span></div>
            <div class="oauth-buttons">
                <a class="oauth-btn text-decoration-none" href="<?php echo e(route('auth.google.redirect')); ?>">
                    <i class="fa-brands fa-google mr-2"></i> Google
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
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

        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#register-btn');
            var originalBtnText = btn.html();

            // Auto-detect referral from URL if not already set
            const urlParams = new URLSearchParams(window.location.search);
            const ref = urlParams.get('ref');
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

            btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...');
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
                    btn.html(originalBtnText);
                    btn.prop('disabled', false);
                    try {
                        if (response && response.status === 'success') {
                            const target = normalizeRedirect(response.redirect) || (window.location.origin + '/dashboard');
                            if (window.Swal && typeof window.Swal.fire === 'function') {
                                window.Swal.fire({
                                    title: 'Success',
                                    text: response.message || 'Registration successful.',
                                    icon: 'success',
                                    background: '#0a0a0f',
                                    color: '#fff',
                                    confirmButtonColor: '#3b82f6'
                                }).then(function () {
                                    try {
                                        window.location.assign(target);
                                    } catch (e) {
                                        window.location.href = target;
                                    }
                                });
                            } else {
                                try {
                                    window.location.assign(target);
                                } catch (e) {
                                    window.location.href = target;
                                }
                            }
                        } else {
                            $('#errorMsg').text((response && response.message) ? response.message : 'Registration could not be completed.');
                        }
                    } catch (e) {
                        $('#errorMsg').text('Something went wrong. Please try again.');
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
                    $('#errorMsg').text(message);
                    btn.html(originalBtnText);
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
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
    .input-wrap input,
    .input-wrap select.form-select-auth { width: 100%; padding: 14px 15px 14px 45px !important; border-radius: 14px; transition: all 0.3s ease; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: #fff; appearance: auto; -webkit-appearance: menulist; }
    .input-wrap select.form-select-auth:disabled { opacity: 0.5; cursor: not-allowed; }
    .input-wrap input:focus,
    .input-wrap select.form-select-auth:focus { background: rgba(255,255,255,0.06); border-color: var(--clr-primary); box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); outline: none; }
    .btn-full { width: 100%; padding: 16px !important; font-size: 1.05rem; border-radius: 14px; font-weight: 700; letter-spacing: 0.5px; margin-top: 15px; transition: all 0.3s ease; }
    .auth-switch { text-align: center; margin-top: 25px; font-size: 0.95rem; color: var(--clr-text-muted); }
    .auth-switch a { color: var(--clr-primary); font-weight: 600; transition: color 0.3s ease; }
    .auth-switch a:hover { color: var(--clr-primary-hover); text-decoration: underline; }
    .auth-divider { position: relative; text-align: center; margin: 20px 0; }
    .auth-divider::before { content: ""; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: rgba(255,255,255,0.08); }
    .auth-divider span { position: relative; background: #080b12; padding: 0 15px; font-size: 0.8rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 1px; }
    .oauth-buttons { display: flex; gap: 15px; }
    .oauth-btn { flex: 1; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 14px; border-radius: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
    .oauth-btn:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); color: #fff; }
    .form-check { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
    .auth-form .agent-fields { display: flex; flex-direction: column; gap: 1.25rem; }
    .auth-file-input {
        width: 100%;
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.08);
        color: var(--clr-text-muted);
        font-size: 0.9rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/auth/register.blade.php ENDPATH**/ ?>