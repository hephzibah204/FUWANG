@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Agent Dashboard: {{ $shop->name }}</h2>
        <div>
            @if($agent->status === 'approved')
                <span class="badge bg-success">Status: Approved</span>
            @elseif($agent->status === 'pending')
                <span class="badge bg-warning text-dark">Status: Pending Approval</span>
            @else
                <span class="badge bg-danger">Status: Suspended</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <!-- Dashboard Metrics -->
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Total in Shop</h6>
                    <h2 class="display-5 fw-bold">{{ $totalInShop }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Customer to Collect</h6>
                    <h2 class="display-5 fw-bold">{{ $customerToCollect }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Driver to Collect</h6>
                    <h2 class="display-5 fw-bold">{{ $driverToCollect }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Defective / Damaged</h6>
                    <h2 class="display-5 fw-bold">{{ $defective }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if($agent->status === 'approved')
            <!-- Quick Actions -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white fw-bold">Customer Transactions</div>
                    <div class="card-body d-flex flex-column gap-3">
                        <a href="{{ route('parcels.dropoff.customer') }}" class="btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center py-4">
                            <i class="fas fa-box-open me-2" style="font-size: 1.5rem;"></i> Customer Drop-off
                        </a>
                        <a href="{{ route('parcels.pickup.customer') }}" class="btn btn-outline-success btn-lg d-flex align-items-center justify-content-center py-4">
                            <i class="fas fa-user-check me-2" style="font-size: 1.5rem;"></i> Customer Pickup
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white fw-bold">Driver Handovers</div>
                    <div class="card-body d-flex flex-column gap-3">
                        <a href="{{ route('parcels.dropoff.driver') }}" class="btn btn-outline-secondary btn-lg d-flex align-items-center justify-content-center py-4">
                            <i class="fas fa-truck me-2" style="font-size: 1.5rem;"></i> Driver Drop-off
                        </a>
                        <a href="{{ route('parcels.pickup.driver') }}" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-center py-4">
                            <i class="fas fa-box me-2" style="font-size: 1.5rem;"></i> Driver Collection
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="col-12 text-center mt-5">
                <div class="p-5 border rounded bg-light">
                    <h4><i class="fas fa-hourglass-half text-warning"></i> Application Pending Review</h4>
                    <p class="text-muted mt-3">Your application to become a Parcel Agent is currently under review by our administration team. Once your NIN and shop location are verified, this dashboard will unlock with all tools to receive and hand over packages.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
