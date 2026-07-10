<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
    <div>
        <h3 class="text-white mb-1 font-weight-bold">{{ $title ?? 'Logistics Ops' }}</h3>
        @if(!empty($subtitle))
            <p class="text-white-50 mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="mt-3 mt-md-0 d-flex flex-wrap">
        @if(session('logistics_ops_impersonator_admin_id'))
            <form action="{{ route('logistics.ops.stop_impersonation') }}" method="POST" class="mr-2 mb-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light" style="border-radius: 12px;">
                    <i class="fa fa-arrow-left mr-2" style="color: var(--clr-primary);"></i> Back to Admin
                </button>
            </form>
        @endif
        <a href="{{ route('logistics.ops.dashboard') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Dashboard</a>
        <a href="{{ route('logistics.ops.orders.index') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Orders</a>
        <a href="{{ route('logistics.ops.shipments.index') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Shipments</a>
        <a href="{{ route('logistics.ops.agents.index') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Agents</a>
        <a href="{{ route('logistics.ops.centers.index') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Centers</a>
        <a href="{{ route('logistics.ops.inventory.index') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Inventory</a>
        <a href="{{ route('logistics.ops.analytics.index') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2">Analytics</a>
        <form action="{{ route('logistics.ops.logout') }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
        </form>
    </div>
</div>
