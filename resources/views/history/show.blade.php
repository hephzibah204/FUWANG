@extends('layouts.nexus')

@section('title', 'Transaction Details - ' . config('app.name'))

@section('content')
<div class="mb-4">
    <a href="{{ route('history') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-arrow-left mr-2"></i>Back to history</a>
</div>

<div class="row">
    <!-- Left Column: Core Transaction Info -->
    <div class="col-lg-5 mb-4">
        <div class="panel-card p-4">
            <div class="h5 font-weight-bold mb-3 text-white">Transaction Summary</div>

            <div class="mb-2 text-muted small">Reference ID</div>
            <div class="font-weight-bold text-white"><code>{{ $tx->transaction_id }}</code></div>

            <div class="mt-3 mb-2 text-muted small">Service Type</div>
            <div class="font-weight-bold text-white">{{ $tx->order_type }}</div>

            <div class="mt-3 mb-2 text-muted small">Status</div>
            <div>
                @if(strtolower($tx->status) === 'success' || strtolower($tx->status) === 'successful')
                    <span class="badge bg-success text-white px-2.5 py-1">Success</span>
                @elseif(strtolower($tx->status) === 'waiting_for_review')
                    <span class="badge bg-info text-white px-2.5 py-1">Waiting Review</span>
                @elseif(strtolower($tx->status) === 'pending')
                    <span class="badge bg-warning text-dark px-2.5 py-1">Processing</span>
                @elseif(strtolower($tx->status) === 'failed')
                    <span class="badge bg-danger text-white px-2.5 py-1">Failed</span>
                @else
                    <span class="badge bg-secondary text-white px-2.5 py-1">{{ ucfirst($tx->status) }}</span>
                @endif
            </div>

            <div class="row mt-3">
                <div class="col-6">
                    <div class="text-muted small mb-1">Balance Before</div>
                    <div class="font-weight-bold text-white">₦{{ number_format((float) $tx->balance_before, 2) }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small mb-1">Balance After</div>
                    <div class="font-weight-bold text-white">₦{{ number_format((float) $tx->balance_after, 2) }}</div>
                </div>
            </div>

            <div class="mt-3 mb-2 text-muted small">Date &amp; Time</div>
            <div class="text-white-50">{{ $tx->created_at?->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <!-- Right Column: Verification, VTU, or Service Context -->
    <div class="col-lg-7 mb-4">
        <div class="panel-card p-4">
            @if($verification)
                <!-- Verification / Identity / Personalization Details -->
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white-10 pb-2 flex-wrap gap-2">
                    <div class="h5 font-weight-bold mb-0 text-white">
                        <i class="fa-solid fa-file-shield text-info mr-2"></i> Verification &amp; Report Details
                    </div>
                    <span class="badge bg-primary text-white px-2.5 py-1">{{ ucfirst(str_replace('_', ' ', $verification->service_type)) }}</span>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Target Identifier / Tracking ID</div>
                        <div class="font-weight-bold text-warning fs-5"><code>{{ $verification->identifier }}</code></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Report Reference</div>
                        <div class="font-weight-bold text-white"><code>{{ $verification->reference_id }}</code></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Processing Status</div>
                        <div>
                            @if(in_array(strtolower($verification->status), ['success', 'successful', 'active']))
                                <span class="badge bg-success text-white px-2.5 py-1"><i class="fa-solid fa-circle-check mr-1"></i> Completed / Active</span>
                            @elseif($verification->status === 'waiting_for_review')
                                <span class="badge bg-warning text-dark px-2.5 py-1"><i class="fa-solid fa-clock mr-1"></i> Under Review</span>
                            @else
                                <span class="badge bg-info text-white px-2.5 py-1">{{ ucfirst($verification->status) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Timestamp</div>
                        <div class="text-white-50">{{ $verification->created_at?->format('M d, Y h:i A') }}</div>
                    </div>
                </div>

                @if($reportUrl)
                    <div class="mt-4 pt-3 border-top border-white-10 d-flex gap-2 flex-wrap">
                        <a href="{{ $reportUrl }}" target="_blank" class="btn btn-info font-weight-bold px-3 py-2">
                            <i class="fa-solid fa-file-invoice mr-1"></i> View / Print Official Report
                        </a>
                        @if($verification->service_type === 'personalization')
                            <a href="{{ route('services.nin.suite') }}" class="btn btn-primary font-weight-bold px-3 py-2">
                                <i class="fa-solid fa-id-card mr-1"></i> Retrieve NIN Slip
                            </a>
                        @endif
                    </div>
                @endif

            @elseif($vtu)
                <!-- VTU Transaction Details -->
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white-10 pb-2">
                    <div class="h5 font-weight-bold mb-0 text-white"><i class="fa-solid fa-bolt text-warning mr-2"></i> VTU Service Details</div>
                    <span class="badge bg-secondary text-white">{{ $vtu->service_type }}</span>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Direction</div>
                        <div class="font-weight-bold text-white">{{ ucfirst($vtu->direction) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Provider Reference</div>
                        <div class="font-weight-bold text-white">{{ $vtu->provider_reference ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small mb-1">Amount</div>
                        <div class="font-weight-bold text-white">₦{{ number_format((float) $vtu->amount, 2) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small mb-1">Fee</div>
                        <div class="font-weight-bold text-white">₦{{ number_format((float) $vtu->fee, 2) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small mb-1">Total</div>
                        <div class="font-weight-bold text-white">₦{{ number_format((float) $vtu->total, 2) }}</div>
                    </div>
                </div>

                @if($vtu->request_payload)
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Request Payload</div>
                        <pre class="p-3 rounded-lg" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.10); color: rgba(255,255,255,0.85); overflow:auto; max-height: 200px;">{{ json_encode($vtu->request_payload, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif

                @if($vtu->response_payload)
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Response Payload</div>
                        <pre class="p-3 rounded-lg" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.10); color: rgba(255,255,255,0.85); overflow:auto; max-height: 200px;">{{ json_encode($vtu->response_payload, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif

                @if($vtu->error_message)
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Error Details</div>
                        <div class="text-danger font-weight-bold">{{ $vtu->error_message }}</div>
                    </div>
                @endif

            @else
                <!-- General Non-VTU / Wallet Activity -->
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white-10 pb-2">
                    <div class="h5 font-weight-bold mb-0 text-white"><i class="fa-solid fa-receipt text-primary mr-2"></i> Activity Record</div>
                    <span class="badge bg-secondary text-white">General Service</span>
                </div>
                <div class="text-white-50 mb-3">
                    This transaction was processed on the Fuwa.NG gateway for service: <strong class="text-white">{{ $tx->order_type }}</strong>.
                </div>
                <div class="p-3 rounded-3 bg-dark" style="border: 1px solid rgba(255,255,255,0.05);">
                    <div class="row text-white-50 small">
                        <div class="col-sm-6 mb-2">
                            <span>Account:</span> <strong class="text-white">{{ $tx->user_email }}</strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <span>Status:</span> <strong class="text-white">{{ ucfirst($tx->status) }}</strong>
                        </div>
                        <div class="col-sm-12">
                            <span>Logged At:</span> <span class="text-white">{{ $tx->created_at?->format('l, F j, Y - h:i:s A') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
