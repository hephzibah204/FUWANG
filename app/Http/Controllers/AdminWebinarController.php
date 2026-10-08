<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminWebinarController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Webinar::withCount('registrations')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $webinars = $query->paginate(15);

        return view('admin.webinars.index', compact('webinars', 'status'));
    }

    public function create()
    {
        return view('admin.webinars.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'speaker_name' => ['required', 'string', 'max:255'],
            'speaker_title' => ['nullable', 'string', 'max:255'],
            'speaker_bio' => ['nullable', 'string'],
            'speaker_avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'recording_url' => ['nullable', 'url', 'max:500'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,scheduled,upcoming,live,completed,ended,cancelled'],
            'flyer_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        if ($request->hasFile('flyer_image')) {
            $path = $request->file('flyer_image')->store('webinars/flyers', 'public');
            $validated['banner_image'] = '/storage/' . $path;
        }

        if ($request->hasFile('speaker_avatar')) {
            $path = $request->file('speaker_avatar')->store('webinars/speakers', 'public');
            $validated['speaker_avatar'] = '/storage/' . $path;
        }

        $validated['presenter_name'] = $validated['speaker_name'];
        $validated['presenter_title'] = $validated['speaker_title'] ?? null;
        $validated['presenter_avatar'] = $validated['speaker_avatar'] ?? null;
        $validated['join_url'] = $validated['meeting_link'] ?? null;
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);

        $webinar = Webinar::create($validated);

        return redirect()->route('admin.webinars.show', $webinar->id)
            ->with('success', 'Webinar created successfully with flyer image.');
    }

    public function show(Webinar $webinar)
    {
        $webinar->load('registrations');
        return view('admin.webinars.show', compact('webinar'));
    }

    public function edit(Webinar $webinar)
    {
        return view('admin.webinars.edit', compact('webinar'));
    }

    public function update(Request $request, Webinar $webinar)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'speaker_name' => ['required', 'string', 'max:255'],
            'speaker_title' => ['nullable', 'string', 'max:255'],
            'speaker_bio' => ['nullable', 'string'],
            'speaker_avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'recording_url' => ['nullable', 'url', 'max:500'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,scheduled,upcoming,live,completed,ended,cancelled'],
            'flyer_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        if ($request->hasFile('flyer_image')) {
            $path = $request->file('flyer_image')->store('webinars/flyers', 'public');
            $validated['banner_image'] = '/storage/' . $path;
        }

        if ($request->hasFile('speaker_avatar')) {
            $path = $request->file('speaker_avatar')->store('webinars/speakers', 'public');
            $validated['speaker_avatar'] = '/storage/' . $path;
        }

        $validated['presenter_name'] = $validated['speaker_name'];
        $validated['presenter_title'] = $validated['speaker_title'] ?? null;
        $validated['presenter_avatar'] = $validated['speaker_avatar'] ?? $webinar->presenter_avatar;
        $validated['join_url'] = $validated['meeting_link'] ?? $webinar->join_url;

        $webinar->update($validated);

        return redirect()->route('admin.webinars.show', $webinar->id)
            ->with('success', 'Webinar updated successfully.');
    }

    public function destroy(Webinar $webinar)
    {
        $webinar->delete();

        return redirect()->route('admin.webinars.index')
            ->with('success', 'Webinar deleted successfully.');
    }

    public function exportAttendees(Webinar $webinar)
    {
        $registrations = $webinar->registrations()->latest()->get();

        $csvFileName = 'webinar-attendees-' . $webinar->slug . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Pass Code', 'Full Name', 'Email', 'Phone', 'Organization', 'Status', 'Registered At']);

            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->access_code,
                    $reg->full_name,
                    $reg->email,
                    $reg->phone_number,
                    $reg->organization,
                    $reg->status,
                    $reg->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
