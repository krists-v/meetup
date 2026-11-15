@props(['event' => null, 'label' => null])

<x-ui.chip :sport="$event?->sportSelection() ?? 'other'" :label="$label ?? $event?->sportLabel()" {{ $attributes }} />
