<?php $__env->startSection('title', 'Logistics & Shipping Hub'); ?>

<?php $__env->startSection('content'); ?>
<div class="row align-items-center mt-4 mb-5">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--po-primary);">
                Nationwide delivery • Real-time tracking
            </span>
        </div>
        <h1 class="display-4 font-weight-bold mb-3">
            Ship Smarter with <span style="color:var(--po-primary)">FuwaPost</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="max-width: 560px;">
            Reliable deliveries for individuals and businesses across Nigeria — with easy booking, instant tracking, and secure payments.
        </p>

        <div class="d-flex flex-wrap align-items-center">
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('logistics.dashboard')); ?>" class="btn btn-po-primary mr-3 mb-2">
                    <i class="fa fa-th-large mr-2"></i> Go to Dashboard
                </a>
                <a href="<?php echo e(route('logistics.book')); ?>" class="btn btn-outline-light mb-2" style="border-radius: 12px;">
                    <i class="fa fa-plus-circle mr-2"></i> Book a Shipment
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('logistics.register')); ?>" class="btn btn-po-primary mr-3 mb-2">
                    <i class="fa fa-user-plus mr-2"></i> Create Account
                </a>
                <a href="<?php echo e(route('logistics.login')); ?>" class="btn btn-outline-light mb-2" style="border-radius: 12px;">
                    <i class="fa fa-arrow-right-to-bracket mr-2"></i> Login
                </a>
            <?php endif; ?>
        </div>

        <div class="row mt-4">
            <div class="col-6 col-md-4 mb-3">
                <div class="glass-card p-3 h-100">
                    <div class="text-white-50 small">Delivery Options</div>
                    <div class="font-weight-bold">Standard • Express</div>
                </div>
            </div>
            <div class="col-6 col-md-4 mb-3">
                <div class="glass-card p-3 h-100">
                    <div class="text-white-50 small">Live Updates</div>
                    <div class="font-weight-bold">Tracking Timeline</div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="glass-card p-3 h-100">
                    <div class="text-white-50 small">For Businesses</div>
                    <div class="font-weight-bold">Bulk & E‑commerce</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 mb-4 mb-md-0">
                    <h5 class="font-weight-bold mb-2">Track a shipment</h5>
                    <p class="text-white-50 small mb-3">Enter your tracking ID to see the latest status and delivery timeline.</p>

                    <div class="input-group">
                        <input type="text" id="trackingId" class="form-control tracking-input mr-2" placeholder="Enter Tracking ID (e.g., NXS-XXXXXX)">
                        <div class="input-group-append">
                            <button class="btn btn-po-primary px-4" id="trackBtn">
                                <i class="fa fa-magnifying-glass mr-2"></i> Track
                            </button>
                        </div>
                    </div>
                    <div id="trackingResult" class="mt-4 d-none"></div>
                </div>
                <div class="col-12 col-md-6 text-center">
                    <img src="<?php echo e(asset('images/hero_visual.png')); ?>" alt="Logistics illustration" class="img-fluid" style="max-height: 260px; opacity: 0.95;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services Grid -->
<div id="services" class="mt-5">
    <div class="text-center mb-4">
        <h2 class="font-weight-bold mb-2">Logistics Services Built for <span style="color:var(--po-primary)">Nigeria</span></h2>
        <p class="text-white-50 mx-auto" style="max-width: 760px;">
            Inspired by the best logistics experiences in Nigeria, FuwaPost focuses on speed, visibility, and convenience — from pickup to delivery.
        </p>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background: rgba(245, 158, 11, 0.12); color: var(--po-primary);">
                        <i class="fa fa-truck-fast fa-lg"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0">Express Delivery</h5>
                </div>
                <p class="text-white-50 small mb-0">Fast shipping for urgent deliveries across major cities.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background: rgba(59, 130, 246, 0.12); color: var(--po-accent);">
                        <i class="fa fa-route fa-lg"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0">Inter‑State Shipping</h5>
                </div>
                <p class="text-white-50 small mb-0">Nationwide coverage for personal and business deliveries.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background: rgba(34, 197, 94, 0.12); color: #22c55e;">
                        <i class="fa fa-location-dot fa-lg"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0">Pickup & Drop‑off</h5>
                </div>
                <p class="text-white-50 small mb-0">Book in minutes and generate your waybill instantly after payment.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background: rgba(168, 85, 247, 0.12); color: #a855f7;">
                        <i class="fa fa-cart-shopping fa-lg"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0">E‑commerce Support</h5>
                </div>
                <p class="text-white-50 small mb-0">Smooth order fulfillment flows designed for online sellers and SMEs.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background: rgba(245, 158, 11, 0.12); color: var(--po-primary);">
                        <i class="fa fa-warehouse fa-lg"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0">Warehousing</h5>
                </div>
                <p class="text-white-50 small mb-0">Secure storage and fulfillment built for growth and reliability.</p>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 54px; height: 54px; background: rgba(255, 255, 255, 0.08); color: #fff;">
                        <i class="fa fa-boxes-stacked fa-lg"></i>
                    </div>
                    <h5 class="font-weight-bold mb-0">Bulk & Corporate</h5>
                </div>
                <p class="text-white-50 small mb-0">Planned pickups, tracking visibility, and dependable service levels.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 align-items-stretch">
    <div class="col-lg-6 mb-4">
        <div class="glass-card p-4 h-100">
            <h4 class="font-weight-bold mb-2">How it works</h4>
            <p class="text-white-50 small mb-4">A simple flow designed for speed and clarity.</p>
            <div class="d-flex mb-3">
                <div class="mr-3 font-weight-bold" style="color:var(--po-primary); width: 28px;">1</div>
                <div>
                    <div class="font-weight-bold">Create an account</div>
                    <div class="text-white-50 small">Use your Fuwa.ng credentials or register directly on Logistics.</div>
                </div>
            </div>
            <div class="d-flex mb-3">
                <div class="mr-3 font-weight-bold" style="color:var(--po-primary); width: 28px;">2</div>
                <div>
                    <div class="font-weight-bold">Book a shipment</div>
                    <div class="text-white-50 small">Enter sender/recipient details and pick delivery speed.</div>
                </div>
            </div>
            <div class="d-flex mb-3">
                <div class="mr-3 font-weight-bold" style="color:var(--po-primary); width: 28px;">3</div>
                <div>
                    <div class="font-weight-bold">Get your tracking ID</div>
                    <div class="text-white-50 small">We generate a waybill and tracking ID immediately.</div>
                </div>
            </div>
            <div class="d-flex">
                <div class="mr-3 font-weight-bold" style="color:var(--po-primary); width: 28px;">4</div>
                <div>
                    <div class="font-weight-bold">Track delivery</div>
                    <div class="text-white-50 small">Follow updates in real time from pickup to delivery.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="glass-card p-4 h-100">
            <div class="row">
                <div class="col-12">
                    <h4 class="font-weight-bold mb-2" id="pricing">Transparent pricing</h4>
                    <p class="text-white-50 small mb-4">Prices depend on weight and delivery speed. Final cost is shown before payment.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px;">
                        <div class="font-weight-bold">Standard</div>
                        <div class="text-white-50 small">3–5 business days</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded-lg" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.18); border-radius: 16px;">
                        <div class="font-weight-bold">Express</div>
                        <div class="text-white-50 small">1–2 business days</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded-lg" style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.18); border-radius: 16px;">
                        <div class="font-weight-bold">Overnight</div>
                        <div class="text-white-50 small">Next day delivery</div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('logistics.book')); ?>" class="btn btn-po-primary">
                        <i class="fa fa-plus-circle mr-2"></i> Book Now
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('logistics.register')); ?>" class="btn btn-po-primary">
                        <i class="fa fa-user-plus mr-2"></i> Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <div class="glass-card p-4">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <img src="<?php echo e(asset('images/people/family.webp')); ?>" alt="Happy customers" class="img-fluid rounded-lg" style="border-radius: 18px; border: 1px solid rgba(255,255,255,0.08);">
            </div>
            <div class="col-lg-7">
                <h4 class="font-weight-bold mb-2">Trusted by individuals and businesses</h4>
                <p class="text-white-50 mb-4">Clear tracking, smooth booking, and reliable service — built for repeat usage.</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <img src="<?php echo e(\App\Support\TestimonialAvatars::url('Tracking is super clear')); ?>" alt="Customer" class="rounded-circle mr-3" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <div class="font-weight-bold">“Tracking is super clear.”</div>
                                <div class="text-white-50 small">I can see every update without calling support.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <img src="<?php echo e(\App\Support\TestimonialAvatars::url('Booking takes minutes')); ?>" alt="Customer" class="rounded-circle mr-3" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <div class="font-weight-bold">“Booking takes minutes.”</div>
                                <div class="text-white-50 small">Waybill generation is instant after payment.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <img src="<?php echo e(\App\Support\TestimonialAvatars::url('Great for my online store')); ?>" alt="Customer" class="rounded-circle mr-3" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <div class="font-weight-bold">“Great for my online store.”</div>
                                <div class="text-white-50 small">I manage multiple shipments easily.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.18); color: var(--po-primary);">
                                <i class="fa fa-shield-halved"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">Secure payments</div>
                                <div class="text-white-50 small">Wallet-backed transactions with clear receipts.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap mt-3">
                    <a href="#services" class="btn btn-outline-light mr-3 mb-2" style="border-radius: 12px;">Explore Services</a>
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('logistics.dashboard')); ?>" class="btn btn-po-primary mb-2">Open Dashboard</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('logistics.register')); ?>" class="btn btn-po-primary mb-2">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 mb-2">
    <div class="glass-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="mb-3 mb-md-0">
                <h4 class="font-weight-bold mb-1">Ready to ship?</h4>
                <div class="text-white-50">Book, pay, generate your waybill, and track — all in one place.</div>
            </div>
            <div class="d-flex flex-wrap">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('logistics.book')); ?>" class="btn btn-po-primary mr-3 mb-2">
                        <i class="fa fa-plus-circle mr-2"></i> Book Shipment
                    </a>
                    <a href="<?php echo e(route('logistics.dashboard')); ?>" class="btn btn-outline-light mb-2" style="border-radius: 12px;">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo e(route('logistics.register')); ?>" class="btn btn-po-primary mr-3 mb-2">
                        <i class="fa fa-user-plus mr-2"></i> Create Account
                    </a>
                    <a href="<?php echo e(route('logistics.login')); ?>" class="btn btn-outline-light mb-2" style="border-radius: 12px;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function trackShipment() {
        const id = ($('#trackingId').val() || '').trim();
        if (!id) {
            return;
        }

        const btn = $('#trackBtn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Tracking...');

        $.post("<?php echo e(route('logistics.track')); ?>", {
            _token: "<?php echo e(csrf_token()); ?>",
            tracking_id: id
        }, function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-magnifying-glass mr-2"></i> Track');
            const resultDiv = $('#trackingResult');
            resultDiv.removeClass('d-none');

            if (res.status) {
                let timelineHtml = '<div class="tracking-timeline mt-4">';
                res.tracking.timeline.forEach(step => {
                    timelineHtml += `
                        <div class="d-flex mb-3 ${step.done ? 'text-white' : 'text-white-50'}">
                            <div class="mr-3 text-center" style="width: 25px;">
                                <i class="fa ${step.done ? 'fa-check-circle text-success' : 'fa-circle-notch'}"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">${step.event}</div>
                                <small class="text-white-50">${step.time}</small>
                            </div>
                        </div>
                    `;
                });
                timelineHtml += '</div>';

                resultDiv.html(`
                    <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 font-weight-bold">Shipment Found: <span class="text-primary">${res.tracking.id}</span></h5>
                            <span class="badge badge-warning px-3 py-2 rounded-pill text-dark">${res.tracking.status}</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 border-right border-white-10">
                                <p class="text-white-50 small mb-1">Current Location</p>
                                <p class="font-weight-bold mb-0">${res.tracking.location}</p>
                            </div>
                            <div class="col-md-6 px-4">
                                <p class="text-white-50 small mb-1">Last Updated</p>
                                <p class="font-weight-bold mb-0">${res.tracking.updated}</p>
                            </div>
                        </div>
                        ${timelineHtml}
                    </div>
                `);
            } else {
                resultDiv.html(`
                    <div class="alert alert-danger border-0 bg-danger text-white rounded-lg">
                        <i class="fa fa-exclamation-triangle mr-2"></i> ${res.message}
                    </div>
                `);
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fa fa-magnifying-glass mr-2"></i> Track');
            alert('Something went wrong. Please try again.');
        });
    }

    $('#trackBtn').on('click', function() {
        trackShipment();
    });

    $('#trackingId').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            trackShipment();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.postoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hephz\Documents\CODEBASE\Fuwa.NG\resources\views/public/logistics/index.blade.php ENDPATH**/ ?>