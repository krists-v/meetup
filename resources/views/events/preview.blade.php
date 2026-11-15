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
        <div class="mt-6 space-y-4">
            <div class="overflow-hidden rounded-card ring-1 ring-line">
                <x-illustrations.sport :sport="$event->sportSelection()" class="h-32 w-full" />
            </div>
            <p class="text-sm text-ink-muted">{{ $event->sportLabel() }}</p>
            @unless ($event->isPoll())
                <p class="text-base text-ink">{{ $event->event_date->format('D j M, H:i') }}</p>
            @endunless
            <a href="{{ $event->mapUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center text-base font-medium text-pitch-700 hover:text-pitch-800">{{ $event->location }}</a>
            @if ($event->attendanceLabel() !== '')
                <p class="flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                    <span>{{ $event->attendanceLabel() }}</span>
                    @if ($event->isFull())
                        <x-ui.badge tone="warn">Full</x-ui.badge>
                    @endif
                </p>
            @endif
        </div>
        <div class="mt-6 space-y-3">
            <x-ui.button :href="route('events.join', $event)" class="w-full">I'm in</x-ui.button>
            <x-ui.button variant="secondary" :href="route('events.join', ['event' => $event, 'account' => 'new'])" class="w-full">Create an account</x-ui.button>
        </div>
    @else
        <div class="mt-6">
            <x-ui.button :href="route('login')" class="w-full">Log in</x-ui.button>
        </div>
    @endif
</x-guest-layout>
