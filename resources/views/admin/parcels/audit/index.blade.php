@extends('layouts.admin')

@section('title', 'Parcel Audit Trail')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="font-weight-bold mb-1">Parcel Custody <span class="text-primary">Audit Trail</span></h3>
        <p class="text-muted">A strict chronological log of all parcel transfers across the agent network.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Timestamp</th>
                        <th class="border-0">Tracking Number</th>
                        <th class="border-0">Event Type</th>
                        <th class="border-0">Agent & Shop</th>
                        <th class="border-0">Notes & Evidence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->created_at->format('M d, Y H:i:s') }}</td>
                            <td><span class="badge badge-light border">{{ $event->parcel->tracking_number ?? 'N/A' }}</span></td>
                            <td>
                                @if($event->event_type === 'customer_to_agent')
                                    <span class="badge badge-primary"><i class="fa fa-arrow-right"></i> Customer &rarr; Shop</span>
                                @elseif($event->event_type === 'driver_to_agent')
                                    <span class="badge badge-info"><i class="fa fa-arrow-right"></i> Driver &rarr; Shop</span>
                                @elseif($event->event_type === 'agent_to_customer')
                                    <span class="badge badge-success"><i class="fa fa-check"></i> Shop &rarr; Customer</span>
                                @elseif($event->event_type === 'agent_to_driver')
                                    <span class="badge badge-warning"><i class="fa fa-truck"></i> Shop &rarr; Driver</span>
                                @elseif($event->event_type === 'customer_rejected')
                                    <span class="badge badge-danger"><i class="fa fa-times"></i> Customer Rejected</span>
                                @else
                                    <span class="badge badge-secondary">{{ $event->event_type }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $event->agent->user->fullname ?? 'Unknown Agent' }}</strong><br>
                                <small class="text-muted">{{ $event->agent->shop->name ?? 'Unknown Shop' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $event->notes }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No custody events recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($events->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $events->links() }}
        </div>
    @endif
</div>
@endsection
