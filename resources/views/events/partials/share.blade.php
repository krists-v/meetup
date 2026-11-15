<x-ui.card class="space-y-3 p-4 sm:p-6">
    <div class="space-y-1">
        <h2 class="font-semibold text-ink">Share</h2>
        <p class="text-sm text-ink-muted">
            Send this to someone who'd be up for it.
            @if ($event->visibility === 'private')
                A private game opens only with this link.
            @endif
        </p>
    </div>
    <p class="break-all rounded-control bg-canvas px-3 py-2 text-sm text-ink ring-1 ring-line">{{ $event->shareUrl() }}</p>
    <div class="flex flex-col gap-2">
        <x-ui.button variant="secondary" :href="$event->whatsAppUrl()" target="_blank" rel="noopener noreferrer" class="w-full">
            <x-ui.icon name="link" />
            Share on WhatsApp
        </x-ui.button>
        <x-ui.button
            type="button"
            variant="secondary"
            class="w-full"
            onclick="navigator.clipboard.writeText(@js($event->shareUrl())); this.textContent = 'Copied';"
        >Copy link</x-ui.button>
        @unless ($event->isPoll())
            <x-ui.button variant="secondary" :href="route('events.calendar', $event)" class="w-full">Download .ics</x-ui.button>
            <x-ui.button variant="secondary" :href="\App\Support\GameCalendar::for($event)->googleUrl()" target="_blank" rel="noopener noreferrer" class="w-full">Google Calendar</x-ui.button>
        @endunless
    </div>
</x-ui.card>
