<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Your games" subtitle="What is coming up, and what the rest of the crew has made public.">
            <x-slot name="actions">
                <div class="hidden sm:block">
                    <x-ui.button :href="route('events.create')">
                        <x-ui.icon name="plus" />
                        Plan a game
                    </x-ui.button>
                </div>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-8">
        <x-ui.section title="My upcoming events">
            @forelse ($myEvents as $event)
                <x-ui.game-card :event="$event" />
            @empty
                <x-ui.empty-state
                    illustration="invites"
                    title="No games yet"
                    description="Plan one for the crew, or open an invite from the group chat."
                >
                    <x-slot name="action">
                        <x-ui.button :href="route('events.create')">Plan a game</x-ui.button>
                    </x-slot>
                </x-ui.empty-state>
            @endforelse
        </x-ui.section>

        <x-ui.section title="Public upcoming events">
            @include('games.partials.filters', ['route' => 'dashboard'])

            @forelse ($publicEvents as $event)
                <x-ui.game-card :event="$event" />
            @empty
                <x-ui.empty-state
                    illustration="public"
                    :title="$filters->active() ? 'No games match' : 'Nothing public right now'"
                    :description="$filters->active() ? 'Try another sport, a different day, or a wider search.' : 'When a friend plans a public game, it shows up here.'"
                >
                    @if ($filters->active())
                        <x-slot name="action">
                            <x-ui.button variant="secondary" :href="route('dashboard')">Clear filters</x-ui.button>
                        </x-slot>
                    @endif
                </x-ui.empty-state>
            @endforelse
        </x-ui.section>
    </div>
</x-app-layout>
