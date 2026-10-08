@extends('layouts.nexus')

@section('title', 'Edit Webinar - Admin Dashboard')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('admin.webinars.show', $webinar->id) }}" class="btn btn-outline-secondary text-white rounded-pill px-3 py-1.5 small text-decoration-none">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Details
        </a>
    </div>

    <div class="glass-card p-4 p-md-5 rounded-20 border border-white-10 max-w-4xl mx-auto">
        <h1 class="h3 text-white font-weight-bold mb-4">Edit Webinar: {{ $webinar->title }}</h1>

        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-12 p-3 text-sm mb-4">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.webinars.update', $webinar->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label text-white small font-weight-semibold">Webinar Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $webinar->title) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white small font-weight-semibold">Category / Topic</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $webinar->category) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Flyer Banner Image (Leave empty to keep existing)</label>
                    <input type="file" name="flyer_image" class="form-control" accept="image/*">
                    @if($webinar->banner_image)
                        <span class="text-muted text-2xs mt-1 d-block">Current: {{ basename($webinar->banner_image) }}</span>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Speaker Photo (Leave empty to keep existing)</label>
                    <input type="file" name="speaker_avatar" class="form-control" accept="image/*">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Speaker Name</label>
                    <input type="text" name="speaker_name" class="form-control" value="{{ old('speaker_name', $webinar->speaker_name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Speaker Title / Role</label>
                    <input type="text" name="speaker_title" class="form-control" value="{{ old('speaker_title', $webinar->speaker_title) }}">
                </div>

                <div class="col-12">
                    <label class="form-label text-white small font-weight-semibold">Speaker Bio (Optional)</label>
                    <textarea name="speaker_bio" class="form-control" rows="2">{{ old('speaker_bio', $webinar->speaker_bio) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label text-white small font-weight-semibold">Full Description & Key Takeaways</label>
                    <textarea name="description" class="form-control" rows="5" required>{{ old('description', $webinar->description) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Scheduled Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', $webinar->scheduled_at->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small font-weight-semibold">Duration (Minutes)</label>
                    <input type="number" name="duration_minutes" class="form-control" min="15" value="{{ old('duration_minutes', $webinar->duration_minutes) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small font-weight-semibold">Price (₦)</label>
                    <input type="number" step="0.01" name="price" class="form-control" min="0" value="{{ old('price', $webinar->price) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Meeting / Live Stream URL</label>
                    <input type="url" name="meeting_link" class="form-control" value="{{ old('meeting_link', $webinar->meeting_link) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Recording / Replay Video URL</label>
                    <input type="url" name="recording_url" class="form-control" value="{{ old('recording_url', $webinar->recording_url) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Max Attendee Limit</label>
                    <input type="number" name="max_attendees" class="form-control" min="1" value="{{ old('max_attendees', $webinar->max_attendees) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="scheduled" {{ old('status', $webinar->status) === 'scheduled' ? 'selected' : '' }}>Scheduled (Published)</option>
                        <option value="live" {{ old('status', $webinar->status) === 'live' ? 'selected' : '' }}>Live Now</option>
                        <option value="draft" {{ old('status', $webinar->status) === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                        <option value="completed" {{ old('status', $webinar->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $webinar->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-3 rounded-12 font-weight-bold">
                        Save Changes <i class="fa-solid fa-save ms-1"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
