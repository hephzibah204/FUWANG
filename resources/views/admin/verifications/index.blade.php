@extends('layouts.nexus')

@section('title', 'Verification Vault | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-5">
            <h1 class="h3 font-weight-bold mb-1">Verification Vault</h1>
            <p class="text-muted mb-0">Audit user verification results, modification requests, and reports.</p>
        </div>
        <div class="col-md-7 text-md-right mt-3 mt-md-0">
            <a href="{{ route('admin.verifications.nin_modifications.index') }}" class="btn btn-warning mr-2">
                <i class="fa-solid fa-id-card mr-1"></i> NIN Modifications
            </a>
            <a href="{{ route('admin.verifications.ipe_clearance.index') }}" class="btn btn-outline-info mr-2">
                <i class="fa-solid fa-certificate mr-1"></i> IPE Clearance
            </a>
            <a href="{{ route('admin.verifications.nin_validation.index') }}" class="btn btn-outline-light">
                <i class="fa-solid fa-user-check mr-1"></i> NIN Validation
            </a>
        </div>
    </div>

    <div class="admin-panel p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
        <form method="GET" class="row">
            <div class="col-lg-4 col-12 mb-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search ref, identifier, provider, service">
            </div>
            <div class="col-lg-3 col-12 mb-3">
                <select name="service_type" class="form-control">
                    <option value="">All Services</option>
                    @foreach($serviceTypes as $svc)
                        <option value="{{ $svc }}" {{ request('service_type') === $svc ? 'selected' : '' }}>{{ strtoupper($svc) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-12 mb-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    @foreach(['success' => 'Success', 'pending' => 'Pending', 'failed' => 'Failed'] as $k => $v)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-6 mb-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-lg-1 col-6 mb-3">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-lg-1 col-12 mb-3">
                <button class="btn btn-primary w-100" type="submit"><i class="fa fa-search"></i></button>
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
                        <th>Service</th>
                        <th>Identifier</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $res)
                        <tr>
                            <td class="align-middle font-weight-bold"><code class="text-primary">{{ $res->reference_id }}</code></td>
                            <td class="align-middle">
                                <div class="text-white">{{ $res->user?->fullname ?? '—' }}</div>
                                <div class="small text-muted">{{ $res->user?->email ?? '—' }}</div>
                            </td>
                            <td class="align-middle text-white">{{ strtoupper($res->service_type) }}</td>
                            <td class="align-middle text-white-50">{{ $res->identifier }}</td>
                            <td class="align-middle">
                                <span class="badge badge-pill {{ $res->status === 'success' ? 'badge-success' : ($res->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $res->status }}
                                </span>
                            </td>
                            <td class="align-middle text-muted small">{{ $res->created_at->format('M d, Y H:i') }}</td>
                            <td class="align-middle text-right pr-4">
                                @if($res->service_type === 'nin_modification')
                                    <a href="{{ route('admin.verifications.nin_modifications.show', $res->id) }}" class="btn btn-sm btn-warning font-weight-bold">
                                        <i class="fa-solid fa-file-pen mr-1"></i> Review
                                    </a>
                                @else
                                    <a href="{{ route('admin.verifications.show', $res->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('admin.verifications.report', $res->id) }}" class="btn btn-sm btn-success"><i class="fa fa-file-pdf mr-1"></i> PDF</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No verification results found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $results->links() }}
    </div>
</div>
@endsection

