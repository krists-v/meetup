<x-guest-layout title="Forgot your password?" description="We'll send a link to choose a new one.">
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Forgot your password?</h1>
        <p class="text-base text-ink-muted">Tell us your email. We'll send a link to choose a new one.</p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <x-ui.label for="email" :value="__('Email')" />
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <x-ui.button type="submit" class="w-full">Send the link</x-ui.button>
    </form>

    <p class="mt-4 text-sm">
        <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('login') }}">Back to log in</a>
    </p>
</x-guest-layout>
