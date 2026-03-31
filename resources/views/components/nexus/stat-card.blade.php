@props([
    'label',
    'value',
    'icon' => null,
    'color' => 'var(--clr-primary)',
    'trend' => null,
    'trendUp' => true
])

<div {{ $attributes->merge(['class' => 'h-stat']) }}>
    @if($icon)
        <i class="fa-solid {{ $icon }} mr-2" style="color: {{ $color }}; opacity: 0.6;"></i>
    @endif
    <div class="h-stat-label">{{ $label }}</div>
    <div class="h-stat-val d-flex align-items-center">
        {{ $value }}
        @if($trend)
            <span class="ml-2 small {{ $trendUp ? 'text-success' : 'text-danger' }}" style="font-size: 0.65rem;">
                <i class="fa-solid fa-caret-{{ $trendUp ? 'up' : 'down' }} mr-1"></i>{{ $trend }}
            </span>
        @endif
    </div>
</div>
