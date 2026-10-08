@extends('layouts.nexus')

@section('title', 'Manage NIN Enrollment Agents | Admin')

@section('content')
<div class="container-fluid py-4">
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
            <h1 class="h3 text-white font-weight-bold mb-1">
                <i class="fa-solid fa-users-gear text-primary me-2"></i>NIN Enrollment Agents Directory
            </h1>
            <p class="text-muted small mb-0">Review agent applications, verify credentials, manage approvals, and track performance.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.agents.licenses.index') }}" class="btn btn-warning text-dark rounded-12 font-weight-bold px-3">
                <i class="fa-solid fa-certificate me-1.5"></i> Station Licenses
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
    <div class="glass-card p-4 rounded-20 border border-white-10 mb-4">
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
    <div class="glass-card rounded-20 overflow-hidden border border-white-10">
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

            <div class="table-responsive agent-table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle table-agents">
                    <thead class="table-light-5">
                        <tr class="text-muted text-xs text-uppercase tracking-wider">
                            <th class="ps-3 ps-md-4" style="width: 38px; min-width: 38px;">
                                <input type="checkbox" id="selectAllAgents" class="form-check-input bg-dark border-white-20" title="Select All On Page">
                            </th>
                            <th style="min-width: 180px;">Agent Info</th>
                            <th style="min-width: 160px;">Contact / Email</th>
                            <th style="min-width: 140px;">NIN & BVN Details</th>
                            <th style="min-width: 130px;">Machine IMEI</th>
                            <th style="min-width: 95px;">Status</th>
                            <th style="min-width: 85px;">License</th>
                            <th style="min-width: 105px;">Enrollments</th>
                            <th class="pe-3 pe-md-4 text-end table-action-col" style="min-width: 145px; width: 145px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td class="ps-3 ps-md-4 py-2.5">
                                    <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}" class="form-check-input agent-checkbox bg-dark border-white-20">
                                </td>
                                <td class="py-2.5">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="avatar-circle bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-xs" style="width: 34px; height: 34px; min-width: 34px; flex-shrink: 0;">
                                            {{ substr($agent->full_name, 0, 1) }}
                                        </div>
                                        <div style="min-width: 0;">
                                            <a href="{{ route('admin.agents.show', $agent->id) }}" class="text-white font-weight-bold text-decoration-none d-block hover-primary text-truncate" style="max-width: 165px;" title="{{ $agent->full_name }}">
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
                                    <span class="d-block text-muted text-2xs text-truncate" style="max-width: 160px;" title="{{ $agent->user->email ?? 'No User Email' }}">{{ $agent->user->email ?? 'No User Email' }}</span>
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
                                <td class="py-2.5 text-nowrap">
                                    @if($agent->machine_imei)
                                        <code class="text-warning text-2xs px-1.5 py-0.5 bg-dark rounded border border-white-5 font-monospace">{{ $agent->machine_imei }}</code>
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
                                <td class="pe-3 pe-md-4 py-2.5 text-end table-action-col">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-sm btn-outline-info text-info rounded-10 p-0" title="View Dossier" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-sm btn-outline-light rounded-10 p-0" title="Edit Profile & Hardware" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        @if($agent->isPending())
                                            <button type="submit" form="singleApprove{{ $agent->id }}" class="btn btn-sm btn-success rounded-10 p-0" title="Approve Agent" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        <button type="submit" form="singleDelete{{ $agent->id }}" class="btn btn-sm btn-outline-danger text-danger rounded-10 p-0" title="Delete Agent Profile Only" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
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

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkBar();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (!this.checked && selectAll) selectAll.checked = false;
            if (document.querySelectorAll('.agent-checkbox:checked').length === checkboxes.length && selectAll) {
                selectAll.checked = true;
            }
            updateBulkBar();
        });
    });
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
        background: rgba(255, 255, 255, 0.03);
    }
    .agent-table-responsive::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }
    .agent-table-responsive::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.6);
    }
    .table-agents {
        min-width: 980px;
        width: 100%;
    }
    .table-agents .table-action-col {
        position: sticky;
        right: 0;
        z-index: 5;
        background-color: #0b1120 !important;
        box-shadow: -10px 0 16px -4px rgba(0, 0, 0, 0.7);
        white-space: nowrap;
        min-width: 145px;
        width: 145px;
        text-align: right;
    }
    .table-agents thead th.table-action-col {
        background-color: #162033 !important;
        z-index: 6;
    }
    .table-agents tbody tr:hover .table-action-col {
        background-color: #1e293b !important;
    }
    .text-3xs {
        font-size: 0.68rem;
    }
    .rounded-10 {
        border-radius: 10px;
    }
</style>
@endsection
