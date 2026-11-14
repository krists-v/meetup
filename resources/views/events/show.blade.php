@php
    $tone = match ($event->sport_type) {
        'football' => 'bg-emerald-100 text-emerald-800',
        'tennis' => 'bg-lime-100 text-lime-800',
        'basketball' => 'bg-orange-100 text-orange-800',
        default => 'bg-slate-100 text-slate-700',
    };
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
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $event->title }}</h2>
                <p class="mt-1 text-sm text-gray-500">Organised by {{ $event->creator->name }}</p>
            </div>
            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $tone }}">{{ $event->sportLabel() }}</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-4">
            @if (session('status'))
                <p class="rounded-md bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</p>
            @endif

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6 space-y-2 text-sm">
                <p><span class="text-gray-500">When</span><br><span class="text-base text-gray-900">{{ $event->event_date->format('l j F Y, H:i') }}</span></p>
                <p><span class="text-gray-500">Where</span><br><span class="text-base text-gray-900">{{ $event->location }}</span></p>
                <p>
                    <span class="text-gray-500">Places</span><br>
                    <span class="text-base text-gray-900">
                        {{ $goingCount }} going
                        @if ($event->max_participants)
                            of {{ $event->max_participants }}
                        @else
                            · no limit
                        @endif
                    </span>
                </p>
                <p class="text-gray-500">{{ $event->visibility === 'public' ? 'Public event' : 'Private event' }}</p>
                @if ($event->description)
                    <p class="pt-2 text-gray-800 whitespace-pre-line">{{ $event->description }}</p>
                @endif
            </section>

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="font-semibold text-gray-900">Your RSVP</h3>
                @if ($full && $userStatus !== 'going')
                    <p class="mt-2 text-sm text-amber-800">This event is full. You can still say Maybe or Not going.</p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
                <form method="POST" action="{{ route('events.rsvp', $event) }}" class="mt-3 grid grid-cols-3 gap-2">
                    @csrf
                    @foreach (['going' => 'Going', 'maybe' => 'Maybe', 'not_going' => 'Not going'] as $value => $label)
                        <button
                            type="submit"
                            name="status"
                            value="{{ $value }}"
                            @disabled($value === 'going' && $full && $userStatus !== 'going')
                            class="px-2 py-3 rounded-md text-sm font-semibold border {{ $userStatus === $value ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-800 border-gray-300' }} disabled:opacity-40"
                        >{{ $label }}</button>
                    @endforeach
                </form>
            </section>

            @if ($event->isCreator(auth()->user()))
                <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6 space-y-3">
                    <h3 class="font-semibold text-gray-900">Invite link</h3>
                    <p class="text-sm text-gray-600">Paste this into WhatsApp. Friends can open it after they log in, including when the event is private.</p>
                    <p class="break-all rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800">{{ $inviteUrl }}</p>
                    <button
                        type="button"
                        class="w-full sm:w-auto px-4 py-3 rounded-md bg-gray-900 text-white text-sm font-semibold"
                        onclick="navigator.clipboard.writeText(@js($inviteUrl)); this.textContent = 'Copied';"
                    >Copy invite link</button>
                    <div class="flex flex-col sm:flex-row gap-2 pt-2">
                        <a href="{{ route('events.edit', $event) }}" class="inline-flex justify-center px-4 py-3 rounded-md border border-gray-300 text-sm font-semibold">Edit event</a>
                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Cancel this event?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-3 rounded-md border border-red-300 text-red-700 text-sm font-semibold">Cancel event</button>
                        </form>
                    </div>
                </section>
            @endif

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">Participants</h3>
                @foreach ($groups as $group)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $group['label'] }} · {{ $group['people']->count() }}</p>
                        @if ($group['people']->isEmpty())
                            <p class="mt-1 text-sm text-gray-400">No one yet</p>
                        @else
                            <ul class="mt-1 text-sm text-gray-800">
                                @foreach ($group['people'] as $person)
                                    <li>{{ $person->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
