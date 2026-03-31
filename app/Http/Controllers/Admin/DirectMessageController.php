<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\DirectMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DirectMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $messages = DirectMessage::query()->latest()->paginate(30);
        return view('admin.direct_messages.index', compact('messages'));
    }

    public function create()
    {
        return view('admin.direct_messages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'audience_type' => ['required', 'in:all,emails,user_ids'],
            'audience_value' => ['nullable', 'string'],
            'channel_in_app' => ['nullable'],
            'channel_email' => ['nullable'],
        ]);

        $channels = [];
        if ($request->boolean('channel_in_app')) $channels[] = 'database';
        if ($request->boolean('channel_email')) $channels[] = 'mail';
        if (empty($channels)) $channels = ['database'];

        $audience = null;
        if ($request->audience_type !== 'all') {
            $list = preg_split('/[\s,;]+/', (string) $request->audience_value, -1, PREG_SPLIT_NO_EMPTY);
            $audience = array_values(array_unique($list ?: []));
        }

        $dm = DirectMessage::create([
            'admin_id' => Auth::guard('admin')->id(),
            'title' => $request->title,
            'message' => $request->message,
            'channels' => $channels,
            'audience_type' => $request->audience_type,
            'audience' => $audience,
            'status' => 'draft',
        ]);

        if ($request->has('send_now')) {
            return $this->send($dm);
        }

        return redirect()->route('admin.direct_messages.index')->with('success', 'Message created as draft.');
    }

    public function send(DirectMessage $directMessage)
    {
        if ($directMessage->status === 'sent') {
            return back()->with('error', 'Message already sent.');
        }

        $users = User::query();
        if ($directMessage->audience_type === 'emails') {
            $users->whereIn('email', $directMessage->audience ?? []);
        } elseif ($directMessage->audience_type === 'user_ids') {
            $ids = array_map('intval', $directMessage->audience ?? []);
            $users->whereIn('id', $ids);
        }

        $recipients = $users->get();
        $directMessage->update([
            'status' => 'sending',
            'recipient_count' => $recipients->count(),
        ]);

        Notification::send($recipients, new DirectMessageNotification($directMessage, $directMessage->channels ?? ['database']));

        $directMessage->update([
            'status' => 'sent',
            'sent_at' => now(),
            'delivered_count' => $recipients->count(),
        ]);

        return back()->with('success', 'Message sent.');
    }
}

