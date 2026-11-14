@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($title)
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-muted">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-ink-muted">{{ $description }}</p>
            @endif
        </div>
    @endif
    {{ $slot }}
</section>
