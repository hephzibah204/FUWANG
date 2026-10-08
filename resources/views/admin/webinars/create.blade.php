@extends('layouts.nexus')

@section('title', 'Schedule New Webinar - Admin Dashboard')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('admin.webinars.index') }}" class="btn btn-outline-secondary text-white rounded-pill px-3 py-1.5 small text-decoration-none">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Webinars
        </a>
    </div>

    <div class="glass-card p-4 p-md-5 rounded-20 border border-white-10 max-w-4xl mx-auto">
        <h1 class="h3 text-white font-weight-bold mb-4">Schedule & Create Webinar</h1>

        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-12 p-3 text-sm mb-4">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.webinars.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label text-white small font-weight-semibold">Webinar Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Masterclass: Scaling Agency Banking with Fuwa.NG" value="{{ old('title') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white small font-weight-semibold">Category / Topic</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Agency, Fintech, Legal" value="{{ old('category', 'Agency') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Flyer Banner Image (PNG/JPG/WebP)</label>
                    <input type="file" name="flyer_image" class="form-control" accept="image/*">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Speaker Avatar / Photo (Optional)</label>
                    <input type="file" name="speaker_avatar" class="form-control" accept="image/*">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Speaker Name</label>
                    <input type="text" name="speaker_name" class="form-control" placeholder="e.g. Dr. Abiodun Gbadamosi" value="{{ old('speaker_name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Speaker Title / Role</label>
                    <input type="text" name="speaker_title" class="form-control" placeholder="e.g. Head of Growth, Fuwa.NG" value="{{ old('speaker_title') }}">
                </div>

                <div class="col-12">
                    <label class="form-label text-white small font-weight-semibold">Speaker Bio (Optional)</label>
                    <textarea name="speaker_bio" class="form-control" rows="2" placeholder="Brief background of the presenter...">{{ old('speaker_bio') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label text-white small font-weight-semibold">Full Description & Key Takeaways</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Detailed outline of what attendees will learn..." required>{{ old('description') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Scheduled Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small font-weight-semibold">Duration (Minutes)</label>
                    <input type="number" name="duration_minutes" class="form-control" min="15" value="{{ old('duration_minutes', 60) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small font-weight-semibold">Price (₦ - 0 for Free)</label>
                    <input type="number" step="0.01" name="price" class="form-control" min="0" value="{{ old('price', 0) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Meeting / Live Stream URL</label>
                    <input type="url" name="meeting_link" class="form-control" placeholder="https://zoom.us/j/123456789 or Google Meet" value="{{ old('meeting_link') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Recording / Replay Video URL</label>
                    <input type="url" name="recording_url" class="form-control" placeholder="https://youtube.com/watch?v=... or Vimeo" value="{{ old('recording_url') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Max Attendee Limit (Leave blank for unlimited)</label>
                    <input type="number" name="max_attendees" class="form-control" min="1" placeholder="e.g. 500" value="{{ old('max_attendees') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small font-weight-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Scheduled (Published)</option>
                        <option value="live" {{ old('status') === 'live' ? 'selected' : '' }}>Live Now</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-3 rounded-12 font-weight-bold">
                        Create & Publish Webinar <i class="fa-solid fa-check ms-1"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
