<x-guest-layout title="Log in" description="Log in, then see what's on.">
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Welcome back</h1>
        <p class="text-base text-ink-muted">Log in, then see what's on.</p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

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

        <label for="remember_me" class="flex min-h-11 items-center gap-2">
            <input id="remember_me" type="checkbox" class="rounded border-line text-ink focus:ring-ink" name="remember">
            <span class="text-sm text-ink-muted">{{ __('Remember me') }}</span>
        </label>

        <x-ui.button type="submit" class="w-full">{{ __('Log in') }}</x-ui.button>
    </form>

    <p class="mt-4 flex items-center justify-between gap-3 text-sm">
        @if (Route::has('password.request'))
            <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('password.request') }}">Forgot your password?</a>
        @endif
        <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('register') }}">Create an account</a>
    </p>
</x-guest-layout>
