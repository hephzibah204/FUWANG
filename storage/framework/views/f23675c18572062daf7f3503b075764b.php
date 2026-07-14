<?php $__env->startSection('title', 'Page Not Found - ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex align-items-center justify-content-center min-vh-100" style="background-color: #f8f9fc;">
    <div class="text-center px-4">
        <div class="mb-4">
            <!-- You can replace this with your actual logo -->
            <h1 class="display-1 font-weight-bold text-primary" style="font-size: 8rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">404</h1>
        </div>
        <h2 class="h3 mb-3 text-dark">Oops! Page Not Found</h2>
        <p class="text-muted mb-5 lead" style="max-width: 500px; margin: 0 auto;">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <a href="<?php echo e(url('/')); ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm transition-all" style="transition: all 0.3s ease;">
            <i class="fa fa-home mr-2"></i> Return Home
        </a>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .transition-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/errors/404.blade.php ENDPATH**/ ?>