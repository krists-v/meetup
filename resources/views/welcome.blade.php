<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Meetup') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="px-4 py-4 flex items-center justify-between max-w-3xl mx-auto w-full">
                <span class="font-semibold text-lg">{{ config('app.name', 'Meetup') }}</span>
                <div class="flex gap-2">
                    <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium text-gray-700">Log in</a>
                    <a href="{{ route('register') }}" class="px-3 py-2 text-sm font-semibold rounded-md bg-gray-900 text-white">Register</a>
                </div>
            </header>

            <main class="flex-1 px-4 pb-12">
                <div class="max-w-3xl mx-auto pt-8 sm:pt-16">
                    <p class="text-sm font-medium text-emerald-700">Football, tennis, basketball</p>
                    <h1 class="mt-2 text-3xl sm:text-4xl font-semibold tracking-tight">Plan the next game without the WhatsApp scroll.</h1>
                    <p class="mt-4 text-base text-gray-600 leading-relaxed">Create a match, paste the invite link in the group chat, and see who is going. Built for a small group of friends, and easy to use on a phone.</p>
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('register') }}" class="inline-flex justify-center px-4 py-3 rounded-md bg-gray-900 text-white font-semibold">Create an account</a>
                        <a href="{{ route('login') }}" class="inline-flex justify-center px-4 py-3 rounded-md bg-white border border-gray-300 font-semibold">I already have one</a>
                    </div>
                    <ul class="mt-10 space-y-3 text-sm text-gray-700">
                        <li class="bg-white rounded-lg px-4 py-3 shadow-sm">Public games show up for everyone. Private games stay with the people who have the link.</li>
                        <li class="bg-white rounded-lg px-4 py-3 shadow-sm">RSVP Going, Maybe, or Not going. A full game still accepts Maybes.</li>
                        <li class="bg-white rounded-lg px-4 py-3 shadow-sm">One invite link to paste into WhatsApp. No extra chat inside the app.</li>
                    </ul>
                </div>
            </main>
        </div>
    </body>
</html>
