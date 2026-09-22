@extends('layouts.nexus')

@section('title', 'Identity Verification Hub | ' . config('app.name'))

@section('content')
<div class="container py-4 py-lg-5">
    <div class="text-center max-w-2xl mx-auto mb-5">
        <h2 class="font-weight-bold text-white mb-2">Identity Verification <span class="text-primary">Hub</span></h2>
        <p class="text-white-50">Instant, verified identity lookups and official document validation.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- NIN Verification -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card glass-card h-100 p-4 rounded-4 border border-white-10">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary me-3">
                        <i class="fa-solid fa-id-card fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">NIN Verification</h5>
                        <small class="text-white-50">National Identity Number</small>
                    </div>
                </div>
                <p class="text-white-50 small mb-4">Verify NIN numbers and download official NIMC slips instantly.</p>
                <a href="{{ route('services.nin_verify') }}" class="btn btn-primary w-100 mt-auto font-weight-bold">
                    Verify NIN <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- BVN Verification -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card glass-card h-100 p-4 rounded-4 border border-white-10">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle p-3 bg-success-subtle text-success me-3">
                        <i class="fa-solid fa-building-columns fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">BVN Verification</h5>
                        <small class="text-white-50">Bank Verification Number</small>
                    </div>
                </div>
                <p class="text-white-50 small mb-4">Validate BVN details and account ownership in real-time.</p>
                <a href="{{ route('services.bvn_verify') }}" class="btn btn-outline-light w-100 mt-auto font-weight-bold">
                    Verify BVN <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- Driver's License -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card glass-card h-100 p-4 rounded-4 border border-white-10">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning me-3">
                        <i class="fa-solid fa-id-badge fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Driver's License</h5>
                        <small class="text-white-50">FRSC Drivers License</small>
                    </div>
                </div>
                <p class="text-white-50 small mb-4">Verify Nigerian driver's license status and records.</p>
                <a href="{{ route('services.drivers_license') }}" class="btn btn-outline-light w-100 mt-auto font-weight-bold">
                    Verify License <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
