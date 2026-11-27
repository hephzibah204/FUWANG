@extends('layouts.nexus')

@section('title', 'Station Onboarding Instructions | Fuwa.NG')

@section('content')
<div class="container py-4" style="max-width: 860px;">
    <div class="mb-4">
        <span class="badge bg-success font-monospace fw-bold mb-2">LICENSE PAYMENT CONFIRMED</span>
        <h3 class="text-white fw-bold mb-1">Station Onboarding Instructions</h3>
        <p class="text-white-50 mb-0">Your station license is active. Read these instructions carefully and complete the three steps below to finish your onboarding.</p>
    </div>

    @if(session('success'))
        <div class="alert border-0 rounded-3 p-3 mb-3" style="background: rgba(34,197,94,0.15); color: #bbf7d0;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert border-0 rounded-3 p-3 mb-3" style="background: rgba(239,68,68,0.15); color: #fca5a5;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- STEP 1: DEVICE -->
    <div class="card border-0 rounded-4 p-4 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-start gap-3">
            <div class="rounded-circle text-center fw-bold text-dark flex-shrink-0" style="background: #fbbf24; width: 38px; height: 38px; line-height: 38px;">1</div>
            <div class="flex-grow-1">
                <h5 class="text-white fw-bold mb-2">Get Your Device Ready</h5>
                <p class="text-white-50 mb-2">Your enrollment device is the machine you will work with at your station. Before anything else, get it ready:</p>
                <ul class="text-white-50 mb-3">
                    <li>Have the device with you, charged and switched on.</li>
                    <li>Dial <strong class="text-white">*#06#</strong> on the device to see its IMEI.</li>
                    <li>Confirm the IMEI matches the one on your agent profile below.</li>
                </ul>
                @if($agent->machine_imei)
                    <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04);">
                        <small class="text-white-50 d-block">IMEI on your profile</small>
                        <span class="text-white font-monospace fw-bold">{{ $agent->machine_imei }}</span>
                    </div>
                @else
                    <div class="alert border-0 rounded-3 p-3 mb-0" style="background: rgba(234,179,8,0.15); color: #fde68a;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>No IMEI on your profile yet. If you registered without a device, get the device ready now and add its IMEI from your <a href="{{ route('agent.onboarding.index') }}" class="text-warning fw-bold text-decoration-underline">agent onboarding page</a>.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- STEP 2: VPN -->
    <div class="card border-0 rounded-4 p-4 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-start gap-3">
            <div class="rounded-circle text-center fw-bold text-dark flex-shrink-0" style="background: #fbbf24; width: 38px; height: 38px; line-height: 38px;">2</div>
            <div class="flex-grow-1">
                <h5 class="text-white fw-bold mb-2">Request Your VPN</h5>
                <p class="text-white-50 mb-3">Station work runs through the Fuwa VPN, and your access must be provisioned before you can start. Request your VPN below and the admin desk will send your VPN details to you.</p>
                @if($vpnRequestedAt)
                    <div class="alert border-0 rounded-3 p-3 mb-0" style="background: rgba(34,197,94,0.15); color: #bbf7d0;">
                        <i class="fa-solid fa-circle-check me-1"></i><strong>VPN requested</strong> on {{ \Carbon\Carbon::parse($vpnRequestedAt)->format('d M Y, h:i A') }}. The admin desk will send your VPN details; watch your phone and email.
                    </div>
                @else
                    <form action="{{ route('agent.license.onboarding_vpn') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                            <i class="fa-solid fa-paper-plane me-2"></i>Request My VPN
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- STEP 3: ADMIN ONBOARDING DETAILS -->
    <div class="card border-0 rounded-4 p-4 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-start gap-3">
            <div class="rounded-circle text-center fw-bold text-dark flex-shrink-0" style="background: #fbbf24; width: 38px; height: 38px; line-height: 38px;">3</div>
            <div class="flex-grow-1">
                <h5 class="text-white fw-bold mb-2">Get Your Admin Onboarding Details</h5>
                <p class="text-white-50 mb-3">Your onboarding is completed by the admin operations desk. Once your device is ready and your VPN request is sent, reach the desk with these details:</p>
                <table class="table table-dark table-sm mb-3" style="background: transparent;">
                    <tr><td class="text-white-50" style="width: 35%;">Your Coordinator</td><td class="text-white fw-bold">{{ $coordinatorName }}</td></tr>
                    <tr><td class="text-white-50">Phone / WhatsApp</td><td class="text-white font-monospace">{{ $supportPhone }}</td></tr>
                    <tr><td class="text-white-50">Email</td><td class="text-white">{{ $supportEmail }}</td></tr>
                </table>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-brands fa-whatsapp me-2"></i>Message the Desk on WhatsApp
                    </a>
                    <a href="mailto:{{ $supportEmail }}" class="btn btn-outline-light rounded-pill px-4">
                        <i class="fa-solid fa-envelope me-2"></i>Email the Desk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- READ CONFIRMATION -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        @if($readAt)
            <p class="text-white-50 mb-0"><i class="fa-solid fa-circle-check text-success me-2"></i>You confirmed reading these instructions on {{ \Carbon\Carbon::parse($readAt)->format('d M Y, h:i A') }}. Head to your <a href="{{ route('agent.dashboard') }}" class="text-warning fw-bold">agent dashboard</a> to continue.</p>
        @else
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <p class="text-white-50 mb-0">Finished reading? Confirm below so the desk knows you are ready.</p>
                <form action="{{ route('agent.license.onboarding_read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                        <i class="fa-solid fa-check me-2"></i>I Have Read These Instructions
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
