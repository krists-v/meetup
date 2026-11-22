<x-guest-layout title="Confirm your email" description="Open the link we sent so we know this address is yours.">
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Confirm your email</h1>
        <p class="text-base text-ink-muted">We sent a link. Open it so we know this address is yours. If it's missing, we can send another.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <p class="mt-4 text-sm font-medium text-success">
            A new link is on its way.
        </p>
    @endif

    <div class="mt-6 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit">Send it again</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost">{{ __('Log out') }}</x-ui.button>
        </form>
    </div>
</x-guest-layout>
