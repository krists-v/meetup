@props(['highlight' => false])

@php
    $surface = $highlight
        ? 'border-highlight-line bg-highlight'
        : 'border-line bg-white shadow-card';
@endphp

<div {{ $attributes->merge(['class' => "rounded-card border p-4 sm:p-6 {$surface}"]) }}>
    {{ $slot }}
</div>
