@extends('layouts.nexus')

@section('title', 'Personalization Services | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); border-color: rgba(139, 92, 246, 0.2);">
        <div class="sh-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.3);">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Personalization</h1>
            <p class="text-muted small">Personalize NIMC tracking IDs to generate active identity records and printable NIN slips.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Personalize Identity</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Personalization Vault ({{ $history->count() }})</button>
            </div>

            <div id="panel-verify" class="main-panel active">
                <div class="panel-card p-4 mb-4" id="searchPanel">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                        <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Personalization Setup</h2>
                        <span class="ml-auto badge badge-primary py-2 px-3">₦{{ number_format($price ?? 100, 2) }}</span>
                    </div>

                    <form id="personalizationForm" action="{{ route('services.personalization.verify') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label for="tracking_id" class="font-weight-600 mb-2 small text-muted">NIMC Tracking ID</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-hashtag"></i>
                                    <input type="text" id="tracking_id" name="tracking_id" class="form-control" value="{{ request('tracking_id') ?? request('number') }}" placeholder="Enter NIMC Tracking ID (e.g., ABC12345XYZ)" required>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fa-solid fa-circle-info mr-1 text-primary"></i> Submitting a tracking ID initiates identity profile personalization on NIMC records.
                                </small>
                            </div>
                            <div class="col-md-4 text-right mt-2 d-flex align-items-end justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5 w-100" id="submit-btn" style="height: 50px;">
                                    <i class="fa-solid fa-bolt mr-2"></i> Set Up Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Result Area -->
            <div class="col-lg-12" id="resultArea" style="display: none;">
                <div class="panel-card p-4">
                    <div id="resultContent" class="text-white">
                        <!-- Dynamic Result & Printable NIN Slip rendered here -->
                    </div>
                </div>
            </div>

            <!-- Vault Panel -->
            <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4">Personalization History</h3>
                    <div class="table-responsive">
                        <table class="table admin-table">
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
                                    @php
                                        $isSucc = in_array(strtolower((string)$res->status), ['successful', 'success', 'cleared', 'active', 'completed']);
                                        $isPend = in_array(strtolower((string)$res->status), ['pending', 'waiting_for_review', 'processing']);
                                    @endphp
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                        <td><strong>{{ $res->identifier }}</strong></td>
                                        <td>
                                            @if($isSucc)
                                                <span class="badge badge-success px-2 py-1"><i class="fa-solid fa-circle-check mr-1"></i> Completed</span>
                                            @elseif($isPend)
                                                <span class="badge badge-warning px-2 py-1"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing</span>
                                            @else
                                                <span class="badge badge-danger px-2 py-1"><i class="fa-solid fa-circle-xmark mr-1"></i> Failed</span>
                                            @endif
                                        </td>
                                        <td>{{ $res->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick='viewResult(@json($res->response_data), "{{ $res->reference_id }}", "{{ $res->identifier }}", "{{ $res->created_at->format('M d, Y H:i') }}", "{{ route('services.personalization.report', $res->id) }}", {{ $res->id }}, "{{ $res->status }}")'>
                                                    <i class="fa fa-eye mr-1"></i> View
                                                </button>
                                                @if($isPend)
                                                    <button class="btn btn-outline-warning" onclick='checkVaultItemStatus({{ $res->id }}, "{{ $res->identifier }}", this)' title="Check live status from provider">
                                                        <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh
                                                    </button>
                                                @endif
                                                <a href="{{ route('services.personalization.report', $res->id) }}" class="btn btn-outline-info" target="_blank" title="View printable certificate & slip">
                                                    <i class="fa-solid fa-file-invoice mr-1"></i> Report
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No records found in vault.</td>
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
    .s-tab.active { color: #8b5cf6; border-bottom-color: #8b5cf6; }
    .main-panel { display: none; }
    .main-panel.active { display: block; }
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 20px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .btn-wide { padding-left: 40px; padding-right: 40px; border-radius: 12px; }

    /* Printable Slip Styling inside Nexus Dark Theme */
    .slip-box {
        background: #ffffff;
        color: #0f172a;
        border: 2px solid #008751;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 10px 30px rgba(0, 135, 81, 0.15);
    }
    .slip-box-header {
        text-align: center;
        border-bottom: 2px solid #008751;
        padding-bottom: 12px;
        margin-bottom: 18px;
    }
    .slip-box-country {
        font-size: 14px;
        font-weight: 800;
        color: #008751;
        letter-spacing: 0.05em;
    }
    .slip-box-sub {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .slip-photo-box {
        width: 100px;
        height: 120px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slip-nin-strip {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
    }
    .slip-nin-digits {
        font-family: 'JetBrains Mono', monospace;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: 2px;
        color: #047857;
    }
</style>
@endpush

@push('scripts')
<script>
    let activePollInterval = null;

    function switchMainPanel(panel, btn) {
        $('.main-panel').hide().removeClass('active');
        $('#panel-' + panel).show().addClass('active');
        $('.s-tab').removeClass('active');
        $(btn).addClass('active');
    }

    function copyText(text, msg = 'Copied to clipboard!') {
        if (!text) return;
        navigator.clipboard.writeText(text.trim()).then(function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: msg,
                showConfirmButton: false,
                timer: 2000,
                background: '#0f172a',
                color: '#fff'
            });
        });
    }

    function resetPersonalization() {
        if (activePollInterval) clearInterval(activePollInterval);
        $('#resultArea').hide();
        $('#searchPanel').fadeIn();
        $('#tracking_id').val('').focus();
    }

    function checkVaultItemStatus(resId, trackingId, btnEl) {
        let original = $(btnEl).html();
        $(btnEl).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('services.personalization.verify') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                mode: 'status',
                tracking_id: trackingId,
                result_id: resId
            },
            success: function(resp) {
                $(btnEl).prop('disabled', false).html(original);
                if (resp.status) {
                    if (resp.is_completed || resp.request_status === 'completed') {
                        Swal.fire({
                            title: 'Personalization Completed!',
                            text: 'Your identity record and NIN slip are now ready.',
                            icon: 'success',
                            confirmButtonText: 'View Slip',
                            confirmButtonColor: '#8b5cf6',
                            background: '#0a0a0f',
                            color: '#fff'
                        }).then(() => {
                            viewResult(resp.data, resp.reference_id, resp.tracking_id, 'Just now', resp.report_url, resp.result_id, 'successful');
                        });
                    } else if (resp.is_pending) {
                        Swal.fire({
                            title: 'Still Processing',
                            text: 'Your request was accepted and is still being processed on the NIMC gateway.',
                            icon: 'info',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    } else {
                        Swal.fire({
                            title: 'Status Update',
                            text: resp.message || 'Processing status checked.',
                            icon: 'info',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                } else {
                    Swal.fire({ title: 'Check Failed', text: resp.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                }
            },
            error: function() {
                $(btnEl).prop('disabled', false).html(original);
                Swal.fire({ title: 'Error', text: 'Gateway timeout while checking status.', icon: 'error', background: '#0a0a0f', color: '#fff' });
            }
        });
    }

    function viewResult(data, refId = null, trackingId = null, dateStr = null, reportUrl = null, resultId = null, rawStatus = null) {
        if (activePollInterval) clearInterval(activePollInterval);

        let tid = trackingId || (data && (data.tracking_id || data.number)) || $('#tracking_id').val() || 'N/A';
        let ref = refId || (data && data.reference_id) || 'CONFIRMED';
        let resId = resultId || (data && data.result_id) || null;
        let repUrl = reportUrl || (resId ? `{{ url('/services/personalization/report') }}/${resId}` : null);
        let dateDisplay = dateStr || 'Just now';

        let statusStr = (rawStatus || (data && (data.request_status || data.status)) || 'pending').toLowerCase();
        let isSuccess = ['successful', 'success', 'cleared', 'active', 'completed'].includes(statusStr);
        let isPending = ['pending', 'waiting_for_review', 'processing', 'in_progress', 'accepted', 'submitted'].includes(statusStr);

        // Check nested candidates
        let userData = (data && typeof data === 'object') ? (data.user_data || (data.data && data.data.user_data) || data.data || data) : {};
        if (typeof userData !== 'object' || userData === null) userData = {};

        let nin = data?.nin || userData?.nin || (data?.data && data?.data?.nin) || null;
        let firstname = userData?.firstname || userData?.first_name || data?.firstname || '';
        let lastname = userData?.lastname || userData?.last_name || userData?.surname || data?.lastname || '';
        let middlename = userData?.middlename || userData?.middle_name || data?.middlename || '';
        let dob = userData?.birthdate || userData?.dob || data?.dob || '—';
        let gender = userData?.gender || userData?.sex || data?.gender || '—';
        let photo = userData?.photo || userData?.image || data?.photo || null;

        if (nin && String(nin).length >= 10) {
            isSuccess = true;
            isPending = false;
        }

        let formattedNin = nin ? String(nin).replace(/\D+/g, '') : '';
        if (formattedNin.length === 11) {
            formattedNin = formattedNin.substring(0, 4) + ' ' + formattedNin.substring(4, 7) + ' ' + formattedNin.substring(7);
        } else {
            formattedNin = nin || 'Generating...';
        }

        let regularSlipUrl = resId ? `{{ url('/services/nin/slip') }}/${resId}/regular_slip?print=1` : null;
        let premiumSlipUrl = resId ? `{{ url('/services/nin/slip') }}/${resId}/premium_slip?print=1` : null;
        let downloadSlipUrl = resId ? `{{ url('/services/nin/slip') }}/${resId}/regular_slip?download=1` : null;

        let html = '';

        if (isPending) {
            // Live Polling / Pending Card
            html = `
                <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, rgba(217, 119, 6, 0.12), rgba(180, 83, 9, 0.05)); border: 1px solid rgba(217, 119, 6, 0.35); box-shadow: 0 10px 30px -10px rgba(217, 119, 6, 0.2);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom border-white-10">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark px-3 py-1.5 font-weight-bold">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing Personalization
                            </span>
                            <span class="text-white-50 small">${dateDisplay}</span>
                        </div>
                        <code class="text-warning font-weight-bold font-monospace fs-6">${ref}</code>
                    </div>

                    <div class="text-center py-4">
                        <div style="font-size: 42px; color: #d97706; margin-bottom: 16px;">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </div>
                        <h4 class="text-white font-weight-bold mb-2">Ticket Accepted — Polling for Outcome</h4>
                        <p class="text-white-50 mx-auto mb-4" style="max-width: 540px;" id="pollStatusMsg">
                            Your tracking ID <strong>${tid}</strong> has been submitted. The gateway is personalizing your profile and generating your printable NIN slip. We are checking status automatically...
                        </p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button type="button" class="btn btn-warning font-weight-bold px-4 py-2" id="manualPollBtn" onclick="pollStatusNow('${tid}', '${ref}', ${resId})">
                                <i class="fa-solid fa-arrows-rotate mr-1"></i> Check Status Now
                            </button>
                            <button type="button" class="btn btn-outline-light px-3 py-2" onclick="resetPersonalization()">
                                <i class="fa-solid fa-arrow-left mr-1"></i> Submit Another
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Start auto-poll every 5 seconds
            activePollInterval = setInterval(() => {
                pollStatusNow(tid, ref, resId);
            }, 5000);

        } else {
            // SUCCESSFUL / COMPLETED VIEW WITH PRINTABLE NIN SLIP
            html = `
                <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.12), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 10px 30px -10px rgba(139, 92, 246, 0.2);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom border-white-10">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white px-3 py-1.5 font-weight-bold">
                                <i class="fa-solid fa-circle-check mr-1"></i> Personalization Completed
                            </span>
                            <span class="text-white-50 small">${dateDisplay}</span>
                        </div>
                        <code class="text-primary font-weight-bold font-monospace fs-6">${ref}</code>
                    </div>

                    <!-- Action Headline -->
                    <div class="p-3 rounded-3 mb-4" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <small class="text-success d-block mb-1 font-weight-bold"><i class="fa-solid fa-circle-check mr-1"></i> Personalization Confirmed:</small>
                                <p class="mb-0 text-white font-weight-500 fs-6">Identity activated on NIMC database. Your official printable NIN slip is ready below.</p>
                            </div>
                            <div class="d-flex gap-2">
                                ${regularSlipUrl ? `
                                    <a href="${regularSlipUrl}" target="_blank" class="btn btn-success btn-sm font-weight-bold">
                                        <i class="fa-solid fa-print mr-1"></i> Print Regular Slip
                                    </a>
                                ` : ''}
                                ${premiumSlipUrl ? `
                                    <a href="${premiumSlipUrl}" target="_blank" class="btn btn-primary btn-sm font-weight-bold">
                                        <i class="fa-solid fa-id-card mr-1"></i> Print Premium ID
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- EMBEDDED PRINTABLE NIN SLIP (The actual result expected) -->
                    <div class="slip-box mb-4">
                        <div class="slip-box-header">
                            <div class="slip-box-country">FEDERAL REPUBLIC OF NIGERIA</div>
                            <div class="slip-box-sub">NATIONAL IDENTITY MANAGEMENT COMMISSION • REGULAR NIN SLIP</div>
                        </div>

                        <div class="row align-items-start g-3">
                            <div class="col-auto">
                                <div class="slip-photo-box">
                                    ${photo ? `<img src="${photo.startsWith('http') || photo.startsWith('data:') ? photo : 'data:image/jpeg;base64,' + photo}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="fa-solid fa-user" style="font-size: 42px; color: #cbd5e1;"></i>`}
                                </div>
                            </div>
                            <div class="col">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">Surname / Nom</small>
                                        <span class="font-weight-bold text-dark fs-6">${(lastname || '—').toUpperCase()}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">Given Names / Prénoms</small>
                                        <span class="font-weight-bold text-dark fs-6">${(`${firstname} ${middlename}`.trim() || '—').toUpperCase()}</span>
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">Date of Birth</small>
                                        <span class="font-weight-bold text-dark">${dob.toUpperCase()}</span>
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">Sex / Sexe</small>
                                        <span class="font-weight-bold text-dark">${gender.toUpperCase().substring(0, 1)}</span>
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px;">Tracking ID</small>
                                        <span class="font-monospace font-weight-bold text-dark">${tid}</span>
                                    </div>
                                </div>

                                <div class="slip-nin-strip">
                                    <span class="font-weight-bold text-success" style="font-size: 12px; text-transform: uppercase;">
                                        <i class="fa-solid fa-fingerprint mr-1"></i> National Identification Number (NIN)
                                    </span>
                                    <span class="slip-nin-digits">${formattedNin}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Print & Download Actions -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-3 border-top border-light">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                ${regularSlipUrl ? `
                                    <a href="${regularSlipUrl}" target="_blank" class="btn btn-success font-weight-bold px-3 py-2">
                                        <i class="fa-solid fa-print mr-1"></i> Print Regular NIN Slip
                                    </a>
                                ` : ''}
                                ${premiumSlipUrl ? `
                                    <a href="${premiumSlipUrl}" target="_blank" class="btn btn-primary font-weight-bold px-3 py-2">
                                        <i class="fa-solid fa-id-card mr-1"></i> Print Premium Plastic ID
                                    </a>
                                ` : ''}
                                ${downloadSlipUrl ? `
                                    <a href="${downloadSlipUrl}" class="btn btn-dark font-weight-bold px-3 py-2">
                                        <i class="fa-solid fa-download mr-1"></i> Download PDF Slip
                                    </a>
                                ` : ''}
                            </div>
                            <button type="button" class="btn btn-outline-secondary px-3 py-2" onclick="copyText('${formattedNin}', 'NIN copied!')">
                                <i class="fa-solid fa-copy mr-1"></i> Copy NIN
                            </button>
                        </div>
                    </div>

                    <!-- Footer Actions: NO REDIRECT - stays on page -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-white-10">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            ${repUrl ? `
                                <a href="${repUrl}" target="_blank" class="btn btn-outline-info font-weight-bold px-3 py-2">
                                    <i class="fa-solid fa-file-invoice mr-1"></i> View Full Certificate
                                </a>
                            ` : ''}
                            <button type="button" class="btn btn-outline-light px-3 py-2" onclick="copyText('${tid}', 'Tracking ID copied!')">
                                <i class="fa-solid fa-copy mr-1"></i> Copy Tracking ID
                            </button>
                        </div>
                        <button type="button" class="btn btn-outline-light px-3 py-2" onclick="resetPersonalization()">
                            <i class="fa-solid fa-plus mr-1"></i> Personalize Another ID
                        </button>
                    </div>
                </div>
            `;
        }

        $('#searchPanel').hide();
        $('#panel-vault').hide();
        $('#resultContent').html(html);
        $('#resultArea').fadeIn();
    }

    function pollStatusNow(tid, ref, resId) {
        let btn = $('#manualPollBtn');
        if (btn.length) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Checking...');
        }

        $.ajax({
            url: "{{ route('services.personalization.verify') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                mode: 'status',
                tracking_id: tid,
                result_id: resId
            },
            success: function(resp) {
                if (btn.length) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-arrows-rotate mr-1"></i> Check Status Now');
                }

                if (resp.status) {
                    if (resp.is_completed || resp.request_status === 'completed') {
                        if (activePollInterval) clearInterval(activePollInterval);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Personalization Completed! Loading NIN Slip...',
                            showConfirmButton: false,
                            timer: 2500,
                            background: '#0f172a',
                            color: '#fff'
                        });
                        viewResult(resp.data, resp.reference_id, resp.tracking_id, 'Just now', resp.report_url, resp.result_id, 'successful');
                    } else if (resp.is_failed || resp.request_status === 'failed') {
                        if (activePollInterval) clearInterval(activePollInterval);
                        $('#pollStatusMsg').html('<span class="text-danger">Personalization request failed on gateway: ' + (resp.message || 'Record not found.') + '</span>');
                    } else {
                        $('#pollStatusMsg').html(`Last checked: <strong>${new Date().toLocaleTimeString()}</strong> — Status is still processing on NIMC gateway...`);
                    }
                }
            },
            error: function() {
                if (btn.length) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-arrows-rotate mr-1"></i> Check Status Now');
                }
            }
        });
    }

    $(document).ready(function() {
        $('#personalizationForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();
            let submittedTid = $('#tracking_id').val().trim();

            Swal.fire({
                title: 'Confirm Personalization',
                text: 'A fee of ₦{{ number_format($price, 2) }} will be charged on submission. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Personalize ID',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            btn.prop('disabled', false).html(originalHtml);
                            if (response.status) {
                                let repUrl = response.report_url || (response.result_id ? `{{ url('/services/personalization/report') }}/${response.result_id}` : null);
                                let isDone = response.is_completed || response.request_status === 'completed';

                                // Render result on page (no redirect anywhere)
                                viewResult(
                                    response.data, 
                                    response.reference_id, 
                                    response.tracking_id || submittedTid, 
                                    'Just now', 
                                    repUrl, 
                                    response.result_id, 
                                    response.request_status
                                );

                                // Smooth scroll down to result area
                                $('html, body').animate({
                                    scrollTop: $("#resultArea").offset().top - 80
                                }, 300);

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: isDone ? 'success' : 'info',
                                    title: response.message || (isDone ? 'Personalization Successful' : 'Ticket Accepted for Processing'),
                                    showConfirmButton: false,
                                    timer: 3000,
                                    background: '#0f172a',
                                    color: '#fff'
                                });
                            } else {
                                Swal.fire({ title: 'Setup Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(originalHtml);
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Personalization service is currently busy.';
                            Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
