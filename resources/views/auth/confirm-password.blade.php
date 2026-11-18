<x-guest-layout title="Confirm it is you" description="Enter your password to continue.">
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">Confirm it is you</h1>
        <p class="text-sm text-ink-muted">Enter your password to continue.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

        <div class="space-y-2">
            <x-ui.label for="password" :value="__('Password')" />
            <x-ui.input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-ui.error :messages="$errors->get('password')" />
        </div>

        <x-ui.button type="submit" class="w-full">{{ __('Continue') }}</x-ui.button>
    </form>
</x-guest-layout>
