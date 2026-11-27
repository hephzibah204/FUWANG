@extends('layouts.nexus')

@section('title', 'Match a Paystack Transaction | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.licenses.index') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Station Licenses
                </a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-warning text-xs fw-semibold">Match a Transaction</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-magnifying-glass-dollar text-warning me-2"></i>Match a Paystack Transaction</h3>
            <p class="text-white-50 mb-0">Paste a Paystack transaction number, session ID, or payment reference to see Fuwa's license record and Paystack's own record side by side. Built for payment disputes.</p>
        </div>
    </div>

    <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <form action="{{ route('admin.agents.licenses.txn_lookup') }}" method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" value="{{ $query }}" class="form-control bg-dark text-white border-secondary font-monospace" style="max-width: 460px;" placeholder="e.g. 3847562019 or LIC-20261127-12-ABC123" required>
            <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4"><i class="fa-solid fa-search me-1"></i>Match Transaction</button>
        </form>
        <small class="text-white-50 d-block mt-2">Tip: the transaction number is on the agent's Paystack receipt and in the Paystack dashboard; our own reference starts with LIC-.</small>
    </div>

    @if($query !== '')
        @if($verdict === 'match')
            <div class="alert border-0 rounded-3 p-3 mb-4" style="background: rgba(34,197,94,0.15); color: #bbf7d0;">
                <i class="fa-solid fa-circle-check me-2"></i><strong>Transaction Matched.</strong> Fuwa's record and Paystack's record agree on this payment.
            </div>
        @elseif($verdict === 'mismatch')
            <div class="alert border-0 rounded-3 p-3 mb-4" style="background: rgba(239,68,68,0.15); color: #fca5a5;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Records Do Not Fully Match:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($verdictNotes as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif($verdict === 'paystack_only')
            <div class="alert border-0 rounded-3 p-3 mb-4" style="background: rgba(234,179,8,0.15); color: #fde68a;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Found on Paystack, but no Fuwa license record matches it.</strong> The customer email on the Paystack record below may identify the agent. If it is a genuine license payment, accredit the agent manually from the Station Licenses Desk.
            </div>
        @elseif($verdict === 'fuwa_only')
            <div class="alert border-0 rounded-3 p-3 mb-4" style="background: rgba(234,179,8,0.15); color: #fde68a;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Found in Fuwa, but Paystack returned no matching transaction.</strong> @if($paystackError) ({{ $paystackError }}) @endif Check the identifier and try again.
            </div>
        @else
            <div class="alert border-0 rounded-3 p-3 mb-4" style="background: rgba(239,68,68,0.15); color: #fca5a5;">
                <i class="fa-solid fa-circle-xmark me-2"></i><strong>No matching transaction found</strong> in Fuwa's license records or on Paystack for "{{ $query }}".
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-database text-info me-2"></i>Fuwa Record</h5>
                    @if($local)
                        <table class="table table-dark table-sm mb-0" style="background: transparent;">
                            <tr><td class="text-white-50" style="width: 40%;">Agent</td><td class="text-white fw-bold">@if($local->agent) <a href="{{ route('admin.agents.show', $local->agent->id) }}" class="text-info text-decoration-none">{{ $local->agent->full_name }}</a> @else - @endif</td></tr>
                            <tr><td class="text-white-50">Agent Code</td><td class="text-white font-monospace">{{ $local->agent->company_agent_code ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Payer Email</td><td class="text-white">{{ $local->user->email ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Amount</td><td class="text-white fw-bold">₦{{ number_format((float) $local->amount, 2) }}</td></tr>
                            <tr><td class="text-white-50">Status</td><td class="text-white text-uppercase">{{ $local->status }}</td></tr>
                            <tr><td class="text-white-50">Method</td><td class="text-white">{{ $local->payment_method }}</td></tr>
                            <tr><td class="text-white-50">Fuwa Reference</td><td class="text-info font-monospace">{{ $local->reference }}</td></tr>
                            <tr><td class="text-white-50">Paystack Transaction ID</td><td class="text-warning font-monospace">{{ $local->gateway_reference ?: '-' }}</td></tr>
                            <tr><td class="text-white-50">Recorded</td><td class="text-white">{{ $local->created_at?->format('d M Y, h:i A') }}</td></tr>
                        </table>
                    @elseif($agent)
                        <p class="text-white-50 mb-2">No license transaction row matched, but this agent's license carries that reference:</p>
                        <table class="table table-dark table-sm mb-0" style="background: transparent;">
                            <tr><td class="text-white-50" style="width: 40%;">Agent</td><td class="text-white fw-bold"><a href="{{ route('admin.agents.show', $agent->id) }}" class="text-info text-decoration-none">{{ $agent->full_name }}</a></td></tr>
                            <tr><td class="text-white-50">License Status</td><td class="text-white text-uppercase">{{ $agent->license_status ?: 'unpaid' }}</td></tr>
                            <tr><td class="text-white-50">Method</td><td class="text-white">{{ $agent->license_payment_method ?: '-' }}</td></tr>
                            <tr><td class="text-white-50">Amount Paid</td><td class="text-white">₦{{ number_format((float) ($agent->license_fee_paid ?? 0), 2) }}</td></tr>
                            <tr><td class="text-white-50">Reference</td><td class="text-info font-monospace">{{ $agent->license_payment_reference }}</td></tr>
                        </table>
                    @else
                        <p class="text-white-50 mb-0">No Fuwa license record carries this identifier.</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-credit-card text-warning me-2"></i>Paystack Record (live)</h5>
                    @if($paystack)
                        <table class="table table-dark table-sm mb-0" style="background: transparent;">
                            <tr><td class="text-white-50" style="width: 40%;">Transaction ID</td><td class="text-warning font-monospace fw-bold">{{ $paystack['id'] ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Reference</td><td class="text-info font-monospace">{{ $paystack['reference'] ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Status</td><td class="text-white text-uppercase">{{ $paystack['status'] ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Amount</td><td class="text-white fw-bold">₦{{ number_format(((int) ($paystack['amount'] ?? 0)) / 100, 2) }}</td></tr>
                            <tr><td class="text-white-50">Channel</td><td class="text-white">{{ $paystack['channel'] ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Customer Email</td><td class="text-white">{{ $paystack['customer']['email'] ?? '-' }}</td></tr>
                            <tr><td class="text-white-50">Paid At</td><td class="text-white">{{ isset($paystack['paid_at']) ? \Carbon\Carbon::parse($paystack['paid_at'])->format('d M Y, h:i A') : '-' }}</td></tr>
                            @if(!empty($paystack['gateway_response']))
                                <tr><td class="text-white-50">Gateway Response</td><td class="text-white">{{ $paystack['gateway_response'] }}</td></tr>
                            @endif
                        </table>
                    @else
                        <p class="text-white-50 mb-0">{{ $paystackError ?: 'Paystack returned no record for this identifier.' }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
