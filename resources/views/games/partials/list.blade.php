<div class="space-y-6">
    @include('games.partials.filters', ['route' => 'games.index'])

    @forelse ($events as $event)
        <x-ui.game-card :event="$event" />
    @empty
        <x-ui.empty-state
            illustration="public"
            :title="$filters->active() ? 'No games match' : 'Nothing public right now'"
            :description="$filters->active() ? 'Try another sport, a different day, or a wider search.' : 'When someone plans a public game, it shows up here.'"
        >
            @if ($filters->active())
                <x-slot name="action">
                    <x-ui.button variant="secondary" :href="route('games.index')">Clear filters</x-ui.button>
                </x-slot>
            @endif
        </x-ui.empty-state>
    @endforelse
</div>
