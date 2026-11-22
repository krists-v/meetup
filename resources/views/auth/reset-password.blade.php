<x-guest-layout title="Choose a new password" description="Make it long, and don't reuse it.">
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Choose a new password</h1>
        <p class="text-base text-ink-muted">Make it long, and don't reuse it.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-2">
            <x-ui.label for="email" :value="__('Email')" />
            <x-ui.input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
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

        <x-ui.button type="submit" class="w-full">Save</x-ui.button>
    </form>

    <p class="mt-4 text-base">
        <a class="font-medium text-ink underline underline-offset-2 hover:text-ink-muted" href="{{ route('login') }}">Back to log in</a>
    </p>
</x-guest-layout>
