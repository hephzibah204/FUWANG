@extends('layouts.nexus')

@section('title', 'Master Agent Roster Management | Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.agents.overview') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Command Center
                </a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-warning text-xs fw-semibold">Master Roster</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-clipboard-user text-warning me-2"></i>Master Agent Roster Management</h3>
            <p class="text-white-50 mb-0">Pre-approved agent registry for fast-track onboarding, automated claiming, and credential governance.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                <i class="fa-solid fa-user-plus me-2"></i>Add Single Agent
            </button>
            <a href="{{ route('admin.agents.upload_preapproved') }}" class="btn btn-outline-warning rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-file-excel me-2"></i>Import CSV/Excel
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
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 text-xs text-uppercase fw-semibold">Total Pre-Approved</span>
                        <h3 class="text-white fw-bold mb-0 mt-1">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="rounded-3 p-2.5 bg-primary/10 text-primary">
                        <i class="fa-solid fa-users fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 text-xs text-uppercase fw-semibold">Claimed Profiles</span>
                        <h3 class="text-success fw-bold mb-0 mt-1">{{ number_format($stats['claimed']) }}</h3>
                    </div>
                    <div class="rounded-3 p-2.5 bg-success/10 text-success">
                        <i class="fa-solid fa-circle-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 rounded-4 p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 text-xs text-uppercase fw-semibold">Unclaimed / Pending</span>
                        <h3 class="text-warning fw-bold mb-0 mt-1">{{ number_format($stats['unclaimed']) }}</h3>
                    </div>
                    <div class="rounded-3 p-2.5 bg-warning/10 text-warning">
                        <i class="fa-solid fa-lock-open fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 rounded-4 p-3 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.agents.roster.index') }}" class="btn btn-sm rounded-pill {{ $status === 'all' || !$status ? 'btn-primary' : 'btn-outline-light' }}">
                    All Records ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.agents.roster.index', ['status' => 'unclaimed']) }}" class="btn btn-sm rounded-pill {{ $status === 'unclaimed' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' }}">
                    Unclaimed ({{ $stats['unclaimed'] }})
                </a>
                <a href="{{ route('admin.agents.roster.index', ['status' => 'claimed']) }}" class="btn btn-sm rounded-pill {{ $status === 'claimed' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                    Claimed ({{ $stats['claimed'] }})
                </a>
            </div>

            <form action="{{ route('admin.agents.roster.index') }}" method="GET" class="d-flex gap-2">
                @if($status && $status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm rounded-pill" placeholder="Search Code, Name, Email, Phone...">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Filter</button>
                @if($search)
                    <a href="{{ route('admin.agents.roster.index', ['status' => $status]) }}" class="btn btn-outline-secondary btn-sm rounded-pill">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Roster Table -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 text-xs text-uppercase border-bottom border-secondary border-opacity-25">
                        <th>Agent Code</th>
                        <th>Full Name</th>
                        <th>Contact Details</th>
                        <th>Claim Status</th>
                        <th>Added Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roster as $item)
                        <tr>
                            <td>
                                <span class="badge bg-dark border border-secondary text-warning font-monospace text-xs px-2 py-1">
                                    {{ $item->agent_code }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-white">{{ $item->full_name }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-info">{{ $item->email }}</div>
                                <div class="text-2xs text-white-50">{{ $item->phone_number }}</div>
                            </td>
                            <td>
                                @if($item->is_claimed)
                                    <span class="badge bg-success rounded-pill text-2xs mb-1">
                                        <i class="fa-solid fa-circle-check me-1"></i>Claimed
                                    </span>
                                    @if($item->claimedByUser)
                                        <div class="text-2xs text-white-50">User: {{ $item->claimedByUser->email }}</div>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill text-2xs">
                                        <i class="fa-solid fa-lock-open me-1"></i>Available to Claim
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-2xs text-white-50">{{ $item->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-light rounded-pill px-2.5" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}" title="Edit Details">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    @if($item->is_claimed)
                                        <form action="{{ route('admin.agents.roster.unclaim', $item->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Unlock agent profile? The current account binding will be detached so it can be re-claimed.');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning rounded-pill px-2.5" title="Unclaim / Unlock Profile">
                                                <i class="fa-solid fa-unlock-keyhole"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.agents.roster.destroy', $item->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to remove this pre-approved agent?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-2.5" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Modal for this agent -->
                                <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 rounded-4" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1) !important;">
                                            <form action="{{ route('admin.agents.roster.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header border-secondary border-opacity-25 pb-3">
                                                    <h5 class="modal-title text-white fw-bold">Edit Pre-Approved Agent</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-4">
                                                    <div class="mb-3">
                                                        <label class="form-label text-white text-xs fw-semibold">Agent Code <span class="text-danger">*</span></label>
                                                        <input type="text" name="agent_code" value="{{ $item->agent_code }}" class="form-control bg-dark border-secondary text-white rounded-3 font-monospace" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white text-xs fw-semibold">Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="full_name" value="{{ $item->full_name }}" class="form-control bg-dark border-secondary text-white rounded-3" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white text-xs fw-semibold">Registered Email <span class="text-danger">*</span></label>
                                                        <input type="email" name="email" value="{{ $item->email }}" class="form-control bg-dark border-secondary text-white rounded-3" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white text-xs fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                                        <input type="text" name="phone_number" value="{{ $item->phone_number }}" class="form-control bg-dark border-secondary text-white rounded-3" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary border-opacity-25 pt-3">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-white-50 py-5">
                                <i class="fa-solid fa-users-slash fa-2x mb-3 text-secondary d-block"></i>
                                No master roster records found matching your query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $roster->links() }}
        </div>
    </div>
</div>

<!-- Add Single Agent Modal -->
<div class="modal fade" id="addAgentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1) !important;">
            <form action="{{ route('admin.agents.roster.store') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary border-opacity-25 pb-3">
                    <h5 class="modal-title text-white fw-bold"><i class="fa-solid fa-user-plus text-primary me-2"></i>Add Pre-Approved Agent</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-white-50 text-xs mb-3">Pre-registering an agent allows them to claim their profile using Email OTP verification during enrollment onboarding.</p>
                    
                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Agent Code <span class="text-danger">*</span></label>
                        <input type="text" name="agent_code" value="{{ old('agent_code') }}" class="form-control bg-dark border-secondary text-white rounded-3 font-monospace" placeholder="e.g. AGT-99214" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="e.g. Samuel Adekunle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Authorized Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="agent@domain.com" required>
                        <small class="text-white-50 text-2xs">The agent will receive OTP verification at this email address to claim their profile.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white text-xs fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="08012345678" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Add to Roster</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
