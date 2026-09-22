@extends('layouts.nexus')

@section('title', 'Manage NIN Enrollment Agents | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-users-gear text-primary me-2"></i>NIN Enrollment Agents Management</h3>
            <p class="text-white-50 mb-0">Review agent applications, verify credentials, manage approvals, and track performance.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.agents.upload_preapproved') }}" class="btn btn-outline-warning rounded-pill fw-bold">
                <i class="fa-solid fa-file-excel me-2"></i>Upload Excel Roster
            </a>
            <a href="{{ route('admin.agents.leaderboard') }}" class="btn btn-warning rounded-pill fw-bold text-dark">
                <i class="fa-solid fa-trophy me-2"></i>Leaderboard & MVA
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Status Filters & Search Bar -->
    <div class="card border-0 rounded-4 p-3 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.agents.index') }}" class="btn btn-sm rounded-pill {{ !$status ? 'btn-primary' : 'btn-outline-light' }}">
                    All Agents ({{ $counts['total'] }})
                </a>
                <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}" class="btn btn-sm rounded-pill {{ $status === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' }}">
                    Pending ({{ $counts['pending'] }})
                </a>
                <a href="{{ route('admin.agents.index', ['status' => 'approved']) }}" class="btn btn-sm rounded-pill {{ $status === 'approved' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                    Approved ({{ $counts['approved'] }})
                </a>
                <a href="{{ route('admin.agents.index', ['status' => 'rejected']) }}" class="btn btn-sm rounded-pill {{ $status === 'rejected' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                    Rejected ({{ $counts['rejected'] }})
                </a>
                <a href="{{ route('admin.agents.index', ['status' => 'suspended']) }}" class="btn btn-sm rounded-pill {{ $status === 'suspended' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' }}">
                    Suspended ({{ $counts['suspended'] }})
                </a>
            </div>

            <form action="{{ route('admin.agents.index') }}" method="GET" class="d-flex gap-2">
                @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-pill" placeholder="Search name, phone, NIN, IMEI...">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Search</button>
            </form>
        </div>
    </div>

    <!-- Agent Applications Table -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th>#</th>
                        <th>Agent Name</th>
                        <th>Phone / User</th>
                        <th>NIN / BVN</th>
                        <th>Machine IMEI</th>
                        <th>Status</th>
                        <th>Enrollments</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        <tr>
                            <td>{{ $agent->id }}</td>
                            <td>
                                <strong class="text-white">{{ $agent->full_name }}</strong>
                                @if($agent->is_fast_tracked)
                                    <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-bolt me-1"></i>Fast-Tracked Existing Agent</span>
                                @endif
                                @if($agent->is_mva_of_month)
                                    <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-star"></i> MVA</span>
                                @endif
                                <br>
                                @if($agent->company_agent_code)
                                    <small class="badge bg-secondary font-monospace">Code: {{ $agent->company_agent_code }}</small>
                                @endif
                                <small class="text-white-50">{{ $agent->meta['station_name'] ?? 'Primary Terminal' }}</small>
                            </td>
                            <td>
                                <span class="text-white">{{ $agent->phone_number }}</span>
                                <br><small class="text-white-50">{{ $agent->user->email ?? 'No User Email' }}</small>
                            </td>
                            <td>
                                <span class="text-info font-monospace">NIN: {{ $agent->nin }}</span>
                                @if($agent->nin_verified)
                                    <i class="fa-solid fa-circle-check text-success ms-1" title="NIN Verified"></i>
                                @endif
                                <br><small class="text-white-50 font-monospace">BVN: {{ $agent->bvn }}</small>
                            </td>
                            <td><code class="text-warning">{{ $agent->machine_imei }}</code></td>
                            <td>
                                @if($agent->isApproved())
                                    <span class="badge bg-success px-2 py-1 rounded-pill">Approved</span>
                                @elseif($agent->isPending())
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">Pending</span>
                                @elseif($agent->isRejected())
                                    <span class="badge bg-danger px-2 py-1 rounded-pill">Rejected</span>
                                @elseif($agent->isSuspended())
                                    <span class="badge bg-secondary px-2 py-1 rounded-pill">Suspended</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-success">{{ number_format($agent->total_enrollments) }}</span>
                                <small class="text-white-50">({{ number_format($agent->monthly_enrollments) }}/mo)</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">View</a>

                                @if($agent->isPending())
                                    <form action="{{ route('admin.agents.approve', $agent->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Approve</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-white-50 py-4">No agent records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $agents->links() }}
        </div>
    </div>
</div>
@endsection
