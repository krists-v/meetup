<div class="space-y-6">
    @include('games.partials.filters', ['route' => 'games.index'])

    @forelse ($events as $event)
        <x-ui.game-card :event="$event" />
    @empty
        <x-ui.empty-state
            illustration="public"
            :title="$filters->active() ? 'Nothing for that.' : 'Quiet around here.'"
            :description="$filters->active() ? 'Try another activity, or a wider day.' : 'Start something, or check back this weekend.'"
        >
            <x-slot name="action">
                @if ($filters->active())
                    <x-ui.button variant="secondary" :href="route('games.index')">Clear filters</x-ui.button>
                @else
                    <x-ui.button :href="auth()->check() ? route('events.create') : route('register')" block>Start a game</x-ui.button>
                @endif
            </x-slot>
        </x-ui.empty-state>
    @endforelse
</div>
