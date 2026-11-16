@extends('layouts.nexus')

@section('title', 'JAMB Profile & e-PIN | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(126, 34, 206, 0.05)); border-color: rgba(168, 85, 247, 0.2);">
        <div class="sh-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3);">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">JAMB UTME / DE e-PIN</h1>
            <p class="text-muted small">Purchase official JAMB candidate registration e-PINs instantly.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-check-double text-success"></i> Instant Delivery</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved text-primary"></i> Official e-PIN</span>
        </div>
    </div>

    <div class="row">
        <!-- Main Form Area -->
        <div class="col-lg-7">
            <div class="panel-card p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                    <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-cart-shopping mr-2 text-primary"></i> Order JAMB e-PIN</h2>
                    <span class="ml-auto badge badge-primary py-2 px-3">₦{{ number_format($price ?? \App\Models\SystemSetting::get('vtu_jamb_price', 4700), 2) }}</span>
                </div>

                <x-nexus.flash-message />

                <form id="jambForm" action="{{ route('services.education.jamb.buy') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 small text-muted">Examination Type</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-book-open"></i>
                            <select class="form-control" name="variation_code">
                                <option value="utme">JAMB UTME (With Mock or Without Mock)</option>
                                <option value="de">JAMB Direct Entry (DE)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone" class="font-weight-600 mb-2 small text-muted">Candidate Profile Phone / Code</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="08012345678" maxlength="11" required>
                        </div>
                        <small class="text-muted mt-2 d-block"><i class="fa-solid fa-circle-info mr-1"></i> The e-PIN will be sent to the registered phone number linked to the candidate profile.</small>
                    </div>

                    @if(isset($providers) && $providers->count() > 1)
                    <div class="form-group mb-4">
                        <label for="provider_id" class="font-weight-600 mb-2 small text-muted">Network Band</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-server"></i>
                            <select id="provider_id" name="provider_id" class="form-control">
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">Band {{ $loop->iteration }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @elseif(isset($providers) && $providers->count() == 1)
                        <input type="hidden" name="provider_id" value="{{ $providers->first()->id }}">
                    @endif

                    <div class="p-3 mb-4 rounded" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Service Charge</span>
                            <span class="small">₦0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold">Total Amount</span>
                            <span class="font-weight-bold text-primary">₦{{ number_format($price ?? \App\Models\SystemSetting::get('vtu_jamb_price', 4700), 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn" style="height: 55px;">
                        <i class="fa-solid fa-bolt mr-2"></i> Purchase JAMB e-PIN Now
                    </button>
                </form>

                <!-- Result Display (Hidden by Default) -->
                <div id="resultArea" class="mt-4 animate__animated animate__fadeIn" style="display: none;">
                    <div class="p-4 rounded-lg text-center" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div class="mb-3">
                            <i class="fa-solid fa-circle-check text-success fa-3x"></i>
                        </div>
                        <h4 class="h5 font-weight-bold text-white mb-1">Purchase Successful</h4>
                        <p class="text-muted small mb-4">Your JAMB Registration e-PIN is ready.</p>
                        
                        <div class="pin-display-card p-3 mb-4" style="background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                            <div class="row no-gutters align-items-center">
                                <div class="col-6 border-right border-white-10">
                                    <span class="text-muted d-block small mb-1">CONFIRMATION CODE</span>
                                    <strong id="res-serial" class="h6 text-primary tracking-wider">-</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block small mb-1">JAMB e-PIN</span>
                                    <strong id="res-pin" class="h6 text-white tracking-wider">-</strong>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-sm btn-outline-light w-100" onclick="copyPin()">
                            <i class="fa-solid fa-copy mr-2"></i> Copy e-PIN
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Instructions -->
        <div class="col-lg-5">
            <div class="panel-card p-4 h-100">
                <h3 class="h6 font-weight-bold mb-4 border-bottom border-white-5 pb-2">How It Works</h3>
                <ul class="list-unstyled">
                    <li class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary-soft d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; font-size: 12px; font-weight: 800; color: var(--clr-primary);">1</div>
                        <div class="small text-muted">Candidate must first generate a profile code by sending NIN to 55019 or 66019.</div>
                    </li>
                    <li class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary-soft d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; font-size: 12px; font-weight: 800; color: var(--clr-primary);">2</div>
                        <div class="small text-muted">Wallet is debited ₦{{ number_format($price ?? \App\Models\SystemSetting::get('vtu_jamb_price', 4700), 2) }}. The official e-PIN is generated immediately.</div>
                    </li>
                    <li class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary-soft d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; font-size: 12px; font-weight: 800; color: var(--clr-primary);">3</div>
                        <div class="small text-muted">Take the generated e-PIN to any accredited JAMB CBT centre to complete candidate biometric registration.</div>
                    </li>
                </ul>

                <hr class="border-white-5 my-4">

                <div class="kyc-banner p-4">
                    <i class="fa-solid fa-shield-halved fa-2x mb-3 text-primary opacity-50"></i>
                    <h4 class="h6 font-weight-bold mb-2">Official JAMB e-PINs</h4>
                    <p class="small text-muted m-0">Directly authorized for UTME and Direct Entry candidate registration nationwide.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-header-card { border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .badge-accent { background: rgba(255,255,255,0.05); border: var(--border-glass); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; color: var(--clr-text-muted); margin-left: 8px; }
    .panel-card { background: var(--clr-bg-card); backdrop-filter: blur(25px); border: var(--border-glass); border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 18px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 50px !important; height: 52px; }
    .bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
    .tracking-wider { letter-spacing: 1px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#jambForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalBtnHtml = btn.html();
            
            Swal.fire({
                title: 'Confirm Purchase',
                text: "You are about to purchase a JAMB e-PIN for ₦{{ number_format($price ?? \App\Models\SystemSetting::get('vtu_jamb_price', 4700), 2) }}. Continue?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#1e293b',
                confirmButtonText: 'Yes, Purchase Now',
                background: '#0a0a0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Generating e-PIN...');
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                $('#jambForm').slideUp();
                                $('#res-pin').text(response.pin || '-');
                                $('#res-serial').text(response.serial || '-');
                                $('#resultArea').fadeIn();
                                Swal.fire({ title: 'Success!', text: 'e-PIN generated successfully!', icon: 'success', background: '#0a0a0f', color: '#fff' });
                            } else {
                                Swal.fire({ title: 'Transaction Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                                btn.prop('disabled', false).html(originalBtnHtml);
                            }
                        },
                        error: function(xhr) {
                            let errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to communicate with the payment server.';
                            Swal.fire({ title: 'Network Error', text: errMsg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                            btn.prop('disabled', false).html(originalBtnHtml);
                        }
                    });
                }
            });
        });
    });

    function copyPin() {
        let text = "JAMB CODE: " + $('#res-serial').text() + "\nJAMB e-PIN: " + $('#res-pin').text();
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Details Copied', showConfirmButton: false, timer: 2000, background: '#1e293b', color: '#fff' });
        });
    }
</script>
@endpush
