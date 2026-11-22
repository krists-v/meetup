<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', [
            'title' => 'EJAM',
            'description' => "Bored at home? See what's on near you and go play.",
            'url' => url('/'),
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <x-ui.shell>
            @include('games.partials.starting-soon', ['stripClass' => 'mx-auto w-full max-w-5xl px-4 pt-8'])

            <div class="mx-auto grid w-full max-w-5xl items-center gap-12 px-4 py-16 lg:grid-cols-2 lg:py-24">
                <div class="space-y-6">
                    <h1 class="text-5xl font-bold tracking-tight sm:text-6xl">Bored at home? Go play.</h1>
                    <p class="text-xl text-ink-muted">Less scrolling, more playing.</p>
                    <p class="max-w-md text-lg text-ink-muted">See what's on near you. Padel, tennis, bowling, climbing. Then go.</p>
                    <div class="grid gap-3">
                        <x-ui.button :href="route('bored')">Bored?</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('games.index')">See what's on</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('register')">Start a game</x-ui.button>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-sm" aria-hidden="true">
                    <div class="overflow-hidden rounded-card border border-line bg-white shadow-card">
                        <div class="px-3 pt-3">
                            <div class="relative overflow-hidden rounded-2xl">
                                <img src="{{ asset('images/sports/padel.svg') }}" alt="" class="h-48 w-full object-cover" />
                                <x-ui.image-pill label="Padel" />
                            </div>
                        </div>
                        <div class="space-y-4 px-5 pb-5 pt-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-xl font-bold text-ink">Saturday padel</p>
                                <x-ui.badge class="shrink-0">2 spots left</x-ui.badge>
                            </div>
                            <div class="space-y-3">
                                <x-ui.info-row variant="date">Saturday, 21 November · 12:00</x-ui.info-row>
                                <x-ui.info-row variant="place">Riga · Mežaparks Tennis Courts</x-ui.info-row>
                                <x-ui.info-row variant="spots">2 / 4 players</x-ui.info-row>
                                <x-ui.info-row variant="level">Intermediate</x-ui.info-row>
                            </div>
                            <div class="border-t border-line pt-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-base text-ink-muted">Hosted by Alex</p>
                                    <p class="text-base font-bold text-ink">€5 / person</p>
                                </div>
                                <x-ui.button tabindex="-1" block class="mt-4">I'm in</x-ui.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <main>
                <section class="mx-auto w-full max-w-5xl px-4 py-16">
                    <h2 class="text-3xl font-bold tracking-tight">Happening this weekend</h2>
                    @if ($weekendGames->isEmpty())
                        <x-ui.empty-state
                            class="mt-6"
                            title="Nothing on this weekend."
                            description="See what's on another day, or start a game."
                        >
                            <x-slot:action>
                                <x-ui.button :href="route('games.index')" block>See what's on</x-ui.button>
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

                <section class="mx-auto w-full max-w-5xl px-4 pb-16">
                    <h2 class="text-3xl font-bold tracking-tight">Rainy day? Go indoors</h2>
                    <p class="mt-2 max-w-xl text-ink-muted">Bowling, climbing, padel. The weather can stay outside.</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach (App\Models\Event::ACTIVITIES as $slug => $activity)
                            @if (($activity['setting'] ?? null) === 'indoor')
                                <a href="{{ route('games.index', ['setting' => 'indoor', 'sport' => $slug]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-base font-medium text-ink hover:bg-mist focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2">
                                    <x-ui.icon :name="App\Models\Event::activityIcon($slug)" class="h-4 w-4" />
                                    {{ $activity['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-16">
                    <h2 class="text-3xl font-bold tracking-tight">How it works</h2>
                    <ol class="mt-6 grid gap-4 sm:grid-cols-3">
                        <li class="rounded-card border border-line bg-white p-6 shadow-card">
                            <x-ui.icon name="map-pin" class="h-6 w-6 text-ink-faint" />
                            <h3 class="mt-4 text-lg font-bold">Find a game</h3>
                            <p class="mt-1 text-base text-ink-muted">See what's on near you.</p>
                        </li>
                        <li class="rounded-card border border-line bg-white p-6 shadow-card">
                            <x-ui.icon name="check" class="h-6 w-6 text-ink-faint" />
                            <h3 class="mt-4 text-lg font-bold">Tap I'm in</h3>
                            <p class="mt-1 text-base text-ink-muted">One tap, and you're on the list.</p>
                        </li>
                        <li class="rounded-card border border-line bg-white p-6 shadow-card">
                            <x-ui.icon name="users" class="h-6 w-6 text-ink-faint" />
                            <h3 class="mt-4 text-lg font-bold">Show up and play</h3>
                            <p class="mt-1 text-base text-ink-muted">Meet someone new and play.</p>
                        </li>
                    </ol>
                    <a href="{{ route('how-it-works') }}" class="mt-6 inline-flex min-h-11 items-center text-base font-medium text-ink underline">See how a game works</a>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-16">
                    <h2 class="text-3xl font-bold tracking-tight">New here? Totally fine</h2>
                    <p class="mt-3 max-w-xl text-ink-muted">You don't need a regular group. Beginners are welcome. Show up and meet someone new.</p>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-16">
                    <h2 class="text-3xl font-bold tracking-tight">Pick an activity</h2>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach (App\Models\Event::SPORTS as $slug => $label)
                            @if ($slug !== 'other')
                                <x-ui.chip :href="route('games.index', ['sport' => $slug])">
                                    <x-ui.icon :name="App\Models\Event::activityIcon($slug)" class="h-4 w-4" />
                                    {{ $label }}
                                </x-ui.chip>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="mx-auto w-full max-w-5xl px-4 pb-16 text-center">
                    <h2 class="text-5xl font-bold tracking-tight sm:text-6xl">Your weekend just got a plan.</h2>
                    <p class="mx-auto mt-3 max-w-md text-ink-muted">Less time on the couch. More time on a court, a lane, or a trail.</p>
                    <div class="mx-auto mt-6 grid max-w-sm gap-3">
                        <x-ui.button :href="route('games.index')">See what's on</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('register')">Start a game</x-ui.button>
                    </div>
                </section>
            </main>
        </x-ui.shell>
    </body>
</html>
