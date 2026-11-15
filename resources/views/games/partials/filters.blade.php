@php
    $url = fn (array $overrides = []) => route($route, $filters->parameters($overrides));
    $chip = 'inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-semibold transition duration-150';
    $on = 'bg-pitch-700 text-white';
    $off = 'bg-white text-ink ring-1 ring-line';
@endphp

<form method="GET" action="{{ route($route) }}" class="space-y-3">
    <div class="flex flex-wrap gap-2" role="group" aria-label="Sport">
        <a href="{{ $url(['sport' => null]) }}" class="{{ $chip }} {{ $filters->sport === null ? $on : $off }}">All</a>
        @foreach (App\Models\Event::SPORTS as $value => $label)
            <a href="{{ $url(['sport' => $value]) }}" class="{{ $chip }} {{ $filters->sport === $value ? $on : $off }}">{{ $label }}</a>
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
        <x-ui.input name="where" type="search" :value="$filters->where" placeholder="City or place" class="sm:flex-1" />
        <x-ui.button type="submit">Search</x-ui.button>
    </div>
</form>
