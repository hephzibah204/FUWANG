@props([
    'title',
    'href' => '#',
    'icon' => 'fa-gear',
    'badge' => null,
    'badgeColor' => 'var(--clr-accent-2)',
    'iconVariant' => 'regular'
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'qa-card']) }}>
    @if($badge)
        <div class="qa-badge" style="background: {{ $badgeColor }};">{{ $badge }}</div>
    @endif
    <div class="qa-icon">
        <i class="{{ $iconVariant === 'regular' ? 'fa-regular' : 'fa-solid' }} {{ $icon }}"></i>
    </div>
    <div class="qa-label">{{ $title }}</div>
</a>
