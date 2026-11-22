@php
    $editing = isset($event);
    $polling = $editing && $event->isPoll();
    $oldOptions = array_values(old('options', ['', '']));
    if (count($oldOptions) < 2) {
        $oldOptions = array_pad($oldOptions, 2, '');
    }
    $favorites = auth()->user()?->favorite_sports ?? [];
    $favorite = $favorites[0] ?? null;
    $fallbackSport = is_string($favorite) && array_key_exists($favorite, App\Models\Event::SPORTS) ? $favorite : 'football';
    $defaultWhen = now()->addDay()->setTime(18, 0)->format('Y-m-d\TH:i');
@endphp

<form id="game-form" method="POST" action="{{ $editing ? route('events.update', $event) : route('events.store') }}" enctype="multipart/form-data" class="space-y-4" @unless ($editing) x-data="{ schedule: @js(old('schedule_type', 'fixed')), times: @js($oldOptions) }" @endunless>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-ui.card class="space-y-4 p-4 sm:p-6">
        <h2 class="flex items-center gap-2 font-semibold text-ink">
            <x-ui.icon name="sparkles" class="h-5 w-5 text-ink-faint" />
            The game
        </h2>
        <p class="text-sm text-ink-muted">A short name people will recognise.</p>
        <div class="space-y-2">
            <x-ui.label for="title" value="Name" />
            <x-ui.input id="title" name="title" type="text" :value="old('title', $editing ? $event->title : '')" required autofocus placeholder="Thursday padel" />
            <x-ui.error :messages="$errors->get('title')" />
        </div>
        @include('events.partials.sport-picker')
    </x-ui.card>

    <x-ui.card class="space-y-4 p-4 sm:p-6">
        <h2 class="flex items-center gap-2 font-semibold text-ink">
            <x-ui.icon name="calendar" class="h-5 w-5 text-ink-faint" />
            When
        </h2>
        <p class="text-sm text-ink-muted">An evening works for most people.</p>
        @if ($polling)
            <p class="rounded-control bg-warn-soft px-3 py-2 text-sm text-warn">People are still picking a time. Set one from the game page. Everything else can still change.</p>
        @elseif ($editing)
            <div class="space-y-2">
                <x-ui.label for="event_date" value="Date and time" />
                <x-ui.input id="event_date" name="event_date" type="datetime-local" :value="old('event_date', $event->event_date->format('Y-m-d\TH:i'))" required />
                <x-ui.error :messages="$errors->get('event_date')" />
            </div>
        @else
            <fieldset class="space-y-2">
                <legend class="text-sm font-medium text-ink">The time</legend>
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line has-[:checked]:bg-ink has-[:checked]:text-white has-[:checked]:ring-ink">
                        <input type="radio" name="schedule_type" value="fixed" class="sr-only" x-model="schedule" @checked(old('schedule_type', 'fixed') === 'fixed') />
                        Set a time
                    </label>
                    <label class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line has-[:checked]:bg-ink has-[:checked]:text-white has-[:checked]:ring-ink">
                        <input type="radio" name="schedule_type" value="poll" class="sr-only" x-model="schedule" @checked(old('schedule_type') === 'poll') />
                        Let people pick
                    </label>
                </div>
            </fieldset>

            <div x-show="schedule === 'fixed'" x-cloak class="space-y-2">
                <x-ui.label for="event_date" value="Date and time" />
                <x-ui.input id="event_date" name="event_date" type="datetime-local" :value="old('event_date', $defaultWhen)" x-bind:required="schedule === 'fixed'" />
                <x-ui.error :messages="$errors->get('event_date')" />
            </div>

            <div x-show="schedule === 'poll'" x-cloak class="space-y-3">
                <x-ui.label value="Times" />
                <template x-for="(time, index) in times" :key="index">
                    <input type="datetime-local" class="block min-h-11 w-full rounded-control border-line text-base text-ink focus:border-ink focus:ring-ink" x-model="times[index]" :name="'options[' + index + ']'" />
                </template>
                <x-ui.button type="button" variant="ghost" x-on:click="times.push('')">Add another time</x-ui.button>
                <p class="text-sm text-ink-muted">Add at least two different times.</p>
                <x-ui.error :messages="$errors->get('options')" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.card class="space-y-4 p-4 sm:p-6">
        <h2 class="flex items-center gap-2 font-semibold text-ink">
            <x-ui.icon name="map-pin" class="h-5 w-5 text-ink-faint" />
            Where
        </h2>
        <div class="space-y-2">
            <x-ui.label for="location" value="Place" />
            <x-ui.input id="location" name="location" type="text" :value="old('location', $editing ? $event->location : '')" required placeholder="Court, lane, or park" />
            <p class="text-sm text-ink-muted">A real place. People open it in maps.</p>
            <x-ui.error :messages="$errors->get('location')" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="photo" value="Photo (optional)" />
            @if ($editing && filled($event->photo_path))
                <img src="{{ $event->photoUrl() }}" alt="" class="h-32 w-full rounded-2xl object-cover" />
                <label class="flex min-h-11 items-center gap-2 text-sm text-ink">
                    <input type="checkbox" name="remove_photo" value="1" class="rounded border-line text-ink focus:ring-ink" @checked(old('remove_photo')) />
                    Remove photo
                </label>
            @endif
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-base text-ink file:me-3 file:min-h-11 file:rounded-control file:border-0 file:bg-mist file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-ink" />
            <p class="text-sm text-ink-muted">A picture of the place. Square works best.</p>
            <x-ui.error :messages="$errors->get('photo')" />
        </div>
    </x-ui.card>

    <x-ui.card class="space-y-4 p-4 sm:p-6">
        <h2 class="flex items-center gap-2 font-semibold text-ink">
            <x-ui.icon name="users" class="h-5 w-5 text-ink-faint" />
            Who it's for
        </h2>
        @include('events.partials.activity-options')

        <div class="space-y-2">
            <x-ui.label for="max_participants" value="Spots (optional)" />
            <x-ui.input id="max_participants" name="max_participants" type="number" min="2" :value="old('max_participants', $editing ? $event->max_participants : '')" />
            <p class="text-sm text-ink-muted">Leave this empty if anyone can join. Maybe is always fine.</p>
            <x-ui.error :messages="$errors->get('max_participants')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="price" value="Total cost (optional)" />
            <x-ui.input id="price" name="price" type="number" min="0" step="0.01" inputmode="decimal" :value="old('price', $editing ? $event->price : '')" />
            <p class="text-sm text-ink-muted">The whole cost, in euros. We split it by the spots.</p>
            <x-ui.error :messages="$errors->get('price')" />
        </div>

        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-ink">Who can see it</legend>
            <div class="flex flex-wrap gap-2">
                @foreach (['public' => 'Public', 'private' => 'Private'] as $value => $label)
                    <label class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line has-[:checked]:bg-ink has-[:checked]:text-white has-[:checked]:ring-ink">
                        <input type="radio" name="visibility" value="{{ $value }}" class="sr-only" @checked(old('visibility', $editing ? $event->visibility : 'public') === $value) />
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <p class="text-sm text-ink-muted">Anyone can find a public game. A private one opens with the link.</p>
            <x-ui.error :messages="$errors->get('visibility')" />
        </fieldset>
    </x-ui.card>

    <x-ui.card class="space-y-4 p-4 sm:p-6">
        <h2 class="flex items-center gap-2 font-semibold text-ink">
            <x-ui.icon name="pencil" class="h-5 w-5 text-ink-faint" />
            Notes
        </h2>
        <div class="space-y-2">
            <x-ui.label for="description" value="Anything else (optional)" />
            <x-ui.textarea id="description" name="description" rows="3" placeholder="Bring a ball. Meet by the door.">{{ old('description', $editing ? $event->description : '') }}</x-ui.textarea>
            <x-ui.error :messages="$errors->get('description')" />
        </div>
    </x-ui.card>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
        <x-ui.button variant="ghost" :href="$editing ? route('events.show', $event) : route('dashboard')">Cancel</x-ui.button>
        @if ($editing)
            <x-ui.button type="submit">Save</x-ui.button>
        @else
            <div class="hidden sm:block">
                <x-ui.button type="submit">Start the game</x-ui.button>
            </div>
        @endif
    </div>
</form>
