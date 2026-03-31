@props([
    'providers',
    'name' => 'api_provider_id',
    'id' => null,
    'placeholder' => 'Auto-Route (Recommended)',
])

@php
    $providersCount = $providers ? $providers->count() : 0;
    $selectId = $id ?: $name;
@endphp

@if($providersCount > 1)
    <select id="{{ $selectId }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'form-control form-control-sm']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($providers as $provider)
            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
        @endforeach
    </select>
@elseif($providersCount === 1)
    <input type="hidden" id="{{ $selectId }}" name="{{ $name }}" value="{{ $providers->first()->id }}">
    <div class="text-white font-weight-bold">{{ $providers->first()->name }}</div>
@else
    <div class="text-warning small"><i class="fa-solid fa-triangle-exclamation"></i> Legacy Gateway Active</div>
@endif
