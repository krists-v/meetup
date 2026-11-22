@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-control border-line bg-white px-3 py-2.5 text-base text-ink shadow-none focus:border-ink focus:ring-ink']) }}>
    {{ $slot }}
</select>
