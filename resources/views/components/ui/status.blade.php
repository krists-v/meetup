@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-base font-medium text-success']) }} role="status">
        {{ $status }}
    </div>
@endif
