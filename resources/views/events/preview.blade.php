<x-guest-layout
    :title="$preview->title"
    :description="$preview->description"
    :image="$preview->image"
    :url="$preview->url"
    plain
>
    <x-ui.page-header :title="$preview->title" :subtitle="$preview->description" />

    @if ($event)
        <article class="mt-6 overflow-hidden rounded-card border border-line bg-white shadow-card">
            <div class="relative px-3 pt-3">
                <img src="{{ $event->photoUrl($joinToken) }}" alt="{{ $event->sportLabel() }}" class="h-48 w-full rounded-2xl object-cover" />
                <span class="absolute bottom-3 left-6 rounded-full bg-white px-3 py-1 text-sm font-medium text-ink">{{ $event->areaLabel() }}</span>
            </div>
            <div class="space-y-4 px-5 pb-5 pt-4">
                <h2 class="text-xl font-bold tracking-tight text-ink">{{ $event->title }}</h2>
                <div class="space-y-3 text-lg">
                    @unless ($event->isPoll())
                        <x-ui.info-row variant="date">{{ $event->event_date->format('D j M, H:i') }}</x-ui.info-row>
                    @endunless
                    <x-ui.info-row variant="place" :href="$event->mapUrl()">{{ $event->location }}</x-ui.info-row>
                    @if ($event->attendanceLabel() !== '')
                        <x-ui.info-row variant="spots">
                            <span>{{ $event->attendanceLabel() }}</span>
                            @if ($event->isFull())
                                <x-ui.badge preset="full" />
                            @endif
                        </x-ui.info-row>
                    @endif
                    <x-ui.info-row variant="level">{{ $event->levelLabel() }}</x-ui.info-row>
                </div>
                <div class="border-t border-line pt-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-base text-ink-muted">Hosted by {{ $event->creator->name }}</p>
                        @if ($event->priceLabel())
                            <p class="text-base font-bold text-ink">{{ $event->priceLabel() }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </article>

        <div class="mt-6 space-y-4">
            <div class="flex flex-col gap-2">
                <x-ui.button variant="secondary" :href="$event->whatsAppUrl()" target="_blank" rel="noopener noreferrer" block>Share on WhatsApp</x-ui.button>
                <x-ui.button
                    type="button"
                    variant="secondary"
                    block
                    onclick="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($event->shareUrl()) }}); this.textContent = 'Copied';"
                >Copy link</x-ui.button>
            </div>
            @unless ($event->isPoll())
                <div class="flex flex-col gap-2">
                    <x-ui.button variant="secondary" :href="route('events.calendar', ['event' => $event, 'token' => $joinToken])" block>Add to your calendar</x-ui.button>
                    <x-ui.button variant="secondary" :href="\App\Support\GameCalendar::for($event)->googleUrl()" target="_blank" rel="noopener noreferrer" block>Google Calendar</x-ui.button>
                </div>
            @endunless
        </div>
        <form id="join-form" method="POST" action="{{ route('events.join', $event) }}" class="mt-6 space-y-3">
            @csrf
            @if ($joinToken)
                <input type="hidden" name="token" value="{{ $joinToken }}">
            @endif
            <div class="hidden sm:block">
                <x-ui.button type="submit" block>I'm in</x-ui.button>
            </div>
            <x-ui.button type="submit" name="account" value="new" variant="secondary" block>Create an account</x-ui.button>
        </form>
        <div class="h-16 sm:hidden" aria-hidden="true"></div>
        <div class="sticky-action fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white p-3 sm:hidden">
            <x-ui.button type="submit" form="join-form" block>I'm in</x-ui.button>
        </div>
    @else
        <div class="mt-6">
            <x-ui.button :href="route('login')" block>Log in</x-ui.button>
        </div>
    @endif
</x-guest-layout>
