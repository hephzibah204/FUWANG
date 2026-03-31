<?php

namespace App\Http\Controllers;

use App\Models\AbEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbEventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => ['required', 'string', 'max:80'],
            'page' => ['nullable', 'string', 'max:255'],
            'experiment' => ['nullable', 'string', 'max:80'],
            'variant' => ['nullable', 'string', 'max:20'],
            'session_id' => ['nullable', 'string', 'max:80'],
            'meta' => ['nullable', 'array'],
        ]);

        AbEvent::create([
            'user_id' => Auth::id(),
            'session_id' => $request->input('session_id'),
            'experiment' => $request->input('experiment'),
            'variant' => $request->input('variant'),
            'event_name' => $request->input('event_name'),
            'page' => $request->input('page') ?: $request->path(),
            'meta' => $request->input('meta'),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json(['status' => true]);
    }
}

