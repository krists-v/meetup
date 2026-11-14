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
    <body class="font-sans antialiased">
        <x-ui.shell>
            @isset($header)
                <div class="border-b border-line bg-white">
                    <div class="mx-auto max-w-content px-4 py-5 sm:px-6">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main class="mx-auto max-w-content px-4 py-6 pb-24 sm:px-6 sm:pb-10">
                @php
                    $status = session('status');
                    $quietStatuses = ['profile-updated', 'password-updated', 'verification-link-sent'];
                @endphp
                @if (is_string($status) && $status !== '' && ! in_array($status, $quietStatuses, true))
                    <p class="mb-4 rounded-card bg-success-soft px-4 py-3 text-sm text-pitch-800" role="status">{{ $status }}</p>
                @endif
                {{ $slot }}
            </main>
        </x-ui.shell>
    </body>
</html>
