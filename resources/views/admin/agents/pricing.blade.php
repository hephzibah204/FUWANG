@extends('layouts.nexus')

@section('title', 'NIN Pricing for Enrollment Agents | Agency Network Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Top Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <a href="{{ route('admin.agents.overview') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Command Center
                </a>
                <span class="text-white-50 text-xs">/</span>
                <a href="{{ route('admin.agents.index') }}" class="text-white-50 text-xs text-decoration-none">Agency Network</a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-emerald-400 text-xs fw-semibold">NIN Agent Wholesale Pricing</span>
            </div>
            <h3 class="text-white fw-bold mb-1">
                <i class="fa-solid fa-tags text-emerald-400 me-2"></i>NIN Pricing for Enrollment Agents
            </h3>
            <p class="text-white-50 mb-0">Configure wholesale rates and default discounts for licensed NIMC Enrollment Agents across all verification services.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.agents.overview') }}" class="btn btn-outline-light rounded-pill fw-bold">
                <i class="fa-solid fa-chart-pie me-1"></i>Command Center
            </a>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-emerald rounded-pill fw-bold" style="color: #34d399; border-color: rgba(52, 211, 153, 0.4);">
                <i class="fa-solid fa-users me-1"></i>Agent Directory
            </a>
            <a href="{{ route('admin.settings.index') }}#tab-pricing" class="btn btn-outline-warning rounded-pill fw-bold">
                <i class="fa-solid fa-gear me-1"></i>All Pricing
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.3) !important;">
            <div><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4 p-3" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Please resolve the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top KPI Matrix -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Default Wholesale Discount</span>
                    <span class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(16, 185, 129, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-percent text-emerald-400"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="text-white fw-bold mb-0 font-monospace">{{ number_format($agentStats['default_discount'], 0) }}%</h2>
                    <span class="badge bg-emerald-500/20 text-emerald-400 text-xs">Active Base</span>
                </div>
                <p class="text-white-50 text-xs mb-0">Applied across any service without an explicit price override.</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Services Covered</span>
                    <span class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(59, 130, 246, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-layer-group text-primary"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="text-white fw-bold mb-0 font-monospace">{{ count($services) }}</h2>
                    <span class="badge bg-primary-subtle text-primary text-xs">NIMC & BVN</span>
                </div>
                <p class="text-white-50 text-xs mb-0">Full coverage of NIN suite, validation, IPE, tracking & BVN.</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Custom Overrides</span>
                    <span class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(245, 158, 11, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-sliders text-warning"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="text-white fw-bold mb-0 font-monospace">{{ $agentStats['active_overrides'] }}</h2>
                    <span class="badge bg-warning text-dark text-xs font-monospace">{{ $agentStats['active_overrides'] > 0 ? 'Custom Active' : 'All Automated' }}</span>
                </div>
                <p class="text-white-50 text-xs mb-0">Explicit fixed wholesale rates set by operations.</p>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Approved Agents Benefiting</span>
                    <span class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(168, 85, 247, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-id-badge text-purple"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="text-white fw-bold mb-0 font-monospace">{{ $agentStats['approved_agents'] }}</h2>
                    <span class="badge bg-purple-subtle text-purple text-xs">Licensed</span>
                </div>
                <p class="text-white-50 text-xs mb-0">Total active agents currently enjoying wholesale rates.</p>
            </div>
        </div>
    </div>

    <!-- Main Pricing Form Card -->
    <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <form id="agentPricingForm" action="{{ route('admin.agents.pricing.update') }}" method="POST">
            @csrf

            <!-- Global Discount Percentage Strip -->
            <div class="p-3 rounded-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 78, 59, 0.25)); border: 1px solid rgba(16, 185, 129, 0.3);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 text-center" style="background: rgba(16, 185, 129, 0.25); width: 44px; height: 44px;">
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-400 fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-white fw-bold mb-0">Global Base Wholesale Discount Policy</h6>
                        <span class="text-white-50 small">All approved enrollment agents receive this discount by default on all NIN Suite tools unless an explicit price override is set below.</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="agent_discount_percentage" class="text-white small fw-bold mb-0 text-nowrap">Discount %:</label>
                    <div class="input-group input-group-sm" style="width: 130px;">
                        <input type="number" step="0.1" min="0" max="100" id="agent_discount_percentage" name="agent_discount_percentage" class="form-control text-center font-monospace fw-bold" value="{{ old('agent_discount_percentage', $discountPercentage) }}" required>
                        <span class="input-group-text bg-dark text-white border-secondary">%</span>
                    </div>
                </div>
            </div>

            <!-- Pricing Table -->
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                    <thead>
                        <tr class="text-white-50 text-xs text-uppercase border-bottom border-white-10">
                            <th style="width: 32%;">Service &amp; Code</th>
                            <th style="width: 15%;" class="text-end">Standard Public Price</th>
                            <th style="width: 18%;" class="text-end">Default (<span id="discountHeaderPct">{{ $discountPercentage }}</span>%) Rate</th>
                            <th style="width: 20%;" class="text-end">Agent Wholesale Override</th>
                            <th style="width: 15%;" class="text-end">Effective Agent Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $svc)
                            <tr class="border-bottom border-white-5" id="row-{{ $svc['agent_key'] }}" data-standard="{{ $svc['standard'] }}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 p-2 text-center d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.05); width: 38px; height: 38px; flex-shrink: 0;">
                                            <i class="{{ $svc['icon'] }} text-emerald-400"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-0.5">
                                                <h6 class="text-white fw-bold mb-0 fs-6">{{ $svc['name'] }}</h6>
                                                <span class="badge bg-secondary rounded-pill font-monospace text-2xs">{{ $svc['code'] }}</span>
                                            </div>
                                            <span class="text-white-50 small d-block" style="font-size: 0.76rem;">{{ $svc['description'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end font-monospace text-white-50">
                                    ₦{{ number_format($svc['standard'], 2) }}
                                </td>
                                <td class="text-end font-monospace">
                                    <span class="calculated-val text-info">₦{{ number_format($svc['discount_calculated'], 2) }}</span>
                                    <span class="d-block text-2xs text-white-50">Auto {{ $discountPercentage }}% Off</span>
                                </td>
                                <td class="text-end">
                                    <div class="input-group input-group-sm ms-auto" style="max-width: 160px;">
                                        <span class="input-group-text bg-dark text-white-50 border-secondary">₦</span>
                                        <input type="number" step="0.01" min="0" name="{{ $svc['agent_key'] }}" class="form-control override-input text-end font-monospace" placeholder="Auto" value="{{ old($svc['agent_key'], $svc['override']) }}" data-agent-key="{{ $svc['agent_key'] }}">
                                    </div>
                                    <small class="text-2xs text-white-50 d-block mt-1">Leave empty to use {{ $discountPercentage }}% discount</small>
                                </td>
                                <td class="text-end">
                                    <div class="effective-wrap">
                                        <h6 class="mb-0 fw-bold font-monospace effective-display text-emerald-400">
                                            ₦{{ number_format($svc['effective'], 2) }}
                                        </h6>
                                        <span class="badge status-badge {{ $svc['has_override'] ? 'bg-warning text-dark' : 'bg-success-subtle text-success' }} text-2xs font-monospace mt-1">
                                            {{ $svc['has_override'] ? 'Custom Fixed' : 'Auto 15% Off' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Form Actions -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-4 pt-3 border-top border-white-10">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 fw-bold btn-sm" onclick="confirmResetOverrides()">
                        <i class="fa-solid fa-rotate-left me-1"></i>Reset All to Default Discount
                    </button>
                    <span class="text-white-50 text-xs">Clears all overrides so every service follows the base percentage formula.</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.agents.overview') }}" class="btn btn-outline-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-emerald rounded-pill px-4 fw-bold" style="background: #10b981; color: #ffffff; border: none;">
                        <i class="fa-solid fa-check me-2"></i>Save Wholesale Pricing
                    </button>
                </div>
            </div>
        </form>

        <form id="resetOverridesForm" action="{{ route('admin.agents.pricing.reset') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>

    <!-- Information Card -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06) !important;">
        <h6 class="text-white fw-bold mb-2"><i class="fa-solid fa-circle-info text-info me-2"></i>How Agency Wholesale Pricing Operates</h6>
        <div class="row g-3 text-white-50 small">
            <div class="col-md-4">
                <strong class="text-white d-block mb-1">1. Automatic Agent Detection</strong>
                Whenever a user accesses any NIN Suite tool or submits a verification job, the system checks if the user possesses an approved enrollment agency account.
            </div>
            <div class="col-md-4">
                <strong class="text-white d-block mb-1">2. Wholesale Price Resolution</strong>
                If an explicit override exists in this table, that exact figure is billed. If empty, the system automatically discounts the standard price by the base percentage.
            </div>
            <div class="col-md-4">
                <strong class="text-white d-block mb-1">3. Unified Wallet Debiting</strong>
                Wholesale fees are debited seamlessly from the agent's single balance. Agents can also switch anytime between Agency Terminal and User Dashboard.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmResetOverrides() {
        if (confirm('Are you sure you want to clear all custom price overrides? All services will immediately revert to the auto-calculated discount rate.')) {
            document.getElementById('resetOverridesForm').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const discountInput = document.getElementById('agent_discount_percentage');
        const discountHeaderPct = document.getElementById('discountHeaderPct');
        const rows = document.querySelectorAll('tr[id^="row-"]');

        function recalculate() {
            const pct = parseFloat(discountInput.value) || 0;
            if (discountHeaderPct) {
                discountHeaderPct.textContent = pct;
            }
            const factor = Math.max(0, 1 - (pct / 100));

            rows.forEach(function (row) {
                const standard = parseFloat(row.dataset.standard) || 0;
                const calculatedValSpan = row.querySelector('.calculated-val');
                const overrideInput = row.querySelector('.override-input');
                const effectiveDisplay = row.querySelector('.effective-display');
                const statusBadge = row.querySelector('.status-badge');

                const autoPrice = Math.round((standard * factor) * 100) / 100;
                if (calculatedValSpan) {
                    calculatedValSpan.textContent = '₦' + autoPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                const overrideVal = overrideInput && overrideInput.value.trim() !== '' ? parseFloat(overrideInput.value) : null;

                if (overrideVal !== null && !isNaN(overrideVal) && overrideVal > 0) {
                    effectiveDisplay.textContent = '₦' + overrideVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    statusBadge.textContent = 'Custom Fixed';
                    statusBadge.className = 'badge status-badge bg-warning text-dark text-2xs font-monospace mt-1';
                } else {
                    effectiveDisplay.textContent = '₦' + autoPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    statusBadge.textContent = 'Auto ' + pct + '% Off';
                    statusBadge.className = 'badge status-badge bg-success-subtle text-success text-2xs font-monospace mt-1';
                }
            });
        }

        if (discountInput) {
            discountInput.addEventListener('input', recalculate);
        }

        document.querySelectorAll('.override-input').forEach(function (inp) {
            inp.addEventListener('input', recalculate);
        });
    });
</script>
@endpush
