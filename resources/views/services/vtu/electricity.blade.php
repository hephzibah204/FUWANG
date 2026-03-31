@extends('layouts.nexus')

@section('title', 'Electricity Bill Payment | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Electricity Bills"
        subtitle="Pay your prepaid or postpaid electricity bills across all DISCOs."
        icon="fa-solid fa-lightbulb"
        icon-class="elect-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Instant Token</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="electricityForm" action="{{ route('services.vtu.electricity.buy') }}" method="POST">
                    @csrf
                    <input type="hidden" name="validation_token" id="validation_token" value="">
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider</label>
                        <select class="form-control" name="provider_id" id="providerSelect">
                            <option value="">Auto-select best provider</option>
                        </select>
                        <div class="text-muted small mt-2" id="providerMeta"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider (DISCO)</label>
                        <select name="serviceID" id="serviceID" class="form-control" required>
                            <option value="">Select a DISCO</option>
                            @foreach((config('discos.electricity') ?? []) as $d)
                                <option value="{{ $d['key'] }}">{{ $d['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Meter Type</label>
                        <div class="d-flex gap-4">
                            <label class="nexus-radio">
                                <input type="radio" name="variation_code" value="prepaid" checked required>
                                <span>Prepaid</span>
                            </label>
                            <label class="nexus-radio">
                                <input type="radio" name="variation_code" value="postpaid" required>
                                <span>Postpaid</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="meter_number" class="font-weight-600 mb-2">Meter Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-id-card"></i>
                            <input type="text" name="meter_number" id="meter_number" class="form-control" placeholder="Enter Meter Number" required>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info mt-2" id="verify-meter">Verify Meter</button>
                        <div id="meterResult" class="mt-3" style="display:none;">
                            <div class="p-3 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.02);">
                                <div class="text-white font-weight-bold mb-1" id="meterName"></div>
                                <div class="text-muted small" id="meterAddress"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2">Amount (₦)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-naira-sign"></i>
                            <input type="number" name="amount" id="amount" class="form-control" placeholder="Min ₦500" min="500" required disabled>
                        </div>
                        <div class="mt-2 text-muted small" id="breakdownText"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone" class="font-weight-600 mb-2">Recipient Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="081 2345 6789" maxlength="11" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="buy-btn">
                        <i class="fa-solid fa-bolt mr-2"></i> Pay Bill
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Transmitting bill payment...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">Quick Actions</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-layer-group mr-2"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-phone mr-2"></i> Buy Airtime
                    </a>
                    <a href="{{ route('services.vtu.data') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-wifi mr-2"></i> Buy Data
                    </a>
                    <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-tv mr-2"></i> Cable TV Hub
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon elect-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">Verified</div>
                <div class="stat-label">Secure Payments</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .service-header-card { background: rgba(255,255,255,0.03); border: var(--border-glass); border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .sh-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .elect-bg { background: rgba(234, 179, 8, 0.1); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.2); }
    .sh-text h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .sh-text p { margin: 4px 0 0; color: var(--clr-text-muted); font-size: 0.95rem; }
    
    .nexus-radio { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .nexus-radio input { accent-color: var(--clr-primary); }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        const providersUrl = @json(route('services.vtu.providers', ['serviceType' => 'vtu_electricity']));
        const validateUrl = @json(route('services.vtu.electricity.validate'));
        let providers = [];

        function fmt(n) {
            const v = Number(n || 0);
            return '₦' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function selectedProvider() {
            const id = $('#providerSelect').val();
            if (!id) return null;
            return providers.find(p => String(p.id) === String(id)) || null;
        }

        function computeFee(provider, amount) {
            const a = Number(amount || 0);
            if (!provider || !a) return { fee: 0, total: a };
            const t = (provider.fee_type || 'flat').toLowerCase();
            const v = Number(provider.fee_value || 0);
            let fee = 0;
            if (v > 0) fee = (t === 'percent') ? (a * v / 100) : v;
            fee = Math.max(0, Math.round(fee * 100) / 100);
            return { fee, total: Math.max(0, a + fee) };
        }

        function invalidateValidation() {
            $('#validation_token').val('');
            $('#meterResult').hide();
            $('#amount').prop('disabled', true);
            $('#buy-btn').prop('disabled', true);
        }

        function renderMeta() {
            const p = selectedProvider();
            const a = Number($('#amount').val() || 0);
            const { fee, total } = computeFee(p, a);
            const min = p && p.min_amount != null ? Number(p.min_amount) : null;
            const max = p && p.max_amount != null ? Number(p.max_amount) : null;

            let meta = [];
            if (p) {
                if (Number(p.fee_value || 0) > 0) meta.push('Fee: ' + (String(p.fee_type).toLowerCase() === 'percent' ? (p.fee_value + '%') : fmt(p.fee_value)));
                if (min) meta.push('Min: ' + fmt(min));
                if (max) meta.push('Max: ' + fmt(max));
            }
            $('#providerMeta').text(meta.join(' • '));

            if (a > 0) {
                $('#breakdownText').text('Fee: ' + fmt(fee) + ' • Total debit: ' + fmt(total));
            } else {
                $('#breakdownText').text('');
            }
        }

        $.get(providersUrl).done(function(res) {
            if (!res || !res.status) return;
            providers = res.providers || [];
            providers.forEach(p => $('#providerSelect').append(`<option value="${p.id}">${p.name}</option>`));
        }).always(renderMeta);

        invalidateValidation();

        $('#providerSelect').on('change', function() {
            invalidateValidation();
            renderMeta();
        });
        $('#serviceID, input[name="variation_code"], #meter_number').on('change keyup', function() {
            invalidateValidation();
        });
        $('#amount').on('input', renderMeta);

        $('#verify-meter').on('click', function() {
            const btn = $(this);
            const disco = $('#serviceID').val();
            const variation = $('input[name="variation_code"]:checked').val();
            const meter = $('#meter_number').val();

            if (!disco || !variation || !meter) {
                Swal.fire({ title: 'Missing info', text: 'Select DISCO, meter type, and enter meter number.', icon: 'warning', background: '#0a0a0f', color: '#fff' });
                return;
            }

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Verifying...');

            $.ajax({
                url: validateUrl,
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    provider_id: $('#providerSelect').val(),
                    serviceID: disco,
                    variation_code: variation,
                    meter_number: meter
                },
                success: function(r) {
                    if (r && r.status) {
                        $('#validation_token').val(r.validation_token || '');
                        $('#amount').prop('disabled', false);
                        $('#buy-btn').prop('disabled', false);
                        $('#meterName').text((r.customer && r.customer.name) ? r.customer.name : 'Meter validated');
                        $('#meterAddress').text((r.customer && r.customer.address) ? r.customer.address : '');
                        $('#meterResult').show();
                        Swal.fire({ title: 'Verified', text: r.message || 'Meter validated.', icon: 'success', background: '#0a0a0f', color: '#fff' });
                    } else {
                        invalidateValidation();
                        Swal.fire({ title: 'Failed', text: (r && r.message) ? r.message : 'Unable to validate meter.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    }
                    btn.prop('disabled', false).html('Verify Meter');
                },
                error: function(xhr) {
                    invalidateValidation();
                    const msg = xhr?.responseJSON?.message || 'Unable to validate meter.';
                    Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
                    btn.prop('disabled', false).html('Verify Meter');
                }
            });
        });

        $('#electricityForm').on('submit', function(e) {
            e.preventDefault();
            const token = $('#validation_token').val();
            if (!token) {
                Swal.fire({ title: 'Verify meter first', text: 'Please verify meter before payment.', icon: 'warning', background: '#0a0a0f', color: '#fff' });
                return;
            }

            const p = selectedProvider();
            const amount = Number($('#amount').val() || 0);
            const { fee, total } = computeFee(p, amount);
            const summary = `Amount: ${fmt(amount)}\nFee: ${fmt(fee)}\nTotal debit: ${fmt(total)}`;

            Swal.fire({
                title: 'Confirm Electricity Payment',
                text: summary,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Pay',
                cancelButtonText: 'Cancel',
                background: '#0a0a0f',
                color: '#fff'
            }).then((r) => {
                if (!r.isConfirmed) return;

            $('#loaderOverlay').show();
            let btn = $('#buy-btn');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#loaderOverlay').hide();
                    if (response.status) {
                        Swal.fire({ title: 'Success!', text: response.message, icon: 'success', background: '#0a0a0f', color: '#fff' });
                        $('#electricityForm')[0].reset();
                        invalidateValidation();
                    } else {
                        Swal.fire({ title: 'Failed', text: response.message, icon: 'error', background: '#0a0a0f', color: '#fff' });
                    }
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Pay Bill');
                },
                error: function() {
                    $('#loaderOverlay').hide();
                    Swal.fire({ title: 'Error', text: 'An unexpected error occurred.', icon: 'error', background: '#0a0a0f', color: '#fff' });
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt mr-2"></i> Pay Bill');
                }
            });
            });
        });
    });
</script>
@endpush
