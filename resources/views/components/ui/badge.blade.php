@props(['tone' => 'pitch'])

@php
    $classes = match ($tone) {
        'warn' => 'bg-warn-soft text-warn',
        'neutral' => 'bg-canvas text-ink-muted ring-1 ring-line',
        default => 'bg-pitch-100 text-pitch-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
