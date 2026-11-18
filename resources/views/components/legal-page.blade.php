@props(['title', 'description' => 'Plain language, not legal advice.'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', [
            'title' => $title.' · Varza',
            'description' => $description,
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen w-full max-w-content flex-col px-4 py-6">
            <x-ui.wordmark href="{{ url('/') }}" />

            <main class="mt-8 flex-1 space-y-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-semibold tracking-tight">{{ $title }}</h1>
                    <p class="text-sm text-ink-muted">Plain language, not legal advice.</p>
                </div>

                <x-ui.card class="space-y-4 px-5 py-6 text-sm leading-6 text-ink-muted">
                    {{ $slot }}
                    <p>
                        Questions go to
                        <a class="font-medium text-pitch-700 hover:text-pitch-800" href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a>.
                    </p>
                </x-ui.card>
            </main>

            <x-ui.footer class="mt-8" />
        </div>
    </body>
</html>
