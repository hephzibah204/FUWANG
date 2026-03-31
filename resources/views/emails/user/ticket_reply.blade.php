<!DOCTYPE html>
<html>
<head>
    <title>New Reply to Your Support Ticket</title>
</head>
<body>
    <h2>New Reply to Your Ticket</h2>
    <p>Dear {{ $ticket->user->first_name ?? 'Customer' }},</p>
    
    <p>An admin has replied to your support ticket <strong>#{{ $ticket->id }} - {{ $ticket->subject }}</strong>.</p>
    
    <hr>
    <h3>Admin Reply:</h3>
    <p>{!! nl2br(e($reply->message)) !!}</p>
    <hr>
    
    <p>You can view the full conversation and reply by logging into your account dashboard.</p>
    
    <p>Best regards,<br>{{ config('app.name') }} Support Team</p>
</body>
</html>