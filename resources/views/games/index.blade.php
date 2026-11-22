<x-app-layout title="What's on" description="See what's on near you and go play.">
    <x-ui.page-header title="What's on" subtitle="Near you. Pick one and go." />

    @include('games.partials.starting-soon', ['stripClass' => 'mt-6'])

    <div class="mt-4 grid gap-2">
        <x-ui.button :href="route('bored')">Bored?</x-ui.button>
        @auth
            <x-ui.button variant="secondary" :href="route('events.create')">Start a game</x-ui.button>
        @endauth
    </div>

    <div class="mt-6">
        @include('games.partials.list')
    </div>
</x-app-layout>
