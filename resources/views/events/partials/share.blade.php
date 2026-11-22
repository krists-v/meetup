<x-ui.card class="space-y-3">
    <div class="space-y-1">
        <h2 class="text-lg font-bold text-ink">Share</h2>
        <p class="text-base text-ink-muted">
            Send this to someone who'd be up for it.
            @if ($event->visibility === 'private')
                A private game opens only with this link.
            @endif
        </p>
    </div>
    <p class="break-all rounded-control bg-canvas px-3 py-2 text-base text-ink ring-1 ring-line">{{ $event->shareUrl() }}</p>
    <div class="flex flex-col gap-2">
        <x-ui.button variant="secondary" :href="$event->whatsAppUrl()" target="_blank" rel="noopener noreferrer" block>
            <x-ui.icon name="link" />
            Share on WhatsApp
        </x-ui.button>
        <x-ui.button
            type="button"
            variant="secondary"
            block
            onclick="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($event->shareUrl()) }}); this.textContent = 'Copied';"
        >Copy link</x-ui.button>
        @unless ($event->isPoll())
            <x-ui.button variant="secondary" :href="route('events.calendar', $event)" block>Add to your calendar</x-ui.button>
            <x-ui.button variant="secondary" :href="\App\Support\GameCalendar::for($event)->googleUrl()" target="_blank" rel="noopener noreferrer" block>Google Calendar</x-ui.button>
        @endunless
    </div>
</x-ui.card>
