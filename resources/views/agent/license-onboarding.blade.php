@extends('layouts.nexus')

@section('title', 'Station Onboarding | Fuwa.NG')

@section('content')
<div class="container py-4" style="max-width: 860px;">
    <div class="mb-4">
        <span class="badge bg-success font-monospace fw-bold mb-2">LICENSE PAYMENT CONFIRMED</span>
        <h3 class="text-white fw-bold mb-1">Station Onboarding</h3>
        <p class="text-white-50 mb-3">Your payment is confirmed. Follow the onboarding process below, step by step. For anything about your onboarding, message the onboarding desk on WhatsApp.</p>
        <a href="{{ $activationWhatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-lg rounded-pill px-4 fw-bold">
            <i class="fa-brands fa-whatsapp me-2"></i>Message {{ $onboardingPhoneDisplay }} on WhatsApp
        </a>
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

    <!-- ACTIVATION REQUEST (recorded in-app, then WhatsApp) -->
    <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <h5 class="text-white fw-bold mb-2"><i class="fa-solid fa-paper-plane text-warning me-2"></i>Send Your Activation Request</h5>
        @if(($activationStatus ?? null) === 'approved')
            <div class="alert border-0 rounded-3 p-3 mb-0" style="background: rgba(34,197,94,0.15); color: #bbf7d0;">
                <i class="fa-solid fa-circle-check me-1"></i><strong>Your activation has been approved.</strong> Continue with the steps below.
            </div>
        @elseif(!empty($activationRequestedAt))
            <div class="alert border-0 rounded-3 p-3 mb-2" style="background: rgba(34,197,94,0.15); color: #bbf7d0;">
                <i class="fa-solid fa-circle-check me-1"></i><strong>Activation request recorded</strong> on {{ \Carbon\Carbon::parse($activationRequestedAt)->format('d M Y, h:i A') }}. Your activation code is <strong class="font-monospace">{{ $activationCode }}</strong>. If you have not messaged the desk yet, do so now so they can match and approve it.
            </div>
            <p class="text-white-50 mb-0"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Message us from the <strong class="text-white">phone number you registered with</strong>. Requests from a different number will not be processed.</p>
        @else
            <p class="text-white-50 mb-3">First record your request here, then message the desk on WhatsApp. Your request carries a personal activation code (<strong class="text-white font-monospace">{{ $activationCode }}</strong>) that only you can see in this account. The desk approves your activation only when the code, your registered phone number and your IMEI all match our records, so keep the code to yourself.</p>
            <form action="{{ route('agent.license.onboarding_activation') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                    <i class="fa-solid fa-paper-plane me-2"></i>Send Activation Request
                </button>
            </form>
            <p class="text-white-50 mt-3 mb-0"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Message us from the <strong class="text-white">phone number you registered with</strong>. Requests from a different number will not be processed.</p>
        @endif
    </div>

    <!-- DEVICE / IMEI -->
    <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <h5 class="text-white fw-bold mb-2"><i class="fa-solid fa-mobile-screen-button text-warning me-2"></i>Get Your Device Ready</h5>
        <p class="text-white-50 mb-2">Your device IMEI was collected at registration and is what we send to NIMC during this process. Have the device with you, charged and switched on. Dial <strong class="text-white">*#06#</strong> on the device to see its IMEI and confirm it matches your profile:</p>
        @if($agent->machine_imei)
            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.04);">
                <small class="text-white-50 d-block">IMEI on your profile</small>
                <span class="text-white font-monospace fw-bold">{{ $agent->machine_imei }}</span>
            </div>
        @else
            <div class="alert border-0 rounded-3 p-3 mb-0" style="background: rgba(234,179,8,0.15); color: #fde68a;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>No IMEI on your profile yet. If you registered without a device, get the device ready now and add its IMEI from your <a href="{{ route('agent.onboarding.index') }}" class="text-warning fw-bold text-decoration-underline">agent onboarding page</a> before sending your activation request.
            </div>
        @endif
    </div>

    <!-- THE PROCESS -->
    <h5 class="text-white fw-bold mb-3">The Onboarding Process</h5>

    @php
        $phases = [
            'Activation' => [
                ['You', 'Send your activation request to us on WhatsApp.', true],
                ['Fuwa', 'We approve your activation request.', false],
                ['Fuwa', 'We send your workflow number to NIMC.', false],
                ['NIMC', 'NIMC approves the workflow.', false],
            ],
            'Biometrics Onboarding' => [
                ['You', 'Log in and do your biometrics onboarding.', false],
                ['NIMC', 'NIMC approves it automatically.', false],
            ],
            'Test Enrolments' => [
                ['You', 'Complete three test enrolments.', false],
                ['Your device', 'The three test enrolments sync on your device.', false],
            ],
            'Switch to Production' => [
                ['You', 'Toggle the switch to production on your device.', false],
                ['You', 'Send us your switch to production request on WhatsApp.', 'production'],
                ['Fuwa', 'We send your IMEI to NIMC for the switch to production.', false],
                ['NIMC', 'NIMC switches you to production. You are live.', false],
            ],
        ];
        $stepNumber = 0;
    @endphp

    @foreach($phases as $phase => $steps)
        <p class="text-warning fw-bold mb-2 mt-4" style="letter-spacing: 0.02em;">{{ $phase }}</p>
        @foreach($steps as [$actor, $text, $cta])
            @php $stepNumber++; @endphp
            <div class="card border-0 rounded-4 p-3 mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="rounded-circle text-center fw-bold text-dark flex-shrink-0" style="background: #fbbf24; width: 34px; height: 34px; line-height: 34px;">{{ $stepNumber }}</div>
                    <div class="flex-grow-1" style="min-width: 200px;">
                        <span class="badge bg-secondary mb-1">{{ $actor }}</span>
                        <p class="text-white mb-0">{{ $text }}</p>
                    </div>
                    @if($cta === true)
                        <a href="{{ $activationWhatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success rounded-pill px-3 fw-bold flex-shrink-0">
                            <i class="fa-brands fa-whatsapp me-1"></i>Send Activation Request
                        </a>
                    @elseif($cta === 'production')
                        <a href="{{ $productionWhatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success rounded-pill px-3 fw-bold flex-shrink-0">
                            <i class="fa-brands fa-whatsapp me-1"></i>Send Switch Request
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    @endforeach

    <!-- WHATSAPP CTA -->
    <div class="card border-0 rounded-4 p-4 mt-4 mb-3 text-center" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25) !important;">
        <h5 class="text-white fw-bold mb-2">Onboarding Desk</h5>
        <p class="text-white-50 mb-3">Message this number for your onboarding process: <strong class="text-white">{{ $onboardingPhoneDisplay }}</strong><br>Use the phone number you registered with. Requests from a different number will not be processed.</p>
        <div>
            <a href="{{ $activationWhatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-lg rounded-pill px-4 fw-bold">
                <i class="fa-brands fa-whatsapp me-2"></i>Chat on WhatsApp
            </a>
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
