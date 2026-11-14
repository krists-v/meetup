<x-guest-layout>
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">Confirm your email</h1>
        <p class="text-sm text-ink-muted">We sent a link to your inbox. Open it so we know the address is yours. If it is missing, we can send another.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <p class="mt-4 text-sm font-medium text-pitch-700">
            {{ __('A new link is on its way to the email you used to register.') }}
        </p>
    @endif

    <div class="mt-6 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit">{{ __('Resend email') }}</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost">{{ __('Log out') }}</x-ui.button>
        </form>
    </div>
</x-guest-layout>
