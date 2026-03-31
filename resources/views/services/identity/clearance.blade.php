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
            <p class="text-muted small">Comprehensive background vetting and clearance checks for individuals and entities.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Check Clearance</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Clearance Vault ({{ $history->count() }})</button>
            </div>

            <div id="panel-verify" class="main-panel active">
                <div class="panel-card p-4 mb-4" id="searchPanel">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                        <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Clearance Lookup</h2>
                        <span class="ml-auto badge badge-primary py-2 px-3" id="priceBadge">₦{{ number_format($price ?? 400, 2) }}</span>
                    </div>

                    <form id="clearanceForm" action="{{ route('services.clearance.verify') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label for="mode" class="font-weight-600 mb-2 small text-muted">Request Mode</label>
                                <select id="mode" name="mode" class="form-control" onchange="updateUI(this.value)">
                                    <option value="submit">Submit New Request</option>
                                    <option value="status">Check Request Status</option>
                                </select>
                            </div>
                            <div class="col-md-5 mb-4">
                                <label for="number" class="font-weight-600 mb-2 small text-muted">Tracking ID</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-hashtag"></i>
                                    <input type="text" id="number" name="number" class="form-control" placeholder="Enter Tracking ID" required>
                                </div>
                            </div>
                            <div class="col-md-3 text-right mt-2 d-flex align-items-end justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5 w-100" id="submit-btn" style="height: 50px;">
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
                    <h4 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Clearance Report</h4>
                    <div id="resultContent" class="text-white">
                        <!-- Dynamic -->
                    </div>
                </div>
                <div class="text-center mt-5">
                    <button class="btn btn-outline-light btn-wide" onclick="window.location.reload()">New Search</button>
                </div>
            </div>

            <!-- Vault Panel -->
            <div id="panel-vault" class="main-panel col-lg-12" style="display: none;">
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4">Clearance History</h3>
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Identifier</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $res)
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                        <td>{{ $res->identifier }}</td>
                                        <td>{{ $res->created_at->format('M d, Y') }}</td>
                                        <td class="text-right">
                                            <button class="btn btn-xs btn-outline-primary" onclick='viewResult(@json($res->response_data))'>
                                                <i class="fa fa-eye"></i> View
                                            </button>
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
    }

    function viewResult(data) {
        $('#searchPanel').hide();
        $('#panel-vault').hide();
        $('#resultContent').html('<pre class="text-white">' + JSON.stringify(data, null, 4) + '</pre>');
        $('#resultArea').fadeIn();
    }

    function updateUI(mode) {
        if (mode === 'status') {
            $('#priceBadge').hide();
            $('#btnText').text('Check Status');
        } else {
            $('#priceBadge').show();
            $('#btnText').text('Submit Request');
        }
    }

    $(document).ready(function() {
        $('#clearanceForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();
            let mode = $('#mode').val();
            let confirmText = (mode === 'submit') ? 'A fee of ₦{{ number_format($price, 2) }} will be charged. Continue?' : 'Check status for this tracking ID?';

            Swal.fire({
                title: (mode === 'submit') ? 'Confirm Clearance Submission' : 'Confirm Status Check',
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
                                        title: 'Submitted!', 
                                        text: response.message, 
                                        icon: 'success', 
                                        background: '#0a0a0f', 
                                        color: '#fff' 
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    viewResult(response.data);
                                    Swal.fire({ title: 'Status Retrieved!', icon: 'success', background: '#0a0a0f', color: '#fff' });
                                }
                            } else {
                                Swal.fire({ title: 'Operation Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'Clearance gateway is currently busy.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
