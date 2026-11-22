@props(['tone' => 'pitch', 'preset' => null])

@php
    $presets = [
        'full' => ['tone' => 'warn', 'label' => 'Full'],
        'beginners' => ['tone' => 'pitch', 'label' => 'Beginners welcome'],
        'indoor' => ['tone' => 'neutral', 'label' => 'Indoor'],
        'outdoor' => ['tone' => 'neutral', 'label' => 'Outdoor'],
    ];
    $presetLabel = null;

    if (is_string($preset) && isset($presets[$preset])) {
        $tone = $presets[$preset]['tone'];
        $presetLabel = $presets[$preset]['label'];
    }

    $classes = match ($tone) {
        'warn' => 'bg-warn-soft text-warn',
        'neutral' => 'bg-canvas text-ink-muted ring-1 ring-line',
        default => 'bg-success-soft text-success',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $presetLabel ?? $slot }}
</span>
