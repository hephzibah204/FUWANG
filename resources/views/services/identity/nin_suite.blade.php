@extends('layouts.nexus')

@section('title', 'NIN Suite - ' . \App\Models\SystemSetting::get('site_name', 'Fuwa.NG'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 font-weight-bold mb-1" style="color: var(--clr-primary);">NIN Suite</h1>
            <p class="text-muted mb-0">Complete suite of NIN related verification and modification services.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Suite Grid -->
    <div class="row">
        <!-- Validation -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <a href="{{ route('services.validation') }}" class="card shadow-sm h-100 border-0 hover-lift text-decoration-none">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(var(--clr-primary-rgb, 59, 130, 246), 0.1);">
                            <i class="fa-solid fa-file-shield text-primary" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">Validation</h5>
                    <p class="text-muted small mb-0">Validate NIN records directly.</p>
                </div>
            </a>
        </div>

        <!-- Personalize -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <a href="{{ route('services.personalization') }}" class="card shadow-sm h-100 border-0 hover-lift text-decoration-none">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(var(--clr-accent-1-rgb, 16, 185, 129), 0.1);">
                            <i class="fa-solid fa-user-pen text-success" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">Personalize</h5>
                    <p class="text-muted small mb-0">Personalize identity records securely.</p>
                </div>
            </a>
        </div>

        <!-- IPE Clearance -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <a href="{{ route('services.clearance') }}" class="card shadow-sm h-100 border-0 hover-lift text-decoration-none">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(var(--clr-accent-2-rgb, 139, 92, 246), 0.1);">
                            <i class="fa-solid fa-user-check text-purple" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">IPE Clearance</h5>
                    <p class="text-muted small mb-0">Obtain or verify clearance data.</p>
                </div>
            </a>
        </div>

        <!-- Print NIN Slip -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <a href="{{ route('services.nin') }}" class="card shadow-sm h-100 border-0 hover-lift text-decoration-none">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(var(--clr-primary-hover-rgb, 37, 99, 235), 0.1);">
                            <i class="fa-solid fa-print text-primary" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">Print NIN Slip</h5>
                    <p class="text-muted small mb-0">Generate standard or premium slips.</p>
                </div>
            </a>
        </div>

        <!-- NIN Verify -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <a href="{{ route('services.nin') }}" class="card shadow-sm h-100 border-0 hover-lift text-decoration-none">
                <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(var(--clr-primary-rgb, 59, 130, 246), 0.1);">
                            <i class="fa-regular fa-id-card text-primary" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-2">NIN Verify</h5>
                    <p class="text-muted small mb-0">Verify NIN identity directly.</p>
                </div>
            </a>
        </div>

    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .text-purple {
        color: var(--clr-accent-2, #8b5cf6) !important;
    }
</style>
@endsection
