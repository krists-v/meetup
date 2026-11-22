@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'block' => false,
])

@php
    $tone = match ($variant) {
        'secondary' => 'bg-mist text-ink ring-1 ring-line hover:bg-line',
        'ghost' => 'bg-transparent text-ink hover:bg-mist',
        'danger' => 'bg-white text-danger ring-1 ring-danger/30 hover:bg-danger-soft',
        default => 'bg-ink text-white hover:bg-ink/90',
    };
    $classes = "motion-press inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-control px-4 py-2.5 text-sm font-bold transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2 {$tone}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes.' disabled:pointer-events-none disabled:opacity-40']) }}>
        {{ $slot }}
    </button>
@endif
