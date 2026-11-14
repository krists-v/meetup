@php
    $tone = match ($event->sport_type) {
        'football' => 'bg-emerald-100 text-emerald-800',
        'tennis' => 'bg-lime-100 text-lime-800',
        'basketball' => 'bg-orange-100 text-orange-800',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<a href="{{ route('events.show', $event) }}" class="block bg-white rounded-lg shadow-sm px-4 py-4 hover:bg-gray-50">
    <div class="flex items-start justify-between gap-3">
        <h4 class="font-semibold text-gray-900">{{ $event->title }}</h4>
        <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $tone }}">{{ $event->sportLabel() }}</span>
    </div>
    <p class="mt-2 text-sm text-gray-700">{{ $event->event_date->format('D j M, H:i') }}</p>
    <p class="text-sm text-gray-600">{{ $event->location }}</p>
    <p class="mt-2 text-xs text-gray-500">
        {{ $event->going_participants_count }} going
        @if ($event->max_participants)
            · max {{ $event->max_participants }}
        @endif
        @if ($event->visibility === 'private')
            · private
        @endif
    </p>
</a>
