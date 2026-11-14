<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Plan a game" subtitle="A title, a sport, and a place. Set the time, or let the crew vote." />
    </x-slot>

    <x-ui.card class="p-4 sm:p-6">
        @include('events.partials.form')
    </x-ui.card>
</x-app-layout>
