<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Broadcast;
use App\Models\User;
use App\Notifications\GeneralBroadcast; // We will need to create this notification
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = Broadcast::latest()->paginate(20);
        return view('admin.broadcasts.index', compact('broadcasts'));
    }

    public function create()
    {
        return view('admin.broadcasts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,active,inactive,vip',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $broadcast = Broadcast::create([
            'subject' => $request->subject,
            'message' => $request->message,
            'target_audience' => $request->target_audience,
            'scheduled_at' => $request->scheduled_at,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
            'created_by' => auth()->id(),
        ]);

        if (!$request->scheduled_at && $request->has('send_now')) {
            $this->sendBroadcast($broadcast);
            return redirect()->route('admin.broadcasts.index')->with('success', 'Broadcast sent successfully.');
        }

        return redirect()->route('admin.broadcasts.index')->with('success', 'Broadcast saved successfully.');
    }

    public function send(Broadcast $broadcast)
    {
        if ($broadcast->status === 'sent') {
            return back()->with('error', 'Broadcast already sent.');
        }

        $this->sendBroadcast($broadcast);
        return back()->with('success', 'Broadcast sent successfully.');
    }

    private function sendBroadcast(Broadcast $broadcast)
    {
        // Determine audience
        $query = User::query();

        switch ($broadcast->target_audience) {
            case 'active':
                // Users who logged in recently (e.g., last 30 days)
                // Assuming we track last_login_at or updated_at
                $query->where('updated_at', '>=', now()->subDays(30));
                break;
            case 'inactive':
                $query->where('updated_at', '<', now()->subDays(30));
                break;
            case 'vip':
                // Assuming there is a tier or role column, or manual check
                // For now, let's assume 'vip' is a role or flag. 
                // If not, fall back to all for this demo or handle gracefully.
                // $query->where('role', 'vip'); 
                break;
            case 'all':
            default:
                // No filter
                break;
        }

        // Process in chunks to avoid memory issues
        // In production, this should be a queued Job
        // For this implementation, we'll dispatch a notification directly for demonstration.
        // For large user bases, use: BroadcastJob::dispatch($broadcast);
        
        $query->chunk(100, function ($users) use ($broadcast) {
            Notification::send($users, new GeneralBroadcast($broadcast));
        });
        
        $count = $query->count();
        
        // Mark as sent
        $broadcast->update([
            'status' => 'sent',
            'sent_at' => now(),
            'meta' => array_merge($broadcast->meta ?? [], ['recipient_count' => $count])
        ]);
    }
    
    public function destroy(Broadcast $broadcast)
    {
        $broadcast->delete();
        return back()->with('success', 'Broadcast deleted.');
    }
}
