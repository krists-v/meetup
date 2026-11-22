<x-guest-layout title="Log in" description="Log in, then see what's on.">
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Welcome back</h1>
        <p class="text-base text-ink-muted">Log in, then see what's on.</p>
    </div>

    <x-ui.status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <x-ui.label for="email" :value="__('Email')" />
            <x-ui.input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-ui.error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="password" :value="__('Password')" />
            <x-ui.input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-ui.error :messages="$errors->get('password')" />
        </div>

        <x-ui.checkbox id="remember_me" name="remember">{{ __('Remember me') }}</x-ui.checkbox>

        <x-ui.button type="submit" class="w-full">{{ __('Log in') }}</x-ui.button>
    </form>

    <p class="mt-4 flex items-center justify-between gap-3 text-base">
        @if (Route::has('password.request'))
            <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('password.request') }}">Forgot your password?</a>
        @endif
        <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('register') }}">Create an account</a>
    </p>
</x-guest-layout>
