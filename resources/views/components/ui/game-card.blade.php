@props(['event', 'highlight' => false])

@php
    $frame = $highlight
        ? 'border-highlight-line bg-highlight'
        : 'border-line bg-white shadow-card';
@endphp

<article style="--vt-name: game-{{ $event->id }}" @if ($highlight) data-highlight="1" @endif {{ $attributes->merge(['class' => "overflow-hidden rounded-card border {$frame}"]) }}>
    <div class="px-3 pt-3">
        <a href="{{ route('events.show', $event) }}" class="relative block overflow-hidden rounded-2xl">
            <img src="{{ $event->photoUrl() }}" alt="{{ $event->sportLabel() }}" class="h-48 w-full object-cover" />
            <x-ui.image-pill :label="$event->sportLabel()" />
        </a>
    </div>

    <div class="space-y-4 px-5 pb-5 pt-4">
        <div class="flex items-start justify-between gap-3">
            <a href="{{ route('events.show', $event) }}" class="min-w-0">
                <h3 class="text-xl font-bold tracking-tight text-ink">{{ $event->title }}</h3>
            </a>
            @if ($event->spotsLeftLabel())
                <x-ui.badge class="shrink-0" :tone="$event->isFull() || $event->isPoll() ? 'warn' : 'pitch'">{{ $event->spotsLeftLabel() }}</x-ui.badge>
            @endif
        </div>

        <div class="space-y-3 text-lg">
            @if ($event->isPoll())
                <x-ui.info-row variant="time">{{ $event->whenLabel() }}</x-ui.info-row>
            @else
                <x-ui.info-row variant="date">{{ $event->whenLabel() }}</x-ui.info-row>
            @endif
            <x-ui.info-row variant="place" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
            <x-ui.info-row variant="spots">{{ $event->playersLabel() }}</x-ui.info-row>
            <x-ui.info-row variant="level">{{ $event->levelLabel() }}</x-ui.info-row>
        </div>

        <div class="border-t border-line pt-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-base text-ink-muted">Hosted by {{ $event->creator->name }}</p>
                @if ($event->priceLabel())
                    <p class="text-base font-bold text-ink">{{ $event->priceLabel() }}</p>
                @endif
            </div>
            <x-ui.button :href="route('events.show', $event)" block class="mt-4">I'm in</x-ui.button>
        </div>
    </div>
</article>
