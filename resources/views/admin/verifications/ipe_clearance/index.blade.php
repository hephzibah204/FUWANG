@extends('layouts.nexus')

@section('title', 'IPE Clearance Requests | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="font-weight-bold text-white mb-1"><i class="fa-solid fa-user-check text-warning me-2"></i>IPE Clearance Requests Desk</h3>
            <p class="text-white-50 small mb-0">Review requests, assign new tracking IDs on approval, process refunds on rejection, and bulk manage reports.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Bulk Export Button -->
            <a href="{{ route('admin.verifications.ipe_clearance.export', request()->all()) }}" class="btn btn-outline-success font-weight-bold">
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
        <form method="GET" action="{{ route('admin.verifications.ipe_clearance.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-dark text-white border-secondary" placeholder="Search by tracking ID, NIN, reference, user, or new tracking ID...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select bg-dark text-white border-secondary">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="waiting_for_review" @selected(request('status') === 'waiting_for_review')>Under Review</option>
                    <option value="successful" @selected(request('status') === 'successful')>Successful (Cleared)</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed (Rejected / Refunded)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.verifications.ipe_clearance.index') }}" class="btn btn-outline-light" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
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
                            <th>Original Tracking ID</th>
                            <th>NIN Number</th>
                            <th>IPE Category</th>
                            <th>User</th>
                            <th>Status &amp; Result</th>
                            <th>New Tracking ID</th>
                            <th>Submitted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            @php $d = $req->response_data ?? []; @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td><code>{{ $req->reference_id ?? ('REF-' . $req->id) }}</code></td>
                                <td><span class="font-weight-600 text-warning font-monospace">{{ $req->identifier }}</span></td>
                                <td>
                                    @if(!empty($d['nin']))
                                        <span class="font-monospace text-info font-weight-bold">{{ $d['nin'] }}</span>
                                    @else
                                        <span class="text-white-50 fst-italic">Not provided</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $d['category'] ?? 'Improcessing Error' }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $req->user->fullname ?? ($d['user_name'] ?? 'Unknown') }}</div>
                                    <small class="text-white-50">{{ $req->user->email ?? ($d['user_email'] ?? 'N/A') }}</small>
                                </td>
                                <td>
                                    @if($req->status === 'successful')
                                        <span class="badge bg-success px-2.5 py-1"><i class="fa-solid fa-check me-1"></i> Successful</span>
                                    @elseif($req->status === 'failed')
                                        <span class="badge bg-danger px-2.5 py-1"><i class="fa-solid fa-xmark me-1"></i> Failed (Refunded)</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i> Under Review</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($d['new_tracking_id']))
                                        <span class="badge bg-success font-monospace px-2.5 py-1 fs-7">
                                            <i class="fa-solid fa-key me-1"></i> {{ $d['new_tracking_id'] }}
                                        </span>
                                    @else
                                        <span class="text-white-50">—</span>
                                    @endif
                                </td>
                                <td><small class="text-white-50">{{ $req->created_at->format('M d, Y H:i') }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.verifications.ipe_clearance.show', $req->id) }}" class="btn btn-sm btn-outline-light">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Process
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-white-50">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    No IPE clearance requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="p-3 border-top border-secondary-subtle">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Results Upload Modal -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1" role="dialog" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content glass-card text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-weight-bold" id="bulkUploadModalLabel">
                    <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Upload Bulk IPE Results (CSV)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" onclick="$('#bulkUploadModal').modal('hide')"></button>
            </div>
            <form action="{{ route('admin.verifications.ipe_clearance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="small text-white-50 mb-3">
                        Upload a completed CSV file to update request statuses in bulk.
                    </p>

                    <div class="p-3 bg-dark rounded-3 border border-secondary mb-3 small">
                        <strong class="d-block text-warning mb-1"><i class="fa-solid fa-lightbulb me-1"></i> Supported CSV Columns:</strong>
                        <ul class="mb-0 ps-3 text-white-50">
                            <li><code>Tracking ID</code> or <code>Reference ID</code> (Required identifier)</li>
                            <li><code>Status</code>: <b>successful</b> (cleared) or <b>failed</b> (rejected)</li>
                            <li><code>New Tracking ID</code>: (Optional, awarded to user if cleared)</li>
                            <li><code>NIN</code>: (Optional National Identification Number)</li>
                            <li><code>Admin Note</code>: (Optional remark / reason)</li>
                        </ul>
                        <div class="mt-2 text-info">
                            <i class="fa-solid fa-rotate-left me-1"></i> Any request marked <b>failed</b> will automatically have its service fee refunded to the user wallet.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small font-weight-bold">Select CSV File</label>
                        <input type="file" name="file" class="form-control bg-dark text-white border-secondary" accept=".csv,text/csv" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal" data-dismiss="modal" onclick="$('#bulkUploadModal').modal('hide')">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fa-solid fa-upload me-1"></i> Process &amp; Update Requests
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
