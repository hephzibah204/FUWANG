@extends('layouts.nexus')

@section('title', 'Ticket #' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.tickets') }}" class="btn btn-sm btn-link text-muted p-0 mb-2">
                        <i class="fa-solid fa-arrow-left"></i> Back to Tickets
                    </a>
                    <h1 class="h4 font-weight-bold mb-1">{{ $ticket->subject }}</h1>
                    <div class="text-muted small">
                        Ticket #TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }} • 
                        User: {{ $ticket->user->fullname ?? 'Unknown' }} ({{ $ticket->user_email }}) • 
                        @if($ticket->status == 'open')
                            <span class="text-warning">Awaiting Reply</span>
                        @elseif($ticket->status == 'answered')
                            <span class="text-primary">Answered</span>
                        @else
                            <span class="text-secondary">Closed</span>
                        @endif
                    </div>
                </div>

                @if($ticket->status != 'closed')
                    <form action="{{ route('admin.tickets.close', $ticket->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this ticket?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-lock mr-2"></i> Close Ticket
                        </button>
                    </form>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="ticket-thread mb-5">
                @foreach($ticket->replies as $reply)
                    <div class="admin-panel p-4 mb-3" style="{{ $reply->sender_type == 'admin' ? 'border: 1px solid var(--clr-primary) !important;' : '' }}">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">
                            <div class="font-weight-bold">
                                @if($reply->sender_type == 'admin')
                                    <i class="fa-solid fa-headset text-primary mr-2"></i> <span class="text-primary">Support Staff (You)</span>
                                @else
                                    <i class="fa-solid fa-user text-muted mr-2"></i> <span class="text-white">{{ $ticket->user->fullname ?? 'User' }}</span>
                                @endif
                            </div>
                            <div class="text-muted small">
                                {{ $reply->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        <div class="ticket-content" style="white-space: pre-wrap; line-height: 1.6;">{{ $reply->message }}</div>
                    </div>
                @endforeach
            </div>

            @if($ticket->status != 'closed')
                <div class="admin-panel p-4 mb-4">
                    <h5 class="mb-3 font-weight-bold"><i class="fa-solid fa-reply mr-2 text-muted"></i>Staff Reply</h5>
                    <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <textarea name="message" class="form-control" rows="5" placeholder="Type your response to the user here..." required></textarea>
                            <small class="text-muted mt-2 d-block">Sending this reply will notify the user and mark the ticket as "Answered".</small>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Send Response</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-secondary text-center py-4 bg-dark border-secondary">
                    <i class="fa-solid fa-lock text-muted mb-2 fa-2x"></i>
                    <h5 class="text-muted mb-0">This ticket is closed.</h5>
                    <p class="text-muted small mt-1">No further replies can be added to this thread.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
