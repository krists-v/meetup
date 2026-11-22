@php
    $requests = $event->participants->where('pivot.status', 'requested');
    $going = $event->participants->where('pivot.status', 'going');
    $started = ! $event->isPoll() && $event->event_date->lte(now());
@endphp

<x-ui.section title="Roster" description="Confirm a request, or take someone off the list.">
    <x-ui.card class="space-y-6 p-4 sm:p-6" data-roster="1">
        <x-ui.error :messages="$errors->get('roster')" />

        <div class="space-y-3">
            <h3 class="text-base font-bold text-ink">Requests</h3>
            @if ($requests->isEmpty())
                <p class="text-sm text-ink-muted">No requests.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($requests as $person)
                        <li class="flex flex-col gap-3 border-t border-line pt-3 first:border-t-0 first:pt-0 sm:flex-row sm:items-center sm:justify-between" data-roster-request="{{ $person->id }}">
                            <span class="flex min-h-11 items-center gap-2 text-base text-ink">
                                <x-ui.avatar :name="$person->name" :user="$person" class="h-8 w-8" />
                                {{ $person->name }}
                            </span>
                            <span class="flex flex-col gap-2 sm:flex-row">
                                <form method="POST" action="{{ route('events.roster.confirm', [$event, $person]) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" class="w-full">Confirm</x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('events.roster.decline', [$event, $person]) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="danger" class="w-full">Decline</x-ui.button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="space-y-3">
            <h3 class="text-base font-bold text-ink">Going</h3>
            @if ($started)
                <p class="text-sm text-ink-muted">Mark who showed up.</p>
            @endif
            @if ($going->isEmpty())
                <p class="text-sm text-ink-muted">No one on the list yet.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($going as $person)
                        <li class="flex flex-col gap-3 border-t border-line pt-3 first:border-t-0 first:pt-0" data-roster-player="{{ $person->id }}">
                            <span class="flex min-h-11 items-center gap-2 text-base text-ink">
                                <x-ui.avatar :name="$person->name" :user="$person" class="h-8 w-8" />
                                {{ $person->name }}
                                @if ($person->pivot->attended === true)
                                    <x-ui.badge>Showed up</x-ui.badge>
                                @elseif ($person->pivot->attended === false)
                                    <x-ui.badge tone="warn">No-show</x-ui.badge>
                                @endif
                            </span>
                            <span class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                @unless ($started || $event->isCreator($person))
                                    <form method="POST" action="{{ route('events.roster.remove', [$event, $person]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" class="w-full">Remove</x-ui.button>
                                    </form>
                                @endunless
                                @if ($started)
                                    <form method="POST" action="{{ route('events.roster.attendance', [$event, $person]) }}" class="flex flex-col gap-2 sm:flex-row">
                                        @csrf
                                        <x-ui.button type="submit" name="attended" value="1" variant="secondary" class="w-full">Showed up</x-ui.button>
                                        <x-ui.button type="submit" name="attended" value="0" variant="secondary" class="w-full">No-show</x-ui.button>
                                    </form>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-ui.card>
</x-ui.section>
