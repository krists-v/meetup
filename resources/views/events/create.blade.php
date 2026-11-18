<x-app-layout title="Start a game" description="A name, an activity, and a place. Set a time, or let people pick one.">
    <x-slot name="header">
        <x-ui.page-header title="Start a game" subtitle="A name, an activity, and a place. Set a time, or let people pick one." />
    </x-slot>

    @include('events.partials.form')

    <div class="h-16 sm:hidden" aria-hidden="true"></div>
    <div class="sticky-action fixed inset-x-0 z-40 border-t border-line bg-white p-3 sm:hidden" style="bottom: calc(3.5rem + env(safe-area-inset-bottom))">
        <x-ui.button type="submit" form="game-form" class="w-full">Start the game</x-ui.button>
    </div>
</x-app-layout>
