<x-app-layout :title="$player->name" :description="$player->name">
    <x-ui.page-header :title="$player->name" :subtitle="auth()->user()->is($player) ? 'This is you.' : 'On the games they join.'" />

    <div class="mt-6 space-y-4">
        <x-ui.card class="flex items-center gap-3">
            <x-ui.avatar :name="$player->name" :user="$player" />
            <x-ui.badge tone="neutral" data-attendance="{{ $player->attendanceLabel() }}">{{ $player->attendanceLabel() }}</x-ui.badge>
        </x-ui.card>

        @if (auth()->user()->is($player))
            <x-ui.card class="space-y-3">
                <p class="text-base text-ink-muted">This is you.</p>
                <x-ui.button :href="route('profile.edit')" variant="secondary">Edit your profile</x-ui.button>
            </x-ui.card>
        @else
            <x-ui.card class="space-y-4" id="report">
                @include('players.partials.actions', ['player' => $player, 'prefix' => 'profile', 'open' => true])
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
