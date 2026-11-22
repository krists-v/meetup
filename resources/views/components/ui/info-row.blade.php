@props([
    'variant',
    'label' => null,
    'href' => null,
])

@php
    $icons = [
        'date' => 'calendar',
        'time' => 'clock',
        'place' => 'map-pin',
        'spots' => 'users',
        'level' => 'bar-chart',
        'outdoor' => 'sun',
        'indoor' => 'home',
    ];
    $icon = $icons[$variant] ?? null;
    $linked = filled($href);
    $root = 'flex gap-3 '.($label ? 'items-start' : 'items-center').($linked ? ' group min-h-11' : '');
@endphp

@if ($linked)
    <a
        href="{{ $href }}"
        @if ($variant === 'place') target="_blank" rel="noopener noreferrer" @endif
        {{ $attributes->merge(['class' => $root]) }}
    >
@else
    <div {{ $attributes->merge(['class' => $root]) }}>
@endif
        @if ($icon)
            <x-ui.icon :name="$icon" @class(['h-5 w-5 shrink-0 text-ink-faint', 'mt-0.5' => (bool) $label]) />
        @endif
        <span class="min-w-0">
            @if ($label)
                <span class="block text-base text-ink-muted">{{ $label }}</span>
            @endif
            <span @class([
                'flex flex-wrap items-center gap-2 text-lg',
                'font-medium text-ink group-hover:underline' => $variant === 'place' && $linked,
                'text-ink' => ! ($variant === 'place' && $linked),
            ])>{{ $slot }}</span>
        </span>
@if ($linked)
    </a>
@else
    </div>
@endif
