@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'secondary' => 'bg-white text-ink ring-1 ring-line hover:bg-pitch-50',
        'ghost' => 'bg-transparent text-ink hover:bg-pitch-50',
        'danger' => 'bg-white text-danger ring-1 ring-danger/30 hover:bg-danger-soft',
        default => 'bg-pitch-700 text-white hover:bg-pitch-800',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "motion-press inline-flex min-h-11 items-center justify-center gap-2 rounded-control px-4 py-2.5 text-sm font-semibold transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600 focus-visible:ring-offset-2 {$classes}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "motion-press inline-flex min-h-11 items-center justify-center gap-2 rounded-control px-4 py-2.5 text-sm font-semibold transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-40 {$classes}"]) }}>
        {{ $slot }}
    </button>
@endif
