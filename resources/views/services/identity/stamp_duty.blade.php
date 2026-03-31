@extends('layouts.nexus')

@section('title', 'Stamp Duty Verification | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(219, 39, 119, 0.05)); border-color: rgba(236, 72, 153, 0.2);">
        <div class="sh-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899; border: 1px solid rgba(236, 72, 153, 0.3);">
            <i class="fa-solid fa-stamp"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Stamp Duty Verification</h1>
            <p class="text-muted small">Validate official stamp duty reference numbers instantly.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-file-check text-pink"></i> Certified Records</span>
            <span class="badge-accent"><i class="fa-solid fa-building-columns text-info"></i> Official Database</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="panel-card p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-magnifying-glass mr-2 text-primary"></i> Document Details</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦200.00</span>
                </div>

                <form id="stampForm" action="{{ route('services.stamp_duty.verify') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label for="number" class="font-weight-600 mb-2 small text-muted">Stamp Duty Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-hashtag"></i>
                            <input type="text" id="number" name="number" class="form-control" placeholder="2022-0000-1111-2222" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="customer_name" class="font-weight-600 mb-2 small text-muted">Customer Name</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Full Name on Document" required>
                        </div>
                    </div>

                    @if(isset($stampProviders) && $stampProviders->count() > 1)
                    <div class="form-group mb-4">
                        <label for="api_provider_id" class="font-weight-600 mb-2 small text-muted">Verification Provider</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-server"></i>
                            <select id="api_provider_id" name="api_provider_id" class="form-control">
                                @foreach($stampProviders as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @elseif(isset($stampProviders) && $stampProviders->count() == 1)
                        <input type="hidden" name="api_provider_id" value="{{ $stampProviders->first()->id }}">
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-check-double mr-2"></i> Verify Authenticity
                    </button>
                </form>

                <!-- Results display area -->
                <div id="resultArea" class="mt-4" style="display: none;">
                    <div class="p-4 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-box" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-certificate fa-lg"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="h6 font-weight-bold text-white mb-0">Record Verified Successfully</h4>
                                <span class="x-small text-muted" id="res-ref">Ref: -</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Certificate Number</label>
                                <strong id="res-cert" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Instrument Type</label>
                                <strong id="res-instrument" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Consideration</label>
                                <strong id="res-consideration" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Duty Paid</label>
                                <strong id="res-paid" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Execution Date</label>
                                <strong id="res-execution" class="text-white small">-</strong>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom border-white-5 pb-2">
                                <label class="x-small text-muted d-block mb-1">Issuance Date</label>
                                <strong id="res-issuance" class="text-white small">-</strong>
                            </div>
                            <div class="col-12">
                                <label class="x-small text-muted d-block mb-1">Beneficiary</label>
                                <strong id="res-beneficiary" class="text-white small">-</strong>
                            </div>
                        </div>

                        <button class="btn btn-outline-light btn-block mt-4" onclick="window.location.reload()">New Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel-card p-4">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">Stamp Duty FAQ</h3>
                <div class="faq-item mb-4">
                    <h6 class="small font-weight-bold text-white mb-2">What is a Stamp Duty?</h6>
                    <p class="x-small text-muted m-0">A tax imposed on legal documents such as receipts, land transactions, and other legal instruments.</p>
                </div>
                <div class="faq-item mb-4">
                    <h6 class="small font-weight-bold text-white mb-2">Why verify it?</h6>
                    <p class="x-small text-muted m-0">To ensure the document is authentic and the required tax has been paid to the government.</p>
                </div>
                
                <div class="alert alert-info p-3" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-circle-info text-info"></i>
                        <p class="x-small text-muted m-0">Ensure the Name matches exactly as it appears on the printed document.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#stampForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalHtml = btn.html();

            Swal.fire({
                title: 'Confirm Verification',
                text: "Charge ₦200.00 for Stamp Duty verification?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ec4899',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Fetching Record...');
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                let data = response.data;
                                $('#stampForm').slideUp();
                                
                                $('#res-cert').text(data.certificate_number || 'N/A');
                                $('#res-instrument').text(data.instrument || 'N/A');
                                $('#res-consideration').text(data.consideration || 'N/A');
                                $('#res-paid').text(data.stamp_duty_paid || 'N/A');
                                $('#res-execution').text(data.date_of_execution || 'N/A');
                                $('#res-issuance').text(data.issuance_date || 'N/A');
                                $('#res-beneficiary').text(data.beneficiary || 'N/A');
                                $('#res-ref').text('Ref: ' + (response.reference || 'SUCCESS'));

                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: 'Stamp duty record found.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Search Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            Swal.fire({ title: 'Error', text: 'IdentityPay server error.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
