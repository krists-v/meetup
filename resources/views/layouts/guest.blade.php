<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', [
            'title' => $title ?? null,
            'description' => $description ?? null,
            'image' => $image ?? null,
            'url' => $url ?? null,
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-ink antialiased">
        <x-ui.shell>
            <main class="mx-auto w-full max-w-md px-4 py-8">
                <x-ui.card class="px-5 py-6 sm:px-6">
                    {{ $slot }}
                </x-ui.card>
            </main>
        </x-ui.shell>
    </body>
</html>
