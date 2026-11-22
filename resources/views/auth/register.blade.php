<x-guest-layout title="Create an account" description="An account takes a minute. Then say you're in, or start a game.">
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Come play</h1>
        <p class="text-base text-ink-muted">An account takes a minute. Then say you're in, or start a game.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <x-ui.label for="name" :value="__('Name')" />
            <x-ui.input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-ui.error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="email" :value="__('Email')" />
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="password" :value="__('Password')" />
            <x-ui.input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-ui.error :messages="$errors->get('password')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="password_confirmation" :value="__('Confirm password')" />
            <x-ui.input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-ui.error :messages="$errors->get('password_confirmation')" />
        </div>

        <p class="text-base text-ink-muted">
            By creating an account you agree to the
            <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('terms') }}">terms</a>
            and the
            <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('privacy') }}">privacy policy</a>.
        </p>

        <x-ui.button type="submit" class="w-full">Create an account</x-ui.button>
    </form>

    <p class="mt-4 text-base text-ink-muted">
        Already playing?
        <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('login') }}">Log in</a>
    </p>
</x-guest-layout>
