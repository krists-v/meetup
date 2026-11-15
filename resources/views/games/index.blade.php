@php
    $list = 'games.partials.list';
@endphp

@if (auth()->check())
    <x-app-layout title="Public games" description="Upcoming games anyone can join.">
        <x-slot name="header">
            <x-ui.page-header title="Public games" subtitle="Upcoming games anyone can join." />
        </x-slot>

        @include($list)
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            @include('partials.meta', [
                'title' => 'Public games',
                'description' => 'Upcoming games anyone can join.',
                'url' => url()->full(),
            ])
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>
        <body class="bg-canvas font-sans text-ink antialiased">
            <div class="min-h-screen">
                <header class="mx-auto flex w-full max-w-3xl items-center justify-between px-4 py-4">
                    <x-ui.wordmark href="/" />
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="ghost" :href="route('games.index')">Games</x-ui.button>
                        <x-ui.button variant="ghost" :href="route('login')">Log in</x-ui.button>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-content px-4 pb-16">
                    <x-ui.page-header title="Public games" subtitle="Upcoming games anyone can join." />
                    <div class="mt-6">
                        @include($list)
                    </div>
                    <x-ui.footer class="mt-8" />
                </main>
            </div>
        </body>
    </html>
@endif
