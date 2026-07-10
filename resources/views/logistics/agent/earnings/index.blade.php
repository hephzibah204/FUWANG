@extends('layouts.postoffice')

@section('title', 'Earnings')

@section('content')
@include('logistics.agent.partials.nav', ['title' => 'Earnings', 'subtitle' => 'Commission summary and delivered orders'])

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i class="fa fa-sack-dollar fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Total earned</small>
                <h4 class="font-weight-bold mb-0">₦{{ number_format($totals['earned_total'] ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); color: var(--po-accent);">
                <i class="fa fa-circle-check fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Total paid</small>
                <h4 class="font-weight-bold mb-0 text-primary">₦{{ number_format($totals['paid_total'] ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="glass-card p-4 d-flex align-items-center">
            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); color: var(--po-primary);">
                <i class="fa fa-hourglass-half fa-lg"></i>
            </div>
            <div>
                <small class="text-white-50 d-block">Pending (accepted)</small>
                <h4 class="font-weight-bold mb-0" style="color: var(--po-primary);">₦{{ number_format($totals['pending_total'] ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="glass-card overflow-hidden">
    <div class="p-4 border-bottom border-white-10 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold">Delivered Orders</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="color: #eee;">
            <thead class="bg-black-10 small text-uppercase" style="letter-spacing: 1px;">
                <tr>
                    <th class="border-0 px-4">Tracking ID</th>
                    <th class="border-0">Delivered</th>
                    <th class="border-0">Commission</th>
                    <th class="border-0">Paid</th>
                    <th class="border-0 text-right px-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($delivered as $o)
                    <tr class="border-white-05">
                        <td class="px-4"><code class="text-primary">{{ $o->tracking_id }}</code></td>
                        <td class="text-white-50 small">{{ $o->last_status_updated_at ? $o->last_status_updated_at->format('M d, Y') : $o->updated_at->format('M d, Y') }}</td>
                        <td class="font-weight-bold">₦{{ number_format((float) ($o->agent_commission_amount ?? 0), 2) }}</td>
                        <td>
                            @if($o->agent_paid_at)
                                <span class="badge badge-pill badge-success px-3">PAID</span>
                            @else
                                <span class="badge badge-pill badge-dark px-3">UNPAID</span>
                            @endif
                        </td>
                        <td class="text-right px-4">
                            <a href="{{ route('logistics.agent.orders.show', $o->id) }}" class="btn btn-sm btn-outline-glass">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white-50 italic">
                            No delivered orders yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($delivered->hasPages())
        <div class="p-4 border-top border-white-10">
            {{ $delivered->links() }}
        </div>
    @endif
</div>
@endsection

