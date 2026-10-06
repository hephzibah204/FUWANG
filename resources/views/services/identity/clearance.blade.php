@extends('layouts.nexus')

@section('title', 'IPE Clearance | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05)); border-color: rgba(245, 158, 11, 0.2);">
        <div class="sh-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">IPE Clearance</h1>
            <p class="text-muted small">Official background vetting and clearance verification manually processed by our administrative desk.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Submit / Check Clearance</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Clearance Vault ({{ $history->count() }})</button>
            </div>

            <div id="panel-verify" class="main-panel active">
                <div class="panel-card p-4 mb-4" id="searchPanel">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                        <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-file-signature mr-2 text-primary"></i> IPE Clearance Processing</h2>
                        <span class="ml-auto badge badge-primary py-2 px-3" id="priceBadge">₦{{ number_format($price ?? 400, 2) }}</span>
                    </div>

                    <div class="alert alert-info border-0 small mb-4" style="background: rgba(59, 130, 246, 0.1); color: #93c5fd;">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        Clearance requests are manually vetted by Fuwa compliance officers. Once submitted, your tracking ID will remain under review until verified. You can track status anytime.
                    </div>

                    <form id="clearanceForm" action="{{ route('services.clearance.verify') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="mode" class="font-weight-600 mb-2 small text-muted">Action Mode</label>
                                <select id="mode" name="mode" class="form-control" onchange="updateUI(this.value)">
                                    <option value="submit">Submit New Clearance Request</option>
                                    <option value="status">Track Request Status</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="number" class="font-weight-600 mb-2 small text-muted">Tracking ID / Clearance Identifier</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-hashtag"></i>
                                    <input type="text" id="number" name="number" class="form-control" placeholder="Enter Tracking ID or Reference Number" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3" id="remarksWrap">
                                <label for="remarks" class="font-weight-600 mb-2 small text-muted">Additional Information / Remarks (Optional)</label>
                                <textarea id="remarks" name="remarks" class="form-control" rows="2" placeholder="Provide any relevant details or applicant notes for admin vetting..."></textarea>
                            </div>

                            <div class="col-md-12 text-right mt-2 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submit-btn" style="height: 50px; min-width: 250px;">
                                    <i class="fa-solid fa-bolt mr-2"></i> <span id="btnText">Submit Request</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Result Area -->
            <div class="col-lg-12" id="resultArea" style="display: none;">
                <div class="panel-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white-5 pb-3">
                        <h4 class="h6 font-weight-bold m-0"><i class="fa-solid fa-clipboard-check text-warning mr-2"></i> Clearance Status Card</h4>
                        <span id="resultStatusBadge" class="badge py-2 px-3"></span>
                    </div>

                    <div id="resultCard" class="text-white">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Tracking ID</small>
                                <strong id="resTrackingId" class="text-warning font-monospace"></strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">System Reference</small>
                                <code id="resReferenceId" class="text-primary"></code>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Submitted At</small>
                                <span id="resSubmittedAt"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Review Status</small>
                                <strong id="resStatusLabel"></strong>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mt-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block mb-1"><i class="fa-solid fa-comment-dots mr-1"></i> Admin Note / Vetting Remarks:</small>
                            <p id="resAdminNote" class="mb-0 text-white font-weight-500"></p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-outline-light btn-wide" onclick="window.location.reload()">
                        <i class="fa-solid fa-arrow-rotate-right mr-1"></i> New Request / Lookup
                    </button>
                </div>
            </div>

            <!-- Vault Panel -->
            <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4">Clearance Vault &amp; History</h3>
                    <div class="table-responsive">
                        <table class="table admin-table text-white">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Tracking ID</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $res)
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                        <td><span class="text-warning font-weight-bold">{{ $res->identifier }}</span></td>
                                        <td>
                                            @if($res->status === 'successful')
                                                <span class="badge badge-success">Cleared</span>
                                            @elseif($res->status === 'failed')
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-warning text-dark">Under Review</span>
                                            @endif
                                        </td>
                                        <td>{{ $res->created_at->format('M d, Y') }}</td>
                                        <td class="text-right">
                                            <button class="btn btn-xs btn-outline-primary" onclick='showVaultDetails(@json($res))'>
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No clearance records found in vault.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; border-bottom: 2px solid rgba(255,255,255,0.05); margin-bottom: 20px; }
    .s-tab { padding: 12px 25px; background: none; border: none; color: var(--clr-text-muted); font-weight: 600; font-size: 0.85rem; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.3s; }
    .s-tab.active { color: #f59e0b; border-bottom-color: #f59e0b; }
    .main-panel { display: none; }
    .main-panel.active { display: block; }
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 20px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .btn-wide { padding-left: 40px; padding-right: 40px; border-radius: 12px; }
</style>
@endpush

@push('scripts')
<script>
    function switchMainPanel(panel, btn) {
        $('.main-panel').hide().removeClass('active');
        $('#panel-' + panel).show().addClass('active');
        $('.s-tab').removeClass('active');
        $(btn).addClass('active');
        $('#resultArea').hide();
    }

    function renderStatusCard(data) {
        $('#searchPanel').hide();
        $('#panel-vault').hide();
        
        let trackingId = data.tracking_id || 'N/A';
        let refId = data.reference_id || 'N/A';
        let status = data.status || 'waiting_for_review';
        let statusLabel = data.status_label || (status === 'successful' ? 'Cleared' : (status === 'failed' ? 'Rejected' : 'Under Review'));
        let adminNote = data.admin_note || 'Awaiting administrative review.';
        let submittedAt = data.submitted_at || new Date().toLocaleString();

        $('#resTrackingId').text(trackingId);
        $('#resReferenceId').text(refId);
        $('#resSubmittedAt').text(submittedAt);
        $('#resStatusLabel').text(statusLabel);
        $('#resAdminNote').text(adminNote);

        let badge = $('#resultStatusBadge');
        if (status === 'successful') {
            badge.removeClass('bg-warning bg-danger text-dark').addClass('bg-success text-white').text('Cleared / Successful');
        } else if (status === 'failed') {
            badge.removeClass('bg-warning bg-success text-dark').addClass('bg-danger text-white').text('Rejected / Failed');
        } else {
            badge.removeClass('bg-success bg-danger text-white').addClass('bg-warning text-dark').text('Under Review by Admin');
        }

        $('#resultArea').fadeIn();
    }

    function showVaultDetails(item) {
        let formatted = {
            tracking_id: item.identifier,
            reference_id: item.reference_id,
            status: item.status,
            status_label: item.status === 'successful' ? 'Cleared' : (item.status === 'failed' ? 'Rejected' : 'Under Review'),
            admin_note: item.admin_note || (item.status === 'successful' ? 'Cleared successfully by administrator.' : 'Under review.'),
            submitted_at: item.created_at,
        };
        renderStatusCard(formatted);
    }

    function updateUI(mode) {
        if (mode === 'status') {
            $('#priceBadge').hide();
            $('#remarksWrap').hide();
            $('#btnText').text('Track Status');
        } else {
            $('#priceBadge').show();
            $('#remarksWrap').show();
            $('#btnText').text('Submit Request');
        }
    }

    $(document).ready(function() {
        $('#clearanceForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();
            let mode = $('#mode').val();
            let confirmText = (mode === 'submit') ? 'A fee of ₦{{ number_format($price, 2) }} will be charged for processing this clearance. Continue?' : 'Track status for this Tracking ID?';

            Swal.fire({
                title: (mode === 'submit') ? 'Confirm Clearance Request' : 'Check Request Status',
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                if (mode === 'submit') {
                                    Swal.fire({ 
                                        title: 'Submitted Successfully!', 
                                        text: response.message, 
                                        icon: 'success', 
                                        background: '#0a0a0f', 
                                        color: '#fff' 
                                    }).then(() => {
                                        if (response.data) {
                                            renderStatusCard(response.data);
                                        } else {
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    renderStatusCard(response.data);
                                    Swal.fire({ 
                                        title: 'Status Retrieved!', 
                                        text: 'Current status: ' + (response.data.status_label || response.data.status), 
                                        icon: 'info', 
                                        background: '#0a0a0f', 
                                        color: '#fff' 
                                    });
                                }
                            } else {
                                Swal.fire({ title: 'Notice', text: response.message, icon: 'warning', background: '#0a0a0f', color: '#fff' });
                            }
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to complete request at this time.';
                            Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
