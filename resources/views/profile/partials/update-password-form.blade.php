<section>
    <header>
        <h2 class="text-lg font-bold text-ink">Password</h2>
        <p class="mt-1 text-base text-ink-muted">Make it long, and don't reuse it.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div class="space-y-2">
            <x-ui.label for="update_password_current_password" :value="__('Current password')" />
            <x-ui.input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-ui.error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="update_password_password" :value="__('New password')" />
            <x-ui.input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-ui.error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="update_password_password_confirmation" :value="__('Confirm password')" />
            <x-ui.input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-ui.error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="space-y-3">
            <x-ui.button type="submit" variant="secondary" block>Save</x-ui.button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-base text-ink-muted"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
