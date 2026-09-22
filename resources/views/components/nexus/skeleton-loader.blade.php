@props([
    'count' => 3,
    'type' => 'card' // 'card', 'list', 'text'
])

<div class="skeleton-wrapper">
    @for ($i = 0; $i < $count; $i++)
        @if ($type === 'card')
            <div class="skeleton-card">
                <div class="skeleton-box skeleton-title"></div>
                <div class="skeleton-box skeleton-sub"></div>
                <div class="skeleton-box skeleton-text"></div>
            </div>
        @elseif ($type === 'list')
            <div class="skeleton-list-item">
                <div class="skeleton-box skeleton-avatar"></div>
                <div class="skeleton-list-body">
                    <div class="skeleton-box skeleton-title"></div>
                    <div class="skeleton-box skeleton-sub"></div>
                </div>
            </div>
        @else
            <div class="skeleton-box skeleton-text mb-2"></div>
        @endif
    @endfor
</div>
