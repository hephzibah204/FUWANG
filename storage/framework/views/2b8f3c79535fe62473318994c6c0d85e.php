<?php $__env->startSection('title', 'Explore Services | ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row align-items-end mb-4">
        <div class="col-lg-8">
            <h1 class="text-white font-weight-bold mb-2">Explore Services</h1>
            <p class="text-white-50 mb-0">Public service pages with previews. Actions unlock after login.</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
            <a href="<?php echo e(route('register')); ?>" class="btn btn-primary mr-2" data-cta="explore_primary">Create account</a>
            <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-glass" data-cta="explore_login">Sign in</a>
        </div>
    </div>

    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $key = $cat['key'];
            $items = $byCategory->get($key, collect());
        ?>
        <?php if($items->count()): ?>
            <div class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="h5 text-white font-weight-bold mb-1"><?php echo e($cat['label']); ?></h2>
                        <div class="text-white-50 small"><?php echo e($cat['description']); ?></div>
                    </div>
                </div>
                <div class="row">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <a href="<?php echo e(url('/explore/' . $svc['slug'])); ?>" class="text-decoration-none d-block h-100" data-cta="service_card">
                                <div class="p-4 h-100 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10);">
                                            <i class="<?php echo e($svc['icon'] ?? 'fa-solid fa-layer-group'); ?> text-white"></i>
                                        </div>
                                        <div>
                                            <div class="text-white font-weight-bold"><?php echo e($svc['title']); ?></div>
                                            <div class="text-white-50 small"><?php echo e($svc['tagline']); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-white-50 small"><?php echo e($svc['summary']); ?></div>
                                    <div class="mt-3 d-flex flex-wrap" style="gap: 8px;">
                                        <?php $__currentLoopData = ($svc['highlights'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="px-2 py-1 rounded-pill small" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10); color: rgba(255,255,255,0.75);"><?php echo e($h); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="mt-4 text-white font-weight-bold">View details <i class="fa-solid fa-arrow-right ml-1"></i></div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/public/services/index.blade.php ENDPATH**/ ?>