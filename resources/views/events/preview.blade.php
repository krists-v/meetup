<x-guest-layout
    :title="$preview->title"
    :description="$preview->description"
    :image="$preview->image"
    :url="$preview->url"
>
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $preview->title }}</h1>
        <p class="text-sm text-ink-muted">{{ $preview->description }}</p>
    </div>

    @if ($event)
        <div class="mt-6 overflow-hidden rounded-card ring-1 ring-line">
            <x-illustrations.sport :sport="$event->sportSelection()" class="h-32 w-full" />
        </div>
    @endif

    <div class="mt-6">
        <x-ui.button :href="route('login')" class="w-full">Log in</x-ui.button>
    </div>
</x-guest-layout>
