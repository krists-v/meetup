<x-app-layout title="Edit game" description="Change the details. The link stays the same.">
    <x-ui.page-header title="Edit game" subtitle="Change the details. The link stays the same." />

    <div class="mt-6">
        @include('events.partials.form', ['event' => $event])
    </div>

    <div class="h-16 sm:hidden" aria-hidden="true"></div>
    <div class="sticky-action fixed inset-x-0 z-40 border-t border-line bg-white p-3 sm:hidden" style="bottom: calc(3.5rem + env(safe-area-inset-bottom))">
        <x-ui.button type="submit" form="game-form" block>Save</x-ui.button>
    </div>
</x-app-layout>
