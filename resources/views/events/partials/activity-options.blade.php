@php
    $selectedSport = old('sport', $editing ? $event->sportSelection() : ($fallbackSport ?? 'football'));
    $selectedSetting = old('setting', $editing ? ($event->setting ?: App\Models\Event::defaultSetting($selectedSport)) : App\Models\Event::defaultSetting($selectedSport));
    $selectedLevel = old('level', $editing ? ($event->level ?? 'all') : 'all');
    $selectedLevel = $selectedLevel ?: 'all';
@endphp

<fieldset class="space-y-2">
    <legend class="text-base font-medium text-ink">Indoors or outdoors</legend>
    <div class="flex flex-wrap gap-2">
        @foreach (['indoor' => 'Indoors', 'outdoor' => 'Outdoors'] as $value => $label)
            <div>
                <input id="setting-{{ $value }}" type="radio" name="setting" value="{{ $value }}" class="peer sr-only" @checked($selectedSetting === $value) />
                <x-ui.chip :for="'setting-'.$value">
                    <x-ui.icon :name="$value === 'indoor' ? 'home' : 'sun'" class="h-4 w-4" />
                    {{ $label }}
                </x-ui.chip>
            </div>
        @endforeach
    </div>
    <x-ui.error :messages="$errors->get('setting')" />
</fieldset>

<fieldset class="space-y-2">
    <legend class="text-base font-medium text-ink">Level</legend>
    <div class="flex flex-wrap gap-2">
        <div>
            <input id="level-all" type="radio" name="level" value="all" class="peer sr-only" @checked($selectedLevel === 'all') />
            <x-ui.chip for="level-all">All levels</x-ui.chip>
        </div>
        @foreach (App\Models\Event::LEVELS as $value => $label)
            <div>
                <input id="level-{{ $value }}" type="radio" name="level" value="{{ $value }}" class="peer sr-only" @checked($selectedLevel === $value) />
                <x-ui.chip :for="'level-'.$value">{{ $label }}</x-ui.chip>
            </div>
        @endforeach
    </div>
    <p class="text-base text-ink-muted">All levels means beginners are welcome.</p>
    <x-ui.error :messages="$errors->get('level')" />
</fieldset>
