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
            <p class="text-muted small">Advanced identity personalization and profile management for verified individuals.</p>
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
                    <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Profile Configuration</h4>
                    <div id="resultContent" class="text-white">
                        <!-- Dynamic -->
                    </div>
                </div>
                <div class="text-center mt-5">
                    <button class="btn btn-outline-light btn-wide" onclick="window.location.reload()">New Setup</button>
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
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $res)
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                        <td><strong>{{ $res->identifier }}</strong></td>
                                        <td>{{ $res->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick='viewResult(@json($res->response_data), "{{ $res->reference_id }}", "{{ $res->identifier }}", "{{ $res->created_at->format('M d, Y H:i') }}", "{{ route('services.personalization.report', $res->id) }}")'>
                                                    <i class="fa fa-eye mr-1"></i> View
                                                </button>
                                                <a href="{{ route('services.personalization.report', $res->id) }}" class="btn btn-outline-info" target="_blank" title="View printable certificate">
                                                    <i class="fa-solid fa-file-invoice mr-1"></i> Certificate
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">No records found in vault.</td>
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
        $('#resultArea').hide();
        $('#searchPanel').fadeIn();
        $('#tracking_id').val('').focus();
    }

    function viewResult(data, refId = null, trackingId = null, dateStr = null, reportUrl = null) {
        let tid = trackingId || (data && (data.tracking_id || data.number)) || $('#tracking_id').val() || 'N/A';
        let ref = refId || (data && data.reference_id) || 'CONFIRMED';
        let msg = (data && (data.message || data.detail)) ? (data.message || data.detail) : 'Identity record successfully personalized and activated on NIMC database.';
        let repUrl = reportUrl || (data && data.report_url) || null;
        let dateDisplay = dateStr || 'Just now';

        // Extract any extra API key-value fields to display in a clean report table
        let extraRows = '';
        if (data && typeof data === 'object') {
            let detailsObj = (data.data && typeof data.data === 'object') ? data.data : data;
            const skipKeys = ['_transaction_id', 'report_url', 'reference_id', 'result_id', 'status', 'message'];
            
            for (let [k, v] of Object.entries(detailsObj)) {
                if (skipKeys.includes(k) || typeof v === 'object' || v === null || v === undefined) continue;
                let formattedKey = k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                extraRows += `
                    <tr>
                        <th class="text-white-50 small py-2 px-3">${formattedKey}:</th>
                        <td class="text-white font-weight-600 py-2 px-3">${v}</td>
                    </tr>
                `;
            }
        }

        let html = `
            <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.12), rgba(124, 58, 237, 0.05)); border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 10px 30px -10px rgba(139, 92, 246, 0.2);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom border-white-10">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white px-3 py-1.5 font-weight-bold">
                            <i class="fa-solid fa-circle-check mr-1"></i> Personalization Report
                        </span>
                        <span class="text-white-50 small">${dateDisplay}</span>
                    </div>
                    <code class="text-primary font-weight-bold font-monospace fs-6">${ref}</code>
                </div>

                <!-- API Response Headline -->
                <div class="p-3 rounded-3 mb-4" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                    <small class="text-success d-block mb-1 font-weight-bold"><i class="fa-solid fa-circle-check mr-1"></i> API Confirmation:</small>
                    <p class="mb-0 text-white font-weight-500 fs-6">${msg}</p>
                </div>

                <!-- Core Details Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-weight-600 mb-1">Personalized NIMC Tracking ID</small>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="font-monospace fs-4 text-warning font-weight-bold tracking-wider">${tid}</span>
                                <button type="button" class="btn btn-sm btn-outline-light px-2.5" onclick="copyText('${tid}')" title="Copy ID">
                                    <i class="fa-solid fa-copy mr-1"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-weight-600 mb-1">Status on Identity Records</small>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-success text-white px-3 py-1 font-weight-bold">Active &amp; Configured</span>
                                <small class="text-white-50">Ready for NIN retrieval</small>
                            </div>
                        </div>
                    </div>
                </div>

                ${extraRows ? `
                    <!-- Extra Details Returned from Gateway -->
                    <div class="table-responsive rounded-3 mb-4" style="border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2);">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                ${extraRows}
                            </tbody>
                        </table>
                    </div>
                ` : ''}

                <!-- Actions: NO REDIRECT - stays on page -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-white-10">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('services.nin.suite') }}" class="btn btn-primary font-weight-bold px-3 py-2">
                            <i class="fa-solid fa-id-card mr-1"></i> Retrieve NIN Slip
                        </a>
                        <button type="button" class="btn btn-outline-info font-weight-bold px-3 py-2" onclick="window.print()">
                            <i class="fa-solid fa-print mr-1"></i> Print Report
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-light px-3 py-2" onclick="resetPersonalization()">
                        <i class="fa-solid fa-plus mr-1"></i> Personalize Another ID
                    </button>
                </div>
            </div>
        `;

        $('#searchPanel').hide();
        $('#panel-vault').hide();
        $('#resultContent').html(html);
        $('#resultArea').fadeIn();
    }

    $(document).ready(function() {
        $('#personalizationForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();
            let submittedTid = $('#tracking_id').val().trim();

            Swal.fire({
                title: 'Confirm Personalization',
                text: 'A fee of ₦{{ number_format($price, 2) }} will be charged. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Personalize ID',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Setting up...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            btn.prop('disabled', false).html(originalHtml);
                            if (response.status) {
                                let repUrl = response.report_url || (response.result_id ? `{{ url('/services/personalization/report') }}/${response.result_id}` : null);
                                
                                // Display report from API right on the page (no redirect anywhere)
                                viewResult(response.data, response.reference_id, response.tracking_id || submittedTid, 'Just now', repUrl);

                                // Smooth scroll down to result area
                                $('html, body').animate({
                                    scrollTop: $("#resultArea").offset().top - 80
                                }, 300);

                                // Lightweight toast - user stays right on this page
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message || 'Personalization Successful',
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
