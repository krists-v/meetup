@php
    $user = auth()->user();
@endphp

<div class="min-h-screen bg-canvas text-ink">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-white focus:px-3 focus:py-2">Skip to content</a>

    <header class="sticky top-0 z-30 border-b border-line bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
            <x-ui.wordmark :href="route('dashboard')" />

            <nav class="hidden items-center gap-1 sm:flex" aria-label="Primary">
                <a href="{{ route('dashboard') }}" class="rounded-control px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-pitch-50 text-pitch-800' : 'text-ink-muted hover:bg-pitch-50 hover:text-ink' }}">Games</a>
                <a href="{{ route('events.create') }}" class="rounded-control px-3 py-2 text-sm font-medium {{ request()->routeIs('events.create') ? 'bg-pitch-50 text-pitch-800' : 'text-ink-muted hover:bg-pitch-50 hover:text-ink' }}">Plan a game</a>
            </nav>

            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" class="inline-flex items-center gap-2 rounded-full py-1 pe-2 ps-1 hover:bg-pitch-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu">
                    <x-ui.avatar :name="$user->name" />
                    <span class="hidden max-w-32 truncate text-sm font-medium sm:inline">{{ $user->name }}</span>
                </button>
                <div x-show="open" x-cloak x-transition.opacity.duration.150ms @click.outside="open = false" class="absolute end-0 mt-2 w-48 rounded-card bg-white py-1 shadow-lift ring-1 ring-line" role="menu">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-ink hover:bg-pitch-50" role="menuitem">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm text-ink hover:bg-pitch-50" role="menuitem">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div id="main">
        {{ $slot }}
    </div>

    <x-ui.footer class="mx-auto w-full max-w-5xl px-4 pb-24 pt-2 sm:pb-8" />

    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 backdrop-blur sm:hidden" style="padding-bottom: env(safe-area-inset-bottom)" aria-label="Mobile">
        <div class="grid grid-cols-3">
            <a href="{{ route('dashboard') }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('dashboard') ? 'text-pitch-700' : 'text-ink-muted' }}">
                <x-ui.icon name="home" />
                Games
            </a>
            <a href="{{ route('events.create') }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('events.create') ? 'text-pitch-700' : 'text-ink-muted' }}">
                <x-ui.icon name="plus" />
                Plan
            </a>
            <a href="{{ route('profile.edit') }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('profile.edit') ? 'text-pitch-700' : 'text-ink-muted' }}">
                <x-ui.icon name="users" />
                Profile
            </a>
        </div>
    </nav>
</div>
