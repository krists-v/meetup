@props(['shirt' => '#f7faf8'])

<g {{ $attributes }}>
    <circle cx="0" cy="0" r="6.5" fill="#e7d3bf" />
    <rect x="-9" y="8" width="18" height="16" rx="7" fill="{{ $shirt }}" />
    <rect x="-7" y="22" width="5.5" height="12" rx="2.5" fill="#163526" />
    <rect x="1.5" y="22" width="5.5" height="12" rx="2.5" fill="#163526" />
</g>
