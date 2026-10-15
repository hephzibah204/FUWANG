@extends('layouts.nexus')

@section('title', 'Document Validation | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    @if(session('status'))
        <div class="alert alert-success mb-4">
            <strong>{{ session('status') }}</strong>
            @if(session('validation_provider'))
                <span class="d-block small mt-1">Provider: {{ session('validation_provider') }}</span>
            @endif
            @if(session('validation_reference_id'))
                <span class="d-block small">Reference: {{ session('validation_reference_id') }}</span>
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05)); border-color: rgba(16, 185, 129, 0.2);">
        <div class="sh-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
            <i class="fa-solid fa-file-shield"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Document &amp; NIN Validation</h1>
            <p class="text-muted small mb-0">
                Verify and validate official identity documents. You will get results in <strong class="text-success font-weight-bold">{{ in_array($validationMode ?? 'manual', ['robosttech', 'dataverify', 'auto', 'api'], true) ? 'a few minutes' : 'less than 24 hours' }}</strong>.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Submit Validation</button>
                <button class="s-tab" onclick="switchMainPanel('status', this)">Check Status</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Validation Vault ({{ $history->count() }})</button>
            </div>

            <!-- Submit Panel -->
            <div id="panel-verify" class="main-panel active">
                <div class="panel-card p-4 mb-4" id="searchPanel">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                        <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Validation Submission</h2>
                        <span class="ml-auto badge badge-primary py-2 px-3 mr-2">₦{{ number_format($price ?? 700, 2) }}</span>
                        <span class="badge badge-outline-light py-2 px-3">Engine: {{ $providerLabel ?? 'Manual Reporting' }}</span>
                    </div>

                    <form id="validationForm" action="{{ route('services.validation.verify') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="submit">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="number" class="font-weight-600 mb-2 small text-muted">Subject NIN Number</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-id-card"></i>
                                    <input type="text" id="number" name="number" class="form-control" placeholder="Enter 11-digit NIN Number" maxlength="11" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="validation_reason" class="font-weight-600 mb-2 small text-muted">Validation Type / Category</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-tag"></i>
                                    <select id="validation_reason" name="validation_reason" class="form-control" required style="padding-left: 45px; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: #fff;">
                                        <option value="no_record_found" selected>No Record Found on NIMC (no_record_found)</option>
                                        <option value="sim_validation">SIM Card / Telco Link (sim_validation)</option>
                                        <option value="bank_validation">Bank / BVN Link Validation (bank_validation)</option>
                                        <option value="modification_validation">Data Modification Validation (modification_validation)</option>
                                        <option value="photographic_error">Photographic Error Validation (photographic_error)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="remarks" class="font-weight-600 mb-2 small text-muted">Applicant Notes / Remarks (Optional)</label>
                                <textarea name="remarks" id="remarks" rows="2" class="form-control" placeholder="Any additional notes or instructions for the administrator or provider..."></textarea>
                            </div>
                            <div class="col-12 text-right mt-2 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submit-btn" style="height: 50px;">
                                    <i class="fa-solid fa-bolt mr-2"></i> Submit for Validation (₦{{ number_format($price ?? 700, 2) }})
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Check Status Panel -->
            <div id="panel-status" class="main-panel" style="display: none;">
                <div class="panel-card p-4 mb-4">
                    <h2 class="h6 font-weight-bold mb-3 pb-2 border-bottom border-white-5"><i class="fa-solid fa-magnifying-glass-chart mr-2 text-info"></i> Query Live Validation Status</h2>
                    <p class="text-muted small mb-4">Check status for a submitted request using your NIN number or system Reference ID. Real-time updates from provider.</p>

                    <form id="statusForm" action="{{ route('services.validation.verify') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="status">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3">
                                <label for="statusLookupInput" class="font-weight-600 mb-2 small text-muted">NIN Number or Reference ID</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-hashtag"></i>
                                    <input type="text" id="statusLookupInput" name="number" class="form-control font-monospace" placeholder="e.g. 11223344556 or VAL-XXXX" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="submit" class="btn btn-info btn-lg w-100 font-weight-bold" id="status-btn" style="height: 50px;">
                                    <i class="fa-solid fa-rotate mr-2"></i> Check Status
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="statusResultCard" class="mt-4 p-4 rounded-3 border border-secondary" style="display: none; background: rgba(255,255,255,0.02);">
                        <h5 class="text-white font-weight-bold mb-3 d-flex justify-content-between align-items-center">
                            <span>Status Result</span>
                            <span id="statusBadge" class="badge"></span>
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6 mb-2">
                                <span class="text-muted small d-block">Reference ID</span>
                                <strong id="statusRef" class="text-white font-monospace"></strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="text-muted small d-block">Subject NIN</span>
                                <strong id="statusNin" class="text-warning font-monospace fs-5"></strong>
                            </div>
                            <div class="col-12 mb-3">
                                <span class="text-muted small d-block">Resolution Note / Remarks</span>
                                <p id="statusNote" class="text-white bg-dark p-3 rounded-2 border border-secondary mb-0"></p>
                            </div>
                            <div class="col-12" id="statusSlipAction" style="display: none;">
                                <div class="p-3 rounded-3" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <strong class="text-success d-block"><i class="fa-solid fa-circle-check mr-1"></i> NIN Successfully Validated on NIMC!</strong>
                                            <small class="text-muted">Your NIN record is now active and ready for live verification &amp; slip printing.</small>
                                        </div>
                                        <a href="#" id="statusSlipBtn" class="btn btn-success btn-sm font-weight-bold px-3">
                                            <i class="fa-solid fa-print mr-1"></i> Retrieve &amp; Print Slip
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vault Panel -->
            <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
                <div class="panel-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h6 font-weight-bold m-0"><i class="fa-solid fa-vault mr-2 text-warning"></i> Validation History Vault</h3>
                        <span class="small text-muted">Click <strong>Live Sync</strong> on any pending record to requery provider</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>NIN Number</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Remarks / Note</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $res)
                                    @php 
                                        $rd = $res->response_data ?? []; 
                                        $cat = $rd['validation_reason'] ?? ($rd['validation_type'] ?? 'NIN Validation');
                                    @endphp
                                    <tr id="row-{{ $res->id }}">
                                        <td><code class="text-primary">{{ $res->reference_id ?? ('VAL-' . $res->id) }}</code></td>
                                        <td><span class="font-monospace text-warning font-weight-bold">{{ $res->identifier }}</span></td>
                                        <td><span class="badge badge-secondary small">{{ $cat }}</span></td>
                                        <td class="status-cell">
                                            @if($res->status === 'successful')
                                                <span class="badge badge-success py-1 px-2.5"><i class="fa-solid fa-check mr-1"></i> Validated</span>
                                            @elseif($res->status === 'failed')
                                                <span class="badge badge-danger py-1 px-2.5"><i class="fa-solid fa-xmark mr-1"></i> Failed (Refunded)</span>
                                            @else
                                                <span class="badge badge-warning text-dark py-1 px-2.5"><i class="fa-solid fa-clock mr-1"></i> Under Review</span>
                                            @endif
                                        </td>
                                        <td class="note-cell">
                                            @if(!empty($res->admin_note))
                                                <span class="text-truncate d-inline-block small text-muted" style="max-width: 200px;" title="{{ $res->admin_note }}">
                                                    {{ $res->admin_note }}
                                                </span>
                                            @else
                                                <span class="text-muted small">In Progress...</span>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">{{ $res->created_at->format('M d, Y H:i') }}</small></td>
                                        <td class="text-right">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if($res->status === 'successful')
                                                    <a href="{{ route('services.nin', ['nin' => $res->identifier]) }}" class="btn btn-xs btn-outline-success font-weight-bold" title="Print Slip">
                                                        <i class="fa-solid fa-print"></i> Slip
                                                    </a>
                                                @elseif($res->status !== 'failed')
                                                    <button class="btn btn-xs btn-outline-info font-weight-bold" onclick="syncRowStatus('{{ $res->identifier }}', {{ $res->id }}, this)" title="Check Live Status">
                                                        <i class="fa-solid fa-rotate"></i> Sync
                                                    </button>
                                                @endif
                                                <button class="btn btn-xs btn-outline-light" onclick='openReportModal(@json($res), @json($rd))' title="View Full Report">
                                                    <i class="fa-solid fa-file-lines"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted small">No validation records found in your vault.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Report Detail Modal -->
            <div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content bg-dark text-white border-secondary" style="border-radius: 16px;">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title font-weight-bold" id="modalTitle"><i class="fa-solid fa-file-shield text-success mr-2"></i>Validation Report</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" onclick="$('#reportModal').modal('hide')" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                                <div>
                                    <span class="small text-muted d-block">Reference ID</span>
                                    <code id="modalRef" class="text-info fs-6"></code>
                                </div>
                                <span id="modalBadge" class="badge"></span>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <span class="small text-muted d-block">Subject NIN</span>
                                    <strong id="modalNin" class="text-warning font-monospace fs-5"></strong>
                                </div>
                                <div class="col-6">
                                    <span class="small text-muted d-block">Category / Reason</span>
                                    <strong id="modalCategory" class="text-white small"></strong>
                                </div>
                                <div class="col-6">
                                    <span class="small text-muted d-block">Date Submitted</span>
                                    <small id="modalDate" class="text-white"></small>
                                </div>
                                <div class="col-6">
                                    <span class="small text-muted d-block">Processing Engine</span>
                                    <small id="modalProvider" class="text-white-50"></small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="small text-muted d-block mb-1">Official Resolution / Admin Note:</span>
                                <div id="modalNote" class="p-3 rounded-3 bg-black border border-secondary text-white small"></div>
                            </div>
                            <div id="modalValidatedAction" class="p-3 rounded-3 mt-3" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-success small d-block">Validation Cleared on NIMC!</strong>
                                        <small class="text-muted">You can now verify this NIN or print slips directly.</small>
                                    </div>
                                    <a href="#" id="modalSlipLink" class="btn btn-success btn-sm font-weight-bold">
                                        <i class="fa-solid fa-print mr-1"></i> Get Slip
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="$('#reportModal').modal('hide')">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchMainPanel(panel, btn) {
        $('.main-panel').hide().removeClass('active');
        $('#panel-' + panel).show().addClass('active');
        $('.s-tab').removeClass('active');
        $(btn).addClass('active');
    }

    function openReportModal(record, details) {
        details = details || {};
        $('#modalRef').text(record.reference_id || ('VAL-' + record.id));
        $('#modalNin').text(record.identifier || details.nin || '—');
        $('#modalCategory').text(details.validation_reason || details.validation_type || 'NIN Validation');
        
        let dateStr = record.created_at ? new Date(record.created_at).toLocaleString() : 'Recent';
        $('#modalDate').text(dateStr);
        $('#modalProvider').text(record.provider_name || 'Official Provider');

        let note = record.admin_note || 'Processing with NIMC / provider.';
        $('#modalNote').text(note);

        let badge = $('#modalBadge');
        let slipAction = $('#modalValidatedAction');
        let slipLink = $('#modalSlipLink');

        if (record.status === 'successful') {
            badge.attr('class', 'badge badge-success px-2.5 py-1').html('<i class="fa-solid fa-check mr-1"></i> Validated');
            slipLink.attr('href', '{{ route("services.nin") }}?nin=' + encodeURIComponent(record.identifier));
            slipAction.show();
        } else if (record.status === 'failed') {
            badge.attr('class', 'badge badge-danger px-2.5 py-1').html('<i class="fa-solid fa-xmark mr-1"></i> Failed (Refunded)');
            slipAction.hide();
        } else {
            badge.attr('class', 'badge badge-warning text-dark px-2.5 py-1').html('<i class="fa-solid fa-clock mr-1"></i> Under Review');
            slipAction.hide();
        }

        $('#reportModal').modal('show');
    }

    function syncRowStatus(nin, resultId, btn) {
        let $btn = $(btn);
        let orig = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("services.validation.verify") }}',
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                _token: '{{ csrf_token() }}',
                mode: 'status',
                number: nin
            },
            success: function(res) {
                $btn.prop('disabled', false).html(orig);
                if (res.status && res.data) {
                    let row = $('#row-' + resultId);
                    let statusCell = row.find('.status-cell');
                    let noteCell = row.find('.note-cell');

                    if (res.data.status === 'successful') {
                        statusCell.html('<span class="badge badge-success py-1 px-2.5"><i class="fa-solid fa-check mr-1"></i> Validated</span>');
                        noteCell.html('<span class="text-truncate d-inline-block small text-muted" style="max-width: 200px;" title="' + (res.data.admin_note || '') + '">' + (res.data.admin_note || 'Validated') + '</span>');
                        $btn.replaceWith('<a href="{{ route("services.nin") }}?nin=' + encodeURIComponent(nin) + '" class="btn btn-xs btn-outline-success font-weight-bold" title="Print Slip"><i class="fa-solid fa-print"></i> Slip</a>');
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Validated successfully on NIMC!',
                            showConfirmButton: false,
                            timer: 3500,
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    } else if (res.data.status === 'failed') {
                        statusCell.html('<span class="badge badge-danger py-1 px-2.5"><i class="fa-solid fa-xmark mr-1"></i> Failed (Refunded)</span>');
                        noteCell.html('<span class="text-truncate d-inline-block small text-muted" style="max-width: 200px;" title="' + (res.data.admin_note || '') + '">' + (res.data.admin_note || 'Failed') + '</span>');
                        $btn.remove();
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Validation rejected & refunded.',
                            showConfirmButton: false,
                            timer: 3500,
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    } else {
                        noteCell.html('<span class="text-truncate d-inline-block small text-muted" style="max-width: 200px;" title="' + (res.data.admin_note || '') + '">' + (res.data.admin_note || 'Under Review') + '</span>');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Status: ' + (res.data.status_label || 'Under Review by Provider'),
                            showConfirmButton: false,
                            timer: 3000,
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: res.message || 'Status updated',
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(orig);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Unable to sync live status right now.',
                    showConfirmButton: false,
                    timer: 3000,
                    background: '#0a0a0f',
                    color: '#fff'
                });
            }
        });
    }

    $(document).ready(function() {
        $('#validationForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Validation',
                text: 'A fee of ₦{{ number_format($price ?? 700, 2) }} will be charged. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'Submitted Successfully!',
                                    text: response.message || 'Validation request submitted.',
                                    icon: 'success',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Submission Failed',
                                    text: response.message || 'Unable to submit validation request.',
                                    icon: 'error',
                                    background: '#0a0a0f',
                                    color: '#fff'
                                });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Validation service is currently busy.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Query Status Form
        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#status-btn');
            let orig = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Checking...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html(orig);
                    if (res.status && res.data) {
                        $('#statusRef').text(res.data.reference_id || '—');
                        $('#statusNin').text(res.data.nin || '—');
                        $('#statusNote').text(res.data.admin_note || 'Awaiting determination.');
                        
                        let badge = $('#statusBadge');
                        let slipAction = $('#statusSlipAction');
                        let slipBtn = $('#statusSlipBtn');

                        if (res.data.status === 'successful') {
                            badge.attr('class', 'badge badge-success px-2.5 py-1').html('<i class="fa-solid fa-check mr-1"></i> Validated');
                            slipBtn.attr('href', '{{ route("services.nin") }}?nin=' + encodeURIComponent(res.data.nin));
                            slipAction.fadeIn();
                        } else if (res.data.status === 'failed') {
                            badge.attr('class', 'badge badge-danger px-2.5 py-1').html('<i class="fa-solid fa-xmark mr-1"></i> Failed / Refunded');
                            slipAction.hide();
                        } else {
                            badge.attr('class', 'badge badge-warning text-dark px-2.5 py-1').html('<i class="fa-solid fa-clock mr-1"></i> Under Review');
                            slipAction.hide();
                        }

                        $('#statusResultCard').fadeIn();
                    } else {
                        $('#statusResultCard').hide();
                        Swal.fire({
                            title: 'Not Found',
                            text: res.message || 'No validation record found.',
                            icon: 'info',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(orig);
                    $('#statusResultCard').hide();
                    let msg = 'Unable to check status at this time.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                }
            });
        });
    });
</script>
@endpush
