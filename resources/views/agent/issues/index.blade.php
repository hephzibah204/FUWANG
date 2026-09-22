@extends('layouts.app')

@section('title', 'Agent Issue Resolution Portal | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-headset text-primary me-2"></i>Agent Issue Resolution Hub</h3>
            <p class="text-white-50 mb-0">Report biometric machine faults, verification errors, upload screenshots, and get rapid resolution from administration.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('agent.issues.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="fa-solid fa-circle-plus me-2"></i>Report New Issue / Fault
            </a>
            <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-light rounded-pill btn-sm">Agency Dashboard</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 p-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary">
                        <th>Ticket ID</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Terminal IMEI</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                        <tr>
                            <td><span class="font-monospace text-info">#TK-{{ str_pad($issue->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                            <td>
                                <span class="badge bg-secondary text-uppercase small">{{ str_replace('_', ' ', $issue->category) }}</span>
                            </td>
                            <td>
                                <strong class="text-white">{{ $issue->subject }}</strong>
                                @if($issue->attachment_path)
                                    <i class="fa-solid fa-paperclip text-warning ms-1" title="Screenshot Attached"></i>
                                @endif
                            </td>
                            <td><code class="text-warning">{{ $issue->machine_imei ?? $agent?->machine_imei ?? 'N/A' }}</code></td>
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
                            <td class="text-white-50 small">{{ $issue->updated_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('agent.issues.show', $issue->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">View Thread</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-white-50 py-4">No reported terminal issues. If you experience hardware or verification faults, click "Report New Issue" above.</td>
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
