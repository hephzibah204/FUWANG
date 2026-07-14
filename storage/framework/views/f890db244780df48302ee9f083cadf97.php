<?php $__env->startSection('title', 'Services | Admin ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Services</h1>
            <p class="text-muted mb-0">Control service availability and integrations without mixing user flows.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <a class="btn btn-outline-primary mr-2" href="<?php echo e(route('admin.custom_apis.index')); ?>">
                <i class="fa-solid fa-code-branch mr-2"></i> Custom APIs
            </a>
            <a class="btn btn-primary" href="<?php echo e(route('admin.settings.index', ['tab' => 'tab-features'])); ?>">
                <i class="fa fa-toggle-on mr-2"></i> Service Toggles
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Feature Key</th>
                        <th>Status</th>
                        <th>Providers</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $catalog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $featureKey = $item['feature_key'];
                            $toggle = $featureKey ? ($featureToggles[$featureKey] ?? null) : null;
                            $isActive = $toggle ? (bool) $toggle->is_active : true;
                            $offlineMessage = $toggle?->offline_message;
                            $serviceType = $item['custom_api_service_type'];
                            $stats = $serviceType ? ($customApiStats[$serviceType] ?? null) : null;
                        ?>
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-white"><?php echo e($item['name']); ?></div>
                                <div class="text-muted small"><?php echo e($item['group']); ?></div>
                            </td>
                            <td class="align-middle">
                                <?php if($featureKey): ?>
                                    <code class="text-white"><?php echo e($featureKey); ?></code>
                                    <?php if($offlineMessage): ?>
                                        <div class="text-muted small mt-1"><?php echo e(Str::limit($offlineMessage, 60)); ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <?php if(!$featureKey): ?>
                                    <span class="badge badge-info">Always On</span>
                                <?php elseif($isActive): ?>
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check mr-1"></i> Enabled</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark mr-1"></i> Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <?php if($serviceType): ?>
                                    <div class="text-white small">
                                        <?php echo e((int) ($stats->active_count ?? 0)); ?> active / <?php echo e((int) ($stats->total_count ?? 0)); ?> total
                                    </div>
                                    <a href="<?php echo e(route('admin.custom_apis.index', ['service_type' => $serviceType])); ?>" class="small text-primary text-decoration-none">Manage</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-right pr-4">
                                <?php if($featureKey): ?>
                                    <form action="<?php echo e(route('admin.services.toggles.set', $featureKey)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="offline_message" value="<?php echo e($offlineMessage); ?>">
                                        <input type="hidden" name="is_active" value="<?php echo e($isActive ? 0 : 1); ?>">
                                        <button type="submit" class="btn btn-sm <?php echo e($isActive ? 'btn-outline-danger' : 'btn-outline-success'); ?>">
                                            <?php echo e($isActive ? 'Disable' : 'Enable'); ?>

                                        </button>
                                    </form>
                                    <a href="<?php echo e(route('admin.settings.index', ['tab' => 'tab-features'])); ?>" class="btn btn-sm btn-outline-primary ml-2">Details</a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('admin.settings.index')); ?>" class="btn btn-sm btn-outline-primary">Settings</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/admin/services/index.blade.php ENDPATH**/ ?>