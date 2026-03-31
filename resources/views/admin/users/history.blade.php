@extends('layouts.nexus')

@section('title', 'Transaction History – {{ $user->fullname }}')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex align-items-center">
        <a href="{{ route('admin.users.index') }}" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
            <i class="fa fa-arrow-left text-white"></i>
        </a>
        <div>
            <h3 class="text-white mb-0 fw-bold">{{ $user->fullname }}'s History</h3>
            <p class="text-white-50 mb-0">All funding & deduction events for {{ $user->email }}</p>
        </div>
    </div>
</div>

<div class="card border-0 rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="table-responsive">
        <table class="table table-borderless mb-0 text-white" style="background: transparent;">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="py-3 px-4 text-white-50 small text-uppercase border-bottom-0">#</th>
                    <th class="py-3 px-4 text-white-50 small text-uppercase border-bottom-0">Type</th>
                    <th class="py-3 px-4 text-white-50 small text-uppercase border-bottom-0 text-right">Amount</th>
                    <th class="py-3 px-4 text-white-50 small text-uppercase border-bottom-0">Processed By</th>
                    <th class="py-3 px-4 text-white-50 small text-uppercase border-bottom-0">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $i => $record)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                    <td class="py-3 px-4 align-middle text-white-50 small">{{ $history->firstItem() + $i }}</td>
                    <td class="py-3 px-4 align-middle">
                        @if(str_contains($record->funding_type, 'Deduction'))
                            <span class="badge rounded-pill px-3 py-1" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);">
                                <i class="fa fa-arrow-down mr-1"></i>{{ $record->funding_type }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-3 py-1" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3);">
                                <i class="fa fa-arrow-up mr-1"></i>{{ $record->funding_type }}
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 align-middle text-right font-weight-bold">
                        @if($record->amount < 0)
                            <span style="color: #ef4444;">-₦{{ number_format(abs($record->amount), 2) }}</span>
                        @else
                            <span style="color: #22c55e;">+₦{{ number_format($record->amount, 2) }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 align-middle text-white-50 small">{{ $record->fullname }}</td>
                    <td class="py-3 px-4 align-middle text-white-50 small">{{ $record->created_at?->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-white-50">No transaction history found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($history->hasPages())
    <div class="px-4 py-3 d-flex justify-content-center border-top" style="border-color: rgba(255,255,255,0.05) !important;">
        {{ $history->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
