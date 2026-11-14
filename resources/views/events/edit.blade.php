<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit game" subtitle="Change the details. The invite link stays the same." />
    </x-slot>

    <x-ui.card class="p-4 sm:p-6">
        @include('events.partials.form', ['event' => $event])
    </x-ui.card>
</x-app-layout>
