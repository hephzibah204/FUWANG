@extends('layouts.nexus')

@section('title', 'ePINs | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <x-nexus.service-header
        title="ePINs"
        subtitle="Buy digital pins with wallet billing and trackable references."
        icon="fa-solid fa-key"
        icon-class="epin-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Fast</span>
            <span class="badge-accent"><i class="fa-solid fa-receipt"></i> References</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <form id="epinForm" action="{{ route('services.vtu.epin.buy') }}" method="POST">
                    @csrf

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Education Products</label>
                        <select class="form-control" id="productSelect">
                            <option value="">Custom ePIN</option>
                            @foreach((config('epin_products.products') ?? []) as $key => $p)
                                <option value="{{ $key }}">{{ $p['label'] ?? $key }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-2">Select WAEC/NECO/NABTEB/JAMB to auto-fill product code and price.</div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-block">Select Provider</label>
                        <select class="form-control" name="provider_id" id="providerSelect">
                            <option value="">Auto-select best provider</option>
                        </select>
                        <div class="text-muted small mt-2" id="providerMeta"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Product Code</label>
                        <input type="text" name="serviceID" id="serviceID" class="form-control" placeholder="e.g., waec, neco, jamb" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Variation</label>
                        <input type="text" name="variation_code" id="variation_code" class="form-control" placeholder="e.g., scratch_card" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="50" value="1" required>
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
                        <i class="fa-solid fa-key mr-2"></i> Buy ePIN
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Processing purchase...</p>
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
    .epin-bg { background: rgba(245,158,11,0.12); color: #fcd34d; border: 1px solid rgba(245,158,11,0.22); }
    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; height: 50px; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  const baseProvidersUrl = @json(route('services.vtu.providers', ['serviceType' => 'vtu_epin']));
  const products = @json(config('epin_products.products') ?? []);
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

  function loadProviders() {
    const serviceID = $('#serviceID').val();
    const variation = $('#variation_code').val();
    const url = baseProvidersUrl + '?serviceID=' + encodeURIComponent(serviceID || '') + '&variation_code=' + encodeURIComponent(variation || '');
    $('#providerSelect').empty().append('<option value="">Auto-select best provider</option>');
    $.get(url).done(function(res) {
      if (!res || !res.status) return;
      providers = res.providers || [];
      providers.forEach(p => $('#providerSelect').append(`<option value="${p.id}">${p.name}</option>`));
    }).always(renderMeta);
  }

  function applyProduct(key) {
    if (!key || !products[key]) {
      $('#quantity').prop('readonly', false);
      return;
    }
    const p = products[key];
    if (p.service_id) $('#serviceID').val(p.service_id);
    if (p.variation_code) $('#variation_code').val(p.variation_code);
    if (p.amount) $('#amount').val(p.amount);
    if (p.quantity) {
      $('#quantity').val(p.quantity).prop('readonly', true);
    } else {
      $('#quantity').prop('readonly', false);
    }
  }

  $('#productSelect').on('change', function() {
    applyProduct($(this).val());
    loadProviders();
  });

  $('#serviceID, #variation_code').on('change keyup', function() {
    loadProviders();
  });

  loadProviders();

  $('#providerSelect').on('change', renderMeta);
  $('#amount').on('input', renderMeta);

  $('#epinForm').on('submit', function(e) {
    e.preventDefault();

    const p = selectedProvider();
    const amount = Number($('#amount').val() || 0);
    const { fee, total } = computeFee(p, amount);
    const summary = `Amount: ${fmt(amount)}\nFee: ${fmt(fee)}\nTotal debit: ${fmt(total)}`;

    Swal.fire({
      title: 'Confirm ePIN Purchase',
      text: summary,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Buy',
      cancelButtonText: 'Cancel',
      background: '#0a0a0f',
      color: '#fff'
    }).then((r) => {
      if (!r.isConfirmed) return;

      $('#loaderOverlay').show();
      const btn = $('#submitBtn');
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i> Processing...');

      $.ajax({
        url: $('#epinForm').attr('action'),
        method: 'POST',
        data: $('#epinForm').serialize(),
        success: function(resp) {
          $('#loaderOverlay').hide();
          if (resp && resp.status) {
            Swal.fire({ title: 'Success!', text: resp.message || 'Purchase successful.', icon: 'success', background: '#0a0a0f', color: '#fff' });
            $('#epinForm')[0].reset();
          } else {
            Swal.fire({ title: 'Failed', text: (resp && resp.message) ? resp.message : 'Purchase failed.', icon: 'error', background: '#0a0a0f', color: '#fff' });
          }
          btn.prop('disabled', false).html('<i class="fa-solid fa-key mr-2"></i> Buy ePIN');
          renderMeta();
        },
        error: function(xhr) {
          $('#loaderOverlay').hide();
          const msg = xhr?.responseJSON?.message || 'An unexpected error occurred.';
          Swal.fire({ title: 'Error', text: msg, icon: 'error', background: '#0a0a0f', color: '#fff' });
          $('#submitBtn').prop('disabled', false).html('<i class="fa-solid fa-key mr-2"></i> Buy ePIN');
          renderMeta();
        }
      });
    });
  });
});
</script>
@endpush
