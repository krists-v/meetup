@php
    $mine = $event->participants->first();
    $status = $tab === 'playing' ? $mine?->pivot->status : null;
    $past = $event->event_date?->isPast() ?? false;
    $chip = match ($status) {
        'going' => "You're in",
        'maybe' => 'Maybe',
        'waitlist' => 'Waitlist',
        'requested' => 'Requested',
        'invited' => 'Invited',
        'not_going' => "Can't make it",
        default => 'Hosting',
    };
    $tone = match ($status) {
        'going' => 'pitch',
        'waitlist', 'requested', 'invited' => 'warn',
        default => 'neutral',
    };
    $rsvp = null;

    if ($tab === 'playing' && ! $event->isPoll() && ! $past && $status !== 'requested') {
        if ($status === 'going') {
            $rsvp = ['value' => 'not_going', 'label' => "Can't make it", 'variant' => 'secondary'];
        } elseif ($event->isFull()) {
            if ($status !== 'waitlist') {
                $rsvp = ['value' => 'waitlist', 'label' => 'Join the waitlist', 'variant' => 'primary'];
            }
        } else {
            $rsvp = ['value' => 'going', 'label' => "I'm in", 'variant' => 'primary'];
        }
    }
@endphp

<x-ui.card class="space-y-4">
    <div class="flex items-start gap-3">
        <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-2xl">
            <img src="{{ $event->photoUrl() }}" alt="{{ $event->sportLabel() }}" class="h-full w-full object-cover" />
            <x-ui.image-pill :label="$event->sportLabel()" />
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <h2 class="text-lg font-bold text-ink">{{ $event->title }}</h2>
                <x-ui.badge :tone="$tone" class="shrink-0">{{ $chip }}</x-ui.badge>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        @if ($event->isPoll())
            <x-ui.info-row variant="time">{{ $event->whenLabel() }}</x-ui.info-row>
        @else
            <x-ui.info-row variant="date">{{ $event->whenLabel() }}</x-ui.info-row>
        @endif
        <x-ui.info-row variant="place" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
        <x-ui.info-row variant="spots">{{ $event->playersLabel() }}</x-ui.info-row>
    </div>

    <div class="grid gap-2">
        @if ($tab === 'hosting')
            <x-ui.button :href="route('events.show', $event)" block>Open roster</x-ui.button>
            <x-ui.button variant="secondary" :href="route('events.edit', $event)" block>Edit</x-ui.button>
            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Cancel this game?');">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger" block>Cancel game</x-ui.button>
            </form>
        @else
            @if ($rsvp)
                <form method="POST" action="{{ route('events.rsvp', $event) }}">
                    @csrf
                    <x-ui.button type="submit" name="status" value="{{ $rsvp['value'] }}" :variant="$rsvp['variant']" block>{{ $rsvp['label'] }}</x-ui.button>
                </form>
            @endif
            <x-ui.button variant="secondary" :href="route('events.show', $event)" block>Open</x-ui.button>
        @endif
    </div>
</x-ui.card>
