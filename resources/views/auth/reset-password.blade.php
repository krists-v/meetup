<x-guest-layout>
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">Choose a new password</h1>
        <p class="text-sm text-ink-muted">Use something long, and something you do not reuse.</p>
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

        <x-ui.button type="submit" class="w-full">{{ __('Save password') }}</x-ui.button>
    </form>
</x-guest-layout>
