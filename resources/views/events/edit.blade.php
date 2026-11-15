<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit game" subtitle="Change the details. The link stays the same." />
    </x-slot>

    @include('events.partials.form', ['event' => $event])
</x-app-layout>
