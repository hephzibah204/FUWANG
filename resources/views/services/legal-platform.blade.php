@extends('layouts.nexus')

@section('title', 'Legal Platform | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(67, 56, 202, 0.05)); border: 1px solid rgba(79, 70, 229, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff;"><i class="fa-solid fa-scale-balanced"></i></div>
        <div class="sh-text">
            <h1 class="h4 font-weight-bold mb-1">Legal Platform</h1>
            <p class="text-muted small mb-0">AI drafting and Notary services in one place.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-gavel text-indigo"></i> AI Drafting</span>
            <span class="badge-accent"><i class="fa-solid fa-stamp text-success"></i> Notary</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="panel-card p-4">
                <h5 class="text-white font-weight-bold mb-2">Quick Actions</h5>
                <div class="d-grid" style="display: grid; gap: 10px;">
                    <a class="btn btn-primary" href="{{ route('services.legal-hub') }}">
                        <i class="fa-solid fa-gavel mr-2"></i> Open AI Legal Hub
                    </a>
                    <a class="btn btn-outline-light" href="{{ route('services.notary') }}">
                        <i class="fa-solid fa-stamp mr-2"></i> Open Notary Services
                    </a>
                </div>
                <div class="mt-3 small text-white-50">
                    If any module is disabled by the administrator, you’ll be redirected with an offline message.
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="panel-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white font-weight-bold mb-0">My Legal Records</h5>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th class="text-right">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td class="text-white-50">{{ $r['source'] === 'notary' ? 'Notary' : 'AI Legal Hub' }}</td>
                                    <td class="text-white">{{ ucwords(str_replace('_', ' ', (string) $r['type'])) }}</td>
                                    <td class="text-white-50">{{ $r['reference'] }}</td>
                                    <td>
                                        @if($r['status'] === 'completed' || $r['status'] === 'sent')
                                            <span class="badge badge-success">{{ $r['status'] }}</span>
                                        @elseif($r['status'] === 'pending_stamp')
                                            <span class="badge badge-info">pending_stamp</span>
                                        @elseif($r['status'] === 'draft')
                                            <span class="badge badge-warning">draft</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $r['status'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($r['download_url'])
                                            <a class="btn btn-sm btn-outline-success" href="{{ $r['download_url'] }}" target="_blank">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        @else
                                            <span class="text-white-50 small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fa-2x mb-2"></i>
                                        <div>No legal records yet.</div>
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

