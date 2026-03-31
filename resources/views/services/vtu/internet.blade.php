@extends('layouts.nexus')

@section('title', 'Internet Subscription | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <x-nexus.service-header
        title="Internet Subscription"
        subtitle="Pay ISP bundles fast with references and wallet billing."
        icon="fa-solid fa-globe"
        icon-class="net-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Fast</span>
            <span class="badge-accent"><i class="fa-solid fa-receipt"></i> References</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="internetForm" action="{{ route('services.vtu.internet.buy') }}" method="POST">
                    @csrf

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider</label>
                        <select class="form-control" name="provider_id" id="providerSelect">
                            <option value="">Auto-select best provider</option>
                        </select>
                        <div class="text-muted small mt-2" id="providerMeta"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">ISP Service Code</label>
                        <input type="text" name="serviceID" class="form-control" placeholder="e.g., spectranet, smile, ipnx" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Package / Variation</label>
                        <input type="text" name="variation_code" class="form-control" placeholder="e.g., plan_code" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Customer ID</label>
                        <input type="text" name="customer_id" class="form-control" placeholder="Account / Customer ID" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Amount (₦)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-naira-sign"></i>
                            <input type="number" name="amount" id="amount" class="form-control" min="100" placeholder="Min ₦100" required>
                        </div>
                        <div class="mt-2 text-muted small" id="breakdownText"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" name="phone" class="form-control" maxlength="11" placeholder="08123456789" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                        <i class="fa-solid fa-globe mr-2"></i> Pay Internet
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Processing payment...</p>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .net-bg { background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.22); }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  const providersUrl = @json(route('services.vtu.providers', ['serviceType' => 'vtu_internet']));
  let providers = [];

  function fmt(n) {
    const v = Number(n || 0);
    return '₦' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

  function selectedProvider() {
    const id = $('#providerSelect').val();
    if (!id) return null;
    return providers.find(p => String(p.id) === String(id)) || null;
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

  $('#providerSelect').on('change', renderMeta);
  $('#amount').on('input', renderMeta);

  $('#internetForm').on('submit', function(e) {
    e.preventDefault();

    const p = selectedProvider();
    const amount = Number($('#amount').val() || 0);
    const { fee, total } = computeFee(p, amount);
    const summary = `Amount: ${fmt(amount)}\nFee: ${fmt(fee)}\nTotal debit: ${fmt(total)}`;

    Swal.fire({
      title: 'Confirm Internet Payment',
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
      const btn = $('#submitBtn');
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing...');

      $.ajax({
        url: $('#internetForm').attr('action'),
        method: 'POST',
        data: $('#internetForm').serialize(),
        success: function(resp) {
          $('#loaderOverlay').hide();
          if (resp && resp.status) {
            Swal.fire({ title: 'Success!', text: resp.message || 'Payment successful.', icon: 'success', background: '#0a0a0f', color: '#fff' });
            $('#internetForm')[0].reset();
          } else {
            Swal.fire({ title: 'Failed', text: (resp && resp.message) ? resp.message : 'Payment failed.', icon: 'error', background: '#0a0a0f', color: '#fff' });
          }
          btn.prop('disabled', false).html('<i class="fa-solid fa-globe mr-2"></i> Pay Internet');
          renderMeta();
        },
        error: function(xhr) {
          $('#loaderOverlay').hide();
          const msg = xhr?.responseJSON?.message || 'An unexpected error occurred.';
          Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
          $('#submitBtn').prop('disabled', false).html('<i class="fa-solid fa-globe mr-2"></i> Pay Internet');
          renderMeta();
        }
      });
    });
  });
});
</script>
@endpush

