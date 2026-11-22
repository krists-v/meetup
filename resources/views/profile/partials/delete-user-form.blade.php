<section class="space-y-4">
    <header>
        <h2 class="text-lg font-semibold text-ink">Delete account</h2>
        <p class="mt-1 text-sm text-ink-muted">This removes your account for good. Games you created go with it.</p>
    </header>

    <x-ui.button
        type="button"
        variant="danger"
        block
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete account') }}</x-ui.button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4 p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-ink">Delete your account?</h2>
            <p class="text-sm text-ink-muted">Enter your password to confirm. This cannot be undone.</p>

            <div class="space-y-2">
                <x-ui.label for="password" value="Password" class="sr-only" />
                <x-ui.input id="password" name="password" type="password" placeholder="Password" />
                <x-ui.error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close')">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="danger">Delete account</x-ui.button>
            </div>
        </form>
    </x-modal>
</section>
