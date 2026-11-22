@php
    $link = 'rounded-control px-3 py-2 text-sm font-medium text-ink-muted hover:bg-mist hover:text-ink';
    $current = 'rounded-control bg-mist px-3 py-2 text-sm font-semibold text-ink';
@endphp

<header data-app-header class="sticky top-0 z-30 border-b border-line bg-white">
    <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
        <x-ui.wordmark href="{{ url('/') }}" />

        @auth
            <nav class="hidden items-center gap-1 sm:flex" aria-label="Primary">
                <a href="{{ route('dashboard') }}" @class([$current => request()->routeIs('dashboard'), $link => ! request()->routeIs('dashboard')])>Games</a>
                <a href="{{ route('games.index') }}" @class([$current => request()->routeIs('games.index'), $link => ! request()->routeIs('games.index')])>What's on</a>
                <a href="{{ route('events.create') }}" @class([$current => request()->routeIs('events.create'), $link => ! request()->routeIs('events.create')])>Start a game</a>
            </nav>

            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" class="inline-flex items-center gap-2 rounded-full py-1 pe-2 ps-1 hover:bg-mist focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu">
                    <x-ui.avatar :name="auth()->user()->name" :user="auth()->user()" />
                    <span class="hidden max-w-32 truncate text-sm font-medium sm:inline">{{ auth()->user()->name }}</span>
                </button>
                <div x-show="open" x-cloak x-transition.opacity.duration.150ms @click.outside="open = false" class="absolute end-0 mt-2 w-48 rounded-card border border-line bg-white py-1" role="menu">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-ink hover:bg-mist" role="menuitem">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm text-ink hover:bg-mist" role="menuitem">Log out</button>
                    </form>
                </div>
            </div>
        @else
            <div class="flex items-center gap-2">
                <x-ui.button variant="ghost" :href="route('login')">Log in</x-ui.button>
                <x-ui.button variant="secondary" :href="route('register')">Sign up</x-ui.button>
            </div>
        @endauth
    </div>
</header>
