<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgentIssueController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        $issues = Ticket::where(function ($q) use ($user, $agent) {
            $q->where('user_email', $user->email);
            if ($agent) {
                $q->orWhere('agent_id', $agent->id);
            }
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(15);

        return view('agent.issues.index', compact('issues', 'agent'));
    }

    public function create()
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        return view('agent.issues.create', compact('agent'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        $validated = $request->validate([
            'category' => ['required', 'string', 'in:terminal_hardware,nin_verification,nin_modification,wallet_funding,account_access,other'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'machine_imei' => ['nullable', 'string', 'max:100'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('agent_issues', 'public');
        }

        $ticket = Ticket::create([
            'user_email' => $user->email,
            'agent_id' => $agent?->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'machine_imei' => $validated['machine_imei'] ?? $agent?->machine_imei,
            'priority' => $validated['priority'],
            'status' => 'open',
            'attachment_path' => $attachmentPath,
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'message' => $validated['message'],
            'attachment_path' => $attachmentPath,
        ]);

        return redirect()->route('agent.issues.show', $ticket->id)->with('success', 'Your terminal issue has been submitted to support team for resolution.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        $ticket = Ticket::with('replies')
            ->where('id', $id)
            ->where(function ($q) use ($user, $agent) {
                $q->where('user_email', $user->email);
                if ($agent) {
                    $q->orWhere('agent_id', $agent->id);
                }
            })
            ->firstOrFail();

        return view('agent.issues.show', compact('ticket', 'agent'));
    }

    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $agent = $user->enrollmentAgent;

        $ticket = Ticket::where('id', $id)
            ->where(function ($q) use ($user, $agent) {
                $q->where('user_email', $user->email);
                if ($agent) {
                    $q->orWhere('agent_id', $agent->id);
                }
            })
            ->firstOrFail();

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This issue resolution ticket is closed.');
        }

        $request->validate([
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('agent_issues', 'public');
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'message' => $request->input('message'),
            'attachment_path' => $attachmentPath,
        ]);

        $ticket->update(['status' => 'open']);

        return back()->with('success', 'Your reply and screenshot have been posted.');
    }
}
