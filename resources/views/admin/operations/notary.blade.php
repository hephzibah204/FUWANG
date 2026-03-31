@extends('layouts.nexus')

@section('title', 'Notary Requests | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Notary Requests</h1>
            <p class="text-muted mb-0">Review legal document requests and manage statuses.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-panel p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
        <form method="GET" class="row">
            <div class="col-lg-6 col-12 mb-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search reference, document type">
            </div>
            <div class="col-lg-3 col-12 mb-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['draft','pending_stamp','completed','cancelled'] as $st)
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
                        <th>Reference</th>
                        <th>User</th>
                        <th>Doc Type</th>
                        <th>Status</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $nr)
                        <tr>
                            <td class="align-middle"><code class="text-primary">{{ $nr->reference }}</code></td>
                            <td class="align-middle">
                                <div class="text-white">{{ $nr->user?->fullname ?? '—' }}</div>
                                <div class="small text-muted">{{ $nr->user?->email ?? '—' }}</div>
                            </td>
                            <td class="align-middle text-white">{{ $nr->document_type }}</td>
                            <td class="align-middle">
                                <span class="badge badge-pill {{ $nr->status === 'completed' ? 'badge-success' : ($nr->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $nr->status }}
                                </span>
                            </td>
                            <td class="align-middle text-right text-white">₦{{ number_format((float) ($nr->amount_paid ?? 0), 2) }}</td>
                            <td class="align-middle text-right pr-4">
                                <form method="POST" action="{{ route('admin.operations.notary.status', $nr->id) }}" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-control d-inline-block" style="width: 170px; height: 34px;" onchange="this.form.submit()">
                                        @foreach(['draft','pending_stamp','completed','cancelled'] as $st)
                                            <option value="{{ $st }}" {{ $nr->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @if($nr->draft_pdf_path)
                                    <a class="btn btn-sm btn-outline-primary ml-2" href="{{ \Illuminate\Support\Facades\Storage::url($nr->draft_pdf_path) }}" target="_blank">Draft</a>
                                @endif
                                @if($nr->final_pdf_path)
                                    <a class="btn btn-sm btn-success ml-2" href="{{ \Illuminate\Support\Facades\Storage::url($nr->final_pdf_path) }}" target="_blank"><i class="fa fa-file-pdf mr-1"></i>Final</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No notary requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>
@endsection

