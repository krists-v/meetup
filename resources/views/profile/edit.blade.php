<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Profile" subtitle="The name friends see, and the password that keeps your account yours." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card class="p-4 sm:p-6">
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card class="p-4 sm:p-6">
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card class="p-4 sm:p-6">
            @include('profile.partials.delete-user-form')
        </x-ui.card>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">Log out</x-ui.button>
        </form>
    </div>
</x-app-layout>
