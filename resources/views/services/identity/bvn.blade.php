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
        <button class="s-tab" onclick="switchMainPanel('retrieve', this)"><i class="fa-solid fa-clock-rotate-left mr-1 text-primary"></i> BVN Retrieval <span class="badge badge-primary py-0 px-1 ml-1" style="font-size: 10px; letter-spacing: 0.5px;">NEW</span></button>
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

    <!-- PANEL: BVN Retrieval (Phone + Owner's Name) -->
    <div id="panel-retrieve" class="main-panel">
        <div class="row">
            <div class="col-lg-12">
                <!-- Info / Banner -->
                <div class="panel-card p-4 mb-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(16, 185, 129, 0.08)); border: 1px solid rgba(79, 70, 229, 0.25);">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <div class="rounded-circle p-3 mr-3 text-center" style="background: rgba(79, 70, 229, 0.2); width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-wand-magic-sparkles text-primary font-size-lg"></i>
                            </div>
                            <div>
                                <h5 class="h6 font-weight-bold text-white mb-1">Lost or Forgotten BVN Retrieval</h5>
                                <p class="text-white-50 small mb-0">Send a phone number with the account owner's full name to retrieve the linked BVN. Most requests resolve within 24 hours.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-right mr-3">
                                <span class="badge badge-success-soft text-success px-2 py-1 small"><i class="fa-solid fa-shield-check mr-1"></i> 100% Refund Guarantee</span>
                                <div class="text-muted small mt-1">Automatic wallet refund if not found</div>
                            </div>
                            <span class="badge badge-info-soft text-info py-2 px-3 font-weight-bold" style="font-size: 15px;">₦{{ number_format($prices['retrieval'] ?? 800, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="panel-card p-4 mb-4">
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-white-5">
                        <h2 class="h6 font-weight-bold m-0"><i class="fa-solid fa-paper-plane mr-2 text-primary"></i> Submit BVN Retrieval Request</h2>
                    </div>

                    <form id="retrievalForm" action="{{ route('services.bvn.retrieve') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-phone"></i>
                                    <input type="tel" name="phone" class="form-control" placeholder="08012345678" required maxlength="15" value="{{ old('phone') }}">
                                </div>
                                <small class="text-muted">Phone number linked to the lost BVN profile.</small>
                            </div>
                            <div class="col-md-5 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">Account Owner's Full Name <span class="text-danger">*</span></label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-user"></i>
                                    <input type="text" name="full_name" class="form-control" placeholder="e.g. MOHAMMED IBRAHIM YUSUF" required maxlength="190" value="{{ old('full_name') }}">
                                </div>
                                <small class="text-muted">Exact full name associated with the bank account.</small>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="font-weight-600 mb-2 small text-muted">Date of Birth <span class="text-muted small">(Optional)</span></label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-calendar"></i>
                                    <input type="text" name="dob" class="form-control" placeholder="DD-MM-YYYY" value="{{ old('dob') }}">
                                </div>
                                <small class="text-muted">Optional: Speeds up identity matching.</small>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top border-white-5 gap-2">
                            <div class="text-white-50 small">
                                <i class="fa-solid fa-clock mr-1 text-warning"></i> Turnaround: <strong>Within 24 Hours</strong> (Available daily including weekends)
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="btn-submit-retrieval">
                                <i class="fa-solid fa-paper-plane mr-2"></i> Submit Request (₦{{ number_format($prices['retrieval'] ?? 800) }})
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Retrieval Requests Tracker -->
                <div class="panel-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="h6 font-weight-bold m-0"><i class="fa-solid fa-list-check mr-2 text-warning"></i> My Retrieval Requests</h3>
                        <span class="badge badge-outline-secondary">{{ $retrievalRequests->count() }} Total</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Ticket Ref</th>
                                    <th>Phone & Name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Result / BVN</th>
                                    <th>Date</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($retrievalRequests as $req)
                                    <tr>
                                        <td>
                                            <code class="text-primary font-weight-bold">{{ $req->transaction_id }}</code>
                                            @if($req->provider_transaction_id)
                                                <div class="text-muted" style="font-size: 11px;">Ext: {{ $req->provider_transaction_id }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-600 text-white">{{ $req->full_name }}</div>
                                            <div class="small text-muted"><i class="fa-solid fa-phone mr-1"></i>{{ $req->phone_number }}</div>
                                        </td>
                                        <td>₦{{ number_format($req->amount, 2) }}</td>
                                        <td>
                                            @if($req->isCompleted())
                                                <span class="badge badge-success px-2 py-1"><i class="fa-solid fa-circle-check mr-1"></i> Completed</span>
                                            @elseif($req->isRefunded())
                                                <span class="badge badge-info px-2 py-1" title="{{ $req->failure_reason }}"><i class="fa-solid fa-arrow-rotate-left mr-1"></i> Refunded</span>
                                            @elseif($req->isFailed())
                                                <span class="badge badge-danger px-2 py-1"><i class="fa-solid fa-circle-xmark mr-1"></i> Failed</span>
                                            @else
                                                <span class="badge badge-warning px-2 py-1"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->isCompleted() && $req->retrieved_bvn)
                                                <div class="d-flex align-items-center">
                                                    <span class="font-weight-bold text-success mr-2 font-monospace" style="letter-spacing: 1px; font-size: 14px;">{{ $req->retrieved_bvn }}</span>
                                                    <button type="button" class="btn btn-xs btn-outline-success" onclick="navigator.clipboard.writeText('{{ $req->retrieved_bvn }}'); alert('BVN copied to clipboard!');" title="Copy BVN">
                                                        <i class="fa-solid fa-copy"></i>
                                                    </button>
                                                </div>
                                            @elseif($req->isRefunded())
                                                <span class="small text-muted">Not Found (Refunded)</span>
                                            @else
                                                <span class="small text-white-50"><i class="fa-regular fa-clock mr-1"></i> Pending provider...</span>
                                            @endif
                                        </td>
                                        <td>{{ $req->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-right">
                                            @if($req->isPending())
                                                <form action="{{ route('services.bvn.retrieve.check', $req->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-outline-warning">
                                                        <i class="fa-solid fa-arrows-rotate mr-1"></i> Check Status
                                                    </button>
                                                </form>
                                            @elseif($req->isCompleted())
                                                <span class="badge badge-outline-success"><i class="fa-solid fa-check mr-1"></i> Saved to Vault</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted small">No BVN retrieval requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
        const target = document.getElementById('panel-' + panel);
        if (target) {
            target.classList.add('active');
        }
        document.querySelectorAll('.s-tab').forEach(t => t.classList.remove('active'));
        if (btn) {
            btn.classList.add('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const defaultPanel = @json(session('bvn_active_panel'));
        if (defaultPanel) {
            const panelBtn = Array.from(document.querySelectorAll('.s-tab')).find((btn) => {
                const onclick = btn.getAttribute('onclick') || '';
                return onclick.includes("switchMainPanel('" + defaultPanel + "'");
            });

            if (panelBtn) {
                switchMainPanel(defaultPanel, panelBtn);
            }
        }
    });
</script>
@endpush
