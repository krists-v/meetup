<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Upcoming</h2>
            <a href="{{ route('events.create') }}" class="inline-flex items-center px-3 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                New event
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-8">
            @if (session('status'))
                <p class="rounded-md bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</p>
            @endif

            <section class="space-y-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">My upcoming events</h3>
                @forelse ($myEvents as $event)
                    @include('events.partials.card', ['event' => $event])
                @empty
                    <p class="bg-white rounded-lg shadow-sm px-4 py-5 text-sm text-gray-600">You have no upcoming events. Create one, or join a public game below.</p>
                @endforelse
            </section>

            <section class="space-y-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Public upcoming events</h3>
                @forelse ($publicEvents as $event)
                    @include('events.partials.card', ['event' => $event])
                @empty
                    <p class="bg-white rounded-lg shadow-sm px-4 py-5 text-sm text-gray-600">No other public games are scheduled.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
