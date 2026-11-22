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
    <x-ui.page-header :title="$event->title" />

    <div class="mt-6 space-y-4">
        <article class="overflow-hidden rounded-card border border-line bg-white shadow-card" style="--vt-name: game-{{ $event->id }}">
            <div class="px-3 pt-3">
                <div class="relative overflow-hidden rounded-2xl">
                    <img src="{{ $event->photoUrl() }}" alt="{{ $event->sportLabel() }}" class="h-56 w-full object-cover" />
                    <x-ui.image-pill :label="$event->sportLabel()" />
                </div>
            </div>
            <div class="space-y-4 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xl font-bold tracking-tight text-ink">{{ $event->title }}</h2>
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
                    <x-ui.info-row variant="spots">
                        <span @class(['motion-tick' => $justJoined])>{{ $event->playersLabel() }}</span>
                    </x-ui.info-row>
                    <x-ui.info-row variant="level">{{ $event->levelLabel() }}</x-ui.info-row>
                </div>
                <div class="border-t border-line pt-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-base text-ink-muted">Hosted by {{ $event->creator->name }}</p>
                        @if ($event->priceLabel())
                            <p class="text-base font-bold text-ink">{{ $event->priceLabel() }}</p>
                        @endif
                    </div>
                </div>
                <div class="space-y-3">
                    <x-ui.info-row :variant="$event->isIndoor() ? 'indoor' : 'outdoor'">
                        {{ $event->isIndoor() ? 'Indoors' : 'Outdoors' }}
                    </x-ui.info-row>
                    @if ($event->welcomesBeginners())
                        <x-ui.badge preset="beginners" />
                    @endif
                    @if ($event->isPoll())
                        <x-ui.info-row variant="spots">
                            @if ($event->max_participants)
                                Up to {{ $event->max_participants }} can play, once a time is set
                            @else
                                No limit, once a time is set
                            @endif
                        </x-ui.info-row>
                    @endif
                    <x-ui.badge tone="neutral">
                        {{ $event->visibility === 'public' ? 'Public game' : 'Private game' }}
                    </x-ui.badge>
                    @if ($event->description)
                        <p class="whitespace-pre-line text-base text-ink">{{ $event->description }}</p>
                    @endif
                </div>
            </div>
        </article>

        @if ($userStatus === 'expired')
            <p class="text-base text-ink-muted" role="status" data-request-expired="1">Request expired</p>
        @endif

        @if ($event->isPoll())
            @include('events.partials.time-poll')
        @else
            <x-ui.card class="space-y-3">
                <h2 class="text-lg font-bold text-ink">Are you in?</h2>
                @if ($offerWaitlist)
                    <p class="text-base text-warn">It's full. Join the waitlist, or say maybe.</p>
                    @if ($userStatus === 'waitlist')
                        <p class="text-base text-ink">{{ 'You\'re '.$event->waitlistPosition(auth()->user()).' on the waitlist.' }}</p>
                    @endif
                @endif
                <x-ui.error :messages="$errors->get('status')" />
                @if ($userStatus === 'maybe')
                    <p class="text-base text-ink-muted">You're a maybe.</p>
                @elseif ($userStatus === 'not_going')
                    <p class="text-base text-ink-muted">You said not going.</p>
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
                            <x-ui.button type="submit" name="status" value="{{ $rsvpValue }}" block>{{ $rsvpLabel }}</x-ui.button>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['maybe' => 'Maybe', 'not_going' => 'Not going'] as $value => $label)
                            <x-ui.button
                                type="submit"
                                name="status"
                                value="{{ $value }}"
                                variant="secondary"
                                block
                                class="px-2"
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
                    <x-ui.button type="submit" form="rsvp-form" name="status" value="{{ $rsvpValue }}" block>{{ $rsvpLabel }}</x-ui.button>
                @endif
            </div>
            <div class="h-16 sm:hidden" aria-hidden="true"></div>
        @endif

        @unless ($event->isPoll())
            <x-ui.section title="Who's going">
                <x-ui.card class="space-y-4">
                    @if ($groups['going']['people']->isEmpty())
                        <p class="text-base text-ink-muted">No one yet. Be the first in.</p>
                    @else
                        <ul class="space-y-4">
                            @foreach ($groups['going']['people'] as $person)
                                <li @class(['space-y-2', 'motion-avatar' => $justJoined && $person->is(auth()->user())])>
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :name="$person->name" :user="$person" />
                                        <div class="min-w-0">
                                            <a href="{{ route('players.show', $person) }}" class="block truncate text-base font-medium text-ink">{{ $person->name }}</a>
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @if ($person->sportLevelLabel($event->sportSelection()))
                                                    <x-ui.badge tone="neutral" class="px-2 py-0.5" data-player-level="{{ $person->sportLevel($event->sportSelection()) }}">{{ $person->sportLevelLabel($event->sportSelection()) }}</x-ui.badge>
                                                @endif
                                                <x-ui.badge tone="neutral" class="px-2 py-0.5" data-attendance="{{ $person->attendanceLabel() }}">{{ $person->attendanceLabel() }}</x-ui.badge>
                                            </div>
                                        </div>
                                    </div>
                                    @include('players.partials.actions', ['player' => $person, 'prefix' => 'going'])
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @foreach (['waitlist', 'maybe', 'not_going'] as $key)
                        @continue($groups[$key]['people']->isEmpty())
                        <div class="space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-wide text-ink-muted">{{ $groups[$key]['label'] }} · {{ $groups[$key]['people']->count() }}</p>
                            <ul class="space-y-2">
                                @foreach ($groups[$key]['people'] as $person)
                                    <li class="space-y-2 text-base text-ink">
                                        <div class="flex min-h-11 items-center gap-2">
                                            <x-ui.avatar :name="$person->name" :user="$person" />
                                            @if ($key === 'waitlist')
                                                {{ $loop->iteration }}.
                                            @endif
                                            <a href="{{ route('players.show', $person) }}" class="font-medium">{{ $person->name }}</a>
                                        </div>
                                        @include('players.partials.actions', ['player' => $person, 'prefix' => $key])
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </x-ui.card>
            </x-ui.section>
        @endunless

        <x-ui.card class="space-y-3">
            <h2 class="text-lg font-bold text-ink">Host</h2>
            <div class="flex items-center gap-3">
                <x-ui.avatar :name="$event->creator->name" :user="$event->creator" />
                <div class="min-w-0">
                    <a href="{{ route('players.show', $event->creator) }}" class="block truncate text-base font-semibold text-ink">{{ $event->creator->name }}</a>
                    <p class="text-base text-ink-muted">Hosting this game</p>
                </div>
            </div>
            @include('players.partials.actions', ['player' => $event->creator, 'prefix' => 'host'])
            @if ($event->isCreator(auth()->user()))
                <div class="grid gap-2">
                    <x-ui.button variant="secondary" :href="route('events.edit', $event)" block>Edit game</x-ui.button>
                    @unless ($event->isPoll())
                        <form method="POST" action="{{ route('events.repeat', $event) }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" block>Same time next week</x-ui.button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Cancel this game?');">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" block>Cancel game</x-ui.button>
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
