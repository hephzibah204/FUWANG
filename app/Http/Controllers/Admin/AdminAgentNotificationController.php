<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AgentBroadcastMail;
use App\Models\Broadcast;
use App\Models\EnrollmentAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminAgentNotificationController extends Controller
{
    public function index()
    {
        $broadcasts = Broadcast::whereIn('target_audience', ['enrollment_agents', 'all'])
            ->latest()
            ->paginate(15);

        $counts = [
            'total_agents' => EnrollmentAgent::count(),
            'approved_agents' => EnrollmentAgent::where('status', 'approved')->count(),
            'pending_agents' => EnrollmentAgent::where('status', 'pending')->count(),
        ];

        $states = EnrollmentAgent::whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        return view('admin.agents.notifications.index', compact('broadcasts', 'counts', 'states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'target_status' => ['required', 'string', 'in:all,approved,pending,suspended'],
            'target_state' => ['nullable', 'string', 'max:100'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $admin = Auth::guard('admin')->user();
        $targetStatus = $validated['target_status'];
        $targetState = $validated['target_state'] ?? 'all';
        $sendEmail = !empty($validated['send_email']);

        // Build target agents query
        $query = EnrollmentAgent::with('user');
        if ($targetStatus !== 'all') {
            $query->where('status', $targetStatus);
        }
        if (!empty($targetState) && $targetState !== 'all') {
            $query->where('state', $targetState);
        }

        $agents = $query->get();
        $emailCount = 0;

        if ($sendEmail) {
            foreach ($agents as $agent) {
                $recipientEmail = $agent->user?->email;
                if (empty($recipientEmail) && !empty($agent->meta['email'])) {
                    $recipientEmail = $agent->meta['email'];
                }

                if (!empty($recipientEmail) && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Mail::to($recipientEmail)->send(new AgentBroadcastMail(
                            $agent->full_name,
                            $validated['subject'],
                            $validated['message']
                        ));
                        $emailCount++;
                    } catch (\Throwable $e) {
                        Log::warning("Failed to dispatch broadcast email to agent {$agent->id} ({$recipientEmail}): " . $e->getMessage());
                    }
                }
            }
        }

        // Create In-App Broadcast
        Broadcast::create([
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'target_audience' => 'enrollment_agents',
            'status' => 'sent',
            'sent_at' => now(),
            'created_by' => $admin?->id,
            'meta' => [
                'target_status' => $targetStatus,
                'target_state' => $targetState,
                'send_email' => $sendEmail,
                'emails_delivered' => $emailCount,
                'agents_targeted' => $agents->count(),
            ],
        ]);

        $feedback = "Broadcast posted to Agent Dashboard successfully!";
        if ($sendEmail) {
            $feedback .= " Direct emails dispatched to {$emailCount} agent(s).";
        }

        return back()->with('success', $feedback);
    }

    public function destroy($id)
    {
        $broadcast = Broadcast::findOrFail($id);
        $broadcast->delete();

        return back()->with('success', 'Broadcast notice deleted successfully.');
    }
}
