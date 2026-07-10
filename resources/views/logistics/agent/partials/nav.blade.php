<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
    <div>
        <h3 class="text-white mb-1 font-weight-bold">{{ $title ?? 'Delivery Agent' }}</h3>
        @if(!empty($subtitle))
            <p class="text-white-50 mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center">
        <a href="{{ route('logistics.agent.dashboard') }}" class="btn btn-sm btn-outline-light mr-2 mb-2" style="border-radius: 12px;">Dashboard</a>
        <a href="{{ route('logistics.agent.orders.index') }}" class="btn btn-sm btn-outline-light mr-2 mb-2" style="border-radius: 12px;">My Deliveries</a>
        <a href="{{ route('logistics.agent.earnings.index') }}" class="btn btn-sm btn-outline-light mr-2 mb-2" style="border-radius: 12px;">Earnings</a>
        <form action="{{ route('logistics.agent.availability') }}" method="POST" class="d-flex align-items-center mr-2 mb-2">
            @csrf
            <select name="availability_status" class="form-control form-control-sm bg-transparent border-secondary text-white mr-2" style="border-radius: 12px; border-color: rgba(255,255,255,0.1) !important; min-width: 160px;">
                <option value="available" @selected(($agent->availability_status ?? '') === 'available')>Available</option>
                <option value="on_delivery" @selected(($agent->availability_status ?? '') === 'on_delivery')>On Delivery</option>
                <option value="offline" @selected(($agent->availability_status ?? '') === 'offline')>Offline</option>
            </select>
            <button type="submit" class="btn btn-sm btn-po-primary" style="border-radius: 12px;">Update</button>
        </form>
        <a href="{{ route('profile') }}" class="btn btn-sm btn-outline-secondary mr-2 mb-2" style="border-radius: 12px;">Profile</a>
        <form action="{{ route('logistics.logout') }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 12px;">Logout</button>
        </form>
    </div>
</div>
