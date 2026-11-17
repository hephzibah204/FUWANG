@extends('layouts.nexus')

@section('title', 'Data Bundles | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="Data Bundles"
        subtitle="Reliable high-speed data for all Nigerian networks with automated delivery."
        icon="fa-solid fa-wifi"
        icon-class="data-bg"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-bolt"></i> Low Latency</span>
            <span class="badge-accent"><i class="fa-solid fa-cloud"></i> Automated</span>
        </x-slot>
    </x-nexus.service-header>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <x-nexus.flash-message />
                
                <form id="dataForm" action="{{ route('services.vtu.data.buy') }}" method="POST" class="vtu-form">
                    @csrf
                    <input type="hidden" name="request_id" id="dataRequestId" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                    <div class="form-group mb-4">
                        <input type="hidden" id="dataBand" name="provider_id" value="{{ $dataProviders->first()?->id }}">
                        <p class="small text-muted mb-0">Data supplied by Datasub247</p>
                        <div id="dataPlanStatus" class="small text-muted mt-2" role="status" aria-live="polite"></div>
                    </div>
                    
                    <!-- 1. Select Network Provider -->
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2 d-flex align-items-center justify-content-between">
                            <span class="text-white">1. Select Network Provider</span>
                            <span class="small text-muted" id="selectedNetLabel">Selected: <strong class="text-warning">MTN</strong></span>
                        </label>
                        <div class="network-grid">
                            <!-- MTN -->
                            <label class="nexus-net-option" for="net_MTN" data-net="MTN">
                                <input type="radio" name="network" id="net_MTN" value="MTN" checked required>
                                <div class="net-box net-mtn">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/mtn.png') }}" alt="MTN" class="net-logo-img" onerror="if(this.src.indexOf('/vtu/')!==-1){this.src='{{ asset('images/mtn.png') }}';}">
                                    </div>
                                    <span class="net-title">MTN</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- GLO -->
                            <label class="nexus-net-option" for="net_GLO" data-net="GLO">
                                <input type="radio" name="network" id="net_GLO" value="GLO" required>
                                <div class="net-box net-glo">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/glo.png') }}" alt="GLO" class="net-logo-img" onerror="if(this.src.indexOf('/vtu/')!==-1){this.src='{{ asset('images/glo.png') }}';}">
                                    </div>
                                    <span class="net-title">GLO</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- AIRTEL -->
                            <label class="nexus-net-option" for="net_AIRTEL" data-net="AIRTEL">
                                <input type="radio" name="network" id="net_AIRTEL" value="AIRTEL" required>
                                <div class="net-box net-airtel">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/airtel.png') }}" alt="AIRTEL" class="net-logo-img" onerror="if(this.src.indexOf('/vtu/')!==-1){this.src='{{ asset('images/airtel.png') }}';}">
                                    </div>
                                    <span class="net-title">AIRTEL</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>

                            <!-- 9MOBILE -->
                            <label class="nexus-net-option" for="net_9MOBILE" data-net="9MOBILE">
                                <input type="radio" name="network" id="net_9MOBILE" value="9MOBILE" required>
                                <div class="net-box net-9mobile">
                                    <div class="net-logo-wrap">
                                        <img src="{{ asset('images/vtu/9mobile.png') }}" alt="9MOBILE" class="net-logo-img" onerror="if(this.src.indexOf('/vtu/')!==-1){this.src='{{ asset('images/9mobile.png') }}';}">
                                    </div>
                                    <span class="net-title">9MOBILE</span>
                                    <span class="net-status-dot"></span>
                                    <i class="fa-solid fa-circle-check net-check"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Select Data Plan -->
                    <div class="form-group mb-4">
                        <label for="planSelect" class="font-weight-600 mb-2 text-white">2. Select Data Plan</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-layer-group text-primary"></i>
                            <select name="plan_id" id="planSelect" class="form-control vtu-input" required>
                                <option value="">-- Choose Data Bundle --</option>
                            </select>
                        </div>
                        <input type="hidden" name="amount" id="amountInput" value="0">
                        <small class="text-muted mt-1 d-block"><i class="fa-solid fa-circle-info mr-1"></i> Data bundles are delivered directly to the phone line within seconds.</small>
                    </div>

                    <!-- 3. Recipient Phone Number -->
                    <div class="form-group mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="phone" class="font-weight-600 text-white m-0">3. Recipient Phone Number</label>
                            @if(auth()->check() && auth()->user()->phone)
                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" id="btnUseMyPhone" data-phone="{{ auth()->user()->phone }}">
                                    <i class="fa-solid fa-user mr-1"></i> Use My Phone
                                </button>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone text-primary"></i>
                            <input type="tel" name="phone" id="phone" class="form-control vtu-input" placeholder="0801 234 5678" maxlength="11" required value="{{ old('phone') }}">
                        </div>
                    </div>

                    <!-- 4. Live Order Summary Card -->
                    <div class="p-3 mb-4 rounded-3 vtu-summary-card" id="summaryCard">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-white-10">
                            <span class="text-muted small">Package Summary</span>
                            <span class="badge badge-primary px-2 py-1 font-weight-bold" id="sumNetworkBadge">MTN DATA</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-white-50 small">Selected Bundle:</span>
                            <span class="text-white font-weight-600 small" id="sumPlanName">Select a plan above</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-white-50 small">Amount to Pay:</span>
                            <span class="text-warning font-weight-bold h5 mb-0" id="sumAmountDisplay">₦0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-white-10">
                            <span class="text-muted small">Wallet Balance:</span>
                            <span class="text-white small font-weight-600">
                                ₦{{ number_format((float) ($walletBalance ?? 0), 2) }}
                                @if(($walletBalance ?? 0) < 150)
                                    <a href="{{ route('wallet.fund') }}" class="ml-2 text-primary font-weight-bold" style="text-decoration: underline;">Fund</a>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-bold" id="buy-btn">
                        <i class="fa-solid fa-cloud-arrow-down mr-2"></i> Activate Bundle
                    </button>
                </form>

                <div id="loaderOverlay" style="display:none;" class="mt-4 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Connecting to Network Band gateway...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3 text-white">Quick Actions</h3>
                <div class="d-grid gap-2">
                    <a href="{{ route('services.vtu.hub') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-layer-group mr-2 text-primary"></i> Back to VTU Hub
                    </a>
                    <a href="{{ route('services.vtu.airtime') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-mobile-screen-button mr-2 text-success"></i> Switch to Airtime
                    </a>
                    <a href="{{ route('services.vtu.electricity') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-lightbulb mr-2 text-warning"></i> Electricity Bills
                    </a>
                    <a href="{{ route('services.vtu.cable') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-tv mr-2 text-info"></i> Cable TV Hub
                    </a>
                    <a href="{{ route('services.vtu.bulksms') }}" class="btn btn-outline w-100 text-left py-3">
                        <i class="fa-solid fa-comments mr-2 text-danger"></i> Bulk SMS
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon data-bg"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="stat-val">Automated</div>
                <div class="stat-label">Instant Network Band Fulfillment</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .network-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .nexus-net-option {
        cursor: pointer;
        margin: 0;
        position: relative;
        user-select: none;
        -webkit-user-select: none;
    }
    .nexus-net-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        border: 0;
        clip: rect(0 0 0 0);
        overflow: hidden;
        pointer-events: none;
    }
    .net-box {
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.03);
        border-radius: 14px;
        padding: 12px 8px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 94px;
    }
    .net-logo-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 6px;
        padding: 5px;
        transition: transform 0.2s ease;
    }
    .net-logo-img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    .nexus-net-option input:checked + .net-box .net-logo-wrap {
        transform: scale(1.08);
        background: rgba(59, 130, 246, 0.15);
    }
    .net-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--clr-text-muted, #94a3b8);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .net-check {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: 0.8rem;
        color: #3b82f6;
        opacity: 0;
        transform: scale(0.6);
        transition: all 0.2s ease;
    }
    .net-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        position: absolute;
        bottom: 6px;
        opacity: 0.6;
    }
    .nexus-net-option input:checked + .net-box {
        border-color: #3b82f6 !important;
        background: rgba(59, 130, 246, 0.12) !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }
    .nexus-net-option input:checked + .net-box .net-title {
        color: #ffffff !important;
        font-weight: 800;
    }
    .nexus-net-option input:checked + .net-box .net-badge-icon {
        transform: scale(1.08);
    }
    .nexus-net-option input:checked + .net-box .net-check {
        opacity: 1;
        transform: scale(1);
    }

    /* Form Controls & Inputs Dark Theme */
    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrap i {
        position: absolute;
        left: 16px;
        font-size: 1rem;
        z-index: 2;
        pointer-events: none;
    }
    .vtu-input,
    .vtu-form .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        padding-left: 48px !important;
        height: 52px !important;
        font-size: 0.95rem !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }
    .vtu-form select.form-control {
        background-color: #111827 !important;
        color: #ffffff !important;
    }
    .vtu-form select.form-control option {
        background: #0f172a !important;
        color: #ffffff !important;
        padding: 8px 12px;
    }
    .vtu-form .form-control:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        color: #ffffff !important;
    }
    .vtu-summary-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .border-white-10 {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function initDataBundlePage() {
        let grouped = @json($plans ?? []), loading = false, paying = false, catalogVersion = 0;
        const form = document.getElementById('dataForm'), band = document.getElementById('dataBand');
        const plan = document.getElementById('planSelect'), amount = document.getElementById('amountInput');
        const button = document.getElementById('buy-btn'), status = document.getElementById('dataPlanStatus');
        const phone = document.getElementById('phone'), overlay = document.getElementById('loaderOverlay');
        const money = n => new Intl.NumberFormat('en-NG', {style: 'currency', currency: 'NGN'}).format(Number(n || 0));
        const network = () => form.querySelector('[name="network"]:checked')?.value || '';
        const catalogUrl = @json(route('services.vtu.catalog', ['serviceType' => 'vtu_data']));
        const quoteUrl = @json(route('services.vtu.quote', ['serviceType' => 'vtu_data']));
        function summary() {
            const option = plan.selectedOptions[0], valid = !!option?.value && !loading;
            amount.value = valid ? option.dataset.price : '';
            document.getElementById('sumNetworkBadge').textContent = network() + ' DATA';
            document.getElementById('sumPlanName').textContent = valid ? option.dataset.label : 'Select an available bundle';
            document.getElementById('sumAmountDisplay').textContent = valid ? money(amount.value) : money(0);
            button.disabled = !valid || paying;
        }
        function populate() {
            const net = network();
            const available = grouped[net] || grouped[net.toUpperCase()] || grouped[net.toLowerCase()] || [];
            plan.replaceChildren(new Option(loading ? 'Loading bundles...' : (available.length ? 'Choose a data bundle' : 'No bundles configured for ' + net), ''));
            if (!loading) available.forEach(p => {
                const label = p.data_plan || p.label;
                const option = new Option(label + (p.validate ? ' (' + p.validate + ')' : '') + ' - ' + money(p.amount), String(p.id));
                option.dataset.price = p.amount; option.dataset.label = label; plan.add(option);
            });
            plan.disabled = loading || available.length === 0;
            const netLabel = document.getElementById('selectedNetLabel');
            if (netLabel) netLabel.innerHTML = 'Selected: <strong class="text-warning">' + net + '</strong>';
            if (status) status.textContent = loading ? 'Loading available bundles...' : (available.length ? 'Select a bundle to see its price.' : 'No bundles configured for ' + net + ' yet.');
            summary();
        }
        async function json(url, body) {
            const response = await fetch(url, {method: body ? 'POST' : 'GET', credentials: 'same-origin',
                headers: {Accept: 'application/json', ...(body ? {'Content-Type':'application/json', 'X-CSRF-TOKEN': form.elements._token.value} : {})},
                ...(body ? {body: JSON.stringify(body)} : {})});
            const data = await response.json();
            if (!response.ok) { const error = new Error(data.message || 'Request could not be completed.'); error.data = data; throw error; }
            return data;
        }
        band.addEventListener('change', async () => {
            const version = ++catalogVersion; loading = true; grouped = {}; populate();
            try {
                const data = await json(catalogUrl + '?provider_id=' + encodeURIComponent(band.value));
                if (version !== catalogVersion) return;
                for (const p of data.products || []) {
                    (grouped[p.network] ||= []).push({id: p.code, data_plan: p.label, validate: p.validity, amount: p.amount});
                }
            } catch (error) { if (version === catalogVersion) status.textContent = error.message; }
            finally { if (version === catalogVersion) { loading = false; populate(); } }
        });
        form.querySelectorAll('[name="network"]').forEach(radio => radio.addEventListener('change', populate));
        document.querySelectorAll('.nexus-net-option').forEach(option => {
            option.addEventListener('click', function () {
                const radio = this.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    populate();
                }
            });
        });
        plan.addEventListener('change', summary);
        document.getElementById('btnUseMyPhone')?.addEventListener('click', event => { phone.value = event.currentTarget.dataset.phone || ''; phone.focus(); });
        async function message(title, text, icon) {
            if (window.Swal) return Swal.fire({title, text, icon});
            window.alert(title + '\n' + text);
        }
        form.addEventListener('submit', async event => {
            event.preventDefault(); if (paying || loading || !plan.value || !form.reportValidity()) return;
            const body = Object.fromEntries(new FormData(form));
            const controls = Array.from(form.elements).map(element => [element, element.disabled]);
            paying = true; summary();
            controls.forEach(([element]) => { element.disabled = true; });
            try {
                const quote = await json(quoteUrl, body);
                document.getElementById('sumAmountDisplay').textContent = money(quote.breakdown.total);
                const details = plan.selectedOptions[0].dataset.label + '\nRecipient: ' + body.phone + '\nTotal debit: ' + money(quote.breakdown.total);
                const confirmed = window.Swal ? (await Swal.fire({title: 'Confirm data purchase', text: details, icon: 'question', showCancelButton:true, confirmButtonText:'Pay ' + money(quote.breakdown.total)})).isConfirmed : window.confirm(details);
                if (!confirmed) { paying = false; return; }
                overlay.style.display = 'block'; band.disabled = true; plan.disabled = true;
                let result;
                try { result = await json(form.action, body); }
                catch (error) {
                    if (!error.data) {
                        status.textContent = 'Connection interrupted. Check transaction history before repeating this purchase. Reference: ' + body.request_id;
                        await message('Awaiting confirmation', status.textContent, 'warning'); return;
                    }
                    throw error;
                }
                if (result.pending) {
                    status.textContent = result.message + ' Reference: ' + result.reference;
                    await message('Awaiting confirmation', status.textContent, 'info'); return;
                }
                paying = false;
                await message(result.status ? 'Bundle activated' : 'Order failed', result.message, result.status ? 'success' : 'error');
                if (result.status) {
                    form.elements.request_id.value = globalThis.crypto?.randomUUID?.() || 'DATA-' + Date.now() + '-' + Math.random().toString(36).slice(2); phone.value = '';
                }
            } catch (error) { paying = false; await message('Order failed', error.message, 'error'); }
            finally {
                overlay.style.display = 'none';
                if (!paying) controls.forEach(([element, disabled]) => { element.disabled = disabled; });
                summary();
            }
        });
        populate();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initDataBundlePage);
    else initDataBundlePage();
})();
</script>
@endpush
