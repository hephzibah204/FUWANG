@extends('layouts.nexus')

@section('title', $webinar->title . ' - Webinar Admin Detail')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.webinars.index') }}" class="btn btn-outline-secondary text-white rounded-pill px-3 py-1.5 small text-decoration-none">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Webinars List
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('webinars.show', $webinar->slug) }}" target="_blank" class="btn btn-outline-light rounded-12 text-sm font-weight-bold">
                Public Page <i class="fa-solid fa-external-link-alt ms-1"></i>
            </a>
            <a href="{{ route('admin.webinars.edit', $webinar->id) }}" class="btn btn-warning rounded-12 text-sm font-weight-bold text-dark">
                <i class="fa-solid fa-pen me-1"></i> Edit Webinar
            </a>
            <a href="{{ route('admin.webinars.export_attendees', $webinar->id) }}" class="btn btn-success rounded-12 text-sm font-weight-bold">
                <i class="fa-solid fa-file-csv me-1"></i> Export Roster (CSV)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-12 p-3 text-sm mb-4">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Webinar Summary Card -->
        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-20 border border-white-10 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-secondary text-white rounded-pill px-3 py-1 text-uppercase font-weight-bold text-2xs">{{ ucfirst($webinar->category) }}</span>
                    @if($webinar->status === 'live')
                        <span class="badge bg-danger rounded-pill px-3 py-1 text-uppercase font-weight-bold text-2xs animate-pulse">LIVE NOW</span>
                    @else
                        <span class="badge bg-primary rounded-pill px-3 py-1 text-uppercase font-weight-bold text-2xs">{{ strtoupper($webinar->status) }}</span>
                    @endif
                </div>

                <h2 class="h4 text-white font-weight-bold mb-3">{{ $webinar->title }}</h2>

                <div class="py-3 border-top border-bottom border-white-10 mb-3 space-y-2">
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Speaker:</span>
                        <span class="text-white font-weight-semibold">{{ $webinar->speaker_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Date & Time:</span>
                        <span class="text-warning font-weight-semibold">{{ $webinar->scheduled_at->format('M d, Y @ h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Duration:</span>
                        <span class="text-white font-weight-semibold">{{ $webinar->duration_minutes }} mins</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Total Registrations:</span>
                        <span class="text-primary font-weight-bold">{{ $webinar->registrations->count() }} {{ $webinar->max_attendees ? '/ ' . $webinar->max_attendees : '' }}</span>
                    </div>
                </div>

                @if($webinar->meeting_link)
                    <div class="mb-3">
                        <label class="form-label text-muted text-2xs text-uppercase font-weight-bold">Live Stream / Meeting Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm text-xs" value="{{ $webinar->meeting_link }}" readonly>
                            <a href="{{ $webinar->meeting_link }}" target="_blank" class="btn btn-sm btn-primary">Join</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Registered Attendees List -->
        <div class="col-lg-8">
            <div class="glass-card rounded-20 overflow-hidden border border-white-10">
                <div class="p-4 border-bottom border-white-10 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="h5 text-white font-weight-bold mb-0">Registered Attendees Roster</h3>
                        <p class="text-muted text-2xs mb-0">Total {{ $webinar->registrations->count() }} confirmed attendees</p>
                    </div>
                    <a href="{{ route('admin.webinars.export_attendees', $webinar->id) }}" class="btn btn-sm btn-outline-success text-white">
                        <i class="fa-solid fa-download me-1"></i> CSV Roster
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="table-light-5">
                            <tr>
                                <th class="ps-4">Access Pass Code</th>
                                <th>Attendee Name</th>
                                <th>Email Address</th>
                                <th>Organization</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($webinar->registrations as $reg)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-dark text-warning font-monospace px-2.5 py-1 border border-warning-subtle text-xs">{{ $reg->access_code }}</span>
                                    </td>
                                    <td>
                                        <span class="text-white font-weight-semibold text-sm d-block">{{ $reg->full_name }}</span>
                                        @if($reg->phone_number)
                                            <span class="text-muted text-2xs"><i class="fa-solid fa-phone me-1"></i> {{ $reg->phone_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted text-xs">{{ $reg->email }}</span>
                                    </td>
                                    <td>
                                        <span class="text-white-50 text-xs">{{ $reg->organization ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted text-2xs">{{ $reg->created_at->format('M d, Y H:i') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-user-slash fa-2x mb-3 d-block"></i>
                                        No registered attendees for this webinar yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
