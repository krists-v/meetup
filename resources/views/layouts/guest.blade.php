@props(['title' => null, 'description' => null, 'image' => null, 'url' => null, 'plain' => false])

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
    <body class="bg-canvas font-sans text-ink antialiased">
        <x-ui.shell>
            @if ($plain)
                <main class="mx-auto w-full max-w-content px-4 py-6 sm:px-6">
                    {{ $slot }}
                </main>
            @else
                <main class="mx-auto w-full max-w-md px-4 py-12">
                    <x-ui.card>
                        {{ $slot }}
                    </x-ui.card>
                </main>
            @endif
        </x-ui.shell>
    </body>
</html>
