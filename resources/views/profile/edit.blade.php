<x-app-layout title="You" description="This is the name people see when you show up.">
    <x-ui.page-header title="You" subtitle="This is the name people see when you show up." />

    <div class="mt-6 space-y-4">
        <x-ui.button :href="route('my-games.index')" variant="secondary" block>My games</x-ui.button>
        <x-ui.card >
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card >
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card class="space-y-3">
            <header>
                <h2 class="text-lg font-bold text-ink">Blocked</h2>
                <p class="mt-1 text-base text-ink-muted">People you block stay off your games, and their notes stay hidden.</p>
            </header>
            @if ($blocked->isEmpty())
                <p class="text-base text-ink-muted">No one is blocked.</p>
            @else
                <ul class="space-y-2">
                    @foreach ($blocked as $person)
                        <li class="flex min-h-11 items-center justify-between gap-3">
                            <a href="{{ route('players.show', $person) }}" class="truncate text-base font-medium text-ink">{{ $person->name }}</a>
                            <form method="POST" action="{{ route('players.unblock', $person) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="secondary">Unblock</x-ui.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card >
            @include('profile.partials.delete-user-form')
        </x-ui.card>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary">Log out</x-ui.button>
        </form>
    </div>
</x-app-layout>
