<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PublicWebinarController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $query = Webinar::where('status', '!=', 'draft')->orderBy('scheduled_at', 'asc');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $upcoming = (clone $query)->where('scheduled_at', '>=', now())->get();
        $past = (clone $query)->where('scheduled_at', '<', now())->latest('scheduled_at')->get();

        $categories = Webinar::where('status', '!=', 'draft')->distinct()->pluck('category');

        return view('public.webinars.index', compact('upcoming', 'past', 'categories', 'category'));
    }

    public function show(string $slug)
    {
        $webinar = Webinar::where('slug', $slug)->firstOrFail();
        $isRegistered = false;
        $userReg = null;

        if (Auth::check()) {
            $userReg = WebinarRegistration::where('webinar_id', $webinar->id)
                ->where('user_id', Auth::id())
                ->first();
            $isRegistered = (bool) $userReg;
        }

        $related = Webinar::where('id', '!=', $webinar->id)
            ->where('status', '!=', 'draft')
            ->latest()
            ->take(3)
            ->get();

        return view('public.webinars.show', compact('webinar', 'isRegistered', 'userReg', 'related'));
    }

    public function register(Request $request, string $slug)
    {
        $webinar = Webinar::where('slug', $slug)->firstOrFail();

        if (!$webinar->isSeatsAvailable()) {
            return back()->with('error', 'Sorry, this webinar has reached maximum capacity.');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'organization' => ['nullable', 'string', 'max:255'],
        ]);

        $existing = WebinarRegistration::where('webinar_id', $webinar->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return redirect()->route('webinars.show', $webinar->slug)
                ->with('info', "You are already registered for this webinar! Your Pass Code is: {$existing->access_code}");
        }

        $registration = WebinarRegistration::create([
            'webinar_id' => $webinar->id,
            'user_id' => Auth::id(),
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'status' => 'confirmed',
            'amount_paid' => $webinar->price,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($registration->email)
                ->send(new \App\Mail\WebinarRegistrationConfirmation($registration));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send webinar registration email: ' . $e->getMessage());
        }

        return redirect()->route('webinars.show', $webinar->slug)
            ->with('success', "Registration Successful! Your Access Pass Code is {$registration->access_code}. A confirmation email has been sent.");
    }
}
