@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium text-pitch-700']) }} role="status">
        {{ $status }}
    </div>
@endif
