@extends('layouts.nexus')

@section('title', 'TIN Identification Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(67, 56, 202, 0.05)); border-color: rgba(79, 70, 229, 0.2);">
        <div class="sh-icon" style="background: rgba(79, 70, 229, 0.15); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.3);">
            <i class="fa-solid fa-calculator"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">TIN Verification</h1>
            <p class="text-muted small">Verify Tax Identification Numbers via FIRS & JTB records.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-building-columns text-primary"></i> Tax Registry</span>
            <span class="badge-accent"><i class="fa-solid fa-check-to-slot text-success"></i> Real-time</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-strip mb-4">
                <button class="s-tab active" onclick="switchMainPanel('verify', this)">Verify Tax ID</button>
                <button class="s-tab" onclick="switchMainPanel('vault', this)">Verification Vault ({{ $myResults->count() }})</button>
            </div>

            <div id="panel-verify" class="main-panel active">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="panel-card p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Taxpayer Search</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦200.00</span>
                </div>

                <form id="tinForm" action="{{ route('services.tin_verify.verify') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="channel" class="font-weight-600 mb-2 small text-muted">Search Channel</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-sitemap"></i>
                                <select id="channel" name="channel" class="form-control" required>
                                    <option value="TIN">Tax ID (TIN)</option>
                                    <option value="CAC">RC/BN Number (CAC)</option>
                                    <option value="Phone">Phone Number</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="number" class="font-weight-600 mb-2 small text-muted">Identifier Value</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hashtag"></i>
                                <input type="text" id="number" name="number" class="form-control" placeholder="Enter identifier..." required>
                            </div>
                        </div>
                    </div>

                    @if(isset($tinProviders) && $tinProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Verification Source</label>
                        <select id="api_provider_id" name="api_provider_id" class="form-control">
                            @foreach($tinProviders as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-bolt mr-2"></i> Verify Tax Identity
                    </button>
                </form>

                <!-- Results display area -->
                <div id="resultArea" class="mt-4" style="display: none;">
                    <div class="p-4 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-sq bg-indigo-soft text-indigo mr-3">
                                <i class="fa-solid fa-user-check fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="h6 font-weight-bold text-white mb-0" id="res-name">Taxpayer Name</h4>
                                <span class="x-small text-muted">Record Verified Successfully</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">CAC Reg Number</label>
                                <strong id="res-cac" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Tax Office</label>
                                <strong id="res-office" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">FIRS TIN</label>
                                <strong id="res-firstin" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">JTB TIN</label>
                                <strong id="res-jittin" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Email</label>
                                <strong id="res-email" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Phone</label>
                                <strong id="res-phone" class="text-white small">-</strong>
                            </div>
                        </div>

                        <div id="res-actions"></div>
                        <button class="btn btn-outline-light btn-block mt-4" onclick="window.location.reload()">New Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">TIN Guidelines</h3>
                <div class="info-item mb-4 d-flex align-items-start gap-3">
                    <div class="icon-circle bg-indigo-soft text-indigo">
                        <i class="fa-solid fa-info"></i>
                    </div>
                    <div>
                        <h6 class="small font-weight-bold text-white mb-1">Multi-Channel Search</h6>
                        <p class="x-small text-muted m-0">You can find a TIN using the company's registration number or the registered phone number.</p>
                    </div>
                </div>
                <div class="info-item mb-4 d-flex align-items-start gap-3">
                    <div class="icon-circle bg-success-soft text-success">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="small font-weight-bold text-white mb-1">Verification Sources</h6>
                        <p class="x-small text-muted m-0">Queries are routed through FIRS (Federal Inland Revenue Service) and JTB (Joint Tax Board) repositories.</p>
                    </div>
                </div>
                
                <div class="alert alert-primary p-3 rounded-xl mt-4" style="background: rgba(79, 70, 229, 0.05); border: 1px solid rgba(79, 70, 229, 0.15);">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fa-solid fa-lightbulb text-indigo"></i>
                        <p class="x-small text-muted m-0">Verification is required for corporate banking, government bidding, and official documentation.</p>
                    </div>
                    </div> <!-- end col-lg-5 -->
                </div> <!-- end row -->
            </div> <!-- end panel-verify -->

            <div id="panel-vault" class="main-panel" style="display: none;">
                <div class="panel-card p-4">
                    <h3 class="h6 font-weight-bold mb-4">TIN Verification History</h3>
                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Taxpayer Name</th>
                                    <th>TIN Number</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myResults as $res)
                                    <tr>
                                        <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                        <td>{{ $res->response_data['taxpayer_name'] ?? 'N/A' }}</td>
                                        <td>{{ $res->identifier }}</td>
                                        <td>{{ $res->created_at->format('M d, Y') }}</td>
                                        <td class="text-right">
                                            <a href="/services/identity/report/{{ $res->id }}" class="btn btn-sm btn-success"><i class="fa-solid fa-file-pdf mr-1"></i> Report</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- end col-lg-12 -->
    </div> <!-- end main row -->
@endsection

@push('styles')
<style>
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 16px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .x-small { font-size: 0.7rem; }
    .icon-sq { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .bg-indigo-soft { background: rgba(79, 70, 229, 0.1); }
    .text-indigo { color: #4f46e5; }
    .icon-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
</style>
@endpush

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; border-bottom: 2px solid rgba(255, 255, 255, 0.05); }
    .s-tab { padding: 12px 25px; background: none; border: none; color: var(--clr-text-muted); font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; transition: 0.2s; }
    .s-tab.active { color: #4f46e5; border-bottom-color: #4f46e5; }
    .main-panel { display: none; }
    .main-panel.active { display: block; }
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

    $(document).ready(function() {
        $('#tinForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Lookup',
                text: "A fee of ₦200.00 will be charged for this TIN search. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Querying Tax Registry...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let data = response.data;
                                $('#tinForm').slideUp();
                                
                                $('#res-name').text(data.taxpayer_name || 'N/A');
                                $('#res-cac').text(data.cac_reg_number || 'N/A');
                                $('#res-firstin').text(data.firstin || 'N/A');
                                $('#res-jittin').text(data.jittin || 'N/A');
                                $('#res-office').text(data.tax_office || 'N/A');
                                $('#res-email').text(data.email || 'N/A');
                                $('#res-phone').text(data.phone_number || 'N/A');

                                if (response.result_id) {
                                    $('#res-actions').html('<a href="/services/identity/report/' + response.result_id + '" class="btn btn-success btn-block mt-4"><i class="fa-solid fa-file-pdf mr-2"></i> Download Verification Report</a>');
                                }

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: 'Taxpayer record found.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'IdentityPay TIN services are currently busy.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
