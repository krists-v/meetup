<section>
    <header>
        <h2 class="text-lg font-bold text-ink">Your details</h2>
        <p class="mt-1 text-base text-ink-muted">This is the name people see when you show up.</p>
        <p class="mt-3">
            <x-ui.badge tone="neutral" data-attendance="{{ $user->attendanceLabel() }}">{{ $user->attendanceLabel() }}</x-ui.badge>
        </p>
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
                <x-ui.avatar :name="$user->name" :user="$user" size="photo" />
                <x-ui.checkbox name="remove_avatar" value="1" :checked="(bool) old('remove_avatar')">Remove photo</x-ui.checkbox>
            @endif
            <x-ui.file id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" />
            <p class="text-base text-ink-muted">Optional. A square photo works best.</p>
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
                    <p class="text-base text-ink">
                        We still need to confirm your email.
                        <button form="send-verification" class="font-medium text-ink underline underline-offset-2">
                            Send it again.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-base font-medium text-success">
                            A new link is on its way.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <fieldset class="space-y-3">
            <legend class="text-base font-medium text-ink">Sports you play</legend>
            <p class="text-base text-ink-muted">Add your level if you want. People see it when you show up.</p>
            @php($selectedSports = old('favorite_sports', $user->favorite_sports ?? []))
            @php($savedLevels = old('sport_levels', $user->sport_levels ?? []))
            @if (collect($user->sport_levels ?? [])->filter(fn ($level, $sport) => $user->sportLevel($sport))->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach (App\Models\Event::SPORTS as $value => $label)
                        @if ($user->sportLevelLabel($value))
                            <x-ui.badge tone="neutral" data-sport-level="{{ $value }}:{{ $user->sportLevel($value) }}">{{ $label }} · {{ $user->sportLevelLabel($value) }}</x-ui.badge>
                        @endif
                    @endforeach
                </div>
            @endif
            <div class="space-y-2">
                @foreach (App\Models\Event::SPORTS as $value => $label)
                    <div class="flex flex-wrap items-center gap-2">
                        <div>
                            <input
                                id="favorite-{{ $value }}"
                                type="checkbox"
                                name="favorite_sports[]"
                                value="{{ $value }}"
                                class="peer sr-only"
                                @checked(in_array($value, $selectedSports, true))
                            />
                            <x-ui.chip :for="'favorite-'.$value">{{ $label }}</x-ui.chip>
                        </div>
                        <div class="min-w-40 flex-1 sm:flex-none">
                            <x-ui.label :for="'sport-level-'.$value" value="{{ $label }} level" class="sr-only" />
                            <x-ui.select :id="'sport-level-'.$value" :name="'sport_levels['.$value.']'" class="min-h-11 sm:w-44">
                                <option value="" @selected(($savedLevels[$value] ?? '') === '' || ($savedLevels[$value] ?? null) === null)>No level</option>
                                @foreach (App\Models\Event::LEVELS as $level => $levelLabel)
                                    <option value="{{ $level }}" @selected(($savedLevels[$value] ?? null) === $level)>{{ $levelLabel }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>
                @endforeach
            </div>
            <x-ui.error :messages="$errors->get('favorite_sports')" />
            <x-ui.error :messages="$errors->get('sport_levels')" />
            @foreach (App\Models\Event::SPORTS as $value => $label)
                <x-ui.error :messages="$errors->get('sport_levels.'.$value)" />
            @endforeach
        </fieldset>

        <div class="space-y-2">
            <x-ui.label for="game_reminders" value="Reminders" />
            <input type="hidden" name="game_reminders" value="0">
            <x-ui.checkbox id="game_reminders" name="game_reminders" value="1" :checked="filter_var(old('game_reminders', $user->game_reminders), FILTER_VALIDATE_BOOLEAN)">Remind me before a game</x-ui.checkbox>
            <p class="text-base text-ink-muted">About three hours before. You'll get the time, the place, and who's in.</p>
        </div>

        <div class="space-y-3">
            <x-ui.button type="submit" block>{{ __('Save') }}</x-ui.button>

            @if (session('status') === 'profile-updated')
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
