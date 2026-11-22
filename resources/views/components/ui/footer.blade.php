<footer {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-4 gap-y-1 text-base text-ink-muted']) }}>
    <p class="w-full text-ink-faint">See what's on. Then go play.</p>
    <a class="inline-flex min-h-11 items-center font-medium text-ink-muted hover:text-ink" href="{{ route('how-it-works') }}">How it works</a>
    <a class="inline-flex min-h-11 items-center font-medium text-ink-muted hover:text-ink" href="{{ route('privacy') }}">Privacy</a>
    <a class="inline-flex min-h-11 items-center font-medium text-ink-muted hover:text-ink" href="{{ route('terms') }}">Terms</a>
    <a class="inline-flex min-h-11 items-center font-medium text-ink-muted hover:text-ink" href="mailto:{{ config('app.contact_email') ?: 'hello@example.com' }}">Send feedback</a>
</footer>
