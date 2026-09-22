@props([
    'title',
    'href' => '#',
    'icon' => 'fa-gear',
    'badge' => null,
    'badgeColor' => 'var(--clr-accent-2)',
    'iconVariant' => 'regular',
    'disabled' => false
])

@if($disabled)
    <div {{ $attributes->merge(['class' => 'qa-card opacity-50 pe-none position-relative']) }} style="cursor: not-allowed; opacity: 0.6;">
        <div class="qa-badge" style="background: rgba(239, 68, 68, 0.25); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4);">Maintenance</div>
        <div class="qa-icon">
            <i class="{{ $iconVariant === 'regular' ? 'fa-regular' : 'fa-solid' }} {{ $icon }}"></i>
        </div>
        <div class="qa-label">{{ $title }}</div>
    </div>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'qa-card']) }}>
        @if($badge)
            <div class="qa-badge" style="background: {{ $badgeColor }};">{{ $badge }}</div>
        @endif
        <div class="qa-icon">
            <i class="{{ $iconVariant === 'regular' ? 'fa-regular' : 'fa-solid' }} {{ $icon }}"></i>
        </div>
        <div class="qa-label">{{ $title }}</div>
    </a>
@endif
