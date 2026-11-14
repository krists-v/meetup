@props(['variant' => 'games'])

<svg {{ $attributes->merge(['class' => 'h-28 w-44', 'viewBox' => '0 0 176 112', 'fill' => 'none']) }} aria-hidden="true">
    <rect width="176" height="112" rx="16" fill="#e3f4e8" />
    @if ($variant === 'invites')
        <rect x="48" y="34" width="80" height="52" rx="8" fill="#ffffff" />
        <path d="M48 42l40 24 40-24" stroke="#1c633b" stroke-width="2" />
        <circle cx="118" cy="74" r="10" fill="#1c633b" />
        <path d="M114 74h8M118 70v8" stroke="#e3f4e8" stroke-width="1.5" />
    @elseif ($variant === 'votes')
        <rect x="28" y="36" width="32" height="44" rx="8" fill="#ffffff" />
        <rect x="72" y="28" width="32" height="52" rx="8" fill="#1c633b" />
        <rect x="116" y="40" width="32" height="40" rx="8" fill="#ffffff" />
        <path d="M80 54l6 6 12-12" stroke="#e3f4e8" stroke-width="2" />
    @elseif ($variant === 'public')
        <path d="M20 78h136" stroke="#1c633b" stroke-width="2" />
        <circle cx="88" cy="62" r="16" stroke="#1c633b" stroke-width="2" />
        <path d="M88 46v32M72 62h32" stroke="#1c633b" stroke-width="2" />
        <circle cx="40" cy="70" r="5" fill="#97d4ad" />
        <circle cx="136" cy="70" r="5" fill="#97d4ad" />
    @else
        <path d="M16 78h144" stroke="#1c633b" stroke-width="2" />
        <rect x="28" y="58" width="28" height="20" rx="2" stroke="#1c633b" stroke-width="2" />
        <circle cx="112" cy="64" r="10" fill="#ffffff" stroke="#1c633b" stroke-width="2" />
        <path d="M112 54v20M102 64h20" stroke="#1c633b" stroke-width="1.5" />
    @endif
</svg>
