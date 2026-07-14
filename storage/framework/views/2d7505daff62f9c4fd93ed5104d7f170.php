<?php $__env->startSection('title', 'Verify email | ' . config('app.name')); ?>
<?php $__env->startSection('public_wrapper_class', 'none'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-container">
    <div class="auth-brand">
        <a href="/" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span></a>
        <h2>Confirm your email</h2>
        <p>We sent a verification link to <strong class="text-white"><?php echo e(Auth::user()->email); ?></strong>. Click the link in that email to unlock your dashboard and full account access.</p>
        <div class="brand-features">
            <div class="brand-feat"><i class="fa-solid fa-envelope-circle-check"></i> Check inbox &amp; spam</div>
            <div class="brand-feat"><i class="fa-solid fa-link"></i> Link expires for your security</div>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h2>Didn’t get the email?</h2>
            <p class="auth-sub">Request a new verification link below.</p>

            <?php if(session('status')): ?>
                <p class="text-success small text-center mb-3"><?php echo e(session('status')); ?></p>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('verification.send')); ?>" class="auth-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Resend verification email
                </button>
            </form>

            <p class="text-white-50 small text-center mt-4 mb-0">
                Need to use a different email?
                <a href="<?php echo e(route('logout')); ?>" class="text-primary font-weight-bold" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                and register again, or contact support to update your address.
            </p>
            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                <?php echo csrf_field(); ?>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/auth/verify-email.blade.php ENDPATH**/ ?>