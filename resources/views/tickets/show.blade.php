@extends('layouts.nexus')

@section('title', 'Ticket #' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' | ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-link text-white-50 p-0 mb-2">
                        <i class="fa-solid fa-arrow-left"></i> Back to Tickets
                    </a>
                    <h1 class="h4 font-weight-bold mb-1">{{ $ticket->subject }}</h1>
                    <div class="text-white-50 small">
                        Ticket #TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }} • 
                        @if($ticket->status == 'open')
                            <span class="text-warning">Open</span>
                        @elseif($ticket->status == 'answered')
                            <span class="text-info">Answered</span>
                        @else
                            <span class="text-muted">Closed</span>
                        @endif
                        • Created {{ $ticket->created_at->format('M d, Y h:i A') }}
                    </div>
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

            <div class="ticket-thread mb-5">
                @foreach($ticket->replies as $reply)
                    <div class="panel-card p-4 mb-3 {{ $reply->sender_type == 'admin' ? 'border-primary' : '' }}" style="{{ $reply->sender_type == 'admin' ? 'border: 1px solid rgba(59, 130, 246, 0.3) !important; background: rgba(59, 130, 246, 0.05);' : '' }}">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">
                            <div class="font-weight-bold">
                                @if($reply->sender_type == 'admin')
                                    <i class="fa-solid fa-headset text-primary mr-2"></i> <span class="text-primary">Support Staff</span>
                                @else
                                    <i class="fa-solid fa-user text-white-50 mr-2"></i> You
                                @endif
                            </div>
                            <div class="text-white-50 small">
                                {{ $reply->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        <div class="ticket-content" style="white-space: pre-wrap; line-height: 1.6;">{{ $reply->message }}</div>
                    </div>
                @endforeach
            </div>

            @if($ticket->status != 'closed')
                <div class="panel-card p-4 mb-4" id="replyFormBox">
                    <h5 class="mb-3 font-weight-bold"><i class="fa-solid fa-reply mr-2 text-white-50"></i>Leave a Reply</h5>
                    <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <textarea name="message" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Submit Reply</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-secondary bg-dark border-secondary text-center py-4">
                    <i class="fa-solid fa-lock text-white-50 mb-2 fa-2x"></i>
                    <h5 class="text-white-50 mb-0">This ticket has been closed.</h5>
                    <p class="text-muted small mt-1">If you need further assistance, please open a new ticket.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
