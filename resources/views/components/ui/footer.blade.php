<footer {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-4 text-sm text-ink-muted']) }}>
    <a class="inline-flex min-h-11 items-center font-medium text-pitch-700 hover:text-pitch-800" href="{{ route('privacy') }}">Privacy</a>
    <a class="inline-flex min-h-11 items-center font-medium text-pitch-700 hover:text-pitch-800" href="{{ route('terms') }}">Terms</a>
</footer>
