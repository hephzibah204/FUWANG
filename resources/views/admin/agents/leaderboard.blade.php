@extends('layouts.nexus')

@section('title', 'Leaderboard & MVA Management | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-xs">
                    <i class="fa-solid fa-bolt me-1"></i> Automatic Ranking Engine
                </span>
                <span class="text-white-50 text-xs">Calculated by Total Enrollments</span>
            </div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-trophy text-warning me-2"></i>Agent Leaderboard & MVP Center</h3>
            <p class="text-white-50 mb-0">Agent rankings and Most Valuable Agent (MVP) are automatically calculated based on total enrollment captures recorded by administration.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.agents.leaderboard.publish') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning text-dark rounded-pill px-3 btn-sm fw-bold shadow-sm">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>Recalculate & Sync
                </button>
            </form>
            <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill btn-sm px-3">Back to Directory</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.3) !important;">
            <div><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Current Automated MVP Card -->
        <div class="col-lg-12">
            <div class="card border-0 rounded-4 p-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.18) 0%, rgba(15, 23, 42, 0.95) 100%); border: 1px solid rgba(234, 179, 8, 0.45) !important;">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle p-3 text-center d-flex align-items-center justify-content-center border border-warning" style="background: rgba(234, 179, 8, 0.25); width: 68px; height: 68px; min-width: 68px;">
                                <i class="fa-solid fa-crown text-warning fa-2x"></i>
                            </div>
                            <div>
                                <span class="badge bg-warning text-dark fw-extrabold px-3 py-1 rounded-pill text-xs mb-1">
                                    <i class="fa-solid fa-star me-1"></i>OFFICIAL NETWORK MVP
                                </span>
                                @if($currentMva)
                                    <h4 class="text-white fw-bold mb-1">{{ $currentMva->full_name }}</h4>
                                    <p class="text-white-50 small mb-2">
                                        <code class="text-warning small me-2">IMEI: {{ $currentMva->machine_imei ?: 'N/A' }}</code>
                                        <span class="text-white-50">{{ $currentMva->office_address ?? $currentMva->state }}</span>
                                    </p>
                                    <div class="d-flex gap-4 text-sm">
                                        <div>
                                            <span class="text-white-50 text-xs text-uppercase d-block">Total Enrollments</span>
                                            <strong class="text-warning fs-5">{{ number_format($currentMva->total_enrollments) }}</strong> <span class="text-white-50 text-xs">citizens</span>
                                        </div>
                                        <div>
                                            <span class="text-white-50 text-xs text-uppercase d-block">Monthly Volume</span>
                                            <strong class="text-emerald-400 fs-5">{{ number_format($currentMva->monthly_enrollments) }}</strong> <span class="text-white-50 text-xs">this month</span>
                                        </div>
                                    </div>
                                @else
                                    <h4 class="text-white fw-bold mb-1">No Active Agent Crowned</h4>
                                    <p class="text-white-50 small mb-0">Record enrollment counts for approved agents to automatically crown the network MVP.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 border-start border-secondary border-opacity-25 ps-lg-4">
                        <div class="small text-white-50 mb-2">
                            <i class="fa-solid fa-circle-info text-info me-1"></i>
                            The system crowns the agent with the highest recorded total enrollments automatically. You can also specify an override below:
                        </div>
                        <form action="{{ route('admin.agents.leaderboard.publish') }}" method="POST" class="d-flex flex-column gap-2">
                            @csrf
                            <select name="mva_agent_id" class="form-select form-select-sm bg-dark text-white border-secondary">
                                <option value="">-- Automatic (#1 Top Producer) --</option>
                                @foreach($approvedAgents as $ag)
                                    <option value="{{ $ag->id }}" {{ ($currentMva && $currentMva->id === $ag->id) ? 'selected' : '' }}>
                                        {{ $ag->full_name }} ({{ number_format($ag->total_enrollments) }} total captures)
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill w-100 fw-semibold">
                                <i class="fa-solid fa-check me-1"></i>Apply Recognition
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Standings Table -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-ranking-star text-warning me-2"></i>Official Enrollment Agent Rankings</h5>
                <small class="text-white-50">Sorted automatically by Total Successful Citizen Captures</small>
            </div>
            <span class="badge bg-emerald-500/20 text-emerald-400 px-3 py-1.5 rounded-pill text-xs border border-emerald-500/30">
                {{ count($approvedAgents) }} Ranked Agents
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary text-xs text-uppercase">
                        <th>Rank</th>
                        <th>Agent Profile</th>
                        <th>Terminal / Hardware</th>
                        <th class="text-end">Total Enrollments (Primary)</th>
                        <th class="text-end">Monthly Volume</th>
                        <th class="text-center">MVP Recognition</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedAgents as $index => $ag)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <span class="badge bg-warning text-dark rounded-circle px-2 py-1"><i class="fa-solid fa-crown"></i> 1</span>
                                @elseif($index === 1)
                                    <span class="badge bg-light text-dark rounded-circle px-2 py-1">2</span>
                                @elseif($index === 2)
                                    <span class="badge bg-secondary rounded-circle px-2 py-1">3</span>
                                @else
                                    <span class="text-white-50 ms-1 fw-bold">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-white">{{ $ag->full_name }}</strong>
                                <br><small class="text-white-50">{{ $ag->phone_number }} &bull; {{ $ag->state ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="text-white small">{{ \Illuminate\Support\Str::limit($ag->office_address, 30) }}</span>
                                <br><code class="text-warning text-2xs">IMEI: {{ $ag->machine_imei ?: 'Unset' }}</code>
                            </td>
                            <td class="text-end">
                                <span class="fs-5 fw-bold text-warning">{{ number_format($ag->total_enrollments) }}</span>
                                <span class="text-white-50 text-2xs d-block">all-time captures</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-emerald-400">{{ number_format($ag->monthly_enrollments) }}</span>
                                <span class="text-white-50 text-2xs d-block">this month</span>
                            </td>
                            <td class="text-center">
                                @if($ag->is_mva_of_month || $index === 0)
                                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                                        <i class="fa-solid fa-crown me-1"></i>MVP (Rank #1)
                                    </span>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.agents.show', $ag->id) }}" class="btn btn-xs btn-outline-info rounded-pill px-2.5">
                                    <i class="fa-solid fa-eye me-1"></i>Dossier
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-white-50 py-4">No approved agents available for ranking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
