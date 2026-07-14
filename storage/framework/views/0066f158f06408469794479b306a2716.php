<?php $__env->startSection('title', 'Logistics Login'); ?>

<?php $__env->startSection('content'); ?>
<div class="row align-items-center" style="min-height: calc(100vh - 180px);">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--po-primary);">
                FuwaPost Logistics
            </span>
        </div>
        <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem; line-height: 1.1;">
            Welcome back to <span style="color:var(--po-primary)">Logistics</span>
        </h1>
        <p class="text-white-50 mb-4" style="max-width: 560px;">
            Sign in to access your logistics dashboard, book shipments, and track deliveries.
        </p>
        <img src="<?php echo e(asset('images/hero_visual.png')); ?>" alt="Logistics illustration" class="img-fluid" style="max-height: 260px; opacity: 0.95;">
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="font-weight-bold mb-0">Login</h4>
                <a href="<?php echo e(route('logistics.register')); ?>" class="text-white-50 text-decoration-none small">
                    New here?
                    <span style="color:var(--po-primary); font-weight: 600;">Create account</span>
                </a>
            </div>

            <?php if(session('error')): ?>
                <div class="alert alert-danger border-0 mb-4" style="background: rgba(220, 38, 38, 0.15); color: #fff;">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('logistics.login')); ?>" id="loginForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="service" value="logistics">

                <div class="form-group">
                    <label for="email" class="text-white-50 small">Email address</label>
                    <input id="email" name="email" type="email" class="form-control tracking-input" required autofocus value="<?php echo e(old('email')); ?>">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="password" class="text-white-50 small d-flex justify-content-between">
                        <span>Password</span>
                        <a href="<?php echo e(route('password.request')); ?>" class="text-decoration-none" style="color: var(--po-primary);">Forgot?</a>
                    </label>
                    <input id="password" name="password" type="password" class="form-control tracking-input" required>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="btn btn-po-primary btn-block">
                    <i class="fa fa-arrow-right-to-bracket mr-2"></i> Sign in
                </button>
            </form>

            <div class="mt-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1" style="height: 1px; background: rgba(255,255,255,0.08);"></div>
                    <div class="px-3 text-white-50 small">or</div>
                    <div class="flex-grow-1" style="height: 1px; background: rgba(255,255,255,0.08);"></div>
                </div>

                <div class="mt-3">
                    <div class="text-white-50 small mb-2">Already have a Fuwa.NG account? Login with your account</div>
                    <?php if(Auth::check()): ?>
                        <form method="POST" action="<?php echo e(route('logistics.sso')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-light btn-block" style="border-radius: 12px;">
                                <i class="fa fa-shield-halved mr-2" style="color: var(--po-primary);"></i> Continue with Fuwa.ng
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>?service=logistics&redirect=<?php echo e(urlencode(request()->get('redirect', '/logistics/dashboard'))); ?>"
                           class="btn btn-outline-light btn-block" style="border-radius: 12px;">
                            <i class="fa fa-shield-halved mr-2" style="color: var(--po-primary);"></i> Continue with Fuwa.ng
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo e(route('auth.google.redirect', ['service' => 'logistics', 'redirect' => request()->get('redirect', '/logistics/dashboard')])); ?>"
                       class="btn btn-outline-light btn-block mt-2" style="border-radius: 12px;">
                        <i class="fa-brands fa-google mr-2" style="color: var(--po-primary);"></i> Continue with Google
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.postoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/logistics/auth/login.blade.php ENDPATH**/ ?>