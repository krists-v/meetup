@props(['value' => null])

<label {{ $attributes->merge(['class' => 'block text-base font-medium text-ink']) }}>
    {{ $value ?? $slot }}
</label>
