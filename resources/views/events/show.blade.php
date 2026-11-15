@php
    $groups = [
        'going' => ['label' => 'Going', 'people' => $event->participants->where('pivot.status', 'going')],
        'waitlist' => [
            'label' => 'Waitlist',
            'people' => $event->participants
                ->where('pivot.status', 'waitlist')
                ->sortBy([
                    fn ($person) => $person->pivot->created_at?->getTimestamp() ?? 0,
                    fn ($person) => $person->pivot->id,
                ])
                ->values(),
        ],
        'maybe' => ['label' => 'Maybe', 'people' => $event->participants->where('pivot.status', 'maybe')],
        'not_going' => ['label' => 'Not going', 'people' => $event->participants->where('pivot.status', 'not_going')],
    ];
    $goingCount = $groups['going']['people']->count();
    $full = $event->max_participants !== null && $goingCount >= $event->max_participants;
@endphp

<x-app-layout
    :title="$preview->title"
    :description="$preview->description"
    :image="$preview->image"
    :url="$preview->url"
>
    <x-slot name="header">
        <x-ui.page-header :title="$event->title" subtitle="Hosted by {{ $event->creator->name }}">
            <x-slot name="actions">
                <x-ui.sport-chip :event="$event" />
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
        <x-ui.card class="overflow-hidden">
            <x-illustrations.sport :sport="$event->sportSelection()" class="h-40 w-full" />
            <div class="space-y-4 p-4 sm:p-6">
                @if ($event->isPoll())
                    <x-ui.info-row variant="time" label="When">Friends are voting on a time</x-ui.info-row>
                @else
                    <x-ui.info-row variant="date" label="When">{{ $event->event_date->format('l j F Y, H:i') }}</x-ui.info-row>
                @endif
                <x-ui.info-row variant="place" label="Where" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
                <x-ui.info-row variant="spots" label="Places">
                    @if ($event->isPoll())
                        @if ($event->max_participants)
                            Up to {{ $event->max_participants }} can go once a time is locked
                        @else
                            No limit once a time is locked
                        @endif
                    @else
                        <span>{{ $event->attendanceLabel() }}</span>
                        @if ($event->isFull())
                            <x-ui.badge preset="full" />
                        @endif
                    @endif
                </x-ui.info-row>
                <x-ui.activity-facts :event="$event" />
                <x-ui.badge :tone="$event->visibility === 'public' ? 'pitch' : 'neutral'">
                    {{ $event->visibility === 'public' ? 'Public game' : 'Private game' }}
                </x-ui.badge>
                @if ($event->description)
                    <p class="whitespace-pre-line text-sm text-ink">{{ $event->description }}</p>
                @endif
            </div>
        </x-ui.card>

        @unless ($event->isPoll())
            <x-ui.card class="space-y-2 p-4 sm:p-6">
                <h2 class="font-semibold text-ink">Add to calendar</h2>
                <x-ui.button variant="secondary" :href="route('events.calendar', $event)" class="w-full">Download .ics</x-ui.button>
                <x-ui.button variant="secondary" :href="\App\Support\GameCalendar::for($event)->googleUrl()" target="_blank" rel="noopener noreferrer" class="w-full">Google Calendar</x-ui.button>
            </x-ui.card>
        @endunless

        @if ($event->isPoll())
            @include('events.partials.time-poll')
        @else
            <x-ui.card class="space-y-3 p-4 sm:p-6">
                <h2 class="font-semibold text-ink">Your RSVP</h2>
                @php($offerWaitlist = $full && $userStatus !== 'going')
                @if ($offerWaitlist)
                    <p class="text-sm text-warn">This game is full. Join the waitlist, or say Maybe or Not going.</p>
                    @if ($userStatus === 'waitlist')
                        <p class="text-sm text-ink">You are {{ $event->waitlistPosition(auth()->user()) }} on the waitlist.</p>
                    @endif
                @endif
                <x-ui.error :messages="$errors->get('status')" />
                <form method="POST" action="{{ route('events.rsvp', $event) }}" class="space-y-2">
                    @csrf
                    @if ($offerWaitlist)
                        <x-ui.button
                            type="submit"
                            name="status"
                            value="waitlist"
                            :variant="$userStatus === 'waitlist' ? 'primary' : 'secondary'"
                            class="w-full"
                        >Join waitlist</x-ui.button>
                    @endif
                    <div @class([
                        'grid gap-2',
                        'grid-cols-2' => $offerWaitlist,
                        'grid-cols-3' => ! $offerWaitlist,
                    ])>
                        @foreach (['going' => 'Going', 'maybe' => 'Maybe', 'not_going' => 'Not going'] as $value => $label)
                            @continue($offerWaitlist && $value === 'going')
                            <x-ui.button
                                type="submit"
                                name="status"
                                value="{{ $value }}"
                                :variant="$userStatus === $value ? 'primary' : 'secondary'"
                                class="w-full px-2"
                            >{{ $label }}</x-ui.button>
                        @endforeach
                    </div>
                </form>
            </x-ui.card>
        @endif

        @include('events.partials.share')

        @if ($event->isCreator(auth()->user()))
            <x-ui.card class="space-y-3 p-4 sm:p-6">
                <h2 class="font-semibold text-ink">Your game</h2>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <x-ui.button variant="secondary" :href="route('events.edit', $event)">Edit game</x-ui.button>
                    @unless ($event->isPoll())
                        <form method="POST" action="{{ route('events.repeat', $event) }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" class="w-full">Same time next week</x-ui.button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Cancel this game?');">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" class="w-full">Cancel game</x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        @endif

        @unless ($event->isPoll())
            <x-ui.section title="Participants">
                <x-ui.card class="space-y-4 p-4 sm:p-6">
                    @foreach ($groups as $group)
                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ $group['label'] }} · {{ $group['people']->count() }}</p>
                            @if ($group['people']->isEmpty())
                                <p class="text-sm text-ink-faint">No one yet</p>
                            @else
                                <ul class="space-y-2">
                                    @foreach ($group['people'] as $person)
                                        <li class="flex items-center gap-2 text-sm text-ink">
                                            <x-ui.avatar :name="$person->name" :user="$person" class="h-7 w-7 text-[11px]" />
                                            @if ($group['label'] === 'Waitlist')
                                                {{ $loop->iteration }}.
                                            @endif
                                            {{ $person->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </x-ui.card>
            </x-ui.section>
        @endunless

        @include('events.partials.comments')
    </div>
</x-app-layout>
