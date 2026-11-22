@php
    $selectedSport = old('sport', $editing ? $event->sportSelection() : ($fallbackSport ?? 'football'));
    $customSport = old('custom_sport', $editing ? $event->customSportInput() : '');
@endphp

<fieldset class="sport-picker space-y-2">
    <legend class="text-sm font-medium text-ink">Activity</legend>
    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Activity">
        @foreach (App\Models\Event::SPORTS as $value => $label)
            <div class="{{ $value === 'other' ? 'contents' : '' }}">
                <input
                    id="sport-{{ $value }}"
                    type="radio"
                    name="sport"
                    value="{{ $value }}"
                    data-setting="{{ App\Models\Event::ACTIVITIES[$value]['setting'] ?? '' }}"
                    class="peer sr-only"
                    @checked($selectedSport === $value)
                    required
                />
                <label for="sport-{{ $value }}" class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-line transition duration-150 peer-checked:bg-ink peer-checked:text-white peer-checked:ring-ink peer-focus-visible:ring-2 peer-focus-visible:ring-ink peer-focus-visible:ring-offset-2">
                    <x-ui.icon :name="App\Models\Event::activityIcon($value)" class="h-4 w-4" />
                    {{ $label }}
                </label>
            </div>
        @endforeach
    </div>
    <div class="sport-custom space-y-2">
        <x-ui.label for="custom_sport" value="Custom activity" />
        <x-ui.input id="custom_sport" name="custom_sport" type="text" maxlength="50" :value="$customSport" placeholder="Floorball, badminton, a kickabout" />
        <x-ui.error :messages="$errors->get('custom_sport')" />
    </div>
    <x-ui.error :messages="$errors->get('sport')" />
</fieldset>

<script>
    document.querySelectorAll('.sport-picker input[name="sport"]').forEach((input) => {
        input.addEventListener('change', () => {
            const setting = input.dataset.setting;

            if (setting !== 'indoor' && setting !== 'outdoor') {
                return;
            }

            const choice = document.querySelector('input[name="setting"][value="' + setting + '"]');

            if (choice) {
                choice.checked = true;
            }
        });
    });
</script>
