@php
    $url = fn (array $overrides = []) => route($route, $filters->parameters($overrides));
    $chip = 'inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-semibold transition duration-150';
    $on = 'bg-pitch-700 text-white';
    $off = 'bg-white text-ink ring-1 ring-line';
@endphp

<form method="GET" action="{{ route($route) }}" class="space-y-3">
    <div class="flex flex-wrap gap-2" role="group" aria-label="Activity">
        <a href="{{ $url(['sport' => null]) }}" class="{{ $chip }} {{ $filters->sport === null ? $on : $off }}">All</a>
        @foreach (App\Models\Event::SPORTS as $value => $label)
            <a href="{{ $url(['sport' => $value]) }}" class="{{ $chip }} {{ $filters->sport === $value ? $on : $off }} gap-2">
                <x-ui.icon :name="App\Models\Event::activityIcon($value)" class="h-4 w-4" />
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-2">
        <p class="text-sm font-medium text-ink">Category</p>
    <div class="flex flex-wrap gap-2" role="group" aria-label="Category">
        <a href="{{ $url(['category' => null]) }}" class="{{ $chip }} {{ $filters->category === null ? $on : $off }}">All</a>
        @foreach (App\Models\Event::CATEGORIES as $value => $label)
            <a href="{{ $url(['category' => $value]) }}" class="{{ $chip }} {{ $filters->category === $value ? $on : $off }}">{{ $label }}</a>
        @endforeach
    </div>
    </div>

    <div class="space-y-2">
        <p class="text-sm font-medium text-ink">Indoors or outdoors</p>
    <div class="flex flex-wrap gap-2" role="group" aria-label="Indoors or outdoors">
        <a href="{{ $url(['setting' => null]) }}" class="{{ $chip }} {{ $filters->setting === null ? $on : $off }}">All</a>
        <a href="{{ $url(['setting' => 'indoor']) }}" class="{{ $chip }} {{ $filters->setting === 'indoor' ? $on : $off }}">Indoors</a>
        <a href="{{ $url(['setting' => 'outdoor']) }}" class="{{ $chip }} {{ $filters->setting === 'outdoor' ? $on : $off }}">Outdoors</a>
    </div>
    </div>

    <div class="flex flex-wrap gap-2" role="group" aria-label="Level">
        <a href="{{ $url(['level' => null]) }}" class="{{ $chip }} {{ $filters->level === null ? $on : $off }}">Any</a>
        <a href="{{ $url(['level' => 'all']) }}" class="{{ $chip }} {{ $filters->level === 'all' ? $on : $off }}">All levels</a>
        @foreach (App\Models\Event::LEVELS as $value => $label)
            <a href="{{ $url(['level' => $value]) }}" class="{{ $chip }} {{ $filters->level === $value ? $on : $off }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2" role="group" aria-label="When">
        @foreach (['all' => 'All', 'today' => 'Today', 'weekend' => 'This weekend', 'week' => 'This week'] as $value => $label)
            <a href="{{ $url(['when' => $value]) }}" class="{{ $chip }} {{ $filters->when === $value ? $on : $off }}">{{ $label }}</a>
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
