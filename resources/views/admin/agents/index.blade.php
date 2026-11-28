@extends('layouts.nexus')

@section('title', 'Manage NIN Enrollment Agents | Admin')

@section('content')
<div class="container-fluid py-4 admin-agents-page">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.overview') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Command Center
                </a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-emerald-400 text-xs font-semibold">Agent Directory</span>
            </div>
            <h1 class="h3 text-white font-weight-bold mb-1 agents-page-title">
                <i class="fa-solid fa-users-gear text-primary me-2"></i>NIN Enrollment Agents Directory
            </h1>
            <p class="text-muted small mb-0">Review agent applications, verify credentials, manage approvals, and track performance.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap agents-header-actions">
            <a href="{{ route('admin.agents.licenses.index') }}" class="btn btn-warning text-dark rounded-12 font-weight-bold px-3">
                <i class="fa-solid fa-certificate me-1.5"></i> NIN Licenses
            </a>
            <a href="{{ route('admin.agents.notifications.index') }}" class="btn btn-primary rounded-12 font-weight-bold px-3">
                <i class="fa-solid fa-bullhorn me-1.5"></i> Broadcast
            </a>
            <a href="{{ route('admin.agents.roster.index') }}" class="btn btn-outline-warning rounded-12 font-weight-bold px-3">
                <i class="fa-solid fa-clipboard-user me-1.5"></i> Master Roster
            </a>
            <a href="{{ route('admin.agents.leaderboard') }}" class="btn btn-outline-light rounded-12 font-weight-bold px-3">
                <i class="fa-solid fa-trophy me-1.5"></i> Leaderboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-12 p-3 text-sm mb-4">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Status Filters & Search Bar Card -->
    <div class="glass-card p-4 rounded-20 border border-white-10 mb-4 agents-filter-card">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-sm {{ !$status && !request('license_status') ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3 py-1.5 font-weight-medium">
                        All Agents ({{ $counts['total'] }})
                    </a>
                    <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning' }} rounded-pill px-3 py-1.5">
                        Pending ({{ $counts['pending'] }})
                    </a>
                    <a href="{{ route('admin.agents.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-success font-weight-bold' : 'btn-outline-success' }} rounded-pill px-3 py-1.5">
                        Approved ({{ $counts['approved'] }})
                    </a>
                    <a href="{{ route('admin.agents.index', ['license_status' => 'paid']) }}" class="btn btn-sm {{ request('license_status') === 'paid' ? 'btn-success font-weight-bold' : 'btn-outline-success' }} rounded-pill px-3 py-1.5">
                        <i class="fa-solid fa-certificate me-1"></i> Licensed ({{ $counts['licensed_paid'] ?? 0 }})
                    </a>
                    <a href="{{ route('admin.agents.index', ['license_status' => 'pending_review']) }}" class="btn btn-sm {{ request('license_status') === 'pending_review' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning' }} rounded-pill px-3 py-1.5">
                        <i class="fa-solid fa-clock me-1"></i> Review ({{ $counts['licensed_pending'] ?? 0 }})
                    </a>
                    <a href="{{ route('admin.agents.index', ['license_status' => 'unpaid']) }}" class="btn btn-sm {{ request('license_status') === 'unpaid' ? 'btn-secondary text-white font-weight-bold' : 'btn-outline-secondary text-white-50' }} rounded-pill px-3 py-1.5">
                        Unpaid ({{ $counts['licensed_unpaid'] ?? 0 }})
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <form action="{{ route('admin.agents.index') }}" method="GET">
                    @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                    @if(request('license_status')) <input type="hidden" name="license_status" value="{{ request('license_status') }}"> @endif
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm bg-dark border-white-10 text-white rounded-start-12" placeholder="Search name, phone, NIN, IMEI...">
                        <button type="submit" class="btn btn-primary btn-sm rounded-end-12 px-3 font-weight-bold">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Agent Applications Table Card -->
    <div class="glass-card rounded-20 overflow-hidden border border-white-10 agents-list-card">
        <form id="bulkActionForm" action="{{ route('admin.agents.bulk_action') }}" method="POST">
            @csrf
            <!-- Table Header Toolbar -->
            <div class="p-3 px-4 border-bottom border-white-10 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white-50 text-xs">
                        Showing <strong class="text-white">{{ $agents->firstItem() ?? 0 }}</strong> to <strong class="text-white">{{ $agents->lastItem() ?? 0 }}</strong> of <strong class="text-white">{{ $agents->total() }}</strong> total agents
                    </span>
                </div>

                <!-- Floating / Inline Bulk Action Controls -->
                <div id="bulkActionBar" class="d-none align-items-center gap-2 bg-dark p-1.5 px-3 rounded-pill border border-primary shadow-sm">
                    <span class="text-xs text-white fw-bold me-1"><span id="selectedCount" class="badge bg-primary text-white rounded-pill">0</span> selected</span>
                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-2xs fw-bold">
                        <i class="fa-solid fa-check me-1"></i>Approve
                    </button>
                    <button type="submit" name="action" value="suspend" class="btn btn-sm btn-warning text-dark rounded-pill px-2.5 py-1 text-2xs fw-bold">
                        <i class="fa-solid fa-ban me-1"></i>Suspend
                    </button>
                    <button type="submit" name="action" value="export" class="btn btn-sm btn-info rounded-pill px-2.5 py-1 text-2xs fw-bold">
                        <i class="fa-solid fa-file-csv me-1"></i>Export CSV
                    </button>
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-2xs fw-bold" onclick="return confirm('Delete selected agent profile records? Main user accounts will remain safe.');">
                        <i class="fa-solid fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>

            <div class="table-responsive agent-table-responsive agents-desktop-table">
                <table class="table table-dark table-hover mb-0 align-middle table-agents">
                    <thead class="table-light-5">
                        <tr class="text-muted text-xs text-uppercase tracking-wider">
                            <th class="ps-3 ps-md-4" style="width: 36px; min-width: 36px;">
                                <input type="checkbox" id="selectAllAgents" class="form-check-input bg-dark border-white-20" title="Select All On Page">
                            </th>
                            <th style="min-width: 155px;">Agent Info</th>
                            <th style="min-width: 130px;">Contact / Email</th>
                            <th style="min-width: 120px;">NIN & BVN Details</th>
                            <th style="min-width: 120px;">Machine IMEI</th>
                            <th style="min-width: 85px;">Status</th>
                            <th style="min-width: 80px;">License</th>
                            <th style="min-width: 90px;">Enrollments</th>
                            <th class="pe-3 pe-md-4 text-end sticky-action-col" style="min-width: 155px; width: 155px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td class="ps-3 ps-md-4 py-2.5">
                                    <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}" class="form-check-input agent-checkbox bg-dark border-white-20">
                                </td>
                                <td class="py-2.5">
                                    <div class="d-flex align-items-start gap-2.5">
                                        <div class="avatar-circle rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-xs" style="width: 34px; height: 34px; min-width: 34px; flex-shrink: 0; background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); margin-top: 2px;">
                                            {{ substr($agent->full_name, 0, 1) }}
                                        </div>
                                        <div style="min-width: 0;">
                                            <a href="{{ route('admin.agents.show', $agent->id) }}" class="text-white font-weight-bold text-decoration-none d-block hover-primary" title="{{ $agent->full_name }}">
                                                {{ $agent->full_name }}
                                            </a>
                                            <div class="d-flex align-items-center gap-1 flex-wrap mt-0.5">
                                                @if($agent->is_fast_tracked)
                                                    <span class="badge bg-warning text-dark font-weight-bold text-3xs rounded-pill px-1.5 py-0.5">Fast-Tracked</span>
                                                @endif
                                                @if($agent->is_mva_of_month)
                                                    <span class="badge bg-warning text-dark font-weight-bold text-3xs rounded-pill px-1.5 py-0.5"><i class="fa-solid fa-star me-0.5"></i> MVP</span>
                                                @endif
                                                @if($agent->company_agent_code)
                                                    <span class="badge bg-secondary text-white font-monospace text-3xs rounded-pill px-1.5 py-0.5">Code: {{ $agent->company_agent_code }}</span>
                                                @endif
                                                <span class="text-muted text-3xs d-block text-truncate" style="max-width: 150px;">{{ $agent->meta['station_name'] ?? 'Primary Terminal' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <span class="d-block text-white font-weight-medium text-xs">{{ $agent->phone_number }}</span>
                                    <span class="d-block text-muted text-2xs" style="word-break: break-all; max-width: 175px;" title="{{ $agent->user->email ?? 'No User Email' }}">{{ $agent->user->email ?? 'No User Email' }}</span>
                                </td>
                                <td class="py-2.5 text-nowrap">
                                    <span class="d-block text-info font-monospace text-xs">
                                        NIN: {{ $agent->nin }}
                                        @if($agent->nin_verified)
                                            <i class="fa-solid fa-circle-check text-success ms-1" title="NIN Verified"></i>
                                        @endif
                                    </span>
                                    <span class="d-block text-muted font-monospace text-2xs">BVN: {{ $agent->bvn }}</span>
                                </td>
                                <td class="py-2.5">
                                    @if($agent->machine_imei)
                                        @php
                                            preg_match_all('/\b\d{14,17}\b/', $agent->machine_imei, $imeiMatches);
                                            $imeis = !empty($imeiMatches[0]) ? $imeiMatches[0] : [trim($agent->machine_imei)];
                                        @endphp
                                        <div class="d-flex flex-column gap-1" style="min-width: 125px; max-width: 165px;">
                                            @foreach($imeis as $imei)
                                                <code class="text-warning text-3xs px-1.5 py-0.5 bg-dark rounded border border-white-10 font-monospace d-inline-block text-truncate" title="{{ $imei }}">{{ $imei }}</code>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted text-2xs">Not set</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-nowrap">
                                    @if($agent->isApproved())
                                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1 text-2xs font-weight-bold">APPROVED</span>
                                    @elseif($agent->isPending())
                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-2xs font-weight-bold">PENDING</span>
                                    @elseif($agent->isRejected())
                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 text-2xs font-weight-bold">REJECTED</span>
                                    @elseif($agent->isSuspended())
                                        <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 text-2xs font-weight-bold">SUSPENDED</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-nowrap">
                                    @if($agent->isLicensePaid())
                                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1 text-2xs font-weight-bold" title="{{ ucfirst($agent->license_payment_method ?? 'paid') }}">
                                            <i class="fa-solid fa-certificate me-1"></i> Paid
                                        </span>
                                    @elseif($agent->isLicensePendingReview())
                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-2xs font-weight-bold">
                                            <i class="fa-solid fa-clock me-1"></i> Review
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white-50 rounded-pill px-2.5 py-1 text-2xs">
                                            Unpaid
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-nowrap">
                                    <span class="d-block text-success font-weight-bold text-xs">{{ number_format($agent->total_enrollments) }} total</span>
                                    <span class="d-block text-muted text-2xs">({{ number_format($agent->monthly_enrollments) }}/mo)</span>
                                </td>
                                <td class="pe-3 pe-md-4 py-2.5 text-end text-nowrap sticky-action-col" style="min-width: 155px; width: 155px;">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1.5">
                                        <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-sm btn-outline-info text-info rounded-10 p-0" title="View Dossier" style="width: 29px; height: 29px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-sm btn-outline-light rounded-10 p-0" title="Edit Profile & Hardware" style="width: 29px; height: 29px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        @if($agent->isPending())
                                            <button type="submit" form="singleApprove{{ $agent->id }}" class="btn btn-sm btn-success rounded-10 p-0" title="Approve Agent" style="width: 29px; height: 29px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        <button type="submit" form="singleDelete{{ $agent->id }}" class="btn btn-sm btn-outline-danger text-danger rounded-10 p-0" title="Delete Agent Profile Only" style="width: 29px; height: 29px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-users-slash fa-2x mb-3 d-block"></i>
                                    No enrollment agent records found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile card list: replaces the wide table under 768px (owner report 2026-11-27) -->
            <div class="agents-mobile-cards">
                <div class="d-flex align-items-center gap-2 pb-2">
                    <input type="checkbox" id="selectAllAgentsMobile" class="form-check-input bg-dark border-white-20">
                    <label for="selectAllAgentsMobile" class="text-white-50 text-xs mb-0">Select all on this page</label>
                </div>
                @forelse($agents as $agent)
                    <div class="agent-card">
                        <div class="d-flex align-items-start gap-2">
                            <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}" class="form-check-input agent-checkbox bg-dark border-white-20 mt-1">
                            <div class="avatar-circle rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-xs" style="width: 34px; height: 34px; min-width: 34px; flex-shrink: 0; background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4);">
                                {{ substr($agent->full_name, 0, 1) }}
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <a href="{{ route('admin.agents.show', $agent->id) }}" class="text-white font-weight-bold text-decoration-none d-block" title="{{ $agent->full_name }}">{{ $agent->full_name }}</a>
                                <div class="d-flex align-items-center gap-1 flex-wrap mt-0.5">
                                    @if($agent->is_fast_tracked)
                                        <span class="badge bg-warning text-dark font-weight-bold text-3xs rounded-pill px-1.5 py-0.5">Fast-Tracked</span>
                                    @endif
                                    @if($agent->is_mva_of_month)
                                        <span class="badge bg-warning text-dark font-weight-bold text-3xs rounded-pill px-1.5 py-0.5"><i class="fa-solid fa-star me-0.5"></i> MVP</span>
                                    @endif
                                    @if($agent->company_agent_code)
                                        <span class="badge bg-secondary text-white font-monospace text-3xs rounded-pill px-1.5 py-0.5">Code: {{ $agent->company_agent_code }}</span>
                                    @endif
                                    <span class="text-muted text-3xs">{{ $agent->meta['station_name'] ?? 'Primary Terminal' }}</span>
                                </div>
                            </div>
                            @if($agent->isApproved())
                                <span class="badge bg-success text-white rounded-pill px-2 py-1 text-3xs font-weight-bold">APPROVED</span>
                            @elseif($agent->isPending())
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1 text-3xs font-weight-bold">PENDING</span>
                            @elseif($agent->isRejected())
                                <span class="badge bg-danger text-white rounded-pill px-2 py-1 text-3xs font-weight-bold">REJECTED</span>
                            @elseif($agent->isSuspended())
                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1 text-3xs font-weight-bold">SUSPENDED</span>
                            @endif
                        </div>

                        <div class="agent-card-meta">
                            <div><i class="fa-solid fa-phone"></i>{{ $agent->phone_number }}</div>
                            <div style="word-break: break-all;"><i class="fa-solid fa-envelope"></i>{{ $agent->user->email ?? 'No User Email' }}</div>
                            <div><i class="fa-solid fa-id-card"></i><span class="font-monospace">NIN: {{ $agent->nin }}@if($agent->nin_verified) <i class="fa-solid fa-circle-check text-success" title="NIN Verified"></i>@endif</span> · <span class="font-monospace">BVN: {{ $agent->bvn }}</span></div>
                            <div>
                                <i class="fa-solid fa-microchip"></i>
                                @if($agent->machine_imei)
                                    @php
                                        preg_match_all('/\b\d{14,17}\b/', $agent->machine_imei, $cardImeiMatches);
                                        $cardImeis = !empty($cardImeiMatches[0]) ? $cardImeiMatches[0] : [trim($agent->machine_imei)];
                                    @endphp
                                    @foreach($cardImeis as $imei)
                                        <code class="text-warning text-3xs px-1.5 py-0.5 bg-dark rounded border border-white-10 font-monospace">{{ $imei }}</code>
                                    @endforeach
                                @else
                                    <span class="text-muted">IMEI not set</span>
                                @endif
                            </div>
                            <div>
                                <i class="fa-solid fa-certificate"></i>
                                @if($agent->isLicensePaid())
                                    <span class="text-success font-weight-bold">License Paid</span>
                                @elseif($agent->isLicensePendingReview())
                                    <span class="text-warning font-weight-bold">License Review</span>
                                @else
                                    <span class="text-white-50">License Unpaid</span>
                                @endif
                                <span class="text-muted">· {{ number_format($agent->total_enrollments) }} enrollments ({{ number_format($agent->monthly_enrollments) }}/mo)</span>
                            </div>
                        </div>

                        <div class="agent-card-actions">
                            <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-outline-info text-info"><i class="fa-solid fa-eye me-1"></i>View</a>
                            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-outline-light"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</a>
                            @if($agent->isPending())
                                <button type="submit" form="singleApprove{{ $agent->id }}" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>Approve</button>
                            @endif
                            <button type="submit" form="singleDelete{{ $agent->id }}" class="btn btn-outline-danger text-danger"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-users-slash fa-2x mb-3 d-block"></i>
                        No enrollment agent records found matching your filters.
                    </div>
                @endforelse
            </div>
        </form>

        <!-- Hidden Single Action Forms to avoid nested forms in table -->
        @foreach($agents as $agent)
            @if($agent->isPending())
                <form id="singleApprove{{ $agent->id }}" action="{{ route('admin.agents.approve', $agent->id) }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endif
            <form id="singleDelete{{ $agent->id }}" action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" class="d-none" onsubmit="return confirm('Delete agent profile for {{ $agent->full_name }}? Their user account will remain safe.');">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        @if($agents->hasPages())
            <div class="p-3 border-top border-white-10">
                {{ $agents->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllAgents');
    const checkboxes = document.querySelectorAll('.agent-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const countSpan = document.getElementById('selectedCount');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.agent-checkbox:checked');
        const count = checked.length;
        if (count > 0) {
            bulkBar.classList.remove('d-none');
            bulkBar.classList.add('d-flex');
            countSpan.textContent = count;
        } else {
            bulkBar.classList.remove('d-flex');
            bulkBar.classList.add('d-none');
            countSpan.textContent = '0';
        }
    }

    // The list renders twice (desktop table + mobile cards). Only the visible
    // set's checkboxes stay enabled, so bulk actions never receive duplicates.
    const selectAllMobile = document.getElementById('selectAllAgentsMobile');
    const mobileMq = window.matchMedia('(max-width: 767.98px)');

    function enabledCheckboxes() {
        return document.querySelectorAll('.agent-checkbox:not(:disabled)');
    }

    function syncSelectAll() {
        const enabled = enabledCheckboxes();
        const allChecked = enabled.length > 0 && document.querySelectorAll('.agent-checkbox:not(:disabled):checked').length === enabled.length;
        if (selectAll) selectAll.checked = allChecked;
        if (selectAllMobile) selectAllMobile.checked = allChecked;
    }

    function applyLayoutMode() {
        const mobile = mobileMq.matches;
        document.querySelectorAll('.agents-desktop-table .agent-checkbox').forEach(cb => { cb.disabled = mobile; if (mobile) cb.checked = false; });
        document.querySelectorAll('.agents-mobile-cards .agent-checkbox').forEach(cb => { cb.disabled = !mobile; if (!mobile) cb.checked = false; });
        syncSelectAll();
        updateBulkBar();
    }

    function bindSelectAll(el) {
        if (!el) return;
        el.addEventListener('change', function () {
            enabledCheckboxes().forEach(cb => cb.checked = el.checked);
            syncSelectAll();
            updateBulkBar();
        });
    }
    bindSelectAll(selectAll);
    bindSelectAll(selectAllMobile);

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            syncSelectAll();
            updateBulkBar();
        });
    });

    if (mobileMq.addEventListener) mobileMq.addEventListener('change', applyLayoutMode);
    applyLayoutMode();
});
</script>

<style>
    .agent-table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    .agent-table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    .agent-table-responsive::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.04);
        border-radius: 4px;
    }
    .agent-table-responsive::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.4);
        border-radius: 4px;
    }
    .agent-table-responsive::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.7);
    }
    .table-agents {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        min-width: 920px;
        width: 100%;
    }
    .table-agents th,
    .table-agents td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-top: none !important;
    }
    .sticky-action-col {
        position: -webkit-sticky !important;
        position: sticky !important;
        right: 0 !important;
        background: #0d1527 !important;
        box-shadow: -8px 0 16px -2px rgba(0, 0, 0, 0.65) !important;
        z-index: 10 !important;
    }
    thead th.sticky-action-col {
        background: #151e33 !important;
        z-index: 11 !important;
    }
    tbody tr:hover td.sticky-action-col {
        background: #1a253e !important;
    }
    .text-3xs {
        font-size: 0.68rem;
    }
    .rounded-10 {
        border-radius: 10px;
    }

    /* Mobile card list (hidden on desktop; the table takes over) */
    .agents-mobile-cards {
        display: none;
    }
    .agent-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 12px;
    }
    .agent-card-meta {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.75);
    }
    .agent-card-meta i {
        width: 16px;
        margin-right: 6px;
        color: rgba(255, 255, 255, 0.4);
        text-align: center;
    }
    .agent-card-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }
    .agent-card-actions .btn {
        flex: 1;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Mobile layout for the whole directory page (owner report 2026-11-27) */
    @media (max-width: 767.98px) {
        .agents-desktop-table {
            display: none !important;
        }
        .agents-mobile-cards {
            display: block;
            padding: 14px;
        }
        .admin-agents-page .agents-page-title {
            font-size: 1.35rem;
            line-height: 1.3;
        }
        .admin-agents-page .agents-page-title i {
            font-size: 1.1rem;
        }
        .agents-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
        }
        .agents-header-actions .btn {
            padding: 0.55rem 0.5rem;
            font-size: 0.85rem;
            text-align: center;
        }
        .agents-filter-card {
            padding: 14px !important;
        }
        .agents-filter-card .btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.8rem;
        }
        #bulkActionBar {
            border-radius: 16px !important;
            flex-wrap: wrap;
        }
        .agents-list-card .pagination {
            flex-wrap: wrap;
            gap: 4px;
        }
    }
</style>
@endsection
