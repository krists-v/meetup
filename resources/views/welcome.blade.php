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
    <body class="bg-white font-sans text-ink antialiased">
        <x-ui.shell>
            <div class="mx-auto grid w-full max-w-5xl items-center gap-10 px-4 py-12 lg:grid-cols-2">
                <div class="space-y-5">
                    <h1 class="text-5xl font-semibold tracking-tight sm:text-6xl">Bored at home? Go play.</h1>
                    <p class="text-xl font-medium text-ink">Less scrolling, more playing.</p>
                    <p class="max-w-md text-base text-ink-muted">See what's on near you. Padel, tennis, bowling, climbing. Then go.</p>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <x-ui.button :href="route('games.index')">See what's on</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('register')">Start a game</x-ui.button>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-sm" aria-hidden="true">
                    <div class="overflow-hidden rounded-card border border-line bg-white">
                        <img src="{{ asset('images/sports/padel.svg') }}" alt="" class="h-40 w-full object-cover" />
                        <div class="space-y-3 px-5 py-5">
                            <p class="text-xl font-bold text-ink">Thursday padel</p>
                            <x-ui.info-row variant="date">Saturday, 21 November · 12:00</x-ui.info-row>
                            <x-ui.info-row variant="place">Riga · Court 2</x-ui.info-row>
                            <x-ui.info-row variant="spots">2 / 4 players</x-ui.info-row>
                            <span class="inline-flex min-h-11 w-full items-center justify-center rounded-control bg-mist text-sm font-bold text-ink">I'm in</span>
                        </div>
                    </div>
                </div>
            </div>

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
                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
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
                                <a href="{{ route('games.index', ['setting' => 'indoor', 'sport' => $slug]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-line hover:bg-mist focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2">
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
                        <li class="rounded-card border border-line bg-white p-5">
                            <x-ui.icon name="map-pin" class="h-6 w-6 text-ink-faint" />
                            <h3 class="mt-3 font-semibold">Find a game</h3>
                            <p class="mt-1 text-sm text-ink-muted">See what's on near you.</p>
                        </li>
                        <li class="rounded-card border border-line bg-white p-5">
                            <x-ui.icon name="check" class="h-6 w-6 text-ink-faint" />
                            <h3 class="mt-3 font-semibold">Tap I'm in</h3>
                            <p class="mt-1 text-sm text-ink-muted">One tap, and you're on the list.</p>
                        </li>
                        <li class="rounded-card border border-line bg-white p-5">
                            <x-ui.icon name="users" class="h-6 w-6 text-ink-faint" />
                            <h3 class="mt-3 font-semibold">Show up and play</h3>
                            <p class="mt-1 text-sm text-ink-muted">Meet someone new and play.</p>
                        </li>
                    </ol>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-14">
                    <h2 class="text-2xl font-semibold tracking-tight">New here? Totally fine</h2>
                    <p class="mt-3 max-w-xl text-ink-muted">You don't need a regular group. Beginners are welcome. Show up and meet someone new.</p>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-14">
                    <h2 class="text-2xl font-semibold tracking-tight">Pick an activity</h2>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach (App\Models\Event::SPORTS as $slug => $label)
                            @if ($slug !== 'other')
                                <a href="{{ route('games.index', ['sport' => $slug]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-line hover:bg-mist focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2">
                                    <x-ui.icon :name="App\Models\Event::activityIcon($slug)" class="h-4 w-4" />
                                    {{ $label }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-16 text-center">
                    <h2 class="text-5xl font-semibold tracking-tight">Your weekend just got a plan.</h2>
                    <p class="mx-auto mt-3 max-w-md text-ink-muted">Less time on the couch. More time on a court, a lane, or a trail.</p>
                    <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <x-ui.button :href="route('games.index')">See what's on</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('register')">Start a game</x-ui.button>
                    </div>
                </section>
            </main>
        </x-ui.shell>
    </body>
</html>
