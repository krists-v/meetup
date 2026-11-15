@props(['sport' => 'other', 'label' => null])

@php
    $sport = array_key_exists((string) $sport, \App\Models\Event::SPORTS) ? (string) $sport : 'other';
    $text = $label ?? \App\Models\Event::SPORTS[$sport];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.\App\Models\Event::accentClasses($sport)]) }}>
    {{ $text }}
</span>
