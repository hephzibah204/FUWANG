@extends('layouts.nexus')

@section('title', 'Transactions | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Transactions</h1>
            <p class="text-muted mb-0">Search and audit platform financial activity.</p>
        </div>
    </div>

    <div class="admin-panel p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
        <form method="GET" class="row">
            <div class="col-lg-4 col-12 mb-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search email, ref, order type">
            </div>
            <div class="col-lg-2 col-12 mb-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['success' => 'Success', 'pending' => 'Pending', 'failed' => 'Failed'] as $k => $v)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-6 mb-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-lg-2 col-6 mb-3">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-lg-2 col-12 mb-3">
                <button class="btn btn-primary w-100" type="submit"><i class="fa fa-search mr-2"></i>Filter</button>
            </div>
        </form>

        <div class="d-flex flex-wrap mt-2" style="gap: 10px;">
            <span class="badge badge-success">Success: {{ (int) ($statusCounts['success'] ?? 0) }}</span>
            <span class="badge badge-warning">Pending: {{ (int) ($statusCounts['pending'] ?? 0) }}</span>
            <span class="badge badge-danger">Failed: {{ (int) ($statusCounts['failed'] ?? 0) }}</span>
        </div>
    </div>

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>User</th>
                        <th>Order Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right pr-4">Delta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php $delta = (float) ($tx->balance_before - $tx->balance_after); @endphp
                        <tr>
                            <td class="align-middle font-weight-bold text-white"><code class="text-primary">{{ $tx->transaction_id }}</code></td>
                            <td class="align-middle">
                                <div class="text-white">{{ $tx->user_email }}</div>
                            </td>
                            <td class="align-middle text-white">{{ $tx->order_type }}</td>
                            <td class="align-middle">
                                <span class="badge badge-pill {{ $tx->status === 'success' ? 'badge-success' : ($tx->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $tx->status }}
                                </span>
                            </td>
                            <td class="align-middle text-muted small">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            <td class="align-middle text-right pr-4 text-white">₦{{ number_format($delta, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
@endsection

