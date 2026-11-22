<div class="min-h-screen bg-canvas text-ink">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-white focus:px-3 focus:py-2">Skip to content</a>

    <x-ui.header />

    <div id="main">
        {{ $slot }}
    </div>

    <x-ui.footer @class(['mx-auto w-full max-w-5xl px-4 pt-2', 'pb-24 sm:pb-8' => auth()->check(), 'pb-8' => auth()->guest()]) />

    @auth
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-canvas sm:hidden" style="padding-bottom: env(safe-area-inset-bottom)" aria-label="Mobile">
            <div class="relative grid grid-cols-4" x-data="motionIndicator" data-indicator="mobile-nav" data-indicator-mode="bar">
                <span class="motion-slide pointer-events-none absolute top-0 h-0.5 rounded-full bg-ink" x-ref="indicator" hidden aria-hidden="true"></span>
                <a href="{{ route('dashboard') }}" data-active="{{ request()->routeIs('dashboard') ? '1' : '0' }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('dashboard') ? 'text-ink' : 'text-ink-muted' }}">
                    <x-ui.icon name="home" />
                    Games
                </a>
                <a href="{{ route('games.index') }}" data-active="{{ request()->routeIs('games.index') ? '1' : '0' }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('games.index') ? 'text-ink' : 'text-ink-muted' }}">
                    <x-ui.icon name="calendar" />
                    What's on
                </a>
                <a href="{{ route('events.create') }}" data-active="{{ request()->routeIs('events.create') ? '1' : '0' }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('events.create') ? 'text-ink' : 'text-ink-muted' }}">
                    <x-ui.icon name="plus" />
                    Start
                </a>
                <a href="{{ route('profile.edit') }}" data-active="{{ request()->routeIs('profile.edit') ? '1' : '0' }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-xs font-medium {{ request()->routeIs('profile.edit') ? 'text-ink' : 'text-ink-muted' }}">
                    <x-ui.icon name="users" />
                    Profile
                </a>
            </div>
        </nav>
    @endauth
</div>
