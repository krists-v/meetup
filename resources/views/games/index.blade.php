@php
    $list = 'games.partials.list';
@endphp

@if (auth()->check())
    <x-app-layout title="What's on" description="See what's on near you and go play.">
        <x-slot name="header">
            <x-ui.page-header title="What's on" subtitle="Near you. Pick one and go.">
                <x-slot name="actions">
                    <div class="hidden sm:block">
                        <x-ui.button :href="route('events.create')">Start a game</x-ui.button>
                    </div>
                </x-slot>
            </x-ui.page-header>
        </x-slot>

        @include($list)
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            @include('partials.meta', [
                'title' => "What's on",
                'description' => "See what's on near you and go play.",
                'url' => url()->full(),
            ])
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>
        <body class="bg-canvas font-sans text-ink antialiased">
            <div class="min-h-screen">
                <header class="mx-auto flex w-full max-w-content items-center justify-between px-4 py-4">
                    <x-ui.wordmark href="/" />
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="ghost" :href="route('login')">Log in</x-ui.button>
                        <x-ui.button :href="route('register')">Start a game</x-ui.button>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-content px-4 pb-16">
                    <x-ui.page-header title="What's on" subtitle="Near you. Pick one and go." />
                    <div class="mt-6">
                        @include($list)
                    </div>
                    <x-ui.footer class="mt-8" />
                </main>
            </div>
        </body>
    </html>
@endif
