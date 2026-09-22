@extends('layouts.nexus')

@section('title', 'NIN Agent Issues Management | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-headset text-primary me-2"></i>NIN Agent Reported Issues & Helpdesk</h3>
            <p class="text-white-50 mb-0">Review agent terminal faults, error screenshot proofs, and provide resolution support.</p>
        </div>
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Agents Directory</a>
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
                <a href="{{ route('admin.agents.issues.index') }}" class="btn btn-sm rounded-pill {{ !$status ? 'btn-primary' : 'btn-outline-light' }}">
                    All Issues ({{ $counts['total'] }})
                </a>
                <a href="{{ route('admin.agents.issues.index', ['status' => 'open']) }}" class="btn btn-sm rounded-pill {{ $status === 'open' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                    Open ({{ $counts['open'] }})
                </a>
                <a href="{{ route('admin.agents.issues.index', ['status' => 'in_progress']) }}" class="btn btn-sm rounded-pill {{ $status === 'in_progress' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' }}">
                    In Progress ({{ $counts['in_progress'] }})
                </a>
                <a href="{{ route('admin.agents.issues.index', ['status' => 'resolved']) }}" class="btn btn-sm rounded-pill {{ $status === 'resolved' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                    Resolved ({{ $counts['resolved'] }})
                </a>
                <a href="{{ route('admin.agents.issues.index', ['status' => 'closed']) }}" class="btn btn-sm rounded-pill {{ $status === 'closed' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' }}">
                    Closed ({{ $counts['closed'] }})
                </a>
            </div>

            <form action="{{ route('admin.agents.issues.index') }}" method="GET" class="d-flex gap-2">
                @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-pill" placeholder="Search subject, IMEI, email...">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Search</button>
            </form>
        </div>
    </div>

    <!-- Agent Issues Table -->
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th>Ticket ID</th>
                        <th>Agent / Terminal</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date Reported</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                        <tr>
                            <td><span class="font-monospace text-info">#TK-{{ str_pad($issue->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                            <td>
                                <strong class="text-white">{{ $issue->agent->full_name ?? $issue->user->fullname ?? $issue->user_email }}</strong>
                                <br><code class="text-warning small">IMEI: {{ $issue->machine_imei ?? $issue->agent->machine_imei ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase small">{{ str_replace('_', ' ', $issue->category) }}</span>
                            </td>
                            <td>
                                <strong class="text-white">{{ $issue->subject }}</strong>
                                @if($issue->attachment_path)
                                    <i class="fa-solid fa-paperclip text-warning ms-1" title="Screenshot Attached"></i>
                                @endif
                            </td>
                            <td>
                                @if($issue->priority === 'urgent')
                                    <span class="badge bg-danger text-uppercase">Urgent</span>
                                @elseif($issue->priority === 'high')
                                    <span class="badge bg-warning text-dark text-uppercase">High</span>
                                @elseif($issue->priority === 'medium')
                                    <span class="badge bg-info text-dark text-uppercase">Medium</span>
                                @else
                                    <span class="badge bg-secondary text-uppercase">Low</span>
                                @endif
                            </td>
                            <td>
                                @if($issue->status === 'open')
                                    <span class="badge bg-primary px-2 py-1 rounded-pill">Open</span>
                                @elseif($issue->status === 'in_progress')
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">In Progress</span>
                                @elseif($issue->status === 'resolved')
                                    <span class="badge bg-success px-2 py-1 rounded-pill">Resolved</span>
                                @elseif($issue->status === 'closed')
                                    <span class="badge bg-secondary px-2 py-1 rounded-pill">Closed</span>
                                @endif
                            </td>
                            <td class="text-white-50 small">{{ $issue->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.agents.issues.show', $issue->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">Resolve Ticket</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-white-50 py-4">No agent issue tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $issues->links() }}
        </div>
    </div>
</div>
@endsection
