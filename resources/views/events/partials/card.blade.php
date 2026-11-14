<a href="{{ route('events.show', $event) }}" class="block overflow-hidden rounded-card bg-white shadow-card ring-1 ring-line transition duration-150 hover:shadow-lift motion-safe:hover:-translate-y-0.5">
    <x-illustrations.sport :sport="$event->sportSelection()" />
    <div class="space-y-2 px-4 py-4">
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
        <p class="flex items-center gap-1.5 text-sm text-ink-muted">
            <x-ui.icon name="map-pin" class="h-4 w-4" />
            {{ $event->location }}
        </p>
        @php
            $meta = [];
            if ($event->isPoll()) {
                if ($event->max_participants) {
                    $meta[] = 'max '.$event->max_participants.' once a time is chosen';
                }
            } else {
                $meta[] = $event->going_participants_count.' going';
                if ($event->max_participants) {
                    $meta[] = 'max '.$event->max_participants;
                }
            }
            if ($event->visibility === 'private') {
                $meta[] = 'private';
            }
        @endphp
        @if ($meta !== [])
            <p class="text-xs text-ink-faint">{{ implode(' · ', $meta) }}</p>
        @endif
    </div>
</a>
