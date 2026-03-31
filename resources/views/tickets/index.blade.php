@extends('layouts.nexus')

@section('title', 'Support Tickets | ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Support Center</h1>
            <p class="text-white-50">View and manage your support tickets.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus mr-2"></i> Open New Ticket
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-dark text-success border-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-dark text-danger border-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="panel-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 nexus-table">
                <thead>
                    <tr>
                        <th class="border-top-0 border-bottom-0 text-white-50 font-weight-600 pl-4 py-3">Ticket ID</th>
                        <th class="border-top-0 border-bottom-0 text-white-50 font-weight-600 py-3">Subject</th>
                        <th class="border-top-0 border-bottom-0 text-white-50 font-weight-600 py-3">Status</th>
                        <th class="border-top-0 border-bottom-0 text-white-50 font-weight-600 py-3">Last Updated</th>
                        <th class="border-top-0 border-bottom-0 text-white-50 font-weight-600 pr-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="pl-4 align-middle font-weight-bold text-white">#TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="align-middle">{{ Str::limit($ticket->subject, 40) }}</td>
                            <td class="align-middle">
                                @if($ticket->status == 'open')
                                    <span class="badge badge-warning px-2 py-1" style="background: rgba(245, 158, 11, 0.2); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3);">Open</span>
                                @elseif($ticket->status == 'answered')
                                    <span class="badge badge-info px-2 py-1" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);">Answered</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1" style="background: rgba(156, 163, 175, 0.2); color: #d1d5db; border: 1px solid rgba(156, 163, 175, 0.3);">Closed</span>
                                @endif
                            </td>
                            <td class="align-middle text-white-50 small">{{ $ticket->updated_at->diffForHumans() }}</td>
                            <td class="pr-4 align-middle text-right">
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-light">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-white-50">
                                <i class="fa-solid fa-ticket fa-3x mb-3 text-muted"></i>
                                <h5>No support tickets found</h5>
                                <p class="mb-0">You haven't opened any support tickets yet.</p>
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
