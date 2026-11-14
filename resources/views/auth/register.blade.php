<x-guest-layout>
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">Join the crew</h1>
        <p class="text-sm text-ink-muted">An account takes a minute. Then you can plan a game or open an invite.</p>
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

        <x-ui.button type="submit" class="w-full">{{ __('Create account') }}</x-ui.button>
    </form>

    <p class="mt-4 text-sm text-ink-muted">
        Already playing?
        <a class="font-medium text-pitch-700 hover:text-pitch-800" href="{{ route('login') }}">Log in</a>
    </p>
</x-guest-layout>
