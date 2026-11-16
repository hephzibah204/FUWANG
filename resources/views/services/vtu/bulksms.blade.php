@extends('layouts.nexus')

@section('title', 'Bulk SMS Service | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Bulk SMS"
        subtitle="Deliver branded SMS campaigns to thousands of Nigerian phone numbers with instant delivery."
        icon="fa-solid fa-comments"
        icon-class="sms-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Route</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> 100% Delivery Report</span>
            <span class="badge-accent"><i class="fa-solid fa-naira-sign"></i> ₦4.00 / SMS</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="bulkSmsForm" action="{{ route('services.vtu.bulksms.send') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label for="sender" class="font-weight-600 mb-2">Sender ID (Max 11 Alphanumeric characters)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-signature"></i>
                            <input type="text" name="sender" id="sender" class="form-control" placeholder="e.g. FUWA, MYSHOP" maxlength="11" required value="{{ strtoupper(substr(config('app.name', 'FUWA'), 0, 11)) }}">
                        </div>
                        <small class="text-muted mt-1 d-block">Your registered business name or brand identifier (1 to 11 characters, no symbols).</small>
                    </div>

                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="recipients" class="font-weight-600 m-0">Recipients Phone Numbers</label>
                            <span class="badge badge-info" id="recipientCountBadge">0 Recipients</span>
                        </div>
                        <textarea name="recipients" id="recipients" rows="5" class="form-control font-monospace" placeholder="Enter recipient phone numbers separated by commas or new lines&#10;e.g.&#10;08012345678&#10;08123456789, 09098765432" required></textarea>
                        <small class="text-muted mt-1 d-block">Supported formats: 080XXXXXXXX, 23480XXXXXXXX. Numbers are automatically deduplicated.</small>
                    </div>

                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="message" class="font-weight-600 m-0">Message Content</label>
                            <span class="text-muted small">
                                <span id="charCount">0</span> chars | <span id="pageCount" class="text-primary font-weight-bold">1</span> page(s)
                            </span>
                        </div>
                        <textarea name="message" id="message" rows="5" class="form-control" placeholder="Type your SMS message here..." required></textarea>
                    </div>

                    <!-- Cost Breakdown Summary -->
                    <div class="p-3 rounded mb-4" style="background: rgba(255,255,255,0.03); border: var(--border-glass);">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Recipients</div>
                                <div class="h5 font-weight-bold text-white mb-0" id="summaryRecipients">0</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Pages</div>
                                <div class="h5 font-weight-bold text-white mb-0" id="summaryPages">1</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Est. Total Cost</div>
                                <div class="h5 font-weight-bold text-success mb-0" id="summaryCost">₦0.00</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="sendBtn">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Send Bulk SMS Campaign
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Broadcasting SMS through Datasub247 Gateway...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">Quick Navigation</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-layer-group mr-2"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-mobile-screen-button mr-2"></i> Buy Airtime
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-wifi mr-2"></i> Buy Data
                    </a>
                    <a href="{{ route('services.vtu.recharge_printing') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-print mr-2"></i> Recharge Card Printing
                    </a>
                </div>
            </div>

            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-2">Bulk SMS Guidelines</h3>
                <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                    <li>1 Page = up to 160 characters.</li>
                    <li>Subsequent pages = 153 characters each.</li>
                    <li>Do not use prohibited keywords (lottery, betting scams, financial fraud).</li>
                    <li>Numbers on the DND (Do-Not-Disturb) register will be routed via transactional channels.</li>
                </ul>
            </div>

            <div class="stat-card">
                <div class="stat-icon sms-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">99.8%</div>
                <div class="stat-label">SMS Gateway Delivery</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sms-bg { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const ratePerSms = 4.00;

        function updateEstimates() {
            // Count recipients
            let raw = $('#recipients').val().trim();
            let count = 0;
            if (raw.length > 0) {
                let parts = raw.split(/[\r\n,]+/);
                let valid = [];
                parts.forEach(function(item) {
                    let cleaned = item.replace(/[^0-9]/g, '');
                    if (cleaned.length >= 10 && !valid.includes(cleaned)) {
                        valid.push(cleaned);
                    }
                });
                count = valid.length;
            }

            $('#recipientCountBadge').text(count + ' Recipient' + (count === 1 ? '' : 's'));
            $('#summaryRecipients').text(count);

            // Count characters & pages
            let msg = $('#message').val();
            let chars = msg.length;
            let pages = 1;
            if (chars > 160) {
                pages = Math.ceil(chars / 153);
            } else if (chars === 0) {
                pages = 1;
            }

            $('#charCount').text(chars);
            $('#pageCount').text(pages);
            $('#summaryPages').text(pages);

            let total = count * pages * ratePerSms;
            $('#summaryCost').text('₦' + total.toFixed(2));
        }

        $('#recipients, #message').on('input propertychange', updateEstimates);
        updateEstimates();

        $('#bulkSmsForm').on('submit', function(e) {
            e.preventDefault();
            $('#loaderOverlay').show();
            let btn = $('#sendBtn');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Transmitting SMS...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#loaderOverlay').hide();
                    if (response.status) {
                        Swal.fire({
                            title: 'SMS Sent!',
                            text: response.message || 'Bulk SMS campaign has been dispatched successfully.',
                            icon: 'success',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                        $('#recipients').val('');
                        $('#message').val('');
                        updateEstimates();
                    } else {
                        Swal.fire({
                            title: 'Failed',
                            text: response.message || 'Unable to send SMS campaign.',
                            icon: 'error',
                            background: '#0a0a0f',
                            color: '#fff'
                        });
                    }
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Send Bulk SMS Campaign');
                },
                error: function(xhr) {
                    $('#loaderOverlay').hide();
                    let errMsg = 'An unexpected error occurred while communicating with the SMS gateway.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errMsg,
                        icon: 'error',
                        background: '#0a0a0f',
                        color: '#fff'
                    });
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane mr-2"></i> Send Bulk SMS Campaign');
                }
            });
        });
    });
</script>
@endpush
