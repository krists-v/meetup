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

    <x-ui.card class="space-y-4">
        <h2 class="flex items-center gap-2 text-lg font-bold text-ink">
            <x-ui.icon name="sparkles" class="h-5 w-5 text-ink-faint" />
            The game
        </h2>
        <p class="text-base text-ink-muted">A short name people will recognise.</p>
        <div class="space-y-2">
            <x-ui.label for="title" value="Name" />
            <x-ui.input id="title" name="title" type="text" :value="old('title', $editing ? $event->title : '')" required autofocus placeholder="Thursday padel" />
            <x-ui.error :messages="$errors->get('title')" />
        </div>
        @include('events.partials.sport-picker')
    </x-ui.card>

    <x-ui.card class="space-y-4">
        <h2 class="flex items-center gap-2 text-lg font-bold text-ink">
            <x-ui.icon name="calendar" class="h-5 w-5 text-ink-faint" />
            When
        </h2>
        <p class="text-base text-ink-muted">An evening works for most people.</p>
        @if ($polling)
            <p class="rounded-control bg-warn-soft px-3 py-2 text-base text-warn">People are still picking a time. Set one from the game page. Everything else can still change.</p>
        @elseif ($editing)
            <div class="space-y-2">
                <x-ui.label for="event_date" value="Date and time" />
                <x-ui.input id="event_date" name="event_date" type="datetime-local" :value="old('event_date', $event->event_date->format('Y-m-d\TH:i'))" required />
                <x-ui.error :messages="$errors->get('event_date')" />
            </div>
        @else
            <fieldset class="space-y-2">
                <legend class="text-base font-medium text-ink">The time</legend>
                <div class="flex flex-wrap gap-2">
                    <div>
                        <input id="schedule-fixed" type="radio" name="schedule_type" value="fixed" class="peer sr-only" x-model="schedule" @checked(old('schedule_type', 'fixed') === 'fixed') />
                        <x-ui.chip for="schedule-fixed">Set a time</x-ui.chip>
                    </div>
                    <div>
                        <input id="schedule-poll" type="radio" name="schedule_type" value="poll" class="peer sr-only" x-model="schedule" @checked(old('schedule_type') === 'poll') />
                        <x-ui.chip for="schedule-poll">Let people pick</x-ui.chip>
                    </div>
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
                    <x-ui.input type="datetime-local" x-model="times[index]" x-bind:name="'options[' + index + ']'" />
                </template>
                <x-ui.button type="button" variant="ghost" block x-on:click="times.push('')">Add another time</x-ui.button>
                <p class="text-base text-ink-muted">Add at least two different times.</p>
                <x-ui.error :messages="$errors->get('options')" />
            </div>
        @endif
    </x-ui.card>

    <x-ui.card class="space-y-4">
        <h2 class="flex items-center gap-2 text-lg font-bold text-ink">
            <x-ui.icon name="map-pin" class="h-5 w-5 text-ink-faint" />
            Where
        </h2>
        <div class="space-y-2">
            <x-ui.label for="location" value="Place" />
            <x-ui.input id="location" name="location" type="text" :value="old('location', $editing ? $event->location : '')" required placeholder="Court, lane, or park" />
            <p class="text-base text-ink-muted">A real place. People open it in maps.</p>
            <x-ui.error :messages="$errors->get('location')" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="photo" value="Photo (optional)" />
            @if ($editing && filled($event->photo_path))
                <img src="{{ $event->photoUrl() }}" alt="" class="h-32 w-full rounded-2xl object-cover" />
                <x-ui.checkbox name="remove_photo" value="1" :checked="(bool) old('remove_photo')">Remove photo</x-ui.checkbox>
            @endif
            <x-ui.file id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" />
            <p class="text-base text-ink-muted">A picture of the place. Square works best.</p>
            <x-ui.error :messages="$errors->get('photo')" />
        </div>
    </x-ui.card>

    <x-ui.card class="space-y-4">
        <h2 class="flex items-center gap-2 text-lg font-bold text-ink">
            <x-ui.icon name="users" class="h-5 w-5 text-ink-faint" />
            Who it's for
        </h2>
        @include('events.partials.activity-options')

        <div class="space-y-2">
            <x-ui.label for="max_participants" value="Spots (optional)" />
            <x-ui.input id="max_participants" name="max_participants" type="number" min="2" :value="old('max_participants', $editing ? $event->max_participants : '')" />
            <p class="text-base text-ink-muted">Leave this empty if anyone can join. Maybe is always fine.</p>
            <x-ui.error :messages="$errors->get('max_participants')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="price" value="Total cost (optional)" />
            <x-ui.input id="price" name="price" type="number" min="0" step="0.01" inputmode="decimal" :value="old('price', $editing ? $event->price : '')" />
            <p class="text-base text-ink-muted">The whole cost, in euros. We split it by the spots.</p>
            <x-ui.error :messages="$errors->get('price')" />
        </div>

        <fieldset class="space-y-2">
            <legend class="text-base font-medium text-ink">Who can see it</legend>
            <div class="flex flex-wrap gap-2">
                @foreach (['public' => 'Public', 'private' => 'Private'] as $value => $label)
                    <div>
                        <input id="visibility-{{ $value }}" type="radio" name="visibility" value="{{ $value }}" class="peer sr-only" @checked(old('visibility', $editing ? $event->visibility : 'public') === $value) />
                        <x-ui.chip :for="'visibility-'.$value">{{ $label }}</x-ui.chip>
                    </div>
                @endforeach
            </div>
            <p class="text-base text-ink-muted">Anyone can find a public game. A private one opens with the link.</p>
            <x-ui.error :messages="$errors->get('visibility')" />
        </fieldset>

        <div class="space-y-2">
            <input type="hidden" name="come_solo" value="0">
            <x-ui.checkbox name="come_solo" value="1" :checked="(string) old('come_solo', $editing ? (int) $event->come_solo : 1) === '1'">Solo players welcome</x-ui.checkbox>
        </div>
    </x-ui.card>

    <x-ui.card class="space-y-4">
        <h2 class="flex items-center gap-2 text-lg font-bold text-ink">
            <x-ui.icon name="pencil" class="h-5 w-5 text-ink-faint" />
            Notes
        </h2>
        <div class="space-y-2">
            <x-ui.label for="description" value="Anything else (optional)" />
            <x-ui.textarea id="description" name="description" rows="3" placeholder="Bring a ball. Meet by the door.">{{ old('description', $editing ? $event->description : '') }}</x-ui.textarea>
            <x-ui.error :messages="$errors->get('description')" />
        </div>
    </x-ui.card>

    <div class="flex flex-col gap-3">
        @if ($editing)
            <div class="hidden sm:block">
                <x-ui.button type="submit" block>Save</x-ui.button>
            </div>
        @else
            <div class="hidden sm:block">
                <x-ui.button type="submit" block>Start the game</x-ui.button>
            </div>
        @endif
        <x-ui.button variant="ghost" :href="$editing ? route('events.show', $event) : route('dashboard')" block>Cancel</x-ui.button>
    </div>
</form>
