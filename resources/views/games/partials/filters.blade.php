@php
    $url = fn (array $overrides = []) => route($route, $filters->parameters($overrides));
    $chip = 'relative z-10 inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-semibold transition duration-150';
    $on = 'bg-ink text-white';
    $off = 'bg-white text-ink ring-1 ring-line';
    $track = 'relative flex flex-wrap gap-2';
    $indicator = 'motion-slide pointer-events-none absolute rounded-full ring-2 ring-pitch-800';
    $sheetId = 'filters-'.str_replace('.', '-', $route);
    $count = $filters->count();
@endphp

<div
    x-data="filterSheet"
    @keydown.escape.window="close()"
    @keydown.tab.window="trap($event)"
>
    <button
        type="button"
        class="filters-trigger motion-press min-h-11 items-center gap-2 rounded-control bg-white px-4 py-2.5 text-sm font-semibold text-ink ring-1 ring-line hover:bg-pitch-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600 focus-visible:ring-offset-2"
        x-ref="trigger"
        @click="show()"
        :aria-expanded="open.toString()"
        aria-haspopup="dialog"
        aria-controls="{{ $sheetId }}"
    >
        <x-ui.icon name="sliders" />
        Filters
        @if ($count > 0)
            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-ink px-1.5 text-xs font-semibold text-white" data-filter-count="{{ $count }}">{{ $count }}</span>
        @endif
    </button>

    <div class="filters-backdrop" x-ref="backdrop" hidden @click="close()"></div>

    <form
        id="{{ $sheetId }}"
        method="GET"
        action="{{ route($route) }}"
        class="filters-sheet"
        hidden
        x-ref="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $sheetId }}-title"
    >
        <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
            <h2 id="{{ $sheetId }}-title" class="text-lg font-semibold text-ink">Filters</h2>
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-control text-ink hover:bg-pitch-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600" x-ref="close" @click="close()" aria-label="Close">
                <x-ui.icon name="x" />
            </button>
        </div>
        <div class="min-h-0 flex-1 space-y-3 overflow-y-auto px-4 py-4">
            @include('games.partials.filter-fields', ['sheet' => true])
        </div>
        <div class="flex gap-2 border-t border-line bg-white p-3">
            <x-ui.button variant="secondary" :href="route($route)" class="flex-1">Clear all</x-ui.button>
            <x-ui.button type="submit" class="flex-1">Show games</x-ui.button>
        </div>
    </form>
</div>

<form method="GET" action="{{ route($route) }}" class="filters-inline space-y-3">
    @include('games.partials.filter-fields', ['sheet' => false])
</form>
