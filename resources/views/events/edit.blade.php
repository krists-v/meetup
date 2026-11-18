<x-app-layout title="Edit game" description="Change the details. The link stays the same.">
    <x-slot name="header">
        <x-ui.page-header title="Edit game" subtitle="Change the details. The link stays the same." />
    </x-slot>

    @include('events.partials.form', ['event' => $event])
</x-app-layout>
