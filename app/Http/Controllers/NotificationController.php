<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function show($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return view('notifications.show', compact('notification'));
    }

    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'All marked as read.']);
        }
        return back()->with('success', 'All notifications marked as read.');
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Marked as read.']);
        }
        return back();
    }
}
