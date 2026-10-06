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
                Verify and validate official identity documents. You will get results in <strong class="text-success font-weight-bold">{{ ($validationMode ?? 'manual') === 'robosttech' ? 'a few minutes' : 'less than 24 hours' }}</strong>.
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
                                    <input type="text" id="number" name="number" class="form-control" placeholder="Enter 11-digit NIN Number" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="validation_reason" class="font-weight-600 mb-2 small text-muted">Validation Reason / Category</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-tag"></i>
                                    <input type="text" id="validation_reason" name="validation_reason" class="form-control" placeholder="e.g. Identity Verification / Bank Request" value="NIN Validation">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="remarks" class="font-weight-600 mb-2 small text-muted">Applicant Notes / Remarks (Optional)</label>
                                <textarea name="remarks" id="remarks" rows="2" class="form-control" placeholder="Any additional notes or instructions for the administrator..."></textarea>
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
                    <h2 class="h6 font-weight-bold mb-3 pb-2 border-bottom border-white-5"><i class="fa-solid fa-magnifying-glass-chart mr-2 text-info"></i> Query Validation Status</h2>
                    <p class="text-muted small mb-4">Check status for a submitted request using your NIN number or system Reference ID.</p>

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
                                <span class="text-muted small d-block">NIN Number</span>
                                <strong id="statusNin" class="text-warning font-monospace"></strong>
                            </div>
                            <div class="col-12 mb-2">
                                <span class="text-muted small d-block">Resolution Note / Remarks</span>
                                <p id="statusNote" class="text-white bg-dark p-3 rounded-2 border border-secondary mb-0"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result Area -->
            <div class="col-lg-12" id="resultArea" style="{{ session('validation_result') ? '' : 'display: none;' }}">
                <div class="panel-card p-4">
                    <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Validation Result</h4>
                    <div id="resultContent" class="text-white">
                        @if(session('validation_result'))
                            <pre class="text-white">{{ json_encode(session('validation_result'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-light btn-wide" onclick="window.location.reload()">New Search</button>
                </div>
            </div>

            <!-- Vault Panel -->
            <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4">Validation History Vault</h3>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>NIN Number</th>
                                    <th>Status</th>
                                    <th>Admin Remark</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $res)
                                    @php $rd = $res->response_data ?? []; @endphp
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id ?? ('VAL-' . $res->id) }}</code></td>
                                        <td><span class="font-monospace text-warning font-weight-bold">{{ $res->identifier }}</span></td>
                                        <td>
                                            @if($res->status === 'successful')
                                                <span class="badge badge-success py-1 px-2.5"><i class="fa-solid fa-check mr-1"></i> Validated</span>
                                            @elseif($res->status === 'failed')
                                                <span class="badge badge-danger py-1 px-2.5"><i class="fa-solid fa-xmark mr-1"></i> Failed (Refunded)</span>
                                            @else
                                                <span class="badge badge-warning text-dark py-1 px-2.5"><i class="fa-solid fa-clock mr-1"></i> Under Review</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($res->admin_note))
                                                <span class="text-truncate d-inline-block small text-muted" style="max-width: 220px;" title="{{ $res->admin_note }}">
                                                    {{ $res->admin_note }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $res->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-right">
                                            <button class="btn btn-xs btn-outline-primary" onclick='viewResult(@json($rd))'>
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">No validation records found in your vault.</td>
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
    .s-tab.active { color: #10b981; border-bottom-color: #10b981; }
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
    }

    function viewResult(data) {
        $('#searchPanel').hide();
        $('#panel-vault').hide();
        $('#resultContent').html('<pre class="text-white">' + JSON.stringify(data, null, 4) + '</pre>');
        $('#resultArea').fadeIn();
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
                        if (res.data.status === 'successful') {
                            badge.attr('class', 'badge badge-success').text('Validated');
                        } else if (res.data.status === 'failed') {
                            badge.attr('class', 'badge badge-danger').text('Failed / Refunded');
                        } else {
                            badge.attr('class', 'badge badge-warning text-dark').text('Under Review');
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
