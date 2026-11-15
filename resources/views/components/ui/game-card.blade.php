@props(['event'])

<article style="--vt-name: game-{{ $event->id }}" {{ $attributes->merge(['class' => 'motion-view overflow-hidden rounded-card bg-white shadow-card ring-1 ring-line transition duration-150 hover:shadow-lift motion-safe:hover:-translate-y-0.5 motion-safe:active:scale-[0.99]']) }}>
    <a href="{{ route('events.show', $event) }}" class="block">
        <x-illustrations.sport :sport="$event->sportSelection()" />
        <div class="space-y-3 px-4 pt-4">
            <div class="flex items-start justify-between gap-3">
                <h3 class="font-semibold text-ink">{{ $event->title }}</h3>
                <x-ui.chip :sport="$event->sportSelection()" :label="$event->sportLabel()" />
            </div>
            @if ($event->isPoll())
                <x-ui.badge tone="warn">Vote on a time</x-ui.badge>
                <x-ui.info-row variant="time">{{ $event->time_options_count }} time options</x-ui.info-row>
            @else
                <x-ui.info-row variant="date">{{ $event->event_date->format('D j M, H:i') }}</x-ui.info-row>
            @endif
            <x-ui.activity-facts :event="$event" />
            @if ($event->attendanceLabel() !== '')
                <x-ui.info-row variant="spots">
                    <span>{{ $event->attendanceLabel() }}</span>
                    @if ($event->isFull())
                        <x-ui.badge preset="full" />
                    @endif
                </x-ui.info-row>
            @endif
        </div>
    </a>
    <div class="px-4 pb-2">
        <x-ui.info-row variant="place" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
    </div>
</article>
