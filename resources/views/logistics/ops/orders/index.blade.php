@extends('layouts.postoffice')

@section('title', 'Logistics Operations - Orders')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Logistics Orders</h1>
        @if($staff && ($staff->hasRole('logistics_manager') || $staff->hasRole('superadmin')))
            <a href="/logistics/ops/orders/create" class="btn btn-primary">Create Order</a>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Orders List</div>
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="p-4 text-muted text-center">No orders found.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking ID</th>
                                <th>Sender</th>
                                <th>Recipient</th>
                                <th>Delivery Type</th>
                                <th>Status</th>
                                <th>Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <span class="fw-mono text-primary">{{ $order->tracking_id }}</span>
                                </td>
                                <td>{{ $order->sender_name }}</td>
                                <td>{{ $order->recipient_name }}</td>
                                <td>{{ ucwords($order->delivery_type) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucwords($order->status) }}</span>
                                </td>
                                <td>₦{{ number_format($order->agent_commission_amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
