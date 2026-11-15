<x-guest-layout
    :title="$preview->title"
    :description="$preview->description"
    :image="$preview->image"
    :url="$preview->url"
>
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $preview->title }}</h1>
        <p class="text-sm text-ink-muted">{{ $preview->description }}</p>
    </div>

    @if ($event)
        <div class="mt-6 space-y-4">
            <div class="overflow-hidden rounded-card ring-1 ring-line">
                <x-illustrations.sport :sport="$event->sportSelection()" class="h-32 w-full" />
            </div>
            <p class="text-sm text-ink-muted">{{ $event->sportLabel() }}</p>
            @unless ($event->isPoll())
                <p class="text-base text-ink">{{ $event->event_date->format('D j M, H:i') }}</p>
            @endunless
            <a href="{{ $event->mapUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center text-base font-medium text-pitch-700 hover:text-pitch-800">{{ $event->location }}</a>
            <div class="flex flex-col gap-2">
                <x-ui.button variant="secondary" :href="$event->whatsAppUrl()" target="_blank" rel="noopener noreferrer" class="w-full">Share on WhatsApp</x-ui.button>
                <x-ui.button
                    type="button"
                    variant="secondary"
                    class="w-full"
                    onclick="navigator.clipboard.writeText(@js($event->shareUrl())); this.textContent = 'Copied';"
                >Copy link</x-ui.button>
            </div>
            @unless ($event->isPoll())
                <div class="flex flex-col gap-2">
                    <x-ui.button variant="secondary" :href="route('events.calendar', ['event' => $event, 'token' => $joinToken])" class="w-full">Download .ics</x-ui.button>
                    <x-ui.button variant="secondary" :href="\App\Support\GameCalendar::for($event)->googleUrl()" target="_blank" rel="noopener noreferrer" class="w-full">Google Calendar</x-ui.button>
                </div>
            @endunless
            @if ($event->attendanceLabel() !== '')
                <p class="flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                    <span>{{ $event->attendanceLabel() }}</span>
                    @if ($event->isFull())
                        <x-ui.badge tone="warn">Full</x-ui.badge>
                    @endif
                </p>
            @endif
        </div>
        <form method="POST" action="{{ route('events.join', $event) }}" class="mt-6 space-y-3">
            @csrf
            @if ($joinToken)
                <input type="hidden" name="token" value="{{ $joinToken }}">
            @endif
            <x-ui.button type="submit" class="w-full">I'm in</x-ui.button>
            <x-ui.button type="submit" name="account" value="new" variant="secondary" class="w-full">Create an account</x-ui.button>
        </form>
    @else
        <div class="mt-6">
            <x-ui.button :href="route('login')" class="w-full">Log in</x-ui.button>
        </div>
    @endif
</x-guest-layout>
