@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-pitch-700 bg-pitch-50 py-2 pe-4 ps-3 text-start text-base font-medium text-pitch-800 transition duration-150 ease-in-out focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-ink-muted transition duration-150 ease-in-out hover:border-line hover:bg-pitch-50 hover:text-ink focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
