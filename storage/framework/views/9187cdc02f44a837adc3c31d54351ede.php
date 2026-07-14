<?php $__env->startSection('title', 'Service Price List | Fuwa.NG - Transparent & Competitive Pricing'); ?>
<?php $__env->startSection('meta_description', 'View the complete price list for all Fuwa.NG services, including NIN verification, BVN validation, VTU, and more. Transparent, pay-as-you-go pricing for your business.'); ?>
<?php $__env->startSection('meta_keywords', 'Fuwa.NG pricing, NIN verification price, BVN validation price, VTU prices Nigeria, identity verification costs'); ?>
<?php $__env->startSection('canonical', route('services.price_list')); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1 text-white">Service Price List</h1>
            <p class="text-muted">Dynamic pricing for all our identity and utility services.</p>
        </div>
    </div>

    <div class="dash-two-col">
        <!-- Main Price Tables -->
        <div class="dash-left-col">
            <?php
                $serviceNames = [
                    'nin' => 'NIN Verification',
                    'nin_verification' => 'NIN Verification',
                    'bvn' => 'BVN Verification',
                    'bvn_verification' => 'BVN Verification',
                    'address_verification' => 'Address Verification',
                    'drivers_license' => 'Drivers License Verify',
                    'biometric_verification' => 'Biometric Verification',
                    'cac_verification' => 'CAC Business Verify',
                    'tin_verification' => 'TIN Verification',
                    'passport_verification' => 'Passport Verification',
                    'voters_card_verification' => 'Voters Card Verify',
                    'vtu_airtime' => 'VTU Airtime',
                    'vtu_data' => 'VTU Data Bundle',
                    'education_waec' => 'WAEC Result Checker',
                    'education_waec_registration' => 'WAEC Registration',
                    'insurance_motor' => 'Motor Insurance',
                    'payment' => 'Payment Processing'
                ];
            ?>

            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryName => $types): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php 
                    $hasContent = false;
                    foreach($types as $t) { if($customPrices->has($t)) $hasContent = true; }
                ?>

                <?php if($hasContent): ?>
                <div class="panel-card mb-4">
                    <div class="panel-hdr">
                        <h3><?php echo e($categoryName); ?></h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Provider</th>
                                    <th>Verification Type</th>
                                    <th class="text-right pr-4">Cost (₦)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($customPrices->has($type)): ?>
                                        <?php $__currentLoopData = $customPrices[$type]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $providerTypes = $provider->verificationTypes ?? collect();
                                            ?>
                                            <?php if($providerTypes->count() > 0): ?>
                                                <?php $__currentLoopData = $providerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td class="align-middle font-weight-bold text-white"><?php echo e($serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type))); ?></td>
                                                        <td class="align-middle text-muted small"><?php echo e($provider->name); ?></td>
                                                        <td class="align-middle text-muted small"><?php echo e($t->label); ?></td>
                                                        <td class="align-middle text-right pr-4 font-weight-bold text-primary">
                                                            ₦<?php echo e(number_format((float) $t->price, 2)); ?>

                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td class="align-middle font-weight-bold text-white"><?php echo e($serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type))); ?></td>
                                                    <td class="align-middle text-muted small"><?php echo e($provider->name); ?></td>
                                                    <td class="align-middle text-muted small">Standard</td>
                                                    <td class="align-middle text-right pr-4 font-weight-bold text-primary">
                                                        ₦<?php echo e(number_format((float) $provider->price, 2)); ?>

                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if((!$customPrices->has('nin') && isset($legacyPrices->nin_by_nin_price)) || (!$customPrices->has('bvn') && isset($legacyPrices->bvn_by_bvn))): ?>
            <div class="panel-card mt-4">
                <div class="panel-hdr text-muted">
                    <h3>Legacy Service Fallbacks</h3>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <tbody>
                            <?php if(!$customPrices->has('nin') && isset($legacyPrices->nin_by_nin_price)): ?>
                                <tr>
                                    <td class="align-middle font-weight-bold text-white">NIN Verification (Legacy)</td>
                                    <td class="align-middle text-muted small">Global System</td>
                                    <td class="align-middle text-right pr-4 font-weight-bold">₦<?php echo e(number_format($legacyPrices->nin_by_nin_price, 2)); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if(!$customPrices->has('bvn') && isset($legacyPrices->bvn_by_bvn)): ?>
                                <tr>
                                    <td class="align-middle font-weight-bold text-white">BVN Verification (Legacy)</td>
                                    <td class="align-middle text-muted small">Global System</td>
                                    <td class="align-middle text-right pr-4 font-weight-bold">₦<?php echo e(number_format($legacyPrices->bvn_by_bvn, 2)); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Side: Service Info -->
        <div class="panel-card">
            <div class="panel-hdr">
                <h3>Pricing Notes</h3>
            </div>
            <div class="p-4">
                <div class="kyc-banner mb-4">
                    <i class="fa-solid fa-circle-info"></i>
                    <div class="kyc-text">
                        <strong>Real-time Updates</strong>
                        <p>Prices are automatically updated whenever the system administrator changes provider configurations.</p>
                    </div>
                </div>

                <div class="ref-card">
                    <p class="small text-muted m-0">Need higher limits?</p>
                    <strong>Contact our Sales team</strong>
                    <div class="mt-2">
                        <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-sm btn-primary w-100">Open Ticket</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Fuwa.NG Service Price List",
    "description": "Transparent, pay-as-you-go pricing for all Fuwa.NG services.",
    "numberOfItems": <?php echo e($customPrices->flatten()->count() + ($legacyPrices ? 2 : 0)); ?>,
    "itemListElement": [
        <?php $counter = 0; ?>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryName => $types): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($customPrices->has($type)): ?>
                    <?php $__currentLoopData = $customPrices[$type]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $providerTypes = $provider->verificationTypes ?? collect();
                        ?>
                        <?php if($providerTypes->count() > 0): ?>
                            <?php $__currentLoopData = $providerTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($counter > 0): ?>,<?php endif; ?>
                                {
                                    "@type": "Product",
                                    "name": "<?php echo e($serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type))); ?> - <?php echo e($t->label); ?>",
                                    "description": "<?php echo e($serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type))); ?> by <?php echo e($provider->name); ?> (<?php echo e($t->label); ?>)",
                                    "brand": {
                                        "@type": "Brand",
                                        "name": "<?php echo e($provider->name); ?>"
                                    },
                                    "offers": {
                                        "@type": "Offer",
                                        "price": "<?php echo e(number_format((float) $t->price, 2, '.', '')); ?>",
                                        "priceCurrency": "NGN"
                                    }
                                }
                                <?php $counter++; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <?php if($counter > 0): ?>,<?php endif; ?>
                            {
                                "@type": "Product",
                                "name": "<?php echo e($serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type))); ?>",
                                "description": "<?php echo e($serviceNames[$type] ?? strtoupper(str_replace('_', ' ', $type))); ?> by <?php echo e($provider->name); ?>",
                                "brand": {
                                    "@type": "Brand",
                                    "name": "<?php echo e($provider->name); ?>"
                                },
                                "offers": {
                                    "@type": "Offer",
                                    "price": "<?php echo e(number_format((float) $provider->price, 2, '.', '')); ?>",
                                    "priceCurrency": "NGN"
                                }
                            }
                            <?php $counter++; ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if((!$customPrices->has('nin') && isset($legacyPrices->nin_by_nin_price))): ?>
        <?php if($counter > 0): ?>,<?php endif; ?>
        {
            "@type": "Product",
            "name": "NIN Verification (Legacy)",
            "offers": {
                "@type": "Offer",
                "price": "<?php echo e(number_format($legacyPrices->nin_by_nin_price, 2, '.', '')); ?>",
                "priceCurrency": "NGN"
            }
        }
        <?php $counter++; ?>
        <?php endif; ?>
        <?php if((!$customPrices->has('bvn') && isset($legacyPrices->bvn_by_bvn))): ?>
        <?php if($counter > 0): ?>,<?php endif; ?>
        {
            "@type": "Product",
            "name": "BVN Verification (Legacy)",
            "offers": {
                "@type": "Offer",
                "price": "<?php echo e(number_format($legacyPrices->bvn_by_bvn, 2, '.', '')); ?>",
                "priceCurrency": "NGN"
            }
        }
        <?php endif; ?>
    ]
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/services/price-list.blade.php ENDPATH**/ ?>