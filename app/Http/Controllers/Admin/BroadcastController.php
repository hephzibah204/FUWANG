<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Broadcast;
use App\Models\User;
use App\Models\SystemSetting;
use App\Notifications\GeneralBroadcast; // We will need to create this notification
use App\Support\RichText;
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
        $vipThreshold = (float) SystemSetting::get('broadcast_vip_min_funding', 50000);

        return view('admin.broadcasts.create', compact('vipThreshold'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'target_audience' => 'required|in:all,active,inactive,vip,enrollment_agents',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $broadcast = Broadcast::create([
            'subject' => $request->subject,
            // Word-style formatting from the rich editor, allowlist-sanitised.
            'message' => RichText::sanitize($request->message),
            'target_audience' => $request->target_audience,
            'scheduled_at' => $request->scheduled_at,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
            // broadcasts.created_by is a foreign key to USERS, but the
            // author here is an admin: writing the admin id pointed the
            // column at an unrelated user and broke creation entirely
            // (FK failure) whenever no user shared the admin's id.
            'created_by' => null,
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
            case 'enrollment_agents':
                $query->whereHas('enrollmentAgent', function ($q) {
                    $q->where('status', 'approved');
                });
                break;
            case 'active':
                $query->where('updated_at', '>=', now()->subDays(30));
                break;
            case 'inactive':
                $query->where('updated_at', '<', now()->subDays(30));
                break;
            case 'vip':
                // VIP / High Value (fixed 2026-11-28: this case previously
                // applied NO filter, so VIP broadcasts went to everyone).
                // A user is VIP when they hold a paid agent license OR
                // their total successful wallet funding (fundings rows are
                // only written for completed credits) reaches the
                // threshold in the broadcast_vip_min_funding setting.
                // Integer binding matters: a float binds as text under
                // SQLite and SUM(amount) >= '50000' then never matches.
                $threshold = (int) round((float) SystemSetting::get('broadcast_vip_min_funding', 50000));
                $bigFunderEmails = DB::table('fundings')
                    ->select('email')
                    ->groupBy('email')
                    ->havingRaw('SUM(amount) >= ?', [$threshold])
                    ->pluck('email');
                $query->where(function ($q) use ($bigFunderEmails) {
                    $q->whereHas('enrollmentAgent', function ($a) {
                        $a->where('license_status', 'paid');
                    })->orWhereIn('email', $bigFunderEmails);
                });
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
