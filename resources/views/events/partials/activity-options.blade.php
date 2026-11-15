@php
    $selectedSport = old('sport', $editing ? $event->sportSelection() : 'football');
    $selectedSetting = old('setting', $editing ? ($event->setting ?: App\Models\Event::defaultSetting($selectedSport)) : App\Models\Event::defaultSetting($selectedSport));
    $selectedLevel = old('level', $editing ? ($event->level ?? 'all') : 'all');
    $selectedLevel = $selectedLevel ?: 'all';
@endphp

<fieldset class="space-y-2">
    <legend class="text-sm font-medium text-ink">Indoors or outdoors</legend>
    <div class="flex flex-wrap gap-2">
        @foreach (['indoor' => 'Indoors', 'outdoor' => 'Outdoors'] as $value => $label)
            <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line has-[:checked]:bg-pitch-700 has-[:checked]:text-white has-[:checked]:ring-pitch-700">
                <input type="radio" name="setting" value="{{ $value }}" class="sr-only" @checked($selectedSetting === $value) />
                <x-ui.icon :name="$value === 'indoor' ? 'home' : 'sun'" class="h-4 w-4" />
                {{ $label }}
            </label>
        @endforeach
    </div>
    <x-ui.error :messages="$errors->get('setting')" />
</fieldset>

<fieldset class="space-y-2">
    <legend class="text-sm font-medium text-ink">Level</legend>
    <div class="flex flex-wrap gap-2">
        <label class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line has-[:checked]:bg-pitch-700 has-[:checked]:text-white has-[:checked]:ring-pitch-700">
            <input type="radio" name="level" value="all" class="sr-only" @checked($selectedLevel === 'all') />
            All levels
        </label>
        @foreach (App\Models\Event::LEVELS as $value => $label)
            <label class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line has-[:checked]:bg-pitch-700 has-[:checked]:text-white has-[:checked]:ring-pitch-700">
                <input type="radio" name="level" value="{{ $value }}" class="sr-only" @checked($selectedLevel === $value) />
                {{ $label }}
            </label>
        @endforeach
    </div>
    <p class="text-sm text-ink-muted">All levels means beginners are welcome.</p>
    <x-ui.error :messages="$errors->get('level')" />
</fieldset>
