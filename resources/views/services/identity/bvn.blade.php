@extends('layouts.nexus')

@section('title', 'BVN Identity Suite | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <x-nexus.service-header
        title="BVN Intelligence Suite"
        title-class="h4 font-weight-bold mb-1"
        subtitle="Verify, Match, and Cross-Reference Bank Verification Number profiles instantly."
        subtitle-class="text-muted small"
        icon="fa-solid fa-university"
        icon-style="background: rgba(79, 70, 229, 0.15); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.3);"
        style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(67, 56, 202, 0.05)); border-color: rgba(79, 70, 229, 0.2);"
    >
        <x-slot name="badges">
            <span class="badge-accent"><i class="fa-solid fa-shield-check text-success"></i> Premium Data</span>
            <span class="badge-accent"><i class="fa-solid fa-link text-primary"></i> Linked Records</span>
        </x-slot>
    </x-nexus.service-header>

    <!-- Tab Navigation -->
    <div class="tab-strip mb-4">
        <button class="s-tab active" onclick="switchMainPanel('verify', this)"><i class="fa-solid fa-search mr-1"></i> BVN Lookup</button>
        <button class="s-tab" onclick="switchMainPanel('match', this)"><i class="fa-solid fa-equals mr-1"></i> Identity Match</button>
        <button class="s-tab" onclick="switchMainPanel('combi', this)"><i class="fa-solid fa-layer-group mr-1"></i> Combined Search</button>
        <button class="s-tab ml-auto border-left border-white-5" onclick="switchMainPanel('vault', this)"><i class="fa-solid fa-vault text-warning mr-1"></i> Vault ({{ $myResults->count() }})</button>
    </div>


    <!-- PANEL 1: BVN Standard Profile Lookup -->
    <div id="panel-verify" class="main-panel active">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel-card p-4 mb-4" id="view-search-panel">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                        <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-id-card-clip mr-2 text-primary"></i> Standard BVN Verify</h2>
                        <div class="ml-auto d-flex gap-2">
                            <span class="badge badge-info-soft text-info py-2 px-3" id="verify-price-tag" data-price="{{ $prices['basic'] ?? 100 }}">₦{{ number_format($prices['basic'] ?? 100, 2) }}</span>
                        </div>
                    </div>

                    <form id="verifyForm" class="bvn-mode-form" action="{{ route('services.bvn.verify') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="standard">
                        
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">BVN Number</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-id-card"></i>
                                    <input type="text" name="number" class="form-control" placeholder="10000000001" required maxlength="11">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">First Name</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-user"></i>
                                    <input type="text" name="firstname" class="form-control" placeholder="JOHN">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">Last Name</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-user"></i>
                                    <input type="text" name="lastname" class="form-control" placeholder="DOE">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">Date of Birth</label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-calendar"></i>
                                    <input type="text" name="dob" class="form-control" placeholder="DD-MM-YYYY">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2 align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-600 mb-2 small text-muted">Provider</label>
                                @if($bvnProviders->count() > 1)
                                    <select id="verify_provider" name="api_provider_id" class="form-control form-control-sm">
                                        @foreach($bvnProviders as $provider)
                                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                        @endforeach
                                    </select>
                                @elseif($bvnProviders->count() == 1)
                                    <input type="hidden" id="verify_provider" name="api_provider_id" value="{{ $bvnProviders->first()->id }}">
                                    <div class="text-white font-weight-bold">{{ $bvnProviders->first()->name }}</div>
                                @else
                                    <div class="text-warning small"><i class="fa-solid fa-triangle-exclamation"></i> Legacy Gateway Active</div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-600 mb-2 small text-muted">Verification Level</label>
                                <select id="verify_type" name="verification_type" class="form-control form-control-sm">
                                    <option value="basic" data-price="{{ $prices['basic'] ?? 100 }}">Essential Data (₦{{ number_format($prices['basic'] ?? 100) }})</option>
                                    <option value="premium" data-price="{{ $prices['premium'] ?? 500 }}">Premium + Image (₦{{ number_format($prices['premium'] ?? 500) }})</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg w-100" id="verify-btn-standard">
                                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Run BVN Check
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional panels (match, combi, vault) would go here, simplified for this migration -->
    <div id="panel-vault" class="main-panel">
        <div class="panel-card p-4">
            <h3 class="h6 font-weight-bold mb-4">BVN Verification History</h3>
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Identifier</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myResults as $res)
                            <tr>
                                <td><code class="text-primary">{{ $res->reference_id }}</code></td>
                                <td>{{ $res->identifier }}</td>
                                <td><span class="badge badge-outline-primary">{{ $res->provider_name }}</span></td>
                                <td>{{ $res->created_at->format('M d, Y') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('services.verification.report', $res->id) }}" class="btn btn-xs btn-outline-light ml-1">
                                        <i class="fa fa-file-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">No records found in vault.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchMainPanel(panel, btn) {
        document.querySelectorAll('.main-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('panel-' + panel).classList.add('active');
        document.querySelectorAll('.s-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
    }
</script>
@endpush
