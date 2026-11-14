<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', ['title' => $title ?? config('app.name', 'Varza')])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-pitch-wash font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col px-4 py-6">
            <div class="mx-auto w-full max-w-md">
                <x-ui.wordmark href="/" />
            </div>

            <main class="mx-auto mt-8 w-full max-w-md flex-1">
                <x-ui.card class="px-5 py-6 sm:px-6">
                    {{ $slot }}
                </x-ui.card>
            </main>
        </div>
    </body>
</html>
