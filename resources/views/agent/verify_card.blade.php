@extends('layouts.nexus')

@section('title', 'Agent Credential Verification | ' . config('app.name'))
@section('public_wrapper_class', 'none')
@section('is_public_page', 'true')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 rounded-4 overflow-hidden shadow-lg p-4 text-center" style="background: linear-gradient(145deg, #0f172a, #1e293b); border: 1px solid rgba(255,255,255,0.1) !important;">
                @if($isValid)
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 75px; height: 75px; background: rgba(16, 185, 129, 0.2); color: #10b981; border: 2px solid #10b981;">
                        <i class="fa-solid fa-shield-check fa-2x"></i>
                    </div>

                    <span class="badge bg-success text-uppercase font-monospace px-3 py-1 mb-2">VERIFIED ACTIVE AGENT</span>
                    <h4 class="text-white fw-bold mb-1">{{ $agent->full_name }}</h4>
                    <p class="text-white-50 small mb-4">Official Accredited NIN Enrollment Operative</p>

                    @if($agent->picture_path)
                        <div class="mb-4">
                            <img src="{{ $agent->picture_url }}" alt="{{ $agent->full_name }}" class="rounded-circle border border-3 border-success shadow" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                    @endif

                    <div class="rounded-3 p-3 text-start mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="row g-3 small">
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 11px;">AGENT CODE</span>
                                <strong class="text-primary font-monospace">{{ $agent->company_agent_code ?: ('AG-' . $agent->id) }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 11px;">STATE JURISDICTION</span>
                                <strong class="text-white">{{ $agent->state ?? 'Federal Capital' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 11px;">ENROLLMENT TERMINAL</span>
                                <code class="text-info">{{ $agent->machine_imei ? substr($agent->machine_imei, 0, 10) . '...' : 'AUTHORIZED' }}</code>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 11px;">ACCREDITATION STATUS</span>
                                <strong class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Compliant & Active</strong>
                            </div>
                        </div>
                    </div>

                    <div class="text-white-50 small" style="font-size: 11px;">
                        <i class="fa-solid fa-lock me-1 text-primary"></i> Live digital verification timestamp: <strong>{{ now()->format('d M Y, h:i A') }}</strong>.
                    </div>
                @else
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 75px; height: 75px; background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 2px solid #ef4444;">
                        <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                    </div>

                    <span class="badge bg-danger text-uppercase font-monospace px-3 py-1 mb-2">UNVERIFIED OR EXPIRED</span>
                    <h4 class="text-white fw-bold mb-1">Credential Not Found</h4>
                    <p class="text-white-50 small mb-4">No active authorized enrollment agent was found matching code <code class="text-warning">{{ $code }}</code>.</p>

                    <div class="alert alert-warning border-0 rounded-3 text-start small mb-4" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                        If you suspect fraud or an unauthorized individual posing as an accredited operative, please contact Fuwa Security immediately.
                    </div>

                    <div class="text-white-50 small">
                        Helpline: <strong>0800-FUWA-AGENT</strong> | support@fuwa.ng
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
