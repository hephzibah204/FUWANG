@extends('layouts.nexus')

@section('title', 'Leaderboard & MVA Management | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-trophy text-warning me-2"></i>Agent Leaderboard & MVA Publishing</h3>
            <p class="text-white-50 mb-0">Publish top performing enrollment agents and award the Most Valuable Agent of the Month.</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Agent List</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Current MVA Award Card -->
        <div class="col-lg-12">
            <div class="card border-0 rounded-4 p-4" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.2), rgba(180, 83, 9, 0.3)); border: 1px solid rgba(234, 179, 8, 0.4) !important;">
                <h5 class="text-warning fw-bold mb-3"><i class="fa-solid fa-star me-2"></i>Publish Most Valuable Agent (MVA) of the Month</h5>

                <form action="{{ route('admin.agents.leaderboard.publish') }}" method="POST" class="row align-items-end g-3">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label text-white small fw-bold">Select Agent for MVA Recognition</label>
                        <select name="mva_agent_id" class="form-select">
                            <option value="">-- None Selected / Unset --</option>
                            @foreach($approvedAgents as $ag)
                                <option value="{{ $ag->id }}" {{ $ag->is_mva_of_month ? 'selected' : '' }}>
                                    {{ $ag->full_name }} — {{ number_format($ag->monthly_enrollments) }} enrollments this month (IMEI: {{ $ag->machine_imei }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill w-100 py-2">
                            <i class="fa-solid fa-award me-2"></i>Save & Publish Recognition
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Agent Standings Table -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-list-ol text-primary me-2"></i>Approved Enrollment Agent Rankings</h5>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th>Rank</th>
                        <th>Agent Name</th>
                        <th>Terminal / Location</th>
                        <th>Monthly Volume</th>
                        <th>Total Volume</th>
                        <th>MVA Status</th>
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
                                    <span class="text-white-50 ms-1">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-white">{{ $ag->full_name }}</strong>
                                <br><small class="text-white-50">{{ $ag->phone_number }}</small>
                            </td>
                            <td>
                                <span class="text-white small">{{ $ag->office_address }}</span>
                                <br><code class="text-warning small">IMEI: {{ $ag->machine_imei }}</code>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($ag->monthly_enrollments) }}</td>
                            <td class="fw-bold text-info">{{ number_format($ag->total_enrollments) }}</td>
                            <td>
                                @if($ag->is_mva_of_month)
                                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill"><i class="fa-solid fa-star me-1"></i>Active MVA</span>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-white-50 py-4">No approved agents available for ranking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
