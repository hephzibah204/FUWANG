@extends('layouts.nexus')

@section('title', 'Edit Inventory Item - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Edit Inventory Item', 'subtitle' => $item->sku])

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 p-4">
    <form method="POST" action="{{ route('logistics.ops.inventory.update', $item->id) }}">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">SKU</label>
                <input type="text" name="sku" class="form-control" required value="{{ old('sku', $item->sku) }}">
            </div>
            <div class="form-group col-md-8">
                <label class="text-white-50 small">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $item->name) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="0" value="{{ old('quantity', $item->quantity) }}">
            </div>
            <div class="form-group col-md-8">
                <label class="text-white-50 small">Location</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $item->location) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="text-white-50 small">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
        </div>

        <div class="custom-control custom-switch mb-3">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $item->is_active))>
            <label class="custom-control-label text-white-50 small" for="is_active">Active</label>
        </div>

        <div class="mt-3 d-flex flex-column flex-md-row justify-content-between">
            <button class="btn btn-primary px-4" type="submit"><i class="fa fa-save mr-2"></i>Save</button>
            <a class="btn btn-outline-secondary mt-3 mt-md-0 px-4" href="{{ route('logistics.ops.inventory.index') }}">Back</a>
        </div>
    </form>

    <form method="POST" action="{{ route('logistics.ops.inventory.destroy', $item->id) }}" class="mt-3" onsubmit="return confirm('Delete this item?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger px-4" type="submit"><i class="fa fa-trash-can mr-2"></i>Delete</button>
    </form>
</div>
@endsection
