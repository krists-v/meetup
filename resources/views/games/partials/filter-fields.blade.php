@php
    $sheet = $sheet ?? false;
    $mark = $sheet ? 'sheet-' : '';
@endphp

<div class="{{ $track }}" role="group" aria-label="Activity" x-data="motionIndicator" data-indicator="activity-{{ $mark }}{{ $route }}">
    <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
    <x-ui.chip :href="$url(['sport' => null])" :selected="$filters->sport === null" data-active="{{ $filters->sport === null ? '1' : '0' }}">All</x-ui.chip>
    @foreach (App\Models\Event::SPORTS as $value => $label)
        <x-ui.chip :href="$url(['sport' => $value])" :selected="$filters->sport === $value" data-active="{{ $filters->sport === $value ? '1' : '0' }}">
            <x-ui.icon :name="App\Models\Event::activityIcon($value)" class="h-4 w-4" />
            {{ $label }}
        </x-ui.chip>
    @endforeach
</div>

<div class="space-y-2">
    <p class="text-base font-medium text-ink">Kind of game</p>
    <div class="{{ $track }}" role="group" aria-label="Kind of game" x-data="motionIndicator" data-indicator="category-{{ $mark }}{{ $route }}">
        <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
        <x-ui.chip :href="$url(['category' => null])" :selected="$filters->category === null" data-active="{{ $filters->category === null ? '1' : '0' }}">All</x-ui.chip>
        @foreach (App\Models\Event::CATEGORIES as $value => $label)
            <x-ui.chip :href="$url(['category' => $value])" :selected="$filters->category === $value" data-active="{{ $filters->category === $value ? '1' : '0' }}">{{ $label }}</x-ui.chip>
        @endforeach
    </div>
</div>

<div class="space-y-2">
    <p class="text-base font-medium text-ink">Indoors or outdoors</p>
    <div class="{{ $track }}" role="group" aria-label="Indoors or outdoors" x-data="motionIndicator" data-indicator="setting-{{ $mark }}{{ $route }}">
        <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
        <x-ui.chip :href="$url(['setting' => null])" :selected="$filters->setting === null" data-active="{{ $filters->setting === null ? '1' : '0' }}">All</x-ui.chip>
        <x-ui.chip :href="$url(['setting' => 'indoor'])" :selected="$filters->setting === 'indoor'" data-active="{{ $filters->setting === 'indoor' ? '1' : '0' }}">Indoors</x-ui.chip>
        <x-ui.chip :href="$url(['setting' => 'outdoor'])" :selected="$filters->setting === 'outdoor'" data-active="{{ $filters->setting === 'outdoor' ? '1' : '0' }}">Outdoors</x-ui.chip>
    </div>
</div>

<div class="{{ $track }}" role="group" aria-label="Level" x-data="motionIndicator" data-indicator="level-{{ $mark }}{{ $route }}">
    <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
    <x-ui.chip :href="$url(['level' => 'any'])" :selected="$filters->level === null" data-active="{{ $filters->level === null ? '1' : '0' }}">Any</x-ui.chip>
    <x-ui.chip :href="$url(['level' => 'all'])" :selected="$filters->level === 'all'" data-active="{{ $filters->level === 'all' ? '1' : '0' }}">All levels</x-ui.chip>
    @foreach (App\Models\Event::LEVELS as $value => $label)
        <x-ui.chip :href="$url(['level' => $value])" :selected="$filters->level === $value" data-active="{{ $filters->level === $value ? '1' : '0' }}">{{ $label }}</x-ui.chip>
    @endforeach
</div>

<div class="{{ $track }}" role="group" aria-label="When" x-data="motionIndicator" data-indicator="when-{{ $mark }}{{ $route }}">
    <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
    @foreach (['all' => 'All', 'today' => 'Today', 'weekend' => 'This weekend', 'week' => 'This week'] as $value => $label)
        <x-ui.chip :href="$url(['when' => $value])" :selected="$filters->when === $value" data-active="{{ $filters->when === $value ? '1' : '0' }}">{{ $label }}</x-ui.chip>
    @endforeach
</div>

<div class="grid gap-2">
    @if ($filters->sport)
        <input type="hidden" name="sport" value="{{ $filters->sport }}">
    @endif
    @if ($filters->when !== 'all')
        <input type="hidden" name="when" value="{{ $filters->when }}">
    @endif
    @if ($filters->setting)
        <input type="hidden" name="setting" value="{{ $filters->setting }}">
    @endif
    @if ($filters->level)
        <input type="hidden" name="level" value="{{ $filters->level }}">
    @elseif ($filters->anyLevel)
        <input type="hidden" name="level" value="any">
    @endif
    @if ($filters->category)
        <input type="hidden" name="category" value="{{ $filters->category }}">
    @endif
    <x-ui.input name="where" type="search" :value="$filters->where" placeholder="City or place" class="sm:flex-1" aria-label="City or place" />
    @unless ($sheet)
        <x-ui.button type="submit">Search</x-ui.button>
    @endunless
</div>
