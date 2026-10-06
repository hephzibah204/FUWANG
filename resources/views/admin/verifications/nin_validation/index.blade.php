@extends('layouts.nexus')

@section('title', 'NIN Validation Requests | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="font-weight-bold text-white mb-1"><i class="fa-solid fa-file-shield text-primary me-2"></i>NIN Validation Requests Desk</h3>
            <p class="text-white-50 small mb-0">Review requests, approve validated records, reject with automatic wallet refund, and bulk manage reports.</p>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <!-- Mode Switcher -->
            <div class="d-flex align-items-center bg-dark p-1 px-2.5 rounded-pill border border-secondary">
                <span class="small text-white-50 me-2"><i class="fa-solid fa-sliders text-warning me-1"></i>Engine:</span>
                <form method="POST" action="{{ route('admin.verifications.nin_validation.mode') }}" class="d-inline-flex align-items-center m-0">
                    @csrf
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="submit" name="mode" value="manual" class="btn btn-sm py-1 px-3 {{ ($currentMode ?? 'manual') === 'manual' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary text-white-50 border-0' }}">
                            <i class="fa-solid fa-user-check me-1"></i> Manual Reporting
                        </button>
                        <button type="submit" name="mode" value="robosttech" class="btn btn-sm py-1 px-3 {{ ($currentMode ?? 'manual') === 'robosttech' ? 'btn-success font-weight-bold shadow-sm' : 'btn-outline-secondary text-white-50 border-0' }}">
                            <i class="fa-solid fa-robot me-1"></i> Robosttech API
                        </button>
                    </div>
                </form>
            </div>
            <!-- Bulk Export Button -->
            <a href="{{ route('admin.verifications.nin_validation.export', request()->all()) }}" class="btn btn-outline-success font-weight-bold">
                <i class="fa-solid fa-file-arrow-down me-1"></i> Download CSV ({{ $requests->total() }})
            </a>
            <!-- Bulk Upload Results Modal Trigger -->
            <button type="button" class="btn btn-primary font-weight-bold" data-toggle="modal" data-target="#bulkUploadModal" onclick="$('#bulkUploadModal').modal('show')">
                <i class="fa-solid fa-file-arrow-up me-1"></i> Upload Bulk Results
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3 shadow-sm">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger text-white bg-danger border-0 mb-4 rounded-3 shadow-sm">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card glass-card border-0 shadow-sm rounded-4 mb-4 p-3">
        <form method="GET" action="{{ route('admin.verifications.nin_validation.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-dark text-white border-secondary" placeholder="Search by NIN, reference, user, or reason...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select bg-dark text-white border-secondary">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="waiting_for_review" @selected(request('status') === 'waiting_for_review')>Under Review</option>
                    <option value="successful" @selected(request('status') === 'successful')>Successful (Validated)</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed (Rejected / Refunded)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.verifications.nin_validation.index') }}" class="btn btn-outline-light" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th>Reference</th>
                            <th>Engine</th>
                            <th>NIN Number</th>
                            <th>Reason / Category</th>
                            <th>User</th>
                            <th>Status &amp; Result</th>
                            <th>Admin Remark</th>
                            <th>Submitted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            @php $d = $req->response_data ?? []; @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td><code>{{ $req->reference_id ?? ('REF-' . $req->id) }}</code></td>
                                <td>
                                    @if(($req->provider_name ?? '') === 'Robosttech')
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-1 px-2">
                                            <i class="fa-solid fa-robot me-1"></i> Robosttech
                                        </span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 py-1 px-2">
                                            <i class="fa-solid fa-user-check me-1"></i> Manual
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-600 text-warning font-monospace fs-6">
                                        {{ $req->identifier ?? ($d['nin'] ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $d['validation_reason'] ?? ($d['category'] ?? 'NIN Validation') }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $req->user->fullname ?? ($d['user_name'] ?? 'Unknown') }}</div>
                                    <small class="text-white-50">{{ $req->user->email ?? ($d['user_email'] ?? 'N/A') }}</small>
                                </td>
                                <td>
                                    @if($req->status === 'successful')
                                        <span class="badge bg-success px-2.5 py-1"><i class="fa-solid fa-check me-1"></i> Validated</span>
                                    @elseif($req->status === 'failed')
                                        <span class="badge bg-danger px-2.5 py-1"><i class="fa-solid fa-xmark me-1"></i> Failed (Refunded)</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i> Under Review</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($req->admin_note))
                                        <span class="text-truncate d-inline-block small text-white-50" style="max-width: 180px;" title="{{ $req->admin_note }}">
                                            {{ $req->admin_note }}
                                        </span>
                                    @else
                                        <span class="text-white-50">—</span>
                                    @endif
                                </td>
                                <td><small class="text-white-50">{{ $req->created_at->format('M d, Y H:i') }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.verifications.nin_validation.show', $req->id) }}" class="btn btn-sm btn-outline-light">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Process
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-white-50">
                                    <i class="fa-solid fa-file-circle-check fs-2 mb-2 d-block text-secondary"></i>
                                    No NIN validation requests found matching the criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="p-3 border-top border-secondary border-opacity-25">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Results Upload Modal -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1" role="dialog" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-weight-bold" id="bulkUploadModalLabel">
                    <i class="fa-solid fa-file-csv text-primary me-2"></i>Upload Bulk Validation Results
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#bulkUploadModal').modal('hide')"></button>
            </div>
            <form action="{{ route('admin.verifications.nin_validation.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-white-50 small mb-3">
                        Upload a CSV file containing resolved validation records. The system will match by <strong>Reference ID</strong> or <strong>NIN</strong> and update each record's status and remarks automatically.
                    </p>

                    <div class="alert alert-info bg-info bg-opacity-25 text-white border-info border-opacity-50 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        <strong>Automatic Refunds:</strong> Any request marked with status <code>failed</code> or <code>rejected</code> will immediately credit the applicant's wallet with a full refund.
                    </div>

                    <div class="mb-3">
                        <label for="csvFileInput" class="form-label text-white-50 small font-weight-bold">Select Results CSV File</label>
                        <input type="file" name="file" id="csvFileInput" class="form-control bg-dark text-white border-secondary" accept=".csv,text/csv" required>
                    </div>

                    <div class="bg-black bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-50">
                        <div class="small text-white-50 font-weight-bold mb-1">Expected CSV Columns (Header Row):</div>
                        <code class="text-warning small d-block mb-1">Reference ID, Status, Admin Note</code>
                        <span class="text-white-50 small">Valid status values: <code>successful</code>, <code>failed</code>, <code>waiting_for_review</code></span>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#bulkUploadModal').modal('hide')">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Process &amp; Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
