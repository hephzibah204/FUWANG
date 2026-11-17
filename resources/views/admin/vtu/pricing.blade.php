@extends('layouts.nexus')

@section('title', 'VTU & Utility Pricing | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.services.index') }}" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fa fa-arrow-left text-white"></i>
                </a>
                <div>
                    <h1 class="h3 font-weight-bold mb-1 text-white"><i class="fa-solid fa-tags text-primary mr-2"></i> VTU & Utility Pricing Manager</h1>
                    <p class="text-muted mb-0">Configure user rates, exam PIN tokens, SMS rates, airtime discounts, and data bundles.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBundleModal" data-bs-toggle="modal" data-bs-target="#addBundleModal">
                <i class="fa-solid fa-plus-circle mr-1"></i> Add Data Bundle
            </button>
            <a href="{{ route('services.vtu.hub') }}" target="_blank" class="btn btn-outline-light ml-2">
                <i class="fa-solid fa-eye mr-1"></i> View Hub &nearr;
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="panel-card p-4 mb-4">
        <h5>Datasub247 data bundles</h5>
        @if(!($catalogueReady ?? true))
            <div class="alert alert-warning" role="alert">Data catalogue database setup is incomplete. Run the provider mapping migration and the verified catalogue migrations before adding or selling bundles.</div>
        @endif
        <p class="text-muted">API credentials do not automatically load data bundles. Add bundles using their actual Datasub247 plan IDs and your selling prices. Bundles without these mappings cannot be purchased.</p>
        <p class="text-muted">Datasub247 is assigned automatically. Use the form below to add bundles or update prices for matching Datasub plan IDs.</p>
        <details {{ old('plans') ? 'open' : '' }}>
            <summary class="mb-3">Add or update several bundles</summary>
            <form action="{{ route('admin.vtu.pricing.data-catalogue') }}" method="POST" data-data-catalogue>
                @csrf
                <div data-catalogue-rows>
                    @foreach(old('plans', [[]]) as $index => $plan)
                        <div class="row mb-3" data-catalogue-row>
                            <label class="col-md-2">Network
                                <select name="plans[{{ $index }}][network]" class="form-control" required>
                                    @foreach(['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $network)
                                        <option @selected(($plan['network'] ?? 'MTN') === $network)>{{ $network }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="col-md-3">Bundle name<input name="plans[{{ $index }}][data_plan]" class="form-control" required maxlength="255" value="{{ $plan['data_plan'] ?? '' }}" placeholder="e.g. 1 GB SME"></label>
                            <label class="col-md-2">Datasub plan ID<input name="plans[{{ $index }}][provider_plan_id]" class="form-control" required pattern="[1-9][0-9]*" inputmode="numeric" maxlength="100" value="{{ $plan['provider_plan_id'] ?? '' }}"></label>
                            <label class="col-md-2">Selling price (₦)<input name="plans[{{ $index }}][amount]" class="form-control" type="number" min="50" max="500000" step="0.01" required value="{{ $plan['amount'] ?? '' }}"></label>
                            <label class="col-md-2">Validity<input name="plans[{{ $index }}][validate]" class="form-control" required maxlength="100" value="{{ $plan['validate'] ?? '' }}" placeholder="e.g. 30 Days"></label>
                            <div class="col-md-1"><button type="button" class="btn btn-outline-danger mt-4" data-remove-plan aria-label="Remove bundle">×</button></div>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-outline-primary mb-2" type="button" data-add-plan>Add another bundle</button>
                <button class="btn btn-primary mb-2" type="submit">Save Datasub247 bundles</button>
            </form>
        </details>
    </div>
    @push('scripts')
        <script src="{{ asset('js/vtu-data-catalogue.js') }}" defer></script>
    @endpush

    <!-- Global VTU Service Rates & PINs Card -->
    <div class="panel-card p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-white-5 pb-3">
            <div>
                <h5 class="text-white mb-1 fw-bold"><i class="fa-solid fa-sliders text-primary mr-2"></i> Global VTU Rates & Exam Tokens</h5>
                <p class="text-muted small mb-0">Prices and discount percentages applied to user checkouts across VTU services.</p>
            </div>
            <span class="badge badge-success px-3 py-2"><i class="fa-solid fa-bolt mr-1"></i> Saved configuration</span>
        </div>

        <form action="{{ route('admin.vtu.pricing.rates') }}" method="POST" id="ratesForm">
            @csrf
            <div class="form-group mb-4">
                <label for="product-catalog" class="font-weight-bold">Cable, internet, betting and printing products</label>
                <p class="text-muted small">Configure real provider product codes and selling prices. Each product needs service_type, provider_id, label, code and amount; include service_id for cable/internet/betting or network for printing. provider_plan_id maps your product code to the provider code.</p>
                <textarea id="product-catalog" name="product_catalog" class="form-control" rows="7" spellcheck="false">{{ old('product_catalog', is_string($productCatalog) ? $productCatalog : json_encode($productCatalog, JSON_PRETTY_PRINT)) }}</textarea>
                <small class="text-muted">Use a JSON array. Supported service types: vtu_cable_tv, vtu_internet, vtu_betting, vtu_data_card, vtu_recharge_card. Unconfigured products are unavailable for purchase.</small>
            </div>
            <div class="row">
                <!-- Airtime Discount -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">Airtime Discount Rate (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="vtu_airtime_discount_percent" class="form-control text-white" value="{{ old('vtu_airtime_discount_percent', $rates['vtu_airtime_discount_percent'] ?? 2.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                        <div class="input-group-append">
                            <span class="input-group-text bg-dark text-white border-secondary">%</span>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">e.g. 2% off airtime purchases</small>
                </div>

                <!-- Bulk SMS Rate -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">Bulk SMS Rate per Page (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_bulk_sms_rate" class="form-control text-white" value="{{ old('vtu_bulk_sms_rate', $rates['vtu_bulk_sms_rate'] ?? 4.00) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">Charged per page per recipient</small>
                </div>

                <!-- Electricity Convenience Fee -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">Electricity Convenience Fee (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_electricity_fee" class="form-control text-white" value="{{ old('vtu_electricity_fee', $rates['vtu_electricity_fee'] ?? 0.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">Platform service fee (0 for free)</small>
                </div>

                <!-- WAEC Result Checker -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">WAEC Result Checker PIN (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_waec_price" class="form-control text-white" value="{{ old('vtu_waec_price', $rates['vtu_waec_price'] ?? 4000.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">WASSCE result verification</small>
                </div>

                <!-- WAEC Registration PIN -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">WAEC Registration PIN (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_waec_reg_price" class="form-control text-white" value="{{ old('vtu_waec_reg_price', $rates['vtu_waec_reg_price'] ?? 18500.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">WASSCE candidate card</small>
                </div>

                <!-- NECO Token -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">NECO Result Token (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_neco_price" class="form-control text-white" value="{{ old('vtu_neco_price', $rates['vtu_neco_price'] ?? 1450.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">NECO result checking</small>
                </div>

                <!-- NABTEB PIN -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">NABTEB Result PIN (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_nabteb_price" class="form-control text-white" value="{{ old('vtu_nabteb_price', $rates['vtu_nabteb_price'] ?? 1100.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">NABTEB voucher card</small>
                </div>

                <!-- JAMB ePIN -->
                <div class="col-md-3 mb-4">
                    <label class="text-white-50 small mb-2 fw-semibold">JAMB UTME / DE ePIN (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-dark text-white border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" name="vtu_jamb_price" class="form-control text-white" value="{{ old('vtu_jamb_price', $rates['vtu_jamb_price'] ?? 4700.0) }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);" required>
                    </div>
                    <small class="text-muted mt-1 d-block">Candidate profile e-PIN</small>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Save Global Rates
                </button>
            </div>
        </form>
    </div>

    <!-- Data Bundles Catalog (price_list table) -->
    <div class="panel-card p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-white-5 pb-3 flex-wrap" style="gap: 12px;">
            <div>
                <h5 class="text-white mb-1 fw-bold"><i class="fa-solid fa-wifi text-primary mr-2"></i> Data Bundles Catalog (Price List)</h5>
                <p class="text-muted small mb-0">Set independent bundle prices for each API band. Customers see only the selected band's configured plans and prices.</p>
            </div>
            
            <ul class="nav nav-pills" id="networkPills" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pill-all-tab" data-toggle="pill" data-bs-toggle="pill" href="#pill-all" role="tab">All Networks</a>
                </li>
                @foreach(['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $net)
                    <li class="nav-item">
                        <a class="nav-link" id="pill-{{ strtolower($net) }}-tab" data-toggle="pill" data-bs-toggle="pill" href="#pill-{{ strtolower($net) }}" role="tab">{{ $net }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="form-group mb-3">
            <label for="pricingBandFilter">Filter prices by API band</label>
            <select id="pricingBandFilter" class="form-control">
                <option value="all">All bands</option>
                @foreach($bandNames as $bandId => $bandName)<option value="{{ $bandId }}">{{ $bandName }}</option>@endforeach
                <option value="unassigned">Unassigned plans (not available for configured bands)</option>
            </select>
        </div>
        <div class="tab-content" id="networkPillsContent">
            <!-- All Networks Tab -->
            <div class="tab-pane fade show active" id="pill-all" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-white">
                        <thead class="text-muted small text-uppercase" style="border-color: rgba(255,255,255,0.08);">
                            <tr>
                                <th>API band</th>
                                <th>Network</th>
                                <th>Data Bundle Plan</th>
                                <th>Validity</th>
                                <th class="text-right">Price (₦)</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasAny = false; @endphp
                            @foreach($bundles as $net => $plans)
                                @foreach($plans as $plan)
                                    @php $hasAny = true; @endphp
                                    <tr data-pricing-band="{{ $plan->custom_api_id ?: 'unassigned' }}">
                                        <td>{{ $bandNames->get($plan->custom_api_id, 'Unassigned') }}</td>
                                        <td>
                                            @php
                                                $netBadge = match($net) {
                                                    'MTN' => 'badge-warning text-dark',
                                                    'GLO' => 'badge-success',
                                                    'AIRTEL' => 'badge-danger',
                                                    '9MOBILE' => 'badge-info',
                                                    default => 'badge-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $netBadge }} px-2 py-1 font-weight-bold">{{ $net }}</span>
                                        </td>
                                        <td class="font-weight-600">{{ $plan->data_plan }}<small class="d-block text-muted">Provider plan ID: {{ $plan->provider_plan_id ?: 'Not mapped' }}</small></td>
                                        <td><span class="badge badge-dark border border-white-10 text-muted">{{ $plan->validate ?: '30 Days' }}</span></td>
                                        <td class="text-right font-weight-bold text-primary font-mono" style="font-size: 1.05rem;">
                                            ₦{{ number_format((float) $plan->amount, 2) }}
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-outline-light mr-1" onclick="openEditModal({{ json_encode($plan) }})" title="Edit Bundle">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteBundle({{ $plan->id }}, '{{ addslashes($plan->data_plan) }}')" title="Delete Bundle">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(!$hasAny)
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No data bundles configured yet. Click "Add Data Bundle" to begin.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Per Network Tabs -->
            @foreach(['MTN', 'GLO', 'AIRTEL', '9MOBILE'] as $net)
                <div class="tab-pane fade" id="pill-{{ strtolower($net) }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-white">
                            <thead class="text-muted small text-uppercase" style="border-color: rgba(255,255,255,0.08);">
                                <tr>
                                    <th>API band</th>
                                <th>Network</th>
                                    <th>Data Bundle Plan</th>
                                    <th>Validity</th>
                                    <th class="text-right">Price (₦)</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $netPlans = $bundles->get($net, collect()); @endphp
                                @forelse($netPlans as $plan)
                                    <tr data-pricing-band="{{ $plan->custom_api_id ?: 'unassigned' }}">
                                        <td>{{ $bandNames->get($plan->custom_api_id, 'Unassigned') }}</td>
                                        <td>
                                            @php
                                                $netBadge = match($net) {
                                                    'MTN' => 'badge-warning text-dark',
                                                    'GLO' => 'badge-success',
                                                    'AIRTEL' => 'badge-danger',
                                                    '9MOBILE' => 'badge-info',
                                                    default => 'badge-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $netBadge }} px-2 py-1 font-weight-bold">{{ $net }}</span>
                                        </td>
                                        <td class="font-weight-600">{{ $plan->data_plan }}<small class="d-block text-muted">Provider plan ID: {{ $plan->provider_plan_id ?: 'Not mapped' }}</small></td>
                                        <td><span class="badge badge-dark border border-white-10 text-muted">{{ $plan->validate ?: '30 Days' }}</span></td>
                                        <td class="text-right font-weight-bold text-primary font-mono" style="font-size: 1.05rem;">
                                            ₦{{ number_format((float) $plan->amount, 2) }}
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-outline-light mr-1" onclick="openEditModal({{ json_encode($plan) }})" title="Edit Bundle">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteBundle({{ $plan->id }}, '{{ addslashes($plan->data_plan) }}')" title="Delete Bundle">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No {{ $net }} bundles configured. Click "Add Data Bundle" to create one.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add Bundle Modal -->
<div class="modal fade" id="addBundleModal" tabindex="-1" role="dialog" aria-labelledby="addBundleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('admin.vtu.pricing.bundle.store') }}" method="POST" class="modal-content border-0 text-white" style="background: #111827; border: 1px solid rgba(255,255,255,0.12);">
            @csrf
            <div class="modal-header border-bottom border-white-10">
                <h5 class="modal-title font-weight-bold" id="addBundleModalLabel"><i class="fa-solid fa-plus-circle text-primary mr-2"></i> Add New Data Bundle</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <p class="text-muted">Provider: Datasub247</p>
                    <input type="hidden" name="custom_api_id"  value="{{ $providers->first()?->id }}">
                </div>
                <div class="form-group mb-3">
                    <label>Provider plan ID</label>
                    <input name="provider_plan_id"  class="form-control" required placeholder="Exact numeric plan ID from Datasub247">
                </div>

                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Network Provider</label>
                    <select name="network" class="form-control text-white" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="MTN">MTN</option>
                        <option value="GLO">GLO</option>
                        <option value="AIRTEL">AIRTEL</option>
                        <option value="9MOBILE">9MOBILE</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Bundle Plan Name</label>
                    <input type="text" name="data_plan" class="form-control text-white" placeholder="e.g. 1.0GB Corporate Gifting" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);" required>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Selling Price (₦)</label>
                    <input type="number" step="1" name="amount" class="form-control text-white" placeholder="e.g. 290" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);" required>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Validity Period</label>
                    <input type="text" name="validate" class="form-control text-white" value="30 Days" placeholder="e.g. 30 Days" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);">
                </div>
            </div>
            <div class="modal-footer border-top border-white-10">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary font-weight-bold px-4">Create Bundle</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bundle Modal -->
<div class="modal fade" id="editBundleModal" tabindex="-1" role="dialog" aria-labelledby="editBundleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="editBundleForm" method="POST" class="modal-content border-0 text-white" style="background: #111827; border: 1px solid rgba(255,255,255,0.12);">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom border-white-10">
                <h5 class="modal-title font-weight-bold" id="editBundleModalLabel"><i class="fa-solid fa-pen-to-square text-primary mr-2"></i> Edit Data Bundle</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <p class="text-muted">Provider: Datasub247</p>
                    <input type="hidden" name="custom_api_id" id="edit_custom_api_id" value="{{ $providers->first()?->id }}">
                </div>
                <div class="form-group mb-3">
                    <label>Provider plan ID</label>
                    <input name="provider_plan_id" id="edit_provider_plan_id" class="form-control" required placeholder="Exact numeric plan ID from Datasub247">
                </div>

                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Network Provider</label>
                    <select id="edit_network" name="network" class="form-control text-white" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);" required>
                        <option value="MTN">MTN</option>
                        <option value="GLO">GLO</option>
                        <option value="AIRTEL">AIRTEL</option>
                        <option value="9MOBILE">9MOBILE</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Bundle Plan Name</label>
                    <input type="text" id="edit_data_plan" name="data_plan" class="form-control text-white" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);" required>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Selling Price (₦)</label>
                    <input type="number" step="1" id="edit_amount" name="amount" class="form-control text-white" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);" required>
                </div>
                <div class="form-group mb-3">
                    <label class="text-white-50 small mb-1">Validity Period</label>
                    <input type="text" id="edit_validate" name="validate" class="form-control text-white" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);">
                </div>
            </div>
            <div class="modal-footer border-top border-white-10">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary font-weight-bold px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteBundleForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('pricingBandFilter').addEventListener('change', function () {
        document.querySelectorAll('[data-pricing-band]').forEach(row => { row.hidden = this.value !== 'all' && row.dataset.pricingBand !== this.value; });
    });
    function openEditModal(plan) {
        let updateUrl = "{{ url(config('app.admin_path', 'admin').'/vtu/pricing/bundle') }}/" + plan.id;
        $('#editBundleForm').attr('action', updateUrl);
        $('#edit_network').val(plan.network.toUpperCase());
        $('#edit_data_plan').val(plan.data_plan);
        $('#edit_amount').val(plan.amount);
        $('#edit_custom_api_id').val(@json($providers->first()?->id));
        $('#edit_provider_plan_id').val(plan.provider_plan_id || '');
        $('#edit_validate').val(plan.validate || '30 Days');
        
        let modalEl = document.getElementById('editBundleModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            let m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            m.show();
        } else {
            $('#editBundleModal').modal('show');
        }
    }

    function confirmDeleteBundle(id, planName) {
        Swal.fire({
            title: 'Delete Data Bundle?',
            text: `Are you sure you want to remove "${planName}"? Users will no longer be able to select it.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#374151',
            confirmButtonText: 'Yes, Delete',
            background: '#111827',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                let delUrl = "{{ url('/admin/vtu/pricing/bundle') }}/" + id;
                let form = $('#deleteBundleForm');
                form.attr('action', delUrl);
                form.submit();
            }
        });
    }
</script>
@endpush
