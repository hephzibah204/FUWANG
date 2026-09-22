<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class AdminAgentIssueController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $category = $request->query('category');
        $priority = $request->query('priority');

        $query = Ticket::with(['agent', 'user'])->whereNotNull('agent_id')->latest();

        if (in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('machine_imei', 'like', "%{$search}%");
            });
        }

        $issues = $query->paginate(20)->withQueryString();

        $counts = [
            'total' => Ticket::whereNotNull('agent_id')->count(),
            'open' => Ticket::whereNotNull('agent_id')->where('status', 'open')->count(),
            'in_progress' => Ticket::whereNotNull('agent_id')->where('status', 'in_progress')->count(),
            'resolved' => Ticket::whereNotNull('agent_id')->where('status', 'resolved')->count(),
            'closed' => Ticket::whereNotNull('agent_id')->where('status', 'closed')->count(),
        ];

        return view('admin.agents.issues.index', compact('issues', 'counts', 'status', 'category', 'priority'));
    }

    public function show($id)
    {
        $ticket = Ticket::with(['agent', 'user', 'replies'])->whereNotNull('agent_id')->findOrFail($id);

        return view('admin.agents.issues.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::whereNotNull('agent_id')->findOrFail($id);

        $request->validate([
            'message' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('agent_issues', 'public');
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'message' => $request->input('message'),
            'attachment_path' => $attachmentPath,
        ]);

        $newStatus = $request->input('status', 'in_progress');
        $ticket->update(['status' => $newStatus]);

        return back()->with('success', 'Admin resolution response posted successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket = Ticket::whereNotNull('agent_id')->findOrFail($id);
        $ticket->update(['status' => $request->input('status')]);

        return back()->with('success', "Issue status updated to {$request->input('status')}.");
    }
}
