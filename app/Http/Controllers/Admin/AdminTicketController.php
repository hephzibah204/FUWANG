<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserTicketReplyMail;

class AdminTicketController extends Controller
{
    /**
     * Display a listing of all support tickets.
     */
    public function index()
    {
        $tickets = Ticket::with('user')
            ->orderByRaw("FIELD(status, 'open', 'answered', 'closed')")
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
            
        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Show the ticket and thread for the admin to review/reply.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['replies', 'user'])->findOrFail($id);
        
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Store admin reply to the ticket.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is already closed.');
        }

        $reply = TicketReply::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'admin',
            'message'     => $request->message,
        ]);

        // Mark ticket as answered
        $ticket->status = 'answered';
        $ticket->save();

        // Send Email to User
        if ($ticket->user && $ticket->user->email) {
            try {
                Mail::to($ticket->user->email)->send(new UserTicketReplyMail($ticket, $reply));
            } catch (\Exception $e) {
                \Log::error('Failed to send ticket reply email to user: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Reply sent to user successfully.');
    }

    /**
     * Close the specified ticket.
     */
    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->status = 'closed';
        $ticket->save();

        return back()->with('success', 'Ticket has been closed.');
    }
}
