@props(['title', 'description' => null, 'illustration' => 'games'])

<div {{ $attributes->merge(['class' => 'rounded-card border border-line bg-white px-6 py-10 text-center']) }}>
    <x-illustrations.empty :variant="$illustration" class="mx-auto h-28 w-44" />
    <h3 class="mt-4 text-lg font-bold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 text-sm text-ink-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4 flex justify-center">{{ $action }}</div>
    @endisset
</div>
