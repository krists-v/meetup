<section>
    <header>
        <h2 class="text-lg font-semibold text-ink">Your details</h2>
        <p class="mt-1 text-sm text-ink-muted">This name is what the crew sees on a game.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <div class="space-y-2">
            <x-ui.label for="name" :value="__('Name')" />
            <x-ui.input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-ui.error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-2">
            <x-ui.label for="email" :value="__('Email')" />
            <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-ui.error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm text-ink">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="font-medium text-pitch-700 underline">
                            {{ __('Send the verification email again.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-pitch-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-ink-muted"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
