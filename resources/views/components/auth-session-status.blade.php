@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-sm font-medium text-success']) }} role="status">
        {{ $status }}
    </div>
@endif
