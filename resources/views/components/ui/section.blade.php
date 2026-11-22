@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($title)
        <div>
            <h2 class="text-lg font-bold tracking-tight text-ink">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-base text-ink-muted">{{ $description }}</p>
            @endif
        </div>
    @endif
    {{ $slot }}
</section>
