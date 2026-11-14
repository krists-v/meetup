@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-3']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-ink-muted">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="shrink-0">{{ $actions }}</div>
    @endisset
</div>
