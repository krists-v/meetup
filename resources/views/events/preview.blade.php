<x-guest-layout
    :title="$preview->title"
    :description="$preview->description"
    :image="$preview->image"
    :url="$preview->url"
>
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">{{ $preview->title }}</h1>
        <p class="text-sm text-ink-muted">{{ $preview->description }}</p>
    </div>

    @if ($event)
        <div class="mt-6 space-y-4">
            <div class="overflow-hidden rounded-card border border-line bg-white shadow-card">
                <img src="{{ $event->photoUrl($joinToken) }}" alt="" class="h-48 w-full object-cover" />
            </div>
            @unless ($event->isPoll())
                <x-ui.info-row variant="date">{{ $event->event_date->format('D j M, H:i') }}</x-ui.info-row>
            @endunless
            <x-ui.info-row variant="place" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
            <x-ui.activity-facts :event="$event" />
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
                    <x-ui.button variant="secondary" :href="route('events.calendar', ['event' => $event, 'token' => $joinToken])" class="w-full">Add to your calendar</x-ui.button>
                    <x-ui.button variant="secondary" :href="\App\Support\GameCalendar::for($event)->googleUrl()" target="_blank" rel="noopener noreferrer" class="w-full">Google Calendar</x-ui.button>
                </div>
            @endunless
            @if ($event->attendanceLabel() !== '')
                <x-ui.info-row variant="spots">
                    <span>{{ $event->attendanceLabel() }}</span>
                    @if ($event->isFull())
                        <x-ui.badge preset="full" />
                    @endif
                </x-ui.info-row>
            @endif
        </div>
        <form id="join-form" method="POST" action="{{ route('events.join', $event) }}" class="mt-6 space-y-3">
            @csrf
            @if ($joinToken)
                <input type="hidden" name="token" value="{{ $joinToken }}">
            @endif
            <div class="hidden sm:block">
                <x-ui.button type="submit" class="w-full">I'm in</x-ui.button>
            </div>
            <x-ui.button type="submit" name="account" value="new" variant="secondary" class="w-full">Create an account</x-ui.button>
        </form>
        <div class="h-16 sm:hidden" aria-hidden="true"></div>
        <div class="sticky-action fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white p-3 sm:hidden">
            <x-ui.button type="submit" form="join-form" class="w-full">I'm in</x-ui.button>
        </div>
    @else
        <div class="mt-6">
            <x-ui.button :href="route('login')" class="w-full">Log in</x-ui.button>
        </div>
    @endif
</x-guest-layout>
