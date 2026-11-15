<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', [
            'title' => 'Varza',
            'description' => "Bored at home? See what's on near you and go play.",
            'url' => url('/'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <section class="bg-pitch-mesh bg-grain">
            <header class="relative z-10 mx-auto flex w-full max-w-5xl items-center justify-between px-4 py-4">
                <x-ui.wordmark href="/" />
                <div class="flex items-center gap-2">
                    <div class="hidden sm:block">
                        <x-ui.button variant="ghost" :href="route('games.index')">See what's on</x-ui.button>
                    </div>
                    <x-ui.button variant="ghost" :href="route('login')">Log in</x-ui.button>
                </div>
            </header>

            <div class="relative z-10 mx-auto grid w-full max-w-5xl items-center gap-10 px-4 pb-16 pt-6 lg:grid-cols-2 lg:pt-10">
                <div class="motion-hero space-y-5">
                    <h1 class="text-5xl sm:text-6xl">Bored at home? Go play.</h1>
                    <p class="text-xl font-medium text-ink">Less scrolling, more playing.</p>
                    <p class="max-w-md text-base text-ink-muted">See what's on near you. Padel, tennis, bowling, climbing. Then go.</p>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <x-ui.button :href="route('games.index')">See what's on</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('register')">Start a game</x-ui.button>
                    </div>
                </div>

                <div class="motion-hero-phone mx-auto w-full max-w-[280px]" aria-hidden="true">
                    <div class="rounded-[2rem] bg-pitch-950 p-3 shadow-card">
                        <div class="overflow-hidden rounded-[1.4rem] bg-white">
                            <x-illustrations.sport sport="padel" class="h-36" />
                            <div class="space-y-1 px-4 py-4">
                                <p class="font-semibold text-ink">Thursday padel</p>
                                <x-ui.info-row variant="date">Thu, 18:00</x-ui.info-row>
                                <x-ui.info-row variant="place">Court 2</x-ui.info-row>
                                <x-ui.info-row variant="spots">3 spots left</x-ui.info-row>
                                <span class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-control bg-pitch-700 text-sm font-semibold text-white">I'm in</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main>
            <section class="mx-auto w-full max-w-5xl px-4 py-14">
                <h2 class="text-2xl font-semibold tracking-tight">Happening this weekend</h2>
                @if ($weekendGames->isEmpty())
                    <x-ui.empty-state
                        class="mt-6"
                        title="Nothing on this weekend."
                        description="See what's on another day, or start a game."
                    >
                        <x-slot:action>
                            <x-ui.button :href="route('games.index')">See what's on</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($weekendGames as $event)
                            <x-ui.game-card :event="$event" />
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="mx-auto w-full max-w-5xl px-4 pb-14">
                <h2 class="text-2xl font-semibold tracking-tight">Rainy day? Go indoors</h2>
                <p class="mt-2 max-w-xl text-ink-muted">Bowling, climbing, padel. The weather can stay outside.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach (App\Models\Event::ACTIVITIES as $slug => $activity)
                        @if (($activity['setting'] ?? null) === 'indoor')
                            <a href="{{ route('games.index', ['setting' => 'indoor', 'sport' => $slug]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ App\Models\Event::accentClasses($slug) }} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600 focus-visible:ring-offset-2">
                                <x-ui.icon :name="App\Models\Event::activityIcon($slug)" class="h-4 w-4" />
                                {{ $activity['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

            <section class="mx-auto w-full max-w-5xl px-4 pb-14">
                <h2 class="text-2xl font-semibold tracking-tight">How it works</h2>
                <ol class="mt-6 grid gap-4 sm:grid-cols-3">
                    <li class="rounded-card bg-white p-5 shadow-card ring-1 ring-line">
                        <x-ui.icon name="map-pin" class="h-6 w-6 text-pitch-700" />
                        <h3 class="mt-3 font-semibold">Find a game</h3>
                        <p class="mt-1 text-sm text-ink-muted">See what's on near you.</p>
                    </li>
                    <li class="rounded-card bg-white p-5 shadow-card ring-1 ring-line">
                        <x-ui.icon name="check" class="h-6 w-6 text-pitch-700" />
                        <h3 class="mt-3 font-semibold">Tap I'm in</h3>
                        <p class="mt-1 text-sm text-ink-muted">One tap, and you're on the list.</p>
                    </li>
                    <li class="rounded-card bg-white p-5 shadow-card ring-1 ring-line">
                        <x-ui.icon name="users" class="h-6 w-6 text-pitch-700" />
                        <h3 class="mt-3 font-semibold">Show up and play</h3>
                        <p class="mt-1 text-sm text-ink-muted">Meet someone new and play.</p>
                    </li>
                </ol>
            </section>

            <section class="bg-pitch-band">
                <div class="mx-auto w-full max-w-5xl px-4 py-14">
                    <h2 class="text-2xl font-semibold tracking-tight">New here? Totally fine</h2>
                    <p class="mt-3 max-w-xl text-pitch-100">You don't need a regular group. Beginners are welcome. Show up and meet someone new.</p>
                </div>
            </section>

            <section class="mx-auto w-full max-w-5xl px-4 py-14">
                <h2 class="text-2xl font-semibold tracking-tight">Pick an activity</h2>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach (App\Models\Event::SPORTS as $slug => $label)
                        @if ($slug !== 'other')
                            <a href="{{ route('games.index', ['sport' => $slug]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ App\Models\Event::accentClasses($slug) }} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600 focus-visible:ring-offset-2">
                                <x-ui.icon :name="App\Models\Event::activityIcon($slug)" class="h-4 w-4" />
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

            <section class="mx-auto w-full max-w-5xl px-4 pb-16 text-center">
                <h2 class="text-5xl">Your weekend just got a plan.</h2>
                <p class="mx-auto mt-3 max-w-md text-ink-muted">Less time on the couch. More time on a court, a lane, or a trail.</p>
                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-ui.button :href="route('games.index')">See what's on</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('register')">Start a game</x-ui.button>
                </div>
            </section>
        </main>

        <x-ui.footer class="mx-auto w-full max-w-5xl px-4 pb-8" />
    </body>
</html>
