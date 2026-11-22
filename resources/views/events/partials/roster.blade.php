@php
    $requests = $event->participants->where('pivot.status', 'requested');
    $going = $event->participants->where('pivot.status', 'going');
    $started = ! $event->isPoll() && $event->event_date->lte(now());
@endphp

<x-ui.section title="Roster" description="Confirm a request, or take someone off the list.">
    <x-ui.card class="space-y-6" data-roster="1">
        <x-ui.error :messages="$errors->get('roster')" />

        <div class="space-y-3">
            <h3 class="text-lg font-bold text-ink">Requests</h3>
            @if ($requests->isEmpty())
                <p class="text-base text-ink-muted">No requests.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($requests as $person)
                        <li class="flex flex-col gap-3 border-t border-line pt-3 first:border-t-0 first:pt-0 sm:flex-row sm:items-center sm:justify-between" data-roster-request="{{ $person->id }}">
                            <span class="flex min-h-11 items-center gap-2 text-base text-ink">
                                <x-ui.avatar :name="$person->name" :user="$person"  />
                                {{ $person->name }}
                            </span>
                            <div class="grid grid-cols-2 gap-2">
                                <form method="POST" action="{{ route('events.roster.confirm', [$event, $person]) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" block>Confirm</x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('events.roster.decline', [$event, $person]) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="danger" block>Decline</x-ui.button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="space-y-3">
            <h3 class="text-lg font-bold text-ink">Going</h3>
            @if ($started)
                <p class="text-base text-ink-muted">Mark who showed up.</p>
            @endif
            @if ($going->isEmpty())
                <p class="text-base text-ink-muted">No one on the list yet.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($going as $person)
                        <li class="flex flex-col gap-3 border-t border-line pt-3 first:border-t-0 first:pt-0" data-roster-player="{{ $person->id }}">
                            <span class="flex min-h-11 items-center gap-2 text-base text-ink">
                                <x-ui.avatar :name="$person->name" :user="$person"  />
                                {{ $person->name }}
                                @if ($person->pivot->attended === true)
                                    <x-ui.badge>Showed up</x-ui.badge>
                                @elseif ($person->pivot->attended === false)
                                    <x-ui.badge tone="warn">No-show</x-ui.badge>
                                @endif
                            </span>
                            <div class="grid gap-2">
                                @unless ($started || $event->isCreator($person))
                                    <form method="POST" action="{{ route('events.roster.remove', [$event, $person]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" block>Remove</x-ui.button>
                                    </form>
                                @endunless
                                @if ($started)
                                    <form method="POST" action="{{ route('events.roster.attendance', [$event, $person]) }}" class="grid grid-cols-2 gap-2">
                                        @csrf
                                        <x-ui.button type="submit" name="attended" value="1" variant="secondary" block>Showed up</x-ui.button>
                                        <x-ui.button type="submit" name="attended" value="0" variant="secondary" block>No-show</x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-ui.card>
</x-ui.section>
