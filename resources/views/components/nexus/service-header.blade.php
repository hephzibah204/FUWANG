@props([
    'title',
    'subtitle' => null,
    'icon',
    'iconClass' => '',
    'iconStyle' => null,
    'titleClass' => '',
    'subtitleClass' => '',
])

<div {{ $attributes->merge(['class' => 'service-header-card mb-4']) }}>
    <div class="sh-icon {{ $iconClass }}" @if($iconStyle) style="{{ $iconStyle }}" @endif><i class="{{ $icon }}"></i></div>
    <div class="sh-text">
        <h1 class="{{ $titleClass }}">{{ $title }}</h1>
        @if($subtitle)
            <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($badges)
        <div class="sh-badges ml-auto d-none d-md-flex">
            {{ $badges }}
        </div>
    @endisset
</div>
