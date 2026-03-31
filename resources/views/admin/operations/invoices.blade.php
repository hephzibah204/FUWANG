@extends('layouts.nexus')

@section('title', 'Invoices | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Invoices</h1>
            <p class="text-muted mb-0">Review generated invoices and manage statuses.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-panel p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
        <form method="GET" class="row">
            <div class="col-lg-6 col-12 mb-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search invoice number, client name/email">
            </div>
            <div class="col-lg-3 col-12 mb-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['draft','sent','paid','overdue','cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-12 mb-3">
                <button class="btn btn-primary w-100" type="submit"><i class="fa fa-search mr-2"></i>Filter</button>
            </div>
        </form>
    </div>

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                        <th>Date</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="align-middle text-white font-weight-bold">
                                <code class="text-primary">{{ $inv->invoice_number }}</code>
                            </td>
                            <td class="align-middle">
                                <div class="text-white">{{ $inv->client_name }}</div>
                                <div class="small text-muted">{{ $inv->client_email }}</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-pill {{ $inv->status === 'paid' ? 'badge-success' : ($inv->status === 'overdue' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="align-middle text-right text-white">₦{{ number_format((float) $inv->total_amount, 2) }}</td>
                            <td class="align-middle text-muted small">{{ $inv->created_at->format('M d, Y') }}</td>
                            <td class="align-middle text-right pr-4">
                                <form method="POST" action="{{ route('admin.operations.invoices.status', $inv->id) }}" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-control d-inline-block" style="width: 140px; height: 34px;" onchange="this.form.submit()">
                                        @foreach(['draft','sent','paid','overdue','cancelled'] as $st)
                                            <option value="{{ $st }}" {{ $inv->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @if($inv->pdf_path)
                                    <a class="btn btn-sm btn-success ml-2" href="{{ \Illuminate\Support\Facades\Storage::url($inv->pdf_path) }}" target="_blank"><i class="fa fa-file-pdf mr-1"></i>PDF</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</div>
@endsection

