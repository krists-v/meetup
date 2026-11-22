<x-app-layout title="What's on" description="See what's on near you and go play.">
    <x-slot name="header">
        <x-ui.page-header title="What's on" subtitle="Near you. Pick one and go.">
            @auth
                <x-slot name="actions">
                    <div class="hidden sm:block">
                        <x-ui.button :href="route('events.create')">Start a game</x-ui.button>
                    </div>
                </x-slot>
            @endauth
        </x-ui.page-header>
    </x-slot>

    @include('games.partials.list')
</x-app-layout>
