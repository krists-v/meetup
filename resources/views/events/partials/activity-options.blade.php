@php
    $selectedSport = old('sport', $editing ? $event->sportSelection() : ($fallbackSport ?? 'football'));
    $selectedSetting = old('setting', $editing ? ($event->setting ?: App\Models\Event::defaultSetting($selectedSport)) : App\Models\Event::defaultSetting($selectedSport));
    $selectedLevel = old('level', $editing ? ($event->level ?? 'all') : 'all');
    $selectedLevel = $selectedLevel ?: 'all';
@endphp

<fieldset class="space-y-2">
    <legend class="text-sm font-medium text-ink">Indoors or outdoors</legend>
    <div class="flex flex-wrap gap-2">
        @foreach (['indoor' => 'Indoors', 'outdoor' => 'Outdoors'] as $value => $label)
            <div>
                <input id="setting-{{ $value }}" type="radio" name="setting" value="{{ $value }}" class="peer sr-only" @checked($selectedSetting === $value) />
                <label for="setting-{{ $value }}" class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line peer-checked:bg-pitch-700 peer-checked:text-white peer-checked:ring-pitch-700 peer-focus-visible:ring-2 peer-focus-visible:ring-pitch-600 peer-focus-visible:ring-offset-2">
                    <x-ui.icon :name="$value === 'indoor' ? 'home' : 'sun'" class="h-4 w-4" />
                    {{ $label }}
                </label>
            </div>
        @endforeach
    </div>
    <x-ui.error :messages="$errors->get('setting')" />
</fieldset>

<fieldset class="space-y-2">
    <legend class="text-sm font-medium text-ink">Level</legend>
    <div class="flex flex-wrap gap-2">
        <div>
            <input id="level-all" type="radio" name="level" value="all" class="peer sr-only" @checked($selectedLevel === 'all') />
            <label for="level-all" class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line peer-checked:bg-pitch-700 peer-checked:text-white peer-checked:ring-pitch-700 peer-focus-visible:ring-2 peer-focus-visible:ring-pitch-600 peer-focus-visible:ring-offset-2">All levels</label>
        </div>
        @foreach (App\Models\Event::LEVELS as $value => $label)
            <div>
                <input id="level-{{ $value }}" type="radio" name="level" value="{{ $value }}" class="peer sr-only" @checked($selectedLevel === $value) />
                <label for="level-{{ $value }}" class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 text-sm font-semibold text-ink ring-1 ring-line peer-checked:bg-pitch-700 peer-checked:text-white peer-checked:ring-pitch-700 peer-focus-visible:ring-2 peer-focus-visible:ring-pitch-600 peer-focus-visible:ring-offset-2">{{ $label }}</label>
            </div>
        @endforeach
    </div>
    <p class="text-sm text-ink-muted">All levels means beginners are welcome.</p>
    <x-ui.error :messages="$errors->get('level')" />
</fieldset>
