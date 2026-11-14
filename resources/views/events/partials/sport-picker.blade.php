@php
    $selectedSport = old('sport', $editing ? $event->sportSelection() : 'football');
    $customSport = old('custom_sport', $editing ? $event->customSportInput() : '');
@endphp

<fieldset class="sport-picker">
    <legend class="block font-medium text-sm text-gray-700">Sport</legend>
    <div class="mt-2 flex flex-wrap gap-2" role="radiogroup" aria-label="Sport">
        @foreach (App\Models\Event::SPORTS as $value => $label)
            <div class="{{ $value === 'other' ? 'contents' : '' }}">
                <input
                    id="sport-{{ $value }}"
                    type="radio"
                    name="sport"
                    value="{{ $value }}"
                    class="peer sr-only"
                    @checked($selectedSport === $value)
                    required
                />
                <label for="sport-{{ $value }}" class="inline-flex min-h-[44px] cursor-pointer items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-gray-900 peer-focus-visible:ring-offset-2">{{ $label }}</label>
            </div>
        @endforeach
    </div>
    <div class="sport-custom mt-3">
        <x-input-label for="custom_sport" value="Custom sport" />
        <x-text-input id="custom_sport" name="custom_sport" type="text" maxlength="50" class="mt-1 block w-full" :value="$customSport" placeholder="e.g. Floorball" />
        <x-input-error class="mt-2" :messages="$errors->get('custom_sport')" />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('sport')" />
</fieldset>

<style>
    .sport-picker .sport-custom { display: none; }
    .sport-picker:has(#sport-other:checked) .sport-custom { display: block; }
</style>
