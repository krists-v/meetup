@if (($startingSoon ?? collect())->isNotEmpty())
    <section class="{{ $stripClass ?? '' }}" aria-label="Starting soon">
        <h2 class="text-lg font-semibold tracking-tight text-ink">Starting soon</h2>
        <div class="mt-3 flex gap-3 overflow-x-auto pb-1">
            @foreach ($startingSoon as $event)
                <a href="{{ route('events.show', $event) }}" class="w-56 shrink-0 rounded-card border border-line bg-white p-4 shadow-card">
                    <p class="text-sm font-semibold text-ink">{{ $event->startsInLabel() }}</p>
                    <p class="mt-1 truncate text-base font-semibold text-ink">{{ $event->title }}</p>
                    <p class="mt-1 truncate text-sm text-ink-muted">{{ $event->sportLabel() }}</p>
                </a>
            @endforeach
        </div>
    </section>
@endif
