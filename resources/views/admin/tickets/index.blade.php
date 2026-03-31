@extends('layouts.nexus')

@section('title', 'Manage Support Tickets | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Support Center</h1>
            <p class="text-muted">Manage and respond to user support inquiries.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="align-middle font-weight-bold text-white">#TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="align-middle">
                                <div>{{ $ticket->user->fullname ?? 'Unknown' }}</div>
                                <div class="small text-muted">{{ $ticket->user_email }}</div>
                            </td>
                            <td class="align-middle">{{ Str::limit($ticket->subject, 40) }}</td>
                            <td class="align-middle">
                                @if($ticket->status == 'open')
                                    <span class="badge badge-warning">Awaiting Reply</span>
                                @elseif($ticket->status == 'answered')
                                    <span class="badge badge-primary">Answered</span>
                                @else
                                    <span class="badge badge-secondary">Closed</span>
                                @endif
                            </td>
                            <td class="align-middle text-muted small">{{ $ticket->updated_at->diffForHumans() }}</td>
                            <td class="align-middle text-right pr-4">
                                <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary">View / Reply</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-ticket fa-3x mb-3"></i>
                                <h5>No support tickets found</h5>
                                <p class="mb-0">There are no support tickets in the system yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
