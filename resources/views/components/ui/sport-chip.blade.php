@props(['event' => null, 'label' => null])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-pitch-100 px-2.5 py-1 text-xs font-semibold text-pitch-800']) }}>
    {{ $label ?? $event?->sportLabel() }}
</span>
