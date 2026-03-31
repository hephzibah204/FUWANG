<!DOCTYPE html>
<html>
<head>
    <title>Support Ticket Received</title>
</head>
<body>
    <h2>Support Ticket Received</h2>
    <p>Dear {{ $ticket->user->first_name ?? 'Customer' }},</p>
    
    <p>Thank you for reaching out to our support team. This email confirms that we have received your ticket.</p>
    
    <p><strong>Ticket ID:</strong> #{{ $ticket->id }}</p>
    <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
    
    <p>Our support team will review your message and get back to you as soon as possible.</p>
    
    <p>Best regards,<br>{{ config('app.name') }} Support Team</p>
</body>
</html>