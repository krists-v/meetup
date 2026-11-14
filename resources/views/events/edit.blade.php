<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit event</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                @include('events.partials.form', ['event' => $event])
            </div>
        </div>
    </div>
</x-app-layout>
