@props(['disabled' => false])

<label class="flex min-h-11 items-center gap-2 text-base text-ink">
    <input
        type="checkbox"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'h-5 w-5 rounded border-line text-ink focus:ring-ink']) }}
    />
    <span>{{ $slot }}</span>
</label>
