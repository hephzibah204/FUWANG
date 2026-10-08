@extends('layouts.nexus')

@section('title', 'Agency Network Command Center | Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-pill fw-semibold text-xs border border-emerald-500/30">
                    <i class="fa-solid fa-satellite-dish me-1"></i> Live Network Operations
                </span>
                <span class="text-white-50 text-xs">Updated in real-time</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-id-card-clip text-emerald-400 me-2"></i>Agency Network Command Center</h3>
            <p class="text-white-50 mb-0">Centralized governance, real-time metrics, terminal monitoring, and mass broadcast dispatch for NIN Enrollment Agents.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.agents.notifications.index') }}" class="btn btn-primary rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-bullhorn me-2"></i>Send Broadcast
            </a>
            <a href="{{ route('admin.agents.roster.index') }}" class="btn btn-outline-warning rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-clipboard-user me-2"></i>Master Roster
            </a>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-users me-2"></i>Directory
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.3) !important;">
            <div><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Operational KPI Matrix -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Agents -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100 position-relative overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Agency Force</span>
                    <a href="{{ route('admin.agents.index') }}" class="rounded-3 p-2 d-flex align-items-center justify-content-center text-decoration-none" style="background: rgba(16, 185, 129, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-users-gear text-emerald-400"></i>
                    </a>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <a href="{{ route('admin.agents.index') }}" class="text-white fw-bold fs-2 text-decoration-none hover-primary mb-0">{{ number_format($totalAgents) }}</a>
                    <span class="text-xs text-white-50">Registered</span>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-auto pt-2 border-top border-secondary border-opacity-25 text-xs">
                    <a href="{{ route('admin.agents.index', ['status' => 'approved']) }}" class="text-success text-decoration-none"><i class="fa-solid fa-check-circle me-1"></i>{{ $approvedAgents }} Active</a>
                    <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}" class="text-warning text-decoration-none"><i class="fa-solid fa-clock me-1"></i>{{ $pendingAgents }} Pending</a>
                    <a href="{{ route('admin.agents.index', ['status' => 'suspended']) }}" class="text-danger text-decoration-none"><i class="fa-solid fa-ban me-1"></i>{{ $suspendedAgents + $rejectedAgents }} Inactive</a>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Enrollments -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100 position-relative overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Citizen Enrollments</span>
                    <a href="{{ route('admin.agents.leaderboard') }}" class="rounded-3 p-2 d-flex align-items-center justify-content-center text-decoration-none" style="background: rgba(59, 130, 246, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-fingerprint text-primary"></i>
                    </a>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <a href="{{ route('admin.agents.leaderboard') }}" class="text-white fw-bold fs-2 text-decoration-none hover-primary mb-0">{{ number_format($totalEnrollments) }}</a>
                    <span class="text-xs text-success fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i>Cumulative</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top border-secondary border-opacity-25 text-xs">
                    <span class="text-white-50">Current Month:</span>
                    <span class="text-info fw-bold">{{ number_format($monthlyEnrollments) }} captures</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Deployed Terminals & Hardware -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100 position-relative overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Enrolled Terminals</span>
                    <a href="{{ route('admin.agents.index') }}" class="rounded-3 p-2 d-flex align-items-center justify-content-center text-decoration-none" style="background: rgba(245, 158, 11, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-laptop-code text-warning"></i>
                    </a>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <a href="{{ route('admin.agents.index') }}" class="text-white fw-bold fs-2 text-decoration-none hover-primary mb-0">{{ number_format($totalTerminals) }}</a>
                    <span class="text-xs text-white-50">IMEI Bound</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top border-secondary border-opacity-25 text-xs">
                    <span class="text-white-50">Terminal Coverage:</span>
                    <span class="text-warning fw-bold">{{ $totalAgents > 0 ? round(($totalTerminals / $totalAgents) * 100) : 0 }}% of agents</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Open Issues & Tickets -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 rounded-4 p-4 h-100 position-relative overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white-50 text-xs text-uppercase fw-semibold tracking-wider">Support & Issues</span>
                    <a href="{{ route('admin.agents.issues.index') }}" class="rounded-3 p-2 d-flex align-items-center justify-content-center text-decoration-none" style="background: rgba(239, 68, 68, 0.15); width: 38px; height: 38px;">
                        <i class="fa-solid fa-headset text-danger"></i>
                    </a>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <a href="{{ route('admin.agents.issues.index') }}" class="text-white fw-bold fs-2 text-decoration-none hover-primary mb-0">{{ number_format($openIssuesCount) }}</a>
                    <span class="badge bg-danger rounded-pill text-2xs">Pending Action</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top border-secondary border-opacity-25 text-xs">
                    <span class="text-white-50">In Resolution:</span>
                    <span class="text-info fw-bold">{{ $inProgressIssuesCount }} tickets</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-Approved Roster Quick Status Banner -->
    <div class="card border-0 rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.9) 100%); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
        <div class="row align-items-center g-3">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background: rgba(234, 179, 8, 0.15); width: 50px; height: 50px;">
                        <i class="fa-solid fa-clipboard-check text-warning fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-white fw-bold mb-1">Pre-Approved Master Roster Status</h6>
                        <p class="text-white-50 text-xs mb-0">Authorized existing enrollment agents eligible for instant profile claiming.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="d-flex justify-content-between text-xs mb-1">
                    <span class="text-white-50">Roster Claimed: <strong class="text-emerald-400">{{ $rosterClaimed }}</strong> of <strong>{{ $rosterTotal }}</strong> agents</span>
                    <span class="text-emerald-400 fw-bold">{{ $rosterTotal > 0 ? round(($rosterClaimed / $rosterTotal) * 100) : 0 }}% Claimed</span>
                </div>
                <div class="progress rounded-pill" style="height: 8px; background: rgba(255,255,255,0.1);">
                    <div class="progress-bar bg-emerald-500 rounded-pill" role="progressbar" style="width: {{ $rosterTotal > 0 ? ($rosterClaimed / $rosterTotal) * 100 : 0 }}%"></div>
                </div>
                <div class="d-flex gap-3 text-2xs text-white-50 mt-1">
                    <span><i class="fa-solid fa-lock-open text-warning me-1"></i>Unclaimed: {{ $rosterUnclaimed }}</span>
                    <span><i class="fa-solid fa-circle-check text-success me-1"></i>Claimed & Bound: {{ $rosterClaimed }}</span>
                </div>
            </div>
            <div class="col-lg-3 text-lg-end">
                <a href="{{ route('admin.agents.roster.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-2 fw-semibold">
                    <i class="fa-solid fa-sliders me-1"></i>Manage Roster
                </a>
                <a href="{{ route('admin.agents.upload_preapproved') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-file-arrow-up me-1"></i>Import File
                </a>
            </div>
        </div>
    </div>

    <!-- Main Grid: Recent Registrations & Active MVA vs Hardware Tickets & Top Performers -->
    <div class="row g-4">
        <!-- Left Column: Recent Registrations -->
        <div class="col-xl-7">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="text-white fw-bold mb-1"><i class="fa-solid fa-user-plus text-primary me-2"></i>Recent Agent Registrations</h5>
                        <p class="text-white-50 text-xs mb-0">Latest applications requiring review, approval, or hardware assignment.</p>
                    </div>
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 text-xs">View All ({{ $totalAgents }})</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                        <thead>
                            <tr class="text-white-50 text-xs text-uppercase border-bottom border-secondary border-opacity-25">
                                <th>Agent / User</th>
                                <th>NIN & State</th>
                                <th>Machine / IMEI</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAgents as $agent)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $agent->full_name }}</div>
                                        <div class="text-xs text-white-50">{{ $agent->phone_number }}</div>
                                        @if($agent->is_fast_tracked)
                                             <span class="badge bg-warning text-dark text-2xs mt-1"><i class="fa-solid fa-bolt me-1"></i>Existing Agent</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-xs font-monospace text-info">{{ $agent->nin }}</div>
                                        <small class="text-white-50">{{ $agent->state ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($agent->machine_imei)
                                            <code class="text-warning text-2xs">{{ $agent->machine_imei }}</code>
                                        @else
                                            <span class="badge bg-secondary text-2xs">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($agent->isApproved())
                                            <span class="badge bg-success rounded-pill text-2xs">Approved</span>
                                        @elseif($agent->isPending())
                                            <span class="badge bg-warning text-dark rounded-pill text-2xs">Pending</span>
                                        @elseif($agent->isRejected())
                                            <span class="badge bg-danger rounded-pill text-2xs">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill text-2xs">{{ ucfirst($agent->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-white-50 text-2xs d-block" title="{{ $agent->created_at?->format('d M Y, h:i A') }}">
                                            <i class="fa-regular fa-clock me-1 text-primary"></i>{{ $agent->created_at ? $agent->created_at->diffForHumans() : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-outline-info rounded-pill px-2.5" title="View Dossier">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-outline-light rounded-pill px-2.5 ms-1" title="Edit Hardware & Profile">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-white-50 py-4">No enrollment agents registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: MVA Spotlight & Open Tickets -->
        <div class="col-xl-5">
            <!-- MVA of the Month Spotlight -->
            <div class="card border-0 rounded-4 p-4 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.12) 0%, rgba(20, 24, 39, 0.8) 100%); border: 1px solid rgba(234, 179, 8, 0.3) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-xs">
                        <i class="fa-solid fa-crown me-1"></i> MVA OF THE MONTH
                    </span>
                    <a href="{{ route('admin.agents.leaderboard') }}" class="text-warning text-xs text-decoration-none fw-semibold">
                        Edit Leaderboard <i class="fa-solid fa-chevron-right ms-1"></i>
                    </a>
                </div>

                @if($mva)
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle overflow-hidden bg-dark border border-warning d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; min-width: 58px;">
                            @if($mva->picture_path)
                                <img src="{{ asset('storage/' . $mva->picture_path) }}" alt="{{ $mva->full_name }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <i class="fa-solid fa-user-tie text-warning fa-xl"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="text-white fw-bold mb-0 text-truncate">{{ $mva->full_name }}</h6>
                            <div class="text-xs text-white-50">{{ $mva->company_agent_code ?? 'Agent #' . $mva->id }} &bull; {{ $mva->state ?? 'National' }}</div>
                            <div class="d-flex gap-3 mt-2 text-xs">
                                <div><strong class="text-warning">{{ number_format($mva->monthly_enrollments) }}</strong> <span class="text-white-50">this month</span></div>
                                <div><strong class="text-white">{{ number_format($mva->total_enrollments) }}</strong> <span class="text-white-50">all-time</span></div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fa-solid fa-trophy text-warning opacity-50 fa-2x mb-2"></i>
                        <p class="text-white-50 text-xs mb-2">No agent is currently crowned as Most Valuable Agent.</p>
                        <a href="{{ route('admin.agents.leaderboard') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 text-xs fw-semibold">
                            Select MVA of the Month
                        </a>
                    </div>
                @endif
            </div>

            <!-- Recent Agent Hardware & Support Issues -->
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="text-white fw-bold mb-0"><i class="fa-solid fa-headset text-danger me-2"></i>Agent Hardware & Inquiries</h6>
                        <small class="text-white-50 text-2xs">Terminal defects, enrollment glitches, license queries</small>
                    </div>
                    <a href="{{ route('admin.agents.issues.index') }}" class="text-xs text-info text-decoration-none">
                        All Tickets <i class="fa-solid fa-chevron-right ms-1"></i>
                    </a>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($recentIssues as $issue)
                        <a href="{{ route('admin.agents.issues.show', $issue->id) }}" class="list-group-item list-group-item-action bg-transparent border-secondary border-opacity-25 px-0 py-2.5 text-white">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-semibold text-xs text-truncate pe-2">{{ $issue->subject }}</span>
                                @if($issue->status === 'open')
                                    <span class="badge bg-danger rounded-pill text-2xs">Open</span>
                                @elseif($issue->status === 'in_progress')
                                    <span class="badge bg-warning text-dark rounded-pill text-2xs">In Progress</span>
                                @else
                                    <span class="badge bg-success rounded-pill text-2xs">Resolved</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between text-2xs text-white-50">
                                <span>Agent: {{ $issue->agent->full_name ?? ($issue->user->name ?? 'Unknown') }}</span>
                                <span>{{ $issue->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-white-50 py-3 text-xs">
                            <i class="fa-solid fa-shield-heart text-success mb-2 fa-lg d-block"></i>
                            No open hardware or agent support tickets. Everything is running smoothly!
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Geographic Distribution Card -->
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="text-white fw-bold mb-0"><i class="fa-solid fa-map-location-dot text-emerald-400 me-2"></i>Network State Coverage</h6>
                        <small class="text-white-50 text-2xs">Regional footprint of active enrollment stations</small>
                    </div>
                    <span class="badge bg-emerald-500/20 text-emerald-400 rounded-pill text-2xs border border-emerald-500/30">{{ count($stateDistribution) }} Regions</span>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse($stateDistribution as $dist)
                        @php $pct = $totalAgents > 0 ? round(($dist->count / $totalAgents) * 100) : 0; @endphp
                        <div>
                            <div class="d-flex justify-content-between text-xs mb-1">
                                <span class="text-white fw-semibold">{{ $dist->state }}</span>
                                <span class="text-white-50">{{ $dist->count }} agents ({{ $pct }}%)</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 6px; background: rgba(255,255,255,0.08);">
                                <div class="progress-bar bg-emerald-500 rounded-pill" role="progressbar" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-white-50 text-xs mb-0 text-center py-2">No regional distribution data available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
