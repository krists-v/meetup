@php
    $groups = [
        'going' => ['label' => 'Going', 'people' => $event->participants->where('pivot.status', 'going')],
        'maybe' => ['label' => 'Maybe', 'people' => $event->participants->where('pivot.status', 'maybe')],
        'not_going' => ['label' => 'Not going', 'people' => $event->participants->where('pivot.status', 'not_going')],
    ];
    $goingCount = $groups['going']['people']->count();
    $full = $event->max_participants !== null && $goingCount >= $event->max_participants;
    $inviteUrl = route('events.invite', $event->invite_token);
@endphp

<x-app-layout>
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
                <div class="flex gap-3">
                    <x-ui.icon name="calendar" class="mt-0.5 text-pitch-700" />
                    <div>
                        <p class="text-sm text-ink-muted">When</p>
                        @if ($event->isPoll())
                            <p class="text-base text-ink">Friends are voting on a time</p>
                        @else
                            <p class="text-base text-ink">{{ $event->event_date->format('l j F Y, H:i') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex gap-3">
                    <x-ui.icon name="map-pin" class="mt-0.5 text-pitch-700" />
                    <div>
                        <p class="text-sm text-ink-muted">Where</p>
                        <p class="text-base text-ink">{{ $event->location }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <x-ui.icon name="users" class="mt-0.5 text-pitch-700" />
                    <div>
                        <p class="text-sm text-ink-muted">Places</p>
                        <p class="text-base text-ink">
                            @if ($event->isPoll())
                                @if ($event->max_participants)
                                    Up to {{ $event->max_participants }} can go once a time is locked
                                @else
                                    No limit once a time is locked
                                @endif
                            @else
                                {{ $goingCount }} going
                                @if ($event->max_participants)
                                    of {{ $event->max_participants }}
                                @else
                                    · no limit
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
                <x-ui.badge :tone="$event->visibility === 'public' ? 'pitch' : 'neutral'">
                    {{ $event->visibility === 'public' ? 'Public game' : 'Private game' }}
                </x-ui.badge>
                @if ($event->description)
                    <p class="whitespace-pre-line text-sm text-ink">{{ $event->description }}</p>
                @endif
            </div>
        </x-ui.card>

        @if ($event->isPoll())
            @include('events.partials.time-poll')
        @else
            <x-ui.card class="space-y-3 p-4 sm:p-6">
                <h2 class="font-semibold text-ink">Your RSVP</h2>
                @if ($full && $userStatus !== 'going')
                    <p class="text-sm text-warn">This game is full. You can still say Maybe or Not going.</p>
                @endif
                <x-ui.error :messages="$errors->get('status')" />
                <form method="POST" action="{{ route('events.rsvp', $event) }}" class="grid grid-cols-3 gap-2">
                    @csrf
                    @foreach (['going' => 'Going', 'maybe' => 'Maybe', 'not_going' => 'Not going'] as $value => $label)
                        <x-ui.button
                            type="submit"
                            name="status"
                            value="{{ $value }}"
                            :variant="$userStatus === $value ? 'primary' : 'secondary'"
                            class="w-full px-2"
                            :disabled="$value === 'going' && $full && $userStatus !== 'going'"
                        >{{ $label }}</x-ui.button>
                    @endforeach
                </form>
            </x-ui.card>
        @endif

        @if ($event->isCreator(auth()->user()))
            <x-ui.card class="space-y-3 p-4 sm:p-6">
                <div class="flex items-start gap-3">
                    <x-illustrations.empty variant="invites" class="hidden h-16 w-24 sm:block" />
                    <div class="space-y-1">
                        <h2 class="font-semibold text-ink">Invite link</h2>
                        <p class="text-sm text-ink-muted">Paste this into WhatsApp. Anyone with the link can open the game after they log in, including a private one.</p>
                    </div>
                </div>
                <p class="break-all rounded-control bg-canvas px-3 py-2 text-sm text-ink ring-1 ring-line">{{ $inviteUrl }}</p>
                <x-ui.button
                    type="button"
                    class="w-full sm:w-auto"
                    onclick="navigator.clipboard.writeText(@js($inviteUrl)); this.textContent = 'Copied';"
                >Copy invite link</x-ui.button>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <x-ui.button variant="secondary" :href="route('events.edit', $event)">Edit game</x-ui.button>
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
                                            <x-ui.avatar :name="$person->name" class="h-7 w-7 text-[11px]" />
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
    </div>
</x-app-layout>
