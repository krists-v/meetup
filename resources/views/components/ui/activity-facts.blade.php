@props(['event'])

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <x-ui.info-row :variant="$event->isIndoor() ? 'indoor' : 'outdoor'">
        {{ $event->isIndoor() ? 'Indoors' : 'Outdoors' }}
    </x-ui.info-row>
    @if ($event->welcomesBeginners())
        <x-ui.badge preset="beginners" />
    @else
        <x-ui.info-row variant="level">{{ $event->levelLabel() }}</x-ui.info-row>
    @endif
</div>
