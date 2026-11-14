<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', ['title' => 'Varza'])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <div class="min-h-screen">
            <header class="mx-auto flex w-full max-w-3xl items-center justify-between px-4 py-4">
                <x-ui.wordmark href="/" />
                <div class="flex items-center gap-2">
                    <x-ui.button variant="ghost" :href="route('login')">Log in</x-ui.button>
                    <div class="hidden sm:block">
                        <x-ui.button :href="route('register')">Create an account</x-ui.button>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-3xl px-4 pb-16">
                <section class="overflow-hidden rounded-card bg-white shadow-card ring-1 ring-line">
                    <x-illustrations.hero />
                    <div class="space-y-4 px-5 py-6 sm:px-8">
                        <p class="text-sm font-medium text-pitch-700">Pickup sports, without the group chat</p>
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Get the crew together.</h1>
                        <p class="max-w-xl text-base text-ink-muted">Pick a time, lock the pitch, play. Varza is how about ten friends organise football, padel, tennis, and whatever else is on this week.</p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <x-ui.button :href="route('register')">Create an account</x-ui.button>
                            <x-ui.button variant="secondary" :href="route('login')">I already play</x-ui.button>
                        </div>
                    </div>
                </section>

                <section class="mt-8 grid gap-3 sm:grid-cols-3">
                    <x-ui.card class="space-y-2 p-4">
                        <x-ui.icon name="link" class="text-pitch-700" />
                        <h2 class="text-base font-semibold">One link in the chat</h2>
                        <p class="text-sm text-ink-muted">Paste it in WhatsApp. Public games are listed. Private games stay with the people who have the link.</p>
                    </x-ui.card>
                    <x-ui.card class="space-y-2 p-4">
                        <x-ui.icon name="users" class="text-pitch-700" />
                        <h2 class="text-base font-semibold">Going, maybe, or not</h2>
                        <p class="text-sm text-ink-muted">Everyone sees the count. A full game still takes maybes.</p>
                    </x-ui.card>
                    <x-ui.card class="space-y-2 p-4">
                        <x-ui.icon name="calendar" class="text-pitch-700" />
                        <h2 class="text-base font-semibold">When the time is the hard part</h2>
                        <p class="text-sm text-ink-muted">Offer a few slots, let friends vote, then lock the winner.</p>
                    </x-ui.card>
                </section>

                <p class="mt-8 text-sm text-ink-muted">Made for a small crew. No feed, no payments, no extra chat.</p>
            </main>
        </div>
    </body>
</html>
