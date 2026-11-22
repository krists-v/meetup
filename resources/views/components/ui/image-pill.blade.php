@props(['label'])

<span data-image-pill {{ $attributes->merge(['class' => 'pointer-events-none absolute left-3 top-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-white px-3 py-1 text-sm font-medium text-ink']) }}>{{ $label }}</span>
