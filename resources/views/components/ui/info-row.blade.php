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
        'level' => 'signal',
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
            <x-ui.icon :name="$icon" @class(['h-5 w-5 shrink-0 text-pitch-700', 'mt-0.5' => (bool) $label]) />
        @endif
        <span class="min-w-0">
            @if ($label)
                <span class="block text-sm text-ink-muted">{{ $label }}</span>
            @endif
            <span @class([
                'flex flex-wrap items-center gap-2 text-base',
                'font-medium text-pitch-700 group-hover:text-pitch-800' => $variant === 'place' && $linked,
                'text-ink' => ! ($variant === 'place' && $linked),
            ])>{{ $slot }}</span>
        </span>
@if ($linked)
    </a>
@else
    </div>
@endif
