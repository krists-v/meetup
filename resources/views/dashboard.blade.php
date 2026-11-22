<x-app-layout title="Your games" description="The next one is up top.">
    <x-slot name="header">
        <x-ui.page-header title="Your games" subtitle="The next one is up top.">
            <x-slot name="actions">
                <div class="hidden sm:block">
                    <x-ui.button :href="route('events.create')">Start a game</x-ui.button>
                </div>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-8">
        @if ($nextGame)
            <x-ui.section title="Your next game">
                <div class="rounded-card border border-line p-2">
                    <x-ui.game-card :event="$nextGame" />
                </div>
            </x-ui.section>
        @elseif (! $hasGames)
            <x-ui.empty-state
                illustration="invites"
                title="Nothing on yet."
                description="Start a game, or see what's on nearby."
            >
                <x-slot name="action">
                    <x-ui.button :href="route('events.create')">Start a game</x-ui.button>
                </x-slot>
            </x-ui.empty-state>
        @endif

        @if ($hosting->isNotEmpty())
            <x-ui.section title="Hosting">
                @foreach ($hosting as $event)
                    <x-ui.game-card :event="$event" />
                @endforeach
            </x-ui.section>
        @endif

        @if ($joined->isNotEmpty())
            <x-ui.section title="Joined">
                @foreach ($joined as $event)
                    <x-ui.game-card :event="$event" />
                @endforeach
            </x-ui.section>
        @endif

        <x-ui.section title="Open near you">
            @include('games.partials.filters', ['route' => 'dashboard'])

            @forelse ($openGames as $event)
                <x-ui.game-card :event="$event" />
            @empty
                <x-ui.empty-state
                    illustration="public"
                    :title="$filters->active() ? 'Nothing for that.' : 'Quiet around here.'"
                    :description="$filters->active() ? 'Try another activity, or a wider day.' : 'Start something, or check back this weekend.'"
                >
                    <x-slot name="action">
                        @if ($filters->active())
                            <x-ui.button variant="secondary" :href="route('dashboard')">Clear filters</x-ui.button>
                        @else
                            <x-ui.button :href="route('events.create')">Start a game</x-ui.button>
                        @endif
                    </x-slot>
                </x-ui.empty-state>
            @endforelse
        </x-ui.section>
    </div>
</x-app-layout>
