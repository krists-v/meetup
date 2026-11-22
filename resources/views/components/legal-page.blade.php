@props([
    'title',
    'description' => 'Plain language, not legal advice.',
    'subtitle' => 'Plain language, not legal advice.',
    'contact' => true,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta', [
            'title' => $title.' · Ejam',
            'description' => $description,
        ])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <x-ui.shell>
            <main class="mx-auto w-full max-w-content space-y-4 px-4 py-8">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight">{{ $title }}</h1>
                    <p class="text-base text-ink-muted">{{ $subtitle }}</p>
                </div>

                <x-ui.card class="space-y-4 text-base leading-6 text-ink-muted">
                    {{ $slot }}
                    @if ($contact)
                        <p>
                            Questions go to
                            <a class="font-medium text-ink underline" href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a>.
                        </p>
                    @endif
                </x-ui.card>
            </main>
        </x-ui.shell>
    </body>
</html>
