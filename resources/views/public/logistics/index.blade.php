@extends('layouts.postoffice')

@section('title', 'Logistics & Interstate Freight Hub')

@section('content')
<!-- Hero Section -->
<div class="row align-items-center mb-5 pb-3">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="d-inline-flex align-items-center mb-3 px-3 py-1 rounded-pill" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.22); font-size: 0.8rem; letter-spacing: 0.02em;">
            <span class="mr-2 rounded-circle" style="width: 7px; height: 7px; background: var(--po-primary);"></span>
            <span style="color: var(--po-primary); font-weight: 500;">Nationwide Parcel Delivery &bull; 36 States</span>
        </div>

        <h1 class="font-weight-bold mb-3" style="font-size: clamp(2rem, 3.8vw, 3rem); line-height: 1.15; letter-spacing: -0.02em;">
            Interstate parcel delivery &amp; freight across Nigeria
        </h1>

        <p class="lead mb-4" style="color: var(--text-secondary); font-size: 1.05rem; line-height: 1.6; max-width: 520px;">
            Book instant doorstep pickups, generate authenticated digital waybills, and track commercial freight in real time with verified custody handovers.
        </p>

        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            @auth
                <a href="{{ route('logistics.dashboard') }}" class="btn btn-po-primary mr-3 mb-2">
                    <i class="fa fa-th-large mr-2"></i> Go to Dashboard
                </a>
                <a href="{{ route('logistics.book') }}" class="btn btn-outline-light mb-2" style="border-radius: 10px; border-color: var(--border-glass); font-size: 0.95rem; padding: 0.65rem 1.35rem;">
                    <i class="fa fa-plus mr-2"></i> Book Shipment
                </a>
            @else
                <a href="{{ route('logistics.register') }}" class="btn btn-po-primary mr-3 mb-2">
                    <i class="fa fa-user-plus mr-2"></i> Create Shipper Account
                </a>
                <a href="{{ route('logistics.login') }}" class="btn btn-outline-light mb-2" style="border-radius: 10px; border-color: var(--border-glass); font-size: 0.95rem; padding: 0.65rem 1.35rem;">
                    <i class="fa fa-arrow-right-to-bracket mr-2"></i> Shipper Login
                </a>
            @endauth
        </div>

        <!-- Telemetry Strip (Replaces clutter cards) -->
        <div class="row pt-2" style="border-top: 1px solid var(--border-glass-subtle); max-width: 520px;">
            <div class="col-4">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); font-family: monospace;">36 / 36</div>
                <div class="small text-truncate" style="color: var(--text-muted); font-size: 0.78rem;">States Covered</div>
            </div>
            <div class="col-4" style="border-left: 1px solid var(--border-glass-subtle); border-right: 1px solid var(--border-glass-subtle);">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); font-family: monospace;">24&ndash;48h</div>
                <div class="small text-truncate" style="color: var(--text-muted); font-size: 0.78rem;">Express Transit</div>
            </div>
            <div class="col-4">
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--po-primary); font-family: monospace;">Instant</div>
                <div class="small text-truncate" style="color: var(--text-muted); font-size: 0.78rem;">Digital Waybill</div>
            </div>
        </div>
    </div>

    <!-- Product Moment: Live Digital Waybill & Route Manifest Card -->
    <div class="col-lg-6">
        <div class="glass-card p-4 position-relative" style="background: #0d121f; border: 1px solid rgba(255,255,255,0.09) !important; border-radius: 18px;">
            <!-- Waybill Header -->
            <div class="d-flex justify-content-between align-items-center pb-3 mb-3" style="border-bottom: 1px solid var(--border-glass-subtle);">
                <div class="d-flex align-items-center">
                    <div class="rounded d-flex align-items-center justify-content-center mr-2" style="width: 28px; height: 28px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary);">
                        <i class="fa fa-qrcode" style="font-size: 0.85rem;"></i>
                    </div>
                    <div>
                        <span class="d-block" style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">Official Waybill</span>
                        <span style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: var(--text-primary); letter-spacing: 0.05em;">FWP-8924-LOS</span>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="px-2 py-1 rounded" style="background: rgba(245, 158, 11, 0.14); color: var(--po-primary); font-size: 0.72rem; font-weight: 600; font-family: monospace; letter-spacing: 0.03em;">
                        &bull; IN TRANSIT
                    </span>
                </div>
            </div>

            <!-- Route Nodes -->
            <div class="p-3 mb-3 rounded" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass-subtle);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Origin Hub</div>
                        <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Ikeja Hub, Lagos</div>
                        <div class="small" style="color: var(--text-muted); font-size: 0.75rem;">Dep: 08:30 WAT</div>
                    </div>
                    <div class="px-3 text-center">
                        <i class="fa fa-arrow-right" style="color: var(--po-primary); font-size: 0.9rem;"></i>
                    </div>
                    <div class="text-right">
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Destination</div>
                        <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Central Area, Abuja</div>
                        <div class="small" style="color: var(--text-muted); font-size: 0.75rem;">Est: Tomorrow 11:00</div>
                    </div>
                </div>
            </div>

            <!-- Checkpoint Telemetry Nodes -->
            <div class="mb-3 px-1">
                <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.04em; margin-bottom: 0.65rem;">Custody Timeline</div>
                
                <div class="d-flex align-items-start mb-2">
                    <div class="mr-3 text-center" style="width: 20px;">
                        <i class="fa fa-circle-check" style="color: #22c55e; font-size: 0.85rem;"></i>
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-between">
                        <span style="font-size: 0.85rem; color: var(--text-primary);">Parcel Sorted &amp; Weighed (4.2 kg Express)</span>
                        <span class="small font-monospace" style="color: var(--text-muted);">09:15 WAT</span>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-2">
                    <div class="mr-3 text-center" style="width: 20px;">
                        <i class="fa fa-circle-check" style="color: #22c55e; font-size: 0.85rem;"></i>
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-between">
                        <span style="font-size: 0.85rem; color: var(--text-primary);">Loaded on Line-Haul Vehicle [LOS-TRK-04]</span>
                        <span class="small font-monospace" style="color: var(--text-muted);">14:20 WAT</span>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-2">
                    <div class="mr-3 text-center" style="width: 20px;">
                        <i class="fa fa-circle-dot" style="color: var(--po-primary); font-size: 0.85rem;"></i>
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-between">
                        <span style="font-size: 0.85rem; color: var(--po-primary); font-weight: 500;">En Route via Lokoja Highway Corridor</span>
                        <span class="small font-monospace" style="color: var(--po-primary);">Live Telemetry</span>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="mr-3 text-center" style="width: 20px;">
                        <i class="fa fa-circle" style="color: rgba(255,255,255,0.15); font-size: 0.85rem;"></i>
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-between">
                        <span style="font-size: 0.85rem; color: var(--text-muted);">Abuja Distribution Center Handover</span>
                        <span class="small font-monospace" style="color: var(--text-muted);">Scheduled</span>
                    </div>
                </div>
            </div>

            <!-- Card Bottom Meta -->
            <div class="pt-2 d-flex justify-content-between align-items-center" style="border-top: 1px solid var(--border-glass-subtle); font-size: 0.75rem; color: var(--text-muted);">
                <span><i class="fa fa-shield-halved mr-1 text-success"></i> Tamper-Evident Seal #9042</span>
                <span>Air/Ground Transit Verified</span>
            </div>
        </div>
    </div>
</div>

<!-- Fast Waybill Tracking Console -->
<div class="glass-card p-4 mb-5" style="background: #0d121f; border: 1px solid rgba(255,255,255,0.08);">
    <div class="row align-items-center">
        <div class="col-lg-5 mb-3 mb-lg-0">
            <h4 class="font-weight-bold mb-1" style="font-size: 1.25rem; color: var(--text-primary);">Track an active shipment</h4>
            <p class="mb-0 small" style="color: var(--text-secondary);">Enter your assigned FuwaPost tracking code to view instant custody telemetry.</p>
        </div>
        <div class="col-lg-7">
            <div class="input-group">
                <input type="text" id="trackingId" class="form-control tracking-input mr-2" placeholder="Enter Waybill or Tracking ID (e.g. FWP-8924-LOS)" style="font-family: monospace;">
                <div class="input-group-append">
                    <button class="btn btn-po-primary px-4" id="trackBtn">
                        <i class="fa fa-magnifying-glass mr-2"></i> Track Waybill
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="trackingResult" class="mt-4 d-none"></div>
</div>

<!-- Logistics Services Grid -->
<div id="services" class="mb-5 pt-3">
    <div class="mb-4">
        <div class="d-inline-block small text-uppercase font-weight-bold" style="color: var(--po-primary); letter-spacing: 0.05em;">Core Capabilities</div>
        <h2 class="font-weight-bold mb-2" style="font-size: 1.75rem; letter-spacing: -0.01em;">Built for speed, volume &amp; Nigerian commerce</h2>
        <p style="color: var(--text-secondary); max-width: 680px; font-size: 0.95rem;">
            Whether sending single critical legal documents or managing high-volume merchant freight, our network provides reliable delivery schedules.
        </p>
    </div>

    <div class="row">
        <!-- Service 1 -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-center rounded mb-3" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.12); color: var(--po-primary);">
                        <i class="fa fa-truck-fast"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2" style="font-size: 1.05rem;">Express Courier</h5>
                    <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                        Same-day intra-city delivery and priority air express for urgent parcels and sensitive documents.
                    </p>
                </div>
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-glass-subtle); font-size: 0.78rem; color: var(--text-muted);">
                    Doorstep Pickup &bull; Priority Dispatch
                </div>
            </div>
        </div>

        <!-- Service 2 -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-center rounded mb-3" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.12); color: var(--po-primary);">
                        <i class="fa fa-route"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2" style="font-size: 1.05rem;">Inter-State Line-Haul</h5>
                    <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                        Scheduled interstate road &amp; air connections linking Lagos, Abuja, Kano, Port Harcourt, and all 36 states.
                    </p>
                </div>
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-glass-subtle); font-size: 0.78rem; color: var(--text-muted);">
                    Scheduled Departures &bull; 24-48h
                </div>
            </div>
        </div>

        <!-- Service 3 -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-center rounded mb-3" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.12); color: var(--po-primary);">
                        <i class="fa fa-cart-shopping"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2" style="font-size: 1.05rem;">E-Commerce Fulfillment</h5>
                    <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                        Bulk waybill creation, automated customer tracking SMS, and cash-on-delivery reconciliation for merchants.
                    </p>
                </div>
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-glass-subtle); font-size: 0.78rem; color: var(--text-muted);">
                    Bulk CSV Upload &bull; API Integration
                </div>
            </div>
        </div>

        <!-- Service 4 -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-center rounded mb-3" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.12); color: var(--po-primary);">
                        <i class="fa fa-warehouse"></i>
                    </div>
                    <h5 class="font-weight-bold mb-2" style="font-size: 1.05rem;">Hub Storage &amp; Freight</h5>
                    <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                        Secure interim holding, pallet storage, and regional distribution for wholesale commercial consignments.
                    </p>
                </div>
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-glass-subtle); font-size: 0.78rem; color: var(--text-muted);">
                    Secure Facility &bull; Inventory Tracking
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process & Pricing Section -->
<div class="row mb-5">
    <!-- How it works (Clean step rhythm) -->
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="glass-card p-4 h-100">
            <h4 class="font-weight-bold mb-1" style="font-size: 1.25rem;">How logistics works</h4>
            <p class="small mb-4" style="color: var(--text-secondary);">Direct 4-step dispatch workflow from booking to final doorstep.</p>

            <div class="d-flex mb-3">
                <div class="mr-3 font-monospace font-weight-bold d-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.85rem;">01</div>
                <div>
                    <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Book Shipment Online</div>
                    <div class="small" style="color: var(--text-secondary);">Input sender and recipient locations, parcel dimensions, and delivery urgency.</div>
                </div>
            </div>

            <div class="d-flex mb-3">
                <div class="mr-3 font-monospace font-weight-bold d-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.85rem;">02</div>
                <div>
                    <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Instant Waybill Generation</div>
                    <div class="small" style="color: var(--text-secondary);">Generate your verifiable FWP waybill with QR code and manifest barcode.</div>
                </div>
            </div>

            <div class="d-flex mb-3">
                <div class="mr-3 font-monospace font-weight-bold d-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.85rem;">03</div>
                <div>
                    <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Pickup or Hub Drop-Off</div>
                    <div class="small" style="color: var(--text-secondary);">A dispatch courier picks up at your doorstep or you drop off at your nearest partner hub.</div>
                </div>
            </div>

            <div class="d-flex">
                <div class="mr-3 font-monospace font-weight-bold d-flex align-items-center justify-content-center rounded" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.85rem;">04</div>
                <div>
                    <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Live Route Tracking</div>
                    <div class="small" style="color: var(--text-secondary);">Sender and receiver monitor progress in real time until OTP-confirmed delivery.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transparent Pricing Tiers -->
    <div class="col-lg-6">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between" id="pricing">
            <div>
                <h4 class="font-weight-bold mb-1" style="font-size: 1.25rem;">Predictable transit pricing</h4>
                <p class="small mb-4" style="color: var(--text-secondary);">Transparent weight and mileage calculation without hidden depot surcharges.</p>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass-subtle);">
                            <div class="font-weight-bold" style="font-size: 0.95rem;">Standard Freight</div>
                            <div class="small font-monospace mb-2" style="color: var(--po-primary);">3&ndash;5 Business Days</div>
                            <p class="small mb-0" style="color: var(--text-muted); font-size: 0.78rem;">Most economical for non-urgent parcels and stock replenishment.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 rounded h-100" style="background: rgba(245, 158, 11, 0.06); border: 1px solid rgba(245, 158, 11, 0.2);">
                            <div class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">Express Route</div>
                            <div class="small font-monospace mb-2" style="color: var(--po-primary);">24&ndash;48 Hours</div>
                            <p class="small mb-0" style="color: var(--text-muted); font-size: 0.78rem;">Rapid interstate line-haul for essential goods and ecommerce orders.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass-subtle);">
                            <div class="font-weight-bold" style="font-size: 0.95rem;">Same-Day City</div>
                            <div class="small font-monospace mb-2" style="color: var(--po-primary);">Within 6 Hours</div>
                            <p class="small mb-0" style="color: var(--text-muted); font-size: 0.78rem;">Direct motorcycle / van dispatch within Lagos, Abuja, and Port Harcourt.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 d-flex flex-wrap align-items-center justify-content-between" style="border-top: 1px solid var(--border-glass-subtle);">
                <div class="small" style="color: var(--text-secondary);">
                    <i class="fa fa-wallet mr-1 text-warning"></i> Pay via your Fuwa.NG wallet or direct transfer
                </div>
                <div>
                    @auth
                        <a href="{{ route('logistics.book') }}" class="btn btn-po-primary btn-sm px-3">
                            <i class="fa fa-plus mr-1"></i> Book a Delivery
                        </a>
                    @else
                        <a href="{{ route('logistics.register') }}" class="btn btn-po-primary btn-sm px-3">
                            <i class="fa fa-user-plus mr-1"></i> Start Shipping
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Proof / Grounded Testimonials -->
<div class="glass-card p-4 mb-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h4 class="font-weight-bold mb-1" style="font-size: 1.25rem;">Built for merchants, businesses and individuals</h4>
            <p class="small mb-0" style="color: var(--text-secondary);">Read verified experiences from high-volume online stores and interstate shippers.</p>
        </div>
        <div class="col-md-4 text-md-right mt-2 mt-md-0">
            <span class="small font-monospace" style="color: var(--po-primary);">
                <i class="fa fa-shield-check mr-1"></i> Verified Custody Network
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass-subtle);">
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.8rem;">
                        TA
                    </div>
                    <div>
                        <div class="font-weight-bold" style="font-size: 0.88rem;">Tunde A.</div>
                        <div class="small" style="color: var(--text-muted); font-size: 0.75rem;">Fashion Retailer &bull; Ikeja, Lagos</div>
                    </div>
                </div>
                <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                    &ldquo;Generating waybills with instant tracking links saved our customer care hours. Packages to Abuja and Kano arrive predictably in 24 hours.&rdquo;
                </p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass-subtle);">
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.8rem;">
                        FO
                    </div>
                    <div>
                        <div class="font-weight-bold" style="font-size: 0.88rem;">Fatima O.</div>
                        <div class="small" style="color: var(--text-muted); font-size: 0.75rem;">Wholesale Electronics &bull; Onitsha</div>
                    </div>
                </div>
                <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                    &ldquo;We ship heavy commercial crates interstate weekly. The wallet billing and automatic manifest reconciliation keep our logistics completely auditable.&rdquo;
                </p>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-glass-subtle);">
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 font-weight-bold" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-size: 0.8rem;">
                        CE
                    </div>
                    <div>
                        <div class="font-weight-bold" style="font-size: 0.88rem;">Chukwuemeka E.</div>
                        <div class="small" style="color: var(--text-muted); font-size: 0.75rem;">Individual Shipper &bull; Abuja</div>
                    </div>
                </div>
                <p class="small mb-0" style="color: var(--text-secondary); line-height: 1.5;">
                    &ldquo;Tracked an urgent original legal document from Lagos to Garki, Abuja. Delivery was confirmed by SMS with zero delay.&rdquo;
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Closing Call to Action -->
<div class="glass-card p-4" style="background: #0d121f; border: 1px solid rgba(245, 158, 11, 0.2) !important;">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div class="mb-3 mb-md-0">
            <h4 class="font-weight-bold mb-1" style="font-size: 1.3rem;">Ready to ship a parcel across Nigeria?</h4>
            <div style="color: var(--text-secondary); font-size: 0.95rem;">Book in 2 minutes, generate your digital waybill, and track to delivery.</div>
        </div>
        <div class="d-flex flex-wrap">
            @auth
                <a href="{{ route('logistics.book') }}" class="btn btn-po-primary mr-3 mb-2">
                    <i class="fa fa-plus mr-2"></i> Book Shipment
                </a>
                <a href="{{ route('logistics.dashboard') }}" class="btn btn-outline-light mb-2" style="border-radius: 10px; border-color: var(--border-glass);">
                    Shipper Dashboard
                </a>
            @else
                <a href="{{ route('logistics.register') }}" class="btn btn-po-primary mr-3 mb-2">
                    <i class="fa fa-user-plus mr-2"></i> Create Shipper Account
                </a>
                <a href="{{ route('logistics.login') }}" class="btn btn-outline-light mb-2" style="border-radius: 10px; border-color: var(--border-glass);">
                    Login
                </a>
            @endauth
        </div>
    </div>
</div>

@push('scripts')
<script>
    function trackShipment() {
        const id = ($('#trackingId').val() || '').trim();
        if (!id) {
            return;
        }

        const btn = $('#trackBtn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Searching...');

        $.post("{{ route('logistics.track') }}", {
            _token: "{{ csrf_token() }}",
            tracking_id: id
        }, function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-magnifying-glass mr-2"></i> Track Waybill');
            const resultDiv = $('#trackingResult');
            resultDiv.removeClass('d-none');

            if (res.status) {
                let timelineHtml = '<div class="tracking-timeline mt-3 pt-3" style="border-top: 1px solid var(--border-glass-subtle);">';
                res.tracking.timeline.forEach((step, idx) => {
                    const isLast = idx === res.tracking.timeline.length - 1;
                    const iconColor = step.done ? '#22c55e' : 'var(--text-muted)';
                    const iconClass = step.done ? 'fa-circle-check' : 'fa-circle-notch';
                    timelineHtml += `
                        <div class="d-flex mb-2 ${step.done ? '' : 'opacity-75'}">
                            <div class="mr-3 text-center" style="width: 22px;">
                                <i class="fa ${iconClass}" style="color: ${iconColor}; font-size: 0.85rem;"></i>
                            </div>
                            <div class="flex-grow-1 d-flex justify-content-between">
                                <span class="font-weight-500" style="color: var(--text-primary); font-size: 0.88rem;">${step.event}</span>
                                <small class="font-monospace" style="color: var(--text-muted);">${step.time}</small>
                            </div>
                        </div>
                    `;
                });
                timelineHtml += '</div>';

                resultDiv.html(`
                    <div class="p-4 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass-subtle);">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 1px solid var(--border-glass-subtle);">
                            <div>
                                <span class="small d-block text-uppercase font-monospace" style="color: var(--text-muted); font-size: 0.72rem;">Shipment Found</span>
                                <h5 class="mb-0 font-weight-bold font-monospace" style="color: var(--text-primary); letter-spacing: 0.04em;">${res.tracking.id}</h5>
                            </div>
                            <span class="px-3 py-1 rounded" style="background: rgba(245, 158, 11, 0.15); color: var(--po-primary); font-weight: 600; font-family: monospace; font-size: 0.8rem;">
                                ${res.tracking.status}
                            </span>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <span class="small d-block" style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Delivery Location</span>
                                <span class="font-weight-500" style="color: var(--text-primary); font-size: 0.9rem;">${res.tracking.location}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="small d-block" style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Last Status Update</span>
                                <span class="font-weight-500 font-monospace" style="color: var(--text-primary); font-size: 0.9rem;">${res.tracking.updated}</span>
                            </div>
                        </div>
                        ${timelineHtml}
                    </div>
                `);
            } else {
                resultDiv.html(`
                    <div class="p-3 rounded d-flex align-items-center" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fca5a5;">
                        <i class="fa fa-triangle-exclamation mr-2"></i>
                        <span class="small">${res.message || 'Tracking ID not found. Please verify the waybill number.'}</span>
                    </div>
                `);
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fa fa-magnifying-glass mr-2"></i> Track Waybill');
            const resultDiv = $('#trackingResult');
            resultDiv.removeClass('d-none');
            resultDiv.html(`
                <div class="p-3 rounded d-flex align-items-center" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fca5a5;">
                    <i class="fa fa-triangle-exclamation mr-2"></i>
                    <span class="small">Unable to query tracking server. Please check your network connection and try again.</span>
                </div>
            `);
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
@endpush
@endsection
