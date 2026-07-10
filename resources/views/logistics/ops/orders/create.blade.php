@extends('layouts.nexus')

@section('title', 'Create Order - Logistics Ops')

@section('content')
@include('logistics.ops.partials.nav', ['title' => 'Create Order', 'subtitle' => 'Create a new logistics order'])

@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 p-4">
    <form method="POST" action="{{ route('logistics.ops.orders.store') }}">
        @csrf

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Customer User ID</label>
                <input type="number" name="user_id" class="form-control" required value="{{ old('user_id') }}">
            </div>
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Delivery type</label>
                <select name="delivery_type" class="form-control" required>
                    <option value="standard" @selected(old('delivery_type') === 'standard')>standard</option>
                    <option value="express" @selected(old('delivery_type') === 'express')>express</option>
                    <option value="overnight" @selected(old('delivery_type') === 'overnight')>overnight</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Sender name</label>
                <input type="text" name="sender_name" class="form-control" required value="{{ old('sender_name') }}">
            </div>
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Recipient name</label>
                <input type="text" name="recipient_name" class="form-control" required value="{{ old('recipient_name') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Sender address</label>
                <textarea name="sender_address" class="form-control" rows="3" required>{{ old('sender_address') }}</textarea>
            </div>
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Recipient address</label>
                <textarea name="recipient_address" class="form-control" rows="3" required>{{ old('recipient_address') }}</textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Weight (kg)</label>
                <input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight') }}">
            </div>
            <div class="form-group col-md-8">
                <label class="text-white-50 small">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}">
            </div>
        </div>

        <div class="mt-3 d-flex flex-column flex-md-row justify-content-between">
            <button class="btn btn-primary px-4" type="submit"><i class="fa fa-save mr-2"></i>Create</button>
            <a class="btn btn-outline-secondary mt-3 mt-md-0 px-4" href="{{ route('logistics.ops.orders.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

