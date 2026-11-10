@extends('layouts.nexus')

@section('title', 'NIN Verification Suite - ' . \App\Models\SystemSetting::get('site_name', 'Fuwa.NG'))

@section('content')
<div class="container-fluid py-3 py-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge-accent"><i class="fa-solid fa-id-card mr-1 text-primary"></i> NIMC Suite</span>
                <span class="badge-accent text-success"><i class="fa-solid fa-circle-check mr-1"></i> Live Gateway</span>
            </div>
            <h1 class="h3 font-weight-bold mb-1 text-white">NIN Verification Suite</h1>
            <p class="text-muted small mb-0">Official NIMC identity verification, slip printing, validation, and biometric services.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto text-center">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Suite Grid -->
    <div class="row g-3 g-md-4">
        <!-- 1. NIN Verification -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.nin') }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-primary-soft text-primary">
                        <i class="fa-regular fa-id-card"></i>
                    </div>
                    <h5 class="suite-title">NIN Verification</h5>
                    <p class="suite-desc d-none d-sm-block">Instant identity verification by 11-digit NIN.</p>
                    <span class="suite-action text-primary">
                        Launch <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 2. Print NIN Slip -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.nin') }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-info-soft text-info">
                        <i class="fa-solid fa-print"></i>
                    </div>
                    <h5 class="suite-title">Print NIN Slip</h5>
                    <p class="suite-desc d-none d-sm-block">Standard, Premium, Regular, and vNIN slips.</p>
                    <span class="suite-action text-info">
                        Print <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 3. Validation -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.validation') }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-success-soft text-success">
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                    <h5 class="suite-title">NIN Validation</h5>
                    <p class="suite-desc d-none d-sm-block">Resolve Record Not Found on banks & SIM registration.</p>
                    <span class="suite-action text-success">
                        Validate <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 4. Personalization -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.personalization') }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-purple-soft text-purple">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h5 class="suite-title">Personalization</h5>
                    <p class="suite-desc d-none d-sm-block">Personalize tracking ID & generate active NIN slip.</p>
                    <span class="suite-action text-purple">
                        Personalize <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 5. IPE Clearance -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.clearance') }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-warning-soft text-warning">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h5 class="suite-title">IPE Clearance</h5>
                    <p class="suite-desc d-none d-sm-block">Resolve Internal Processing Errors without NIN.</p>
                    <span class="suite-action text-warning">
                        Clear Error <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 6. Face Verification -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.nin_face') }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-pink-soft text-pink">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <h5 class="suite-title">Face Verification</h5>
                    <p class="suite-desc d-none d-sm-block">Biometric selfie facial matching with NIMC.</p>
                    <span class="suite-action text-pink">
                        Match Face <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 7. Phone Lookup -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.nin', ['mode' => 'phone']) }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-teal-soft text-teal">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <h5 class="suite-title">Phone Lookup</h5>
                    <p class="suite-desc d-none d-sm-block">Find linked NIN using registered phone number.</p>
                    <span class="suite-action text-teal">
                        Find by Phone <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>

        <!-- 8. Tracking ID -->
        <div class="col-6 col-md-4 col-xl-3 mb-3">
            <a href="{{ route('services.nin', ['mode' => 'tracking']) }}" class="suite-card h-100 text-decoration-none">
                <div class="suite-card-body">
                    <div class="suite-icon-wrap bg-amber-soft text-amber">
                        <i class="fa-solid fa-fingerprint"></i>
                    </div>
                    <h5 class="suite-title">Tracking ID</h5>
                    <p class="suite-desc d-none d-sm-block">Retrieve full NIN record using enrollment tracking ID.</p>
                    <span class="suite-action text-amber">
                        Search ID <i class="fa-solid fa-arrow-right ml-1"></i>
                    </span>
                </div>
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .suite-card {
        display: block;
        background: var(--clr-bg-card, rgba(255, 255, 255, 0.03));
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .suite-card:hover, .suite-card:active {
        transform: translateY(-4px);
        border-color: rgba(59, 130, 246, 0.4);
        box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.35);
    }
    .suite-card-body {
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        height: 100%;
    }
    .suite-icon-wrap {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 14px;
        flex-shrink: 0;
    }
    .suite-title {
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 6px;
    }
    .suite-desc {
        color: var(--clr-text-muted, #94a3b8);
        font-size: 0.8rem;
        line-height: 1.4;
        margin-bottom: 14px;
        flex-grow: 1;
    }
    .suite-action {
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        margin-top: auto;
    }

    /* Soft Color Themes */
    .bg-primary-soft { background: rgba(59, 130, 246, 0.12); }
    .bg-info-soft { background: rgba(14, 165, 233, 0.12); }
    .bg-success-soft { background: rgba(16, 185, 129, 0.12); }
    .bg-purple-soft { background: rgba(139, 92, 246, 0.12); }
    .text-purple { color: #8b5cf6 !important; }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.12); }
    .bg-pink-soft { background: rgba(236, 72, 153, 0.12); }
    .text-pink { color: #ec4899 !important; }
    .bg-teal-soft { background: rgba(20, 184, 166, 0.12); }
    .text-teal { color: #14b8a6 !important; }
    .bg-amber-soft { background: rgba(217, 119, 6, 0.12); }
    .text-amber { color: #f59e0b !important; }

    .badge-accent {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--clr-text-muted, #94a3b8);
    }

    @media (max-width: 576px) {
        .suite-card-body {
            padding: 16px 10px;
        }
        .suite-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .suite-title {
            font-size: 0.88rem;
            margin-bottom: 4px;
        }
        .suite-action {
            font-size: 0.72rem;
        }
    }
</style>
@endpush
@endsection
