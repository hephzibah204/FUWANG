<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserTicketCreatedMail;
use App\Mail\AdminNewTicketMail;
use App\Models\Admin;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets.
     */
    public function index()
    {
        $tickets = Ticket::where('user_email', Auth::user()->email)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
            
        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'user_email' => Auth::user()->email,
            'subject'    => $request->subject,
            'status'     => 'open',
        ]);

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'user',
            'message'     => $request->message,
        ]);

        // Send Email to User
        try {
            Mail::to(Auth::user()->email)->send(new UserTicketCreatedMail($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket creation email to user: ' . $e->getMessage());
        }

        // Send Email to Admins (assuming role 'super_admin' or just first admin for now, or all admins)
        try {
            $admins = Admin::all();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new AdminNewTicketMail($ticket));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket creation email to admin: ' . $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Your support ticket has been created successfully.');
    }

    /**
     * Display the specified ticket and its replies.
     */
    public function show($id)
    {
        $ticket = Ticket::with('replies')->where('id', $id)->where('user_email', Auth::user()->email)->firstOrFail();
        
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Store a reply to an existing ticket.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = Ticket::where('id', $id)->where('user_email', Auth::user()->email)->firstOrFail();

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is closed. You cannot reply to it. Please open a new ticket.');
        }

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'user',
            'message'     => $request->message,
        ]);

        // Update the ticket's updated_at timestamp so it floats to the top of lists
        $ticket->touch();

        return back()->with('success', 'Your reply has been added.');
    }
}
