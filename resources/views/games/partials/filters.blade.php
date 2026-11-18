@php
    $url = fn (array $overrides = []) => route($route, $filters->parameters($overrides));
    $chip = 'relative z-10 inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-semibold transition duration-150';
    $on = 'bg-pitch-700 text-white';
    $off = 'bg-white text-ink ring-1 ring-line';
    $track = 'relative flex flex-wrap gap-2';
    $indicator = 'motion-slide pointer-events-none absolute rounded-full ring-2 ring-pitch-800';
@endphp

<form method="GET" action="{{ route($route) }}" class="space-y-3">
    <div class="{{ $track }}" role="group" aria-label="Activity" x-data="motionIndicator" data-indicator="activity-{{ $route }}">
        <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
        <a href="{{ $url(['sport' => null]) }}" data-active="{{ $filters->sport === null ? '1' : '0' }}" class="{{ $chip }} {{ $filters->sport === null ? $on : $off }}">All</a>
        @foreach (App\Models\Event::SPORTS as $value => $label)
            <a href="{{ $url(['sport' => $value]) }}" data-active="{{ $filters->sport === $value ? '1' : '0' }}" class="{{ $chip }} {{ $filters->sport === $value ? $on : $off }} gap-2">
                <x-ui.icon :name="App\Models\Event::activityIcon($value)" class="h-4 w-4" />
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-2">
        <p class="text-sm font-medium text-ink">Kind of game</p>
        <div class="{{ $track }}" role="group" aria-label="Kind of game" x-data="motionIndicator" data-indicator="category-{{ $route }}">
            <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
            <a href="{{ $url(['category' => null]) }}" data-active="{{ $filters->category === null ? '1' : '0' }}" class="{{ $chip }} {{ $filters->category === null ? $on : $off }}">All</a>
            @foreach (App\Models\Event::CATEGORIES as $value => $label)
                <a href="{{ $url(['category' => $value]) }}" data-active="{{ $filters->category === $value ? '1' : '0' }}" class="{{ $chip }} {{ $filters->category === $value ? $on : $off }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="space-y-2">
        <p class="text-sm font-medium text-ink">Indoors or outdoors</p>
        <div class="{{ $track }}" role="group" aria-label="Indoors or outdoors" x-data="motionIndicator" data-indicator="setting-{{ $route }}">
            <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
            <a href="{{ $url(['setting' => null]) }}" data-active="{{ $filters->setting === null ? '1' : '0' }}" class="{{ $chip }} {{ $filters->setting === null ? $on : $off }}">All</a>
            <a href="{{ $url(['setting' => 'indoor']) }}" data-active="{{ $filters->setting === 'indoor' ? '1' : '0' }}" class="{{ $chip }} {{ $filters->setting === 'indoor' ? $on : $off }}">Indoors</a>
            <a href="{{ $url(['setting' => 'outdoor']) }}" data-active="{{ $filters->setting === 'outdoor' ? '1' : '0' }}" class="{{ $chip }} {{ $filters->setting === 'outdoor' ? $on : $off }}">Outdoors</a>
        </div>
    </div>

    <div class="{{ $track }}" role="group" aria-label="Level" x-data="motionIndicator" data-indicator="level-{{ $route }}">
        <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
        <a href="{{ $url(['level' => null]) }}" data-active="{{ $filters->level === null ? '1' : '0' }}" class="{{ $chip }} {{ $filters->level === null ? $on : $off }}">Any</a>
        <a href="{{ $url(['level' => 'all']) }}" data-active="{{ $filters->level === 'all' ? '1' : '0' }}" class="{{ $chip }} {{ $filters->level === 'all' ? $on : $off }}">All levels</a>
        @foreach (App\Models\Event::LEVELS as $value => $label)
            <a href="{{ $url(['level' => $value]) }}" data-active="{{ $filters->level === $value ? '1' : '0' }}" class="{{ $chip }} {{ $filters->level === $value ? $on : $off }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="{{ $track }}" role="group" aria-label="When" x-data="motionIndicator" data-indicator="when-{{ $route }}">
        <span class="{{ $indicator }}" x-ref="indicator" hidden aria-hidden="true"></span>
        @foreach (['all' => 'All', 'today' => 'Today', 'weekend' => 'This weekend', 'week' => 'This week'] as $value => $label)
            <a href="{{ $url(['when' => $value]) }}" data-active="{{ $filters->when === $value ? '1' : '0' }}" class="{{ $chip }} {{ $filters->when === $value ? $on : $off }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
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
        @endif
        @if ($filters->category)
            <input type="hidden" name="category" value="{{ $filters->category }}">
        @endif
        <x-ui.input name="where" type="search" :value="$filters->where" placeholder="City or place" class="sm:flex-1" />
        <x-ui.button type="submit">Search</x-ui.button>
    </div>
</form>
