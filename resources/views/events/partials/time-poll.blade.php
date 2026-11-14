@php
    $best = $event->bestTimeOption();
    $userId = auth()->id();
@endphp

<section class="space-y-3">
    <h3 class="font-semibold text-gray-900">Availability</h3>
    <p class="text-sm text-gray-600">Vote Yes, Maybe, or No on each time. The best time is the one with the most Yes votes, then the most Maybe votes.</p>
    <x-input-error :messages="$errors->get('vote')" />

    @foreach ($event->timeOptions as $option)
        @php
            $mine = $option->votes->firstWhere('user_id', $userId);
            $isBest = $best && $best->is($option);
        @endphp
        <article class="bg-white rounded-lg shadow-sm p-4 space-y-3 {{ $isBest ? 'ring-2 ring-emerald-500' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h4 class="font-semibold text-gray-900">{{ $option->starts_at->format('D j M, H:i') }}</h4>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $option->voteCount('yes') }} yes · {{ $option->voteCount('maybe') }} maybe · {{ $option->voteCount('no') }} no
                    </p>
                </div>
                @if ($isBest)
                    <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Best time</span>
                @endif
            </div>

            <ul class="text-sm text-gray-800 divide-y divide-gray-100">
                @forelse ($option->votes->sortBy('user.name') as $vote)
                    <li class="flex items-center justify-between py-1.5">
                        <span>{{ $vote->user->name }}</span>
                        <span class="text-xs font-semibold uppercase tracking-wide {{ $vote->vote === 'yes' ? 'text-emerald-700' : ($vote->vote === 'maybe' ? 'text-amber-700' : 'text-gray-500') }}">
                            {{ $vote->vote }}
                        </span>
                    </li>
                @empty
                    <li class="py-1.5 text-gray-400">No votes yet</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('events.options.vote', [$event, $option]) }}" class="grid grid-cols-3 gap-2">
                @csrf
                @foreach (['yes' => 'Yes', 'maybe' => 'Maybe', 'no' => 'No'] as $value => $label)
                    <button
                        type="submit"
                        name="vote"
                        value="{{ $value }}"
                        class="px-2 py-3 rounded-md text-sm font-semibold border {{ $mine?->vote === $value ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-800 border-gray-300' }}"
                    >{{ $label }}</button>
                @endforeach
            </form>

            @if ($event->isCreator(auth()->user()))
                <form method="POST" action="{{ route('events.options.lock', [$event, $option]) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 rounded-md bg-emerald-700 text-white text-sm font-semibold">Lock in this time</button>
                </form>
            @endif
        </article>
    @endforeach
</section>
