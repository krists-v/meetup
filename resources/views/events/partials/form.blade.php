@php
    $editing = isset($event);
    $polling = $editing && $event->isPoll();
    $oldOptions = array_values(old('options', ['', '']));
    if (count($oldOptions) < 2) {
        $oldOptions = array_pad($oldOptions, 2, '');
    }
@endphp

<form method="POST" action="{{ $editing ? route('events.update', $event) : route('events.store') }}" class="space-y-5" @unless ($editing) x-data="{ schedule: @js(old('schedule_type', 'fixed')), times: @js($oldOptions) }" @endunless>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="space-y-2">
        <x-ui.label for="title" value="Title" />
        <x-ui.input id="title" name="title" type="text" :value="old('title', $editing ? $event->title : '')" required autofocus placeholder="Thursday five-a-side" />
        <x-ui.error :messages="$errors->get('title')" />
    </div>

    @include('events.partials.sport-picker')

    @if ($polling)
        <p class="rounded-control bg-warn-soft px-3 py-2 text-sm text-warn">Friends are voting on a time. Lock a time from the game page. The other details can still change.</p>
    @elseif ($editing)
        <div class="space-y-2">
            <x-ui.label for="event_date" value="Date and time" />
            <x-ui.input id="event_date" name="event_date" type="datetime-local" :value="old('event_date', $event->event_date->format('Y-m-d\TH:i'))" required />
            <x-ui.error :messages="$errors->get('event_date')" />
        </div>
    @else
        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-ink">When</legend>
            <div class="space-y-2">
                <label class="flex min-h-11 items-center gap-3 text-sm text-ink">
                    <input type="radio" name="schedule_type" value="fixed" class="border-line text-pitch-700 focus:ring-pitch-600" x-model="schedule" />
                    <span>Set a date</span>
                </label>
                <label class="flex min-h-11 items-center gap-3 text-sm text-ink">
                    <input type="radio" name="schedule_type" value="poll" class="border-line text-pitch-700 focus:ring-pitch-600" x-model="schedule" />
                    <span>Let friends vote on a time</span>
                </label>
            </div>
        </fieldset>

        <div x-show="schedule === 'fixed'" x-cloak class="space-y-2">
            <x-ui.label for="event_date" value="Date and time" />
            <x-ui.input id="event_date" name="event_date" type="datetime-local" :value="old('event_date')" x-bind:required="schedule === 'fixed'" />
            <x-ui.error :messages="$errors->get('event_date')" />
        </div>

        <div x-show="schedule === 'poll'" x-cloak class="space-y-3">
            <x-illustrations.empty variant="votes" class="h-24 w-full" />
            <x-ui.label value="Time options" />
            <template x-for="(time, index) in times" :key="index">
                <input type="datetime-local" class="block w-full rounded-control border-line text-base text-ink focus:border-pitch-600 focus:ring-pitch-600" x-model="times[index]" :name="'options[' + index + ']'" />
            </template>
            <x-ui.button type="button" variant="ghost" x-on:click="times.push('')">Add another time</x-ui.button>
            <p class="text-sm text-ink-muted">Add at least two different times. Friends vote Yes, Maybe, or No on each one.</p>
            <x-ui.error :messages="$errors->get('options')" />
        </div>
    @endif

    <div class="space-y-2">
        <x-ui.label for="location" value="Location" />
        <x-ui.input id="location" name="location" type="text" :value="old('location', $editing ? $event->location : '')" required placeholder="Park, court, or address" />
        <x-ui.error :messages="$errors->get('location')" />
    </div>

    <div class="space-y-2">
        <x-ui.label for="max_participants" value="Max players (optional)" />
        <x-ui.input id="max_participants" name="max_participants" type="number" min="2" :value="old('max_participants', $editing ? $event->max_participants : '')" />
        <p class="text-sm text-ink-muted">Limits how many people can mark Going. Maybe is always allowed.</p>
        <x-ui.error :messages="$errors->get('max_participants')" />
    </div>

    <div class="space-y-2">
        <x-ui.label for="description" value="Notes (optional)" />
        <x-ui.textarea id="description" name="description" rows="3" placeholder="Bring a light shirt. Meet by the gate.">{{ old('description', $editing ? $event->description : '') }}</x-ui.textarea>
        <x-ui.error :messages="$errors->get('description')" />
    </div>

    <fieldset class="space-y-2">
        <legend class="text-sm font-medium text-ink">Who can see it</legend>
        <div class="space-y-2">
            @foreach (['public' => 'Public. Listed for every logged-in friend.', 'private' => 'Private. Only you and people with the invite link.'] as $value => $label)
                <label class="flex min-h-11 items-start gap-3 text-sm text-ink">
                    <input type="radio" name="visibility" value="{{ $value }}" class="mt-1 border-line text-pitch-700 focus:ring-pitch-600" @checked(old('visibility', $editing ? $event->visibility : 'public') === $value) />
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <x-ui.error :messages="$errors->get('visibility')" />
    </fieldset>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
        <x-ui.button variant="ghost" :href="$editing ? route('events.show', $event) : route('dashboard')">Cancel</x-ui.button>
        <x-ui.button type="submit">{{ $editing ? 'Save changes' : 'Create game' }}</x-ui.button>
    </div>
</form>
