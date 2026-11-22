<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') · Varza</title>
        <meta name="theme-color" content="#F5F5F7">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <x-ui.shell>
            <main class="mx-auto w-full max-w-md px-4 py-8">
                <x-ui.card class="space-y-4">
                    <h1 class="text-3xl font-bold tracking-tight">@yield('title')</h1>
                    <p class="text-base text-ink-muted">@yield('message')</p>
                    <x-ui.button :href="url('/')">Back home</x-ui.button>
                </x-ui.card>
            </main>
        </x-ui.shell>
    </body>
</html>
