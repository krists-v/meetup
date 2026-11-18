<section>
    <header>
        <h2 class="text-lg font-semibold text-ink">Your details</h2>
        <p class="mt-1 text-sm text-ink-muted">This is the name people see when you show up.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <div class="space-y-2">
            <x-ui.label for="avatar" value="Photo" />
            @if ($user->avatarUrl())
                <x-ui.avatar :name="$user->name" :user="$user" class="h-16 w-16 text-lg" />
                <label class="flex min-h-11 items-center gap-2 text-sm text-ink">
                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-line text-pitch-700 focus:ring-pitch-600" @checked(old('remove_avatar')) />
                    Remove photo
                </label>
            @endif
            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-sm text-ink file:me-3 file:min-h-11 file:rounded-control file:border-0 file:bg-pitch-700 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white" />
            <p class="text-sm text-ink-muted">Optional. A square photo works best.</p>
            <x-ui.error :messages="$errors->get('avatar')" />
        </div>

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
                        We still need to confirm your email.
                        <button form="send-verification" class="font-medium text-pitch-700 underline">
                            Send it again.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-pitch-700">
                            A new link is on its way.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-ink">Sports you play</legend>
            @php($selectedSports = old('favorite_sports', $user->favorite_sports ?? []))
            <div class="flex flex-wrap gap-2">
                @foreach (App\Models\Event::SPORTS as $value => $label)
                    <div>
                        <input
                            id="favorite-{{ $value }}"
                            type="checkbox"
                            name="favorite_sports[]"
                            value="{{ $value }}"
                            class="peer sr-only"
                            @checked(in_array($value, $selectedSports, true))
                        />
                        <label for="favorite-{{ $value }}" class="inline-flex min-h-11 cursor-pointer items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-line transition duration-150 peer-checked:bg-pitch-700 peer-checked:text-white peer-checked:ring-pitch-700 peer-focus-visible:ring-2 peer-focus-visible:ring-pitch-600 peer-focus-visible:ring-offset-2">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <x-ui.error :messages="$errors->get('favorite_sports')" />
        </fieldset>

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
