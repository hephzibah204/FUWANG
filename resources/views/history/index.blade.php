@extends('layouts.nexus')

@section('title', 'Transaction History - Fuwa.NG')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-white mb-1 fw-bold">Transaction History</h3>
        <p class="text-white-50">View all your recent activities and logs.</p>
    </div>
</div>

<div class="card glass-card border-0 rounded-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="card-body p-0">
        @if($transactions->isEmpty())
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fa fa-history fa-3x text-white-50 opacity-50"></i>
                </div>
                <h5 class="text-white">No transactions found</h5>
                <p class="text-white-50">When you make a transaction, it will appear here.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-borderless table-hover mb-0 text-white" style="background: transparent;">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="py-3 px-4 text-white-50 font-weight-normal border-bottom-0">Reference / Date</th>
                            <th class="py-3 px-4 text-white-50 font-weight-normal border-bottom-0">Service Type</th>
                            <th class="py-3 px-4 text-white-50 font-weight-normal border-bottom-0 text-right">Amount</th>
                            <th class="py-3 px-4 text-white-50 font-weight-normal border-bottom-0 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                            @php
                                $isCredit = strpos(strtolower($tx->order_type), 'fund') !== false || ($tx->balance_after > $tx->balance_before);
                                $amountRaw = abs($tx->balance_before - $tx->balance_after);
                                $sign = $isCredit ? '+' : '-';
                                $colorClass = $isCredit ? 'text-success' : 'text-danger';
                                $bgClass = $isCredit ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)';
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td class="py-3 px-4 align-middle">
                                    <h6 class="mb-1 text-white font-weight-bold">
                                        <a href="{{ route('history.show', $tx->transaction_id) }}" class="text-white text-decoration-none">{{ $tx->transaction_id }}</a>
                                    </h6>
                                    <small class="text-white-50">{{ $tx->created_at->format('M d, Y h:i A') }}</small>
                                </td>
                                <td class="py-3 px-4 align-middle">
                                    <span class="text-light">{{ $tx->order_type }}</span>
                                </td>
                                <td class="py-3 px-4 align-middle text-right">
                                    <h6 class="mb-0 {{ $colorClass }} font-weight-bold">
                                        {{ $sign }}₦{{ number_format($amountRaw, 2) }}
                                    </h6>
                                </td>
                                <td class="py-3 px-4 align-middle text-center">
                                    @if($tx->status == 'success' || strtolower($tx->status) == 'successful')
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(40,167,69,0.15); color: #28a745; border: 1px solid rgba(40,167,69,0.3);">Success</span>
                                    @elseif($tx->status == 'waiting_for_review')
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(23,162,184,0.15); color: #17a2b8; border: 1px solid rgba(23,162,184,0.3);">Waiting Review</span>
                                    @elseif($tx->status == 'pending')
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,193,7,0.15); color: #ffc107; border: 1px solid rgba(255,193,7,0.3);">Processing</span>
                                    @elseif($tx->status == 'failed')
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(220,53,69,0.15); color: #dc3545; border: 1px solid rgba(220,53,69,0.3);">Failed</span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,193,7,0.15); color: #ffc107; border: 1px solid rgba(255,193,7,0.3);">{{ ucfirst($tx->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 d-flex justify-content-center border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                {{ $transactions->links('pagination::bootstrap-4') }}
            </div>
            <style>
                .pagination .page-link { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff; }
                .pagination .page-item.active .page-link { background: var(--clr-primary); border-color: var(--clr-primary); }
                .pagination .page-item.disabled .page-link { background: rgba(255,255,255,0.02); color: #666; border-color: rgba(255,255,255,0.05); }
            </style>
        @endif
    </div>
</div>
@endsection
