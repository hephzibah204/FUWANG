@extends('layouts.nexus')

@section('title', 'NIN Modification Review | Admin')

@section('content')
<div class="container py-4" style="max-width: 950px;">
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="font-weight-bold text-white mb-1"><i class="fa-solid fa-id-card text-warning me-2"></i>Review NIN Modification Request</h3>
            <p class="text-white-50 small mb-0">Inspect applicant details, submitted documents, and update the processing status.</p>
        </div>
        <a href="{{ route('admin.verifications.nin_modifications.index') }}" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Requests
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-white bg-success border-0 mb-4 rounded-3">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger text-white bg-danger border-0 mb-4 rounded-3">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    @php
        $data = is_array($request->response_data) ? $request->response_data : [];
        $modType = $data['modification_type'] ?? 'N/A';
        $modLabels = [
            'name' => 'Change of Name',
            'dob' => 'Date of Birth Modification',
            'address' => 'Change of Address',
            'phone' => 'Change of Phone Number',
            'email' => 'Change of Email',
        ];
        $typeLabel = $modLabels[$modType] ?? ucfirst(str_replace('_', ' ', $modType));
        $docPath = $data['document_path'] ?? null;
        $isImage = $docPath && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $docPath);
    @endphp

    <div class="row g-4">
        <!-- Main Request & Data Details -->
        <div class="col-lg-7">
            <!-- Modification Payload Card -->
            <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white-10 pb-2">
                    <h5 class="text-white mb-0"><i class="fa-solid fa-file-pen text-info me-2"></i>Modification Details</h5>
                    <span class="badge bg-primary text-white px-2.5 py-1">{{ $typeLabel }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-sm text-white mb-0">
                        <tbody>
                            <tr>
                                <th class="text-white-50" style="width: 35%;">Target NIN:</th>
                                <td><code class="text-warning font-weight-bold fs-6">{{ $data['nin'] ?? $request->identifier }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-white-50">Name on NIN:</th>
                                <td class="font-weight-600">{{ $data['full_name'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-white-50">Modification Type:</th>
                                <td><strong>{{ $typeLabel }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-white-50">Current Detail (Old):</th>
                                <td><span class="badge bg-secondary text-white px-2 py-1 text-wrap text-start">{{ $data['old_value'] ?? 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-white-50">Requested Update (New):</th>
                                <td><span class="badge bg-success text-white px-2 py-1 text-wrap text-start font-weight-bold">{{ $data['new_value'] ?? 'N/A' }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Supporting Document / Photo Card -->
            <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
                <h5 class="text-white mb-3"><i class="fa-solid fa-paperclip text-warning me-2"></i>Supporting Document</h5>
                @if($docPath)
                    @php $docUrl = asset('storage/' . $docPath); @endphp
                    @if($isImage)
                        <div class="text-center mb-3 p-2 rounded-3 bg-dark" style="border: 1px solid rgba(255,255,255,0.1);">
                            <a href="{{ $docUrl }}" target="_blank" title="Click to view full image">
                                <img src="{{ $docUrl }}" alt="Supporting Document" class="img-fluid rounded-2" style="max-height: 280px; object-fit: contain;">
                            </a>
                        </div>
                    @endif
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ $docUrl }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fa-solid fa-up-right-from-square me-1"></i> Open Document in New Tab
                        </a>
                        <a href="{{ $docUrl }}" download class="btn btn-outline-light btn-sm">
                            <i class="fa-solid fa-download me-1"></i> Download File
                        </a>
                    </div>
                @else
                    <div class="p-3 text-center text-white-50 bg-dark rounded-3">
                        <i class="fa-solid fa-file-circle-xmark fa-2x mb-2 d-block text-secondary"></i>
                        No supporting document file path found on record.
                    </div>
                @endif
            </div>

            <!-- User / Applicant Info Card -->
            <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
                <h5 class="text-white mb-3"><i class="fa-solid fa-user-circle text-primary me-2"></i>Fuwa Account Info</h5>
                <div class="row g-2 text-white-50 small">
                    <div class="col-sm-6">
                        <span class="d-block text-white-50">Account Holder:</span>
                        <strong class="text-white">{{ $request->user?->fullname ?? $request->user?->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="d-block text-white-50">Username:</span>
                        <strong class="text-white">{{ $request->user?->username ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="d-block text-white-50">Email Address:</span>
                        <strong class="text-white">{{ $request->user?->email ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="d-block text-white-50">Wallet Balance:</span>
                        <strong class="text-success">₦{{ number_format($request->user?->balance?->user_balance ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Action Card -->
        <div class="col-lg-5">
            <!-- Metadata Snapshot -->
            <div class="card glass-card border-0 shadow rounded-4 p-4 mb-4">
                <h6 class="text-white mb-3 text-uppercase font-weight-bold tracking-wider small">Request Summary</h6>
                <div class="mb-3">
                    <span class="d-block small text-white-50">Reference ID</span>
                    <strong class="text-white"><code>{{ $request->reference_id ?? ('REF-' . $request->id) }}</code></strong>
                </div>
                <div class="mb-3">
                    <span class="d-block small text-white-50">Current Status</span>
                    @if($request->status === 'successful')
                        <span class="badge bg-success px-2.5 py-1.5"><i class="fa-solid fa-circle-check me-1"></i> Successful</span>
                    @elseif($request->status === 'failed')
                        <span class="badge bg-danger px-2.5 py-1.5"><i class="fa-solid fa-circle-xmark me-1"></i> Failed (Refunded)</span>
                    @else
                        <span class="badge bg-warning text-dark px-2.5 py-1.5"><i class="fa-solid fa-clock me-1"></i> Awaiting Review</span>
                    @endif
                </div>
                <div class="mb-3">
                    <span class="d-block small text-white-50">Date Submitted</span>
                    <span class="text-white">{{ $request->created_at->format('M d, Y h:i A') }}</span>
                </div>
                @if($request->admin_note)
                    <div class="p-3 bg-dark rounded-3 border border-secondary mt-2">
                        <span class="d-block small text-white-50 mb-1 font-weight-bold">Last Admin Remarks:</span>
                        <p class="mb-0 text-white small">{{ $request->admin_note }}</p>
                    </div>
                @endif
            </div>

            <!-- Update Status Form -->
            <div class="card glass-card border-0 shadow rounded-4 p-4">
                <h5 class="text-white mb-3"><i class="fa-solid fa-sliders text-warning me-2"></i>Update Processing Status</h5>
                <form action="{{ route('admin.verifications.nin_modifications.update', $request->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="status" class="form-label text-white-50 small font-weight-600">Select Status</label>
                        <select name="status" id="status" class="form-select bg-dark text-white border-secondary" required>
                            <option value="waiting_for_review" @selected(in_array($request->status, ['waiting_for_review', 'pending']))>Pending / Awaiting Review</option>
                            <option value="successful" @selected($request->status === 'successful')>Successful (Mark Completed)</option>
                            <option value="failed" @selected($request->status === 'failed')>Failed / Rejected (Auto-Refunds User)</option>
                        </select>
                        <div class="form-text text-white-50 small mt-1">
                            Selecting <strong>Failed</strong> will automatically refund the service fee back into the user's wallet.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="admin_note" class="form-label text-white-50 small font-weight-600">Admin Remarks / Reason</label>
                        <textarea name="admin_note" id="admin_note" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Enter remarks, rejection reason, or tracking info for the applicant...">{{ old('admin_note', $request->admin_note) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-warning font-weight-bold w-100 py-2.5">
                        <i class="fa-solid fa-save me-2"></i> Save Status Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
