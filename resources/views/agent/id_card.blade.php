@extends('layouts.nexus')

@section('title', 'Official Agent ID Card | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <!-- Header with Action Buttons -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-light btn-sm rounded-pill mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Agency Dashboard
            </a>
            <h3 class="text-white fw-bold mb-0"><i class="fa-solid fa-id-card text-primary me-2"></i>Official Enrollment Agent ID Card</h3>
            <p class="text-white-50 small mb-0">Authorized Digital Identity Credential for Field Operations & Regulatory Verification</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-info rounded-pill px-4">
                <i class="fa-solid fa-print me-2"></i>Print Card
            </button>
            <a href="{{ route('agent.id_card.download') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-download me-2"></i>Download PDF
            </a>
        </div>
    </div>

    <!-- Dual-Sided ID Card Container -->
    <div class="row justify-content-center g-4 my-2">
        <!-- FRONT OF CARD -->
        <div class="col-lg-5 col-md-8">
            <div class="card border-0 rounded-4 overflow-hidden position-relative shadow-lg id-card-frame" style="background: linear-gradient(145deg, #0f172a, #1e293b); border: 2px solid rgba(59, 130, 246, 0.4) !important; min-height: 480px;">
                <!-- Header Ribbon -->
                <div class="p-3 text-center position-relative" style="background: linear-gradient(90deg, #1e3a8a, #0284c7); border-bottom: 2px solid #38bdf8;">
                    <div class="d-flex align-items-center justify-content-between px-2">
                        <div class="fw-bold text-white small text-uppercase" style="letter-spacing: 1.5px;">FUWA PARTNER NETWORK</div>
                        <span class="badge bg-success text-white px-2 py-1 small"><i class="fa-solid fa-check-double me-1"></i>VERIFIED AGENT</span>
                    </div>
                    <div class="text-white-50 font-monospace" style="font-size: 11px;">FEDERAL REPUBLIC OF NIGERIA • NIMC ENROLLMENT</div>
                </div>

                <!-- Card Body -->
                <div class="p-4 text-center">
                    <!-- Photo with holographic glow -->
                    <div class="position-relative d-inline-block mb-3">
                        @if($agent->picture_path)
                            <img src="{{ $agent->picture_url }}" alt="{{ $agent->full_name }}" class="rounded-3 shadow" style="width: 125px; height: 140px; object-fit: cover; border: 3px solid #38bdf8;">
                        @else
                            <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary text-white" style="width: 125px; height: 140px; border: 3px solid #f59e0b;">
                                <i class="fa-solid fa-user-tie fa-3x"></i>
                            </div>
                        @endif
                        <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1" style="font-size: 10px; width: 22px; height: 22px; line-height: 1;">
                            <i class="fa-solid fa-shield"></i>
                        </span>
                    </div>

                    <!-- Agent Name & Code -->
                    <h5 class="text-white fw-bold mb-1 text-uppercase">{{ $agent->full_name }}</h5>
                    <div class="badge bg-primary px-3 py-1 font-monospace mb-3" style="font-size: 13px; letter-spacing: 1px;">
                        CODE: {{ $code }}
                    </div>

                    <!-- Details Table -->
                    <div class="rounded-3 p-3 text-start mb-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 10px;">STATE / REGION</span>
                                <strong class="text-white">{{ $agent->state ?? 'Federal Capital' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 10px;">TERMINAL IMEI</span>
                                <code class="text-info">{{ $agent->machine_imei ? substr($agent->machine_imei, 0, 8) . '...' : 'ASSIGNED' }}</code>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 10px;">DATE ISSUED</span>
                                <strong class="text-white">{{ $issueDate }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 10px;">EXPIRATION DATE</span>
                                <strong class="text-warning">{{ $expiryDate }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Footer Bar -->
                <div class="position-absolute bottom-0 w-100 p-2 text-center" style="background: rgba(0,0,0,0.5); font-size: 10px; color: #94a3b8;">
                    OFFICIAL ENROLLMENT AGENT CREDENTIAL • PROPERTY OF FUWA.NG
                </div>
            </div>
            <div class="text-center mt-2 text-white-50 small">Front Side</div>
        </div>

        <!-- BACK OF CARD -->
        <div class="col-lg-5 col-md-8">
            <div class="card border-0 rounded-4 overflow-hidden position-relative shadow-lg id-card-frame" style="background: linear-gradient(145deg, #0f172a, #1e293b); border: 2px solid rgba(59, 130, 246, 0.4) !important; min-height: 480px;">
                <!-- Magnetic Stripe Emulation -->
                <div class="w-100" style="height: 45px; background: #000; margin-top: 25px;"></div>

                <div class="p-4">
                    <div class="row align-items-center mb-3">
                        <div class="col-7">
                            <h6 class="text-white fw-bold mb-1">SECURITY & VERIFICATION</h6>
                            <p class="text-white-50" style="font-size: 11px; line-height: 1.4;">
                                Scan QR code with any mobile camera to verify active authorization status with Fuwa Identity Services.
                            </p>
                            <div class="small text-white-50 mt-2">
                                <strong>Station:</strong> {{ $agent->office_address ? \Illuminate\Support\Str::limit($agent->office_address, 35) : 'Authorized Center' }}
                            </div>
                        </div>
                        <div class="col-5 text-center">
                            @if($qrCode)
                                <div class="bg-white p-2 rounded-3 d-inline-block shadow">
                                    <img src="{{ $qrCode }}" alt="Scan QR Code" style="width: 105px; height: 105px;">
                                </div>
                                <span class="d-block text-white-50 mt-1" style="font-size: 10px;">SCAN TO VERIFY</span>
                            @endif
                        </div>
                    </div>

                    <!-- Terms & Disclaimer Box -->
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); font-size: 10px; color: #94a3b8; line-height: 1.4;">
                        This card remains the property of Fuwa and certifies that the bearer is an accredited NIN enrollment operative. If found, please return to any accredited branch or call the toll-free agent verification hotline.
                    </div>

                    <!-- Hotline Info -->
                    <div class="d-flex justify-content-between align-items-center text-white-50 small pt-2 border-top border-secondary">
                        <span><i class="fa-solid fa-headset me-1 text-primary"></i> Hotline: 0800-FUWA-AGENT</span>
                        <span>portal.fuwa.ng</span>
                    </div>
                </div>

                <div class="position-absolute bottom-0 w-100 p-2 text-center" style="background: rgba(0,0,0,0.5); font-size: 10px; color: #94a3b8;">
                    FOR ENQUIRIES: SUPPORT@FUWA.NG • WWW.FUWA.NG
                </div>
            </div>
            <div class="text-center mt-2 text-white-50 small">Back Side (Verification QR)</div>
        </div>
    </div>
</div>
@endsection
