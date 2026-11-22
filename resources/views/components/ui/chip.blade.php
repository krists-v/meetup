@props([
    'sport' => null,
    'label' => null,
    'href' => null,
    'selected' => false,
    'for' => null,
])

@php
    $text = $label;

    if ($text === null && $sport !== null) {
        $key = array_key_exists((string) $sport, \App\Models\Event::SPORTS) ? (string) $sport : 'other';
        $text = \App\Models\Event::SPORTS[$key];
    }

    $base = 'relative z-10 inline-flex min-h-11 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition duration-150';

    if ($for) {
        $tone = 'cursor-pointer bg-white text-ink ring-1 ring-line peer-checked:bg-ink peer-checked:text-white peer-checked:ring-ink peer-focus-visible:ring-2 peer-focus-visible:ring-ink peer-focus-visible:ring-offset-2';
    } elseif ($selected) {
        $tone = 'bg-ink text-white';
    } else {
        $tone = 'bg-white text-ink ring-1 ring-line hover:bg-mist focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2';
    }
@endphp

@if ($for)
    <label for="{{ $for }}" {{ $attributes->merge(['class' => "{$base} {$tone}"]) }}>
        {{ $slot->isEmpty() ? $text : $slot }}
    </label>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "{$base} {$tone}"]) }}>
        {{ $slot->isEmpty() ? $text : $slot }}
    </a>
@else
    <span {{ $attributes->merge(['class' => "{$base} {$tone}"]) }}>
        {{ $slot->isEmpty() ? $text : $slot }}
    </span>
@endif
