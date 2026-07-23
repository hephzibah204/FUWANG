@extends('layouts.postoffice')

@section('title', 'My Earnings')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">My Earnings</h1>

    {{-- Totals Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Earned</div>
                    <div class="fs-4 fw-bold text-success">
                        ₦{{ number_format($totals['earned_total'], 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Paid Out</div>
                    <div class="fs-4 fw-bold text-primary">
                        ₦{{ number_format($totals['paid_total'], 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Pending Commission</div>
                    <div class="fs-4 fw-bold text-warning">
                        ₦{{ number_format($totals['pending_total'], 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delivered Orders Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Delivered Orders</div>
        <div class="card-body p-0">
            @if($delivered->isEmpty())
                <div class="p-4 text-muted text-center">No delivered orders yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking ID</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Paid Out</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($delivered as $order)
                            <tr>
                                <td>
                                    <span class="fw-mono text-primary">{{ $order->tracking_id }}</span>
                                </td>
                                <td>₦{{ number_format($order->agent_commission_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-success">Delivered</span>
                                </td>
                                <td>
                                    @if($order->agent_paid_at)
                                        <span class="badge bg-primary">Paid</span>
                                        <small class="text-muted d-block">{{ \Carbon\Carbon::parse($order->agent_paid_at)->format('d M Y') }}</small>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $order->updated_at?->format('d M Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $delivered->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
