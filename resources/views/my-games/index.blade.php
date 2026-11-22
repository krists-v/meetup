<x-app-layout title="My games" description="Games you host, and ones you're in.">
    <x-ui.page-header title="My games" subtitle="Games you host, and ones you're in." />

    <div class="mt-6 space-y-4">
        <div class="flex flex-wrap gap-2">
            <x-ui.chip :href="route('my-games.index', ['tab' => 'hosting', 'when' => $when])" :selected="$tab === 'hosting'">Hosting</x-ui.chip>
            <x-ui.chip :href="route('my-games.index', ['tab' => 'playing', 'when' => $when])" :selected="$tab === 'playing'">Playing</x-ui.chip>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.chip :href="route('my-games.index', ['tab' => $tab, 'when' => 'upcoming'])" :selected="$when === 'upcoming'">Upcoming</x-ui.chip>
            <x-ui.chip :href="route('my-games.index', ['tab' => $tab, 'when' => 'past'])" :selected="$when === 'past'">Past</x-ui.chip>
        </div>

        <x-ui.error :messages="$errors->get('status')" />

        @forelse ($events as $event)
            @include('my-games.partials.row', ['event' => $event, 'tab' => $tab])
        @empty
            @if ($tab === 'hosting')
                <x-ui.empty-state
                    illustration="games"
                    :title="$when === 'past' ? 'No past games you hosted.' : 'Nothing you\'re hosting yet.'"
                    description="Pick a time and a place. People can join from the link."
                >
                    <x-slot name="action">
                        <x-ui.button :href="route('events.create')" block>Host a game</x-ui.button>
                    </x-slot>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state
                    illustration="public"
                    :title="$when === 'past' ? 'No past games.' : 'You\'re not in a game yet.'"
                    description="See what's on near you, then say you're in."
                >
                    <x-slot name="action">
                        <x-ui.button :href="route('games.index')" block>Find a game</x-ui.button>
                    </x-slot>
                </x-ui.empty-state>
            @endif
        @endforelse
    </div>
</x-app-layout>
