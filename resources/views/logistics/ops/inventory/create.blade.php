@extends('layouts.nexus')

@section('title', 'Create Inventory Item - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Create Inventory Item', 'subtitle' => 'Add a new item to inventory'])

@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 p-4">
    <form method="POST" action="{{ route('logistics.ops.inventory.store') }}">
        @csrf

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">SKU</label>
                <input type="text" name="sku" class="form-control" required value="{{ old('sku') }}">
            </div>
            <div class="form-group col-md-8">
                <label class="text-white-50 small">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="0" value="{{ old('quantity', 0) }}">
            </div>
            <div class="form-group col-md-8">
                <label class="text-white-50 small">Location</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="text-white-50 small">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="custom-control custom-switch mb-3">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
            <label class="custom-control-label text-white-50 small" for="is_active">Active</label>
        </div>

        <div class="mt-3 d-flex flex-column flex-md-row justify-content-between">
            <button class="btn btn-primary px-4" type="submit"><i class="fa fa-save mr-2"></i>Create</button>
            <a class="btn btn-outline-secondary mt-3 mt-md-0 px-4" href="{{ route('logistics.ops.inventory.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

