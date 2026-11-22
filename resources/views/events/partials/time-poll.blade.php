@php
    $best = $event->bestTimeOption();
    $userId = auth()->id();
@endphp

<x-ui.section title="Pick a time" description="Say yes, maybe, or no. The best time is the one most people can make.">
    <x-illustrations.empty variant="votes" class="h-24 w-full" />
    <x-ui.error :messages="$errors->get('vote')" />
    <x-ui.error :messages="$errors->get('option')" />

    @foreach ($event->timeOptions as $option)
        @php
            $mine = $option->votes->firstWhere('user_id', $userId);
            $isBest = $best && $best->is($option);
        @endphp
        <x-ui.card @class(['space-y-3 p-4', 'outline outline-2 outline-success' => $isBest, 'motion-pop' => (int) session('voted') === $option->id])>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-ink">{{ $option->starts_at->format('D j M, H:i') }}</h3>
                    <p @class(['mt-1 text-xs text-ink-muted', 'motion-tick' => (int) session('voted') === $option->id])>
                        {{ $option->voteCount('yes') }} yes · {{ $option->voteCount('maybe') }} maybe · {{ $option->voteCount('no') }} no
                    </p>
                </div>
                @if ($isBest)
                    <x-ui.badge>Best time</x-ui.badge>
                @endif
            </div>

            <ul class="divide-y divide-line text-sm text-ink">
                @forelse ($option->votes->sortBy('user.name') as $vote)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <span class="flex items-center gap-2">
                            <x-ui.avatar :name="$vote->user->name" :user="$vote->user" class="h-7 w-7 text-[11px]" />
                            {{ $vote->user->name }}
                        </span>
                        <span @class([
                            'text-xs font-semibold uppercase tracking-wide',
                            'text-success' => $vote->vote === 'yes',
                            'text-warn' => $vote->vote === 'maybe',
                            'text-ink-faint' => $vote->vote === 'no',
                        ])>{{ $vote->vote }}</span>
                    </li>
                @empty
                    <li class="py-2 text-ink-faint">No one yet.</li>
                @endforelse
            </ul>

            @if ($option->hasPassed())
                <p class="text-sm text-ink-muted">This time has passed.</p>
            @else
                <form method="POST" action="{{ route('events.options.vote', [$event, $option]) }}" class="grid grid-cols-3 gap-2">
                    @csrf
                    @foreach (['yes' => 'Yes', 'maybe' => 'Maybe', 'no' => 'No'] as $value => $label)
                        <x-ui.button
                            type="submit"
                            name="vote"
                            value="{{ $value }}"
                            :variant="$mine?->vote === $value ? 'primary' : 'secondary'"
                            class="w-full px-2"
                        >{{ $label }}</x-ui.button>
                    @endforeach
                </form>

                @if ($event->isCreator(auth()->user()))
                    <form method="POST" action="{{ route('events.options.lock', [$event, $option]) }}">
                        @csrf
                        <x-ui.button type="submit" class="w-full">Set this time</x-ui.button>
                    </form>
                @endif
            @endif
        </x-ui.card>
    @endforeach
</x-ui.section>
