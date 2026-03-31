@extends('layouts.nexus')

@section('title', 'Airtime to Cash | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <x-nexus.service-header
        title="Airtime to Cash"
        subtitle="Convert airtime value with trackable references and wallet credit."
        icon="fa-solid fa-money-bill-transfer"
        icon-class="a2c-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-receipt"></i> References</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> Secure</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="a2cForm" action="{{ route('services.vtu.airtime_to_cash.submit') }}" method="POST">
                    @csrf

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider</label>
                        <select class="form-control" name="provider_id" id="providerSelect">
                            <option value="">Auto-select best provider</option>
                        </select>
                        <div class="text-muted small mt-2" id="providerMeta"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Network</label>
                        <div class="network-grid">
                            @foreach(['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $net)
                                <label class="nexus-net-option">
                                    <input type="radio" name="network" value="{{ $net }}" required>
                                    <div class="net-box">
                                        <img src="{{ asset('images/' . strtolower($net) . '.png') }}" alt="{{ $net }}">
                                        <span>{{ $net }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone" class="font-weight-600 mb-2">Sender Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="081 2345 6789" maxlength="11" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="amount" class="font-weight-600 mb-2">Airtime Amount (₦)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-naira-sign"></i>
                            <input type="number" name="amount" id="amount" class="form-control" placeholder="Min ₦200" min="200" required>
                        </div>
                        <div class="mt-2 text-muted small" id="breakdownText"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Payout Account</label>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <input type="text" name="bank_code" class="form-control" placeholder="Bank code" required>
                                <div class="text-muted small mt-1">Example: 058</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <input type="text" name="account_number" class="form-control" placeholder="Account number" maxlength="10" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <input type="text" name="account_name" class="form-control" placeholder="Account name (optional)">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                        <i class="fa-solid fa-money-bill-transfer mr-2"></i> Submit Conversion
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Submitting conversion request...</p>
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
                    <a href="{{ route('history') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> View Transaction History
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon a2c-bg"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">Tracked</div>
                <div class="stat-label">References and records</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .a2c-bg { background: rgba(16, 185, 129, 0.10); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.20); }
    .network-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .nexus-net-option { cursor: pointer; }
    .nexus-net-option input { display: none; }
    .net-box { border: var(--border-glass); background: rgba(255,255,255,0.03); border-radius: 14px; padding: 12px; text-align: center; transition: all 0.2s; }
    .net-box img { max-height: 25px; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; }
    .net-box span { font-size: 0.7rem; font-weight: 700; color: var(--clr-text-muted); }
    .nexus-net-option input:checked + .net-box { border-color: var(--clr-primary); background: rgba(59, 130, 246, 0.1); }
    .nexus-net-option input:checked + .net-box span { color: #fff; }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  const serviceType = 'vtu_airtime_to_cash';
  const providersUrl = @json(route('services.vtu.providers', ['serviceType' => 'vtu_airtime_to_cash']));
  let providers = [];

  function fmt(n) {
    const v = Number(n || 0);
    return '₦' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function computeFee(provider, amount) {
    const a = Number(amount || 0);
    if (!provider || !a) return { fee: 0, payout: a };
    const t = (provider.fee_type || 'flat').toLowerCase();
    const v = Number(provider.fee_value || 0);
    let fee = 0;
    if (v > 0) fee = (t === 'percent') ? (a * v / 100) : v;
    fee = Math.max(0, Math.round(fee * 100) / 100);
    return { fee, payout: Math.max(0, a - fee) };
  }

  function selectedProvider() {
    const id = $('#providerSelect').val();
    if (!id) return null;
    return providers.find(p => String(p.id) === String(id)) || null;
  }

  function renderMeta() {
    const p = selectedProvider();
    const a = Number($('#amount').val() || 0);
    const { fee, payout } = computeFee(p, a);
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
      $('#breakdownText').text('Estimated fee: ' + fmt(fee) + ' • Estimated wallet credit: ' + fmt(payout));
    } else {
      $('#breakdownText').text('');
    }
  }

  $.get(providersUrl).done(function(res) {
    if (!res || !res.status) return;
    providers = res.providers || [];
    providers.forEach(p => {
      $('#providerSelect').append(`<option value="${p.id}">${p.name}</option>`);
    });
  }).always(renderMeta);

  $('#providerSelect').on('change', renderMeta);
  $('#amount').on('input', renderMeta);

  $('#a2cForm').on('submit', function(e) {
    e.preventDefault();

    const p = selectedProvider();
    const amount = Number($('#amount').val() || 0);
    const { fee, payout } = computeFee(p, amount);

    const summary = `Airtime amount: ${fmt(amount)}\nFee: ${fmt(fee)}\nWallet credit: ${fmt(payout)}`;
    Swal.fire({
      title: 'Confirm Airtime to Cash',
      text: summary,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Submit',
      cancelButtonText: 'Cancel',
      background: '#0a0a0f',
      color: '#fff'
    }).then((r) => {
      if (!r.isConfirmed) return;

      $('#loaderOverlay').show();
      const btn = $('#submitBtn');
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Submitting...');

      $.ajax({
        url: $('#a2cForm').attr('action'),
        method: 'POST',
        data: $('#a2cForm').serialize(),
        success: function(resp) {
          $('#loaderOverlay').hide();
          if (resp && resp.status) {
            Swal.fire({ title: 'Submitted!', text: resp.message || 'Request submitted.', icon: 'success', background: '#0a0a0f', color: '#fff' });
            $('#a2cForm')[0].reset();
          } else {
            Swal.fire({ title: 'Failed', text: (resp && resp.message) ? resp.message : 'Request failed.', icon: 'error', background: '#0a0a0f', color: '#fff' });
          }
          btn.prop('disabled', false).html('<i class="fa-solid fa-money-bill-transfer mr-2"></i> Submit Conversion');
          renderMeta();
        },
        error: function(xhr) {
          $('#loaderOverlay').hide();
          const msg = xhr?.responseJSON?.message || 'An unexpected error occurred.';
          Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
          $('#submitBtn').prop('disabled', false).html('<i class="fa-solid fa-money-bill-transfer mr-2"></i> Submit Conversion');
          renderMeta();
        }
      });
    });
  });
});
</script>
@endpush

