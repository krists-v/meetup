@php
    $mine = $event->participants->first();
    $status = $tab === 'playing' ? $mine?->pivot->status : null;
    $chip = match ($status) {
        'going' => "You're in",
        'maybe' => 'Maybe',
        'waitlist' => 'Waitlist',
        'requested', 'invited' => 'Requested',
        'not_going' => "Can't make it",
        default => 'Hosting',
    };
    $tone = match ($status) {
        'going' => 'pitch',
        'waitlist', 'requested', 'invited' => 'warn',
        default => 'neutral',
    };
@endphp

<x-ui.card class="space-y-4">
    <div class="flex items-start gap-3">
        <img src="{{ $event->photoUrl() }}" alt="{{ $event->sportLabel() }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover" />
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
            @unless ($event->isPoll())
                <form method="POST" action="{{ route('events.rsvp', $event) }}">
                    @csrf
                    @if ($status === 'going')
                        <x-ui.button type="submit" name="status" value="not_going" variant="secondary" block>Can't make it</x-ui.button>
                    @else
                        <x-ui.button type="submit" name="status" value="going" block>I'm in</x-ui.button>
                    @endif
                </form>
            @endunless
            <x-ui.button variant="secondary" :href="route('events.show', $event)" block>Open</x-ui.button>
        @endif
    </div>
</x-ui.card>
