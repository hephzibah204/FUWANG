<!DOCTYPE html>
<html>
<head>
    <title>New Support Ticket</title>
</head>
<body>
    <h2>New Support Ticket Created</h2>
    <p>A new support ticket has been submitted on the platform.</p>
    
    <p><strong>Ticket ID:</strong> #{{ $ticket->id }}</p>
    <p><strong>User:</strong> {{ $ticket->user->first_name ?? 'Guest' }} ({{ $ticket->email }})</p>
    <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
    <p><strong>Priority:</strong> {{ $ticket->priority }}</p>
    
    <hr>
    <h3>Message:</h3>
    <p>{!! nl2br(e($ticket->message)) !!}</p>
    <hr>
    
    <p>Please log in to the admin dashboard to reply to this ticket.</p>
</body>
</html>