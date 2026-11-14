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

    <div>
        <x-input-label for="title" value="Title" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $editing ? $event->title : '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="sport_type" value="Sport" />
        <select id="sport_type" name="sport_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
            @foreach (['football' => 'Football', 'tennis' => 'Tennis', 'basketball' => 'Basketball', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" @selected(old('sport_type', $editing ? $event->sport_type : 'football') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('sport_type')" />
    </div>

    @if ($polling)
        <p class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900">Friends are voting on a time. Lock a time from the event page. You can still edit the other details.</p>
    @elseif ($editing)
        <div>
            <x-input-label for="event_date" value="Date and time" />
            <x-text-input id="event_date" name="event_date" type="datetime-local" class="mt-1 block w-full" :value="old('event_date', $event->event_date->format('Y-m-d\TH:i'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('event_date')" />
        </div>
    @else
        <fieldset>
            <legend class="block font-medium text-sm text-gray-700">When</legend>
            <div class="mt-2 space-y-2">
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="radio" name="schedule_type" value="fixed" class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="schedule" />
                    <span>Set a date</span>
                </label>
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="radio" name="schedule_type" value="poll" class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="schedule" />
                    <span>Let friends vote on a time</span>
                </label>
            </div>
        </fieldset>

        <div x-show="schedule === 'fixed'" x-cloak>
            <x-input-label for="event_date" value="Date and time" />
            <x-text-input id="event_date" name="event_date" type="datetime-local" class="mt-1 block w-full" :value="old('event_date')" x-bind:required="schedule === 'fixed'" />
            <x-input-error class="mt-2" :messages="$errors->get('event_date')" />
        </div>

        <div x-show="schedule === 'poll'" x-cloak class="space-y-3">
            <x-input-label value="Time options" />
            <template x-for="(time, index) in times" :key="index">
                <input type="datetime-local" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="times[index]" :name="'options[' + index + ']'" />
            </template>
            <button type="button" class="text-sm font-semibold text-gray-700 underline" @click="times.push('')">Add another time</button>
            <p class="text-xs text-gray-500">Add at least two different times. Friends vote Yes, Maybe, or No on each one.</p>
            <x-input-error class="mt-2" :messages="$errors->get('options')" />
        </div>
    @endif

    <div>
        <x-input-label for="location" value="Location" />
        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $editing ? $event->location : '')" required placeholder="Park, court, or address" />
        <x-input-error class="mt-2" :messages="$errors->get('location')" />
    </div>

    <div>
        <x-input-label for="max_participants" value="Max participants (optional)" />
        <x-text-input id="max_participants" name="max_participants" type="number" min="2" class="mt-1 block w-full" :value="old('max_participants', $editing ? $event->max_participants : '')" />
        <p class="mt-1 text-xs text-gray-500">Limits how many people can mark Going. Maybe is always allowed.</p>
        <x-input-error class="mt-2" :messages="$errors->get('max_participants')" />
    </div>

    <div>
        <x-input-label for="description" value="Description (optional)" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">{{ old('description', $editing ? $event->description : '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <fieldset>
        <legend class="block font-medium text-sm text-gray-700">Visibility</legend>
        <div class="mt-2 space-y-2">
            @foreach (['public' => 'Public — listed for every logged-in friend', 'private' => 'Private — only you and people with the invite link'] as $value => $label)
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="radio" name="visibility" value="{{ $value }}" class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('visibility', $editing ? $event->visibility : 'public') === $value) />
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('visibility')" />
    </fieldset>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $editing ? 'Save changes' : 'Create event' }}</x-primary-button>
        <a href="{{ $editing ? route('events.show', $event) : route('dashboard') }}" class="text-sm text-gray-600 underline">Cancel</a>
    </div>
</form>
