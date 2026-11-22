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
    $offerWaitlist = ! $event->isPoll() && $full && $userStatus !== 'going';
    $rsvpValue = 'going';
    $rsvpLabel = "I'm in";
    $rsvpSettled = false;
    $justJoined = session('joined') === true;
    if ($userStatus === 'going') {
        $rsvpLabel = "You're in";
        $rsvpSettled = true;
    } elseif ($offerWaitlist) {
        $rsvpValue = 'waitlist';
        $rsvpLabel = 'Join the waitlist';
    }
@endphp

<x-app-layout
    :title="$preview->title"
    :description="$preview->description"
    :image="$preview->image"
    :url="$preview->url"
>
    <div class="space-y-4">
        <article class="overflow-hidden rounded-card border border-line bg-white" style="--vt-name: game-{{ $event->id }}">
            <div class="relative px-3 pt-3">
                <img src="{{ $event->photoUrl() }}" alt="{{ $event->sportLabel() }}" class="h-56 w-full rounded-2xl object-cover" />
                <span class="absolute bottom-3 left-6 rounded-full bg-white/75 px-3 py-1 text-sm font-medium text-ink backdrop-blur-md">{{ $event->areaLabel() }}</span>
            </div>
            <div class="space-y-4 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-ink">{{ $event->title }}</h1>
                    @if ($event->spotsLeftLabel())
                        <x-ui.badge class="shrink-0" :tone="$event->isFull() || $event->isPoll() ? 'warn' : 'pitch'">{{ $event->spotsLeftLabel() }}</x-ui.badge>
                    @endif
                </div>
                @if ($event->isPoll())
                    <x-ui.info-row variant="time" label="When">{{ $event->whenLabel() }}</x-ui.info-row>
                @else
                    <x-ui.info-row variant="date" label="When">{{ $event->whenLabel() }}</x-ui.info-row>
                @endif
                <x-ui.info-row variant="place" label="Where" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
                <x-ui.info-row variant="spots" label="Players">
                    <span @class(['motion-tick' => $justJoined])>{{ $event->playersLabel() }}</span>
                </x-ui.info-row>
                @if ($event->priceLabel())
                    <p class="text-lg font-bold text-ink">{{ $event->priceLabel() }}</p>
                @endif
                @if ($event->isPoll())
                    <x-ui.info-row variant="spots" label="Spots">
                        @if ($event->max_participants)
                            Up to {{ $event->max_participants }} can play, once a time is set
                        @else
                            No limit, once a time is set
                        @endif
                    </x-ui.info-row>
                @endif
                <x-ui.activity-facts :event="$event" />
                <x-ui.badge :tone="$event->visibility === 'public' ? 'pitch' : 'neutral'">
                    {{ $event->visibility === 'public' ? 'Public game' : 'Private game' }}
                </x-ui.badge>
                @if ($event->description)
                    <p class="whitespace-pre-line text-sm text-ink">{{ $event->description }}</p>
                @endif
            </div>
        </article>

        @if ($userStatus === 'expired')
            <p class="text-sm text-ink-muted" role="status" data-request-expired="1">Request expired</p>
        @endif

        @if ($event->isPoll())
            @include('events.partials.time-poll')
        @else
            <x-ui.card class="space-y-3 p-4 sm:p-6">
                <h2 class="font-semibold text-ink">Are you in?</h2>
                @if ($offerWaitlist)
                    <p class="text-sm text-warn">It's full. Join the waitlist, or say maybe.</p>
                    @if ($userStatus === 'waitlist')
                        <p class="text-sm text-ink">{{ 'You\'re '.$event->waitlistPosition(auth()->user()).' on the waitlist.' }}</p>
                    @endif
                @endif
                <x-ui.error :messages="$errors->get('status')" />
                @if ($userStatus === 'maybe')
                    <p class="text-sm text-ink-muted">You're a maybe.</p>
                @elseif ($userStatus === 'not_going')
                    <p class="text-sm text-ink-muted">You said not going.</p>
                @endif
                <form id="rsvp-form" method="POST" action="{{ route('events.rsvp', $event) }}" class="space-y-2">
                    @csrf
                    @if ($rsvpSettled)
                        <div @class(['relative hidden min-h-11 items-center justify-center gap-2 rounded-control bg-success-soft px-4 text-sm font-bold text-success sm:flex', 'motion-pop motion-joined' => $justJoined]) role="status">
                            @if ($rsvpLabel === "You're in")
                                @if ($justJoined)
                                    <span class="motion-burst {{ \App\Models\Event::accentInk($event->sportSelection()) }}" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>
                                @endif
                                <x-ui.icon name="check" class="h-4 w-4" />
                            @endif
                            {{ $rsvpLabel }}
                        </div>
                    @else
                        <div class="hidden sm:block">
                            <x-ui.button type="submit" name="status" value="{{ $rsvpValue }}" class="w-full">{{ $rsvpLabel }}</x-ui.button>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['maybe' => 'Maybe', 'not_going' => 'Not going'] as $value => $label)
                            <x-ui.button
                                type="submit"
                                name="status"
                                value="{{ $value }}"
                                variant="secondary"
                                class="w-full px-2"
                            >{{ $label }}</x-ui.button>
                        @endforeach
                    </div>
                </form>
            </x-ui.card>

            <div class="sticky-action fixed inset-x-0 z-40 border-t border-line bg-white p-3 sm:hidden" style="bottom: calc(3.5rem + env(safe-area-inset-bottom))">
                @if ($rsvpSettled)
                    <div @class(['relative flex min-h-11 w-full items-center justify-center gap-2 rounded-control bg-success-soft px-4 text-sm font-bold text-success', 'motion-pop motion-joined' => $justJoined]) role="status">
                        @if ($rsvpLabel === "You're in")
                            @if ($justJoined)
                                <span class="motion-burst {{ \App\Models\Event::accentInk($event->sportSelection()) }}" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>
                            @endif
                            <x-ui.icon name="check" class="h-4 w-4" />
                        @endif
                        {{ $rsvpLabel }}
                    </div>
                @else
                    <x-ui.button type="submit" form="rsvp-form" name="status" value="{{ $rsvpValue }}" class="w-full">{{ $rsvpLabel }}</x-ui.button>
                @endif
            </div>
            <div class="h-16 sm:hidden" aria-hidden="true"></div>
        @endif

        @unless ($event->isPoll())
            <x-ui.section title="Who's going">
                <x-ui.card class="space-y-4 p-4 sm:p-6">
                    @if ($groups['going']['people']->isEmpty())
                        <p class="text-sm text-ink-muted">No one yet. Be the first in.</p>
                    @else
                        <ul class="flex flex-wrap gap-4">
                            @foreach ($groups['going']['people'] as $person)
                                <li @class(['flex max-w-28 flex-col items-center gap-1 text-center', 'motion-avatar' => $justJoined && $person->is(auth()->user())])>
                                    <x-ui.avatar :name="$person->name" :user="$person" class="h-11 w-11" />
                                    <span class="w-full truncate text-xs text-ink">{{ $person->name }}</span>
                                    @if ($person->sportLevelLabel($event->sportSelection()))
                                        <x-ui.badge tone="neutral" class="px-2 py-0.5" data-player-level="{{ $person->sportLevel($event->sportSelection()) }}">{{ $person->sportLevelLabel($event->sportSelection()) }}</x-ui.badge>
                                    @endif
                                    <x-ui.badge tone="neutral" class="px-2 py-0.5" data-attendance="{{ $person->attendanceLabel() }}">{{ $person->attendanceLabel() }}</x-ui.badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @foreach (['waitlist', 'maybe', 'not_going'] as $key)
                        @continue($groups[$key]['people']->isEmpty())
                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ $groups[$key]['label'] }} · {{ $groups[$key]['people']->count() }}</p>
                            <ul class="space-y-2">
                                @foreach ($groups[$key]['people'] as $person)
                                    <li class="flex min-h-11 items-center gap-2 text-sm text-ink">
                                        <x-ui.avatar :name="$person->name" :user="$person" class="h-7 w-7 text-[11px]" />
                                        @if ($key === 'waitlist')
                                            {{ $loop->iteration }}.
                                        @endif
                                        {{ $person->name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </x-ui.card>
            </x-ui.section>
        @endunless

        <x-ui.card class="space-y-3 p-4 sm:p-6">
            <h2 class="font-semibold text-ink">Host</h2>
            <div class="flex items-center gap-3">
                <x-ui.avatar :name="$event->creator->name" :user="$event->creator" class="h-11 w-11" />
                <div>
                    <p class="font-semibold text-ink">{{ $event->creator->name }}</p>
                    <p class="text-sm text-ink-muted">Hosting this game</p>
                </div>
            </div>
            @if ($event->isCreator(auth()->user()))
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
            @endif
        </x-ui.card>

        @if ($event->isCreator(auth()->user()))
            @include('events.partials.roster')
        @endif

        @include('events.partials.share')

        @include('events.partials.comments')
    </div>
</x-app-layout>
