@props(['name', 'user' => null])

@php
    $initials = collect(preg_split('/\s+/u', trim((string) $name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

@if ($user?->avatarUrl())
    <img
        src="{{ $user->avatarUrl() }}"
        alt="{{ $name }}"
        {{ $attributes->merge(['class' => 'inline-flex h-8 w-8 shrink-0 rounded-full bg-pitch-700 object-cover']) }}
    />
@else
<span {{ $attributes->merge(['class' => 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pitch-700 text-xs font-semibold text-white']) }} title="{{ $name }}">
    <span aria-hidden="true">{{ $initials !== '' ? $initials : '?' }}</span>
    <span class="sr-only">{{ $name }}</span>
</span>
@endif
