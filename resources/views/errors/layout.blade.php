<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') · Varza</title>
        <meta name="theme-color" content="#1c633b">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-pitch-wash font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen w-full max-w-md flex-col px-4 py-6">
            <x-ui.wordmark href="{{ url('/') }}" />

            <main class="mt-8">
                <x-ui.card class="space-y-4 px-5 py-6">
                    <h1 class="text-2xl font-semibold tracking-tight">@yield('title')</h1>
                    <p class="text-sm text-ink-muted">@yield('message')</p>
                    <x-ui.button :href="url('/')">Back home</x-ui.button>
                </x-ui.card>
            </main>
        </div>
    </body>
</html>
