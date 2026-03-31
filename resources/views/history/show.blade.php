@extends('layouts.nexus')

@section('title', 'Transaction Details - ' . config('app.name'))

@section('content')
<div class="mb-4">
    <a href="{{ route('history') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-arrow-left mr-2"></i>Back to history</a>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="panel-card p-4">
            <div class="h5 font-weight-bold mb-3">Transaction</div>

            <div class="mb-2 text-muted small">Reference</div>
            <div class="font-weight-bold">{{ $tx->transaction_id }}</div>

            <div class="mt-3 mb-2 text-muted small">Service</div>
            <div class="font-weight-bold">{{ $tx->order_type }}</div>

            <div class="mt-3 mb-2 text-muted small">Status</div>
            <div class="font-weight-bold">{{ $tx->status }}</div>

            <div class="mt-3 mb-2 text-muted small">Balance Before</div>
            <div class="font-weight-bold">₦{{ number_format((float) $tx->balance_before, 2) }}</div>

            <div class="mt-3 mb-2 text-muted small">Balance After</div>
            <div class="font-weight-bold">₦{{ number_format((float) $tx->balance_after, 2) }}</div>

            <div class="mt-3 mb-2 text-muted small">Date</div>
            <div class="text-white-50">{{ $tx->created_at?->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="panel-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="h5 font-weight-bold mb-0">VTU Details</div>
                @if($vtu)
                    <span class="badge badge-secondary">{{ $vtu->service_type }}</span>
                @else
                    <span class="badge badge-secondary">N/A</span>
                @endif
            </div>

            @if(!$vtu)
                <div class="text-white-50">No VTU details are recorded for this transaction.</div>
            @else
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Direction</div>
                        <div class="font-weight-bold">{{ $vtu->direction }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted small mb-1">Provider Reference</div>
                        <div class="font-weight-bold">{{ $vtu->provider_reference ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small mb-1">Amount</div>
                        <div class="font-weight-bold">₦{{ number_format((float) $vtu->amount, 2) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small mb-1">Fee</div>
                        <div class="font-weight-bold">₦{{ number_format((float) $vtu->fee, 2) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small mb-1">Total</div>
                        <div class="font-weight-bold">₦{{ number_format((float) $vtu->total, 2) }}</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-muted small mb-2">Request Payload</div>
                    <pre class="p-3 rounded-lg" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.10); color: rgba(255,255,255,0.85); overflow:auto; max-height: 260px;">{{ json_encode($vtu->request_payload, JSON_PRETTY_PRINT) }}</pre>
                </div>

                <div class="mt-3">
                    <div class="text-muted small mb-2">Response Payload</div>
                    <pre class="p-3 rounded-lg" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.10); color: rgba(255,255,255,0.85); overflow:auto; max-height: 260px;">{{ json_encode($vtu->response_payload, JSON_PRETTY_PRINT) }}</pre>
                </div>

                @if($vtu->error_message)
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Error</div>
                        <div class="text-danger font-weight-bold">{{ $vtu->error_message }}</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

