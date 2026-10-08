@extends('layouts.nexus')

@section('title', 'Webinars Management - Admin Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 text-white font-weight-bold mb-1">Webinars & Masterclasses</h1>
            <p class="text-muted small mb-0">Create, publish, and manage webinar events and attendee registrations.</p>
        </div>
        <div>
            <a href="{{ route('admin.webinars.create') }}" class="btn btn-primary rounded-12 font-weight-bold px-4 py-2">
                <i class="fa-solid fa-plus me-1"></i> Schedule New Webinar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-12 p-3 text-sm mb-4">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Status Filters -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.webinars.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
            All Webinars
        </a>
        <a href="{{ route('admin.webinars.index', ['status' => 'scheduled']) }}" class="btn btn-sm {{ $status === 'scheduled' ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
            Scheduled
        </a>
        <a href="{{ route('admin.webinars.index', ['status' => 'live']) }}" class="btn btn-sm {{ $status === 'live' ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
            Live Now
        </a>
        <a href="{{ route('admin.webinars.index', ['status' => 'completed']) }}" class="btn btn-sm {{ $status === 'completed' ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
            Completed
        </a>
        <a href="{{ route('admin.webinars.index', ['status' => 'draft']) }}" class="btn btn-sm {{ $status === 'draft' ? 'btn-primary' : 'btn-outline-secondary text-white' }} rounded-pill px-3">
            Drafts
        </a>
    </div>

    <!-- Webinars Table -->
    <div class="glass-card rounded-20 overflow-hidden border border-white-10">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="table-light-5">
                    <tr>
                        <th class="ps-4">Title & Speaker</th>
                        <th>Category</th>
                        <th>Scheduled Date</th>
                        <th>Attendees</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webinars as $webinar)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    @if($webinar->speaker_avatar)
                                        <img src="{{ $webinar->speaker_avatar }}" class="rounded-circle" width="36" height="36" alt="{{ $webinar->speaker_name }}">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold text-xs" style="width: 36px; height: 36px;">
                                            {{ substr($webinar->speaker_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.webinars.show', $webinar->id) }}" class="text-white font-weight-semibold text-decoration-none d-block">
                                            {{ $webinar->title }}
                                        </a>
                                        <span class="text-muted text-2xs">by {{ $webinar->speaker_name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-white rounded-pill px-2.5 py-0.5 text-2xs">{{ ucfirst($webinar->category) }}</span>
                            </td>
                            <td>
                                <span class="d-block text-white text-xs font-weight-medium">{{ $webinar->scheduled_at->format('M d, Y') }}</span>
                                <span class="d-block text-muted text-2xs">{{ $webinar->scheduled_at->format('h:i A') }} ({{ $webinar->duration_minutes }}m)</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.webinars.show', $webinar->id) }}" class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 font-weight-bold text-decoration-none hover-primary d-inline-flex align-items-center gap-1.5" title="View Registered Attendees">
                                    <i class="fa-solid fa-users"></i> {{ $webinar->registrations_count }} @if($webinar->max_attendees) / {{ $webinar->max_attendees }} @endif
                                </a>
                            </td>
                            <td>
                                @if($webinar->price > 0)
                                    <span class="text-warning font-weight-bold text-xs">₦{{ number_format($webinar->price, 2) }}</span>
                                @else
                                    <span class="text-success font-weight-bold text-xs">FREE</span>
                                @endif
                            </td>
                            <td>
                                @if($webinar->status === 'live')
                                    <span class="badge bg-danger rounded-pill px-2.5 py-1 text-2xs animate-pulse">LIVE NOW</span>
                                @elseif($webinar->status === 'scheduled')
                                    <span class="badge bg-info rounded-pill px-2.5 py-1 text-2xs">SCHEDULED</span>
                                @elseif($webinar->status === 'completed')
                                    <span class="badge bg-success rounded-pill px-2.5 py-1 text-2xs">COMPLETED</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-2xs">{{ strtoupper($webinar->status) }}</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('webinars.show', $webinar->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary text-white" title="Public Link">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    <a href="{{ route('admin.webinars.show', $webinar->id) }}" class="btn btn-sm btn-info text-white" title="Manage & Attendees">
                                        <i class="fa-solid fa-users"></i>
                                    </a>
                                    <a href="{{ route('admin.webinars.edit', $webinar->id) }}" class="btn btn-sm btn-warning text-dark" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.webinars.destroy', $webinar->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this webinar?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-video-slash fa-2x mb-3 d-block"></i>
                                No webinars found. Click <strong>Schedule New Webinar</strong> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($webinars->hasPages())
            <div class="p-3 border-top border-white-10">
                {{ $webinars->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
