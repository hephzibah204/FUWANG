@extends('layouts.nexus')

@section('title', 'Sandbox | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Admin Sandbox</h1>
            <p class="text-muted mb-0">Test services with admin-only entrypoints. No user wallet deductions.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <a class="btn btn-outline-primary mr-2" href="{{ route('admin.services.index') }}">
                <i class="fa-solid fa-layer-group mr-2"></i> Services
            </a>
            <a class="btn btn-primary" href="{{ route('admin.settings.index') }}">
                <i class="fa-solid fa-sliders mr-2"></i> Settings
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <a href="{{ route('admin.sandbox.services.index') }}" class="text-decoration-none d-block">
                <div class="p-4 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-white font-weight-bold">All Service Sandboxes</div>
                            <div class="text-white-50 small">Drivers License, CAC, TIN, Passport, VTU, Education, Insurance, more</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-12 mb-4">
            <a href="{{ route('admin.sandbox.nin') }}" class="text-decoration-none d-block">
                <div class="p-4 rounded-lg" style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.22);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 46px; height: 46px; background: rgba(59,130,246,0.18); color: #3b82f6;">
                            <i class="fa-regular fa-id-card"></i>
                        </div>
                        <div>
                            <div class="text-white font-weight-bold">NIN Suite (Sandbox)</div>
                            <div class="text-white-50 small">Admin-only test endpoint</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-12 mb-4">
            <a href="{{ route('admin.sandbox.bvn') }}" class="text-decoration-none d-block">
                <div class="p-4 rounded-lg" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.22);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 46px; height: 46px; background: rgba(16,185,129,0.18); color: #10b981;">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <div class="text-white font-weight-bold">BVN Suite (Sandbox)</div>
                            <div class="text-white-50 small">Admin-only test endpoint</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
