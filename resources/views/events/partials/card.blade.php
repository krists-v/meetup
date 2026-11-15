<article class="overflow-hidden rounded-card bg-white shadow-card ring-1 ring-line transition duration-150 hover:shadow-lift motion-safe:hover:-translate-y-0.5">
    <a href="{{ route('events.show', $event) }}" class="block">
        <x-illustrations.sport :sport="$event->sportSelection()" />
        <div class="space-y-2 px-4 pt-4">
            <div class="flex items-start justify-between gap-3">
                <h3 class="font-semibold text-ink">{{ $event->title }}</h3>
                <x-ui.sport-chip :event="$event" />
            </div>
            @if ($event->isPoll())
                <x-ui.badge tone="warn">Vote on a time</x-ui.badge>
                <p class="flex items-center gap-1.5 text-sm text-ink-muted">
                    <x-ui.icon name="clock" class="h-4 w-4" />
                    {{ $event->time_options_count }} time options
                </p>
            @else
                <p class="flex items-center gap-1.5 text-sm text-ink-muted">
                    <x-ui.icon name="calendar" class="h-4 w-4" />
                    {{ $event->event_date->format('D j M, H:i') }}
                </p>
            @endif
            @if ($event->attendanceLabel() !== '')
                <p class="flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                    <span>{{ $event->attendanceLabel() }}</span>
                    @if ($event->isFull())
                        <x-ui.badge tone="warn">Full</x-ui.badge>
                    @endif
                </p>
            @endif
        </div>
    </a>
    <div class="px-4 pb-2">
        <a href="{{ $event->mapUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-pitch-700 hover:text-pitch-800">
            <x-ui.icon name="map-pin" class="h-4 w-4" />
            {{ $event->location }}
        </a>
    </div>
</article>
