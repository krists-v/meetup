@props(['title', 'description' => null, 'illustration' => 'games'])

<div {{ $attributes->merge(['class' => 'rounded-card bg-white px-4 py-6 text-center ring-1 ring-line']) }}>
    <x-illustrations.empty :variant="$illustration" class="mx-auto h-28 w-44" />
    <h3 class="mt-3 text-base font-semibold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 text-sm text-ink-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4 flex justify-center">{{ $action }}</div>
    @endisset
</div>
