<x-guest-layout>
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">Forgot your password?</h1>
        <p class="text-sm text-ink-muted">Tell us your email and we will send a link to choose a new one.</p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <x-ui.label for="email" :value="__('Email')" />
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <x-ui.button type="submit" class="w-full">{{ __('Send reset link') }}</x-ui.button>
    </form>

    <p class="mt-4 text-sm">
        <a class="font-medium text-pitch-700 hover:text-pitch-800" href="{{ route('login') }}">Back to log in</a>
    </p>
</x-guest-layout>
