@props(['sport' => 'other'])

@php
    $sport = array_key_exists($sport, \App\Models\Event::SPORTS) ? $sport : 'other';
@endphp

<svg {{ $attributes->merge(['class' => 'h-32 w-full', 'viewBox' => '0 0 320 128', 'fill' => 'none', 'preserveAspectRatio' => 'xMidYMid slice']) }} aria-hidden="true">
    <rect width="320" height="128" fill="#e3f4e8" />
    @switch($sport)
        @case('football')
            <path d="M0 78h320v50H0z" fill="#1c633b" />
            <circle cx="168" cy="103" r="18" stroke="#e3f4e8" stroke-width="2" />
            <path d="M168 85v36M150 103h36" stroke="#e3f4e8" stroke-width="2" />
            <rect x="18" y="88" width="36" height="24" rx="2" stroke="#e3f4e8" stroke-width="2" />
            <x-illustrations.person transform="translate(86 46)" shirt="#ffffff" />
            <x-illustrations.person transform="translate(236 50)" shirt="#144129" />
            <circle cx="150" cy="58" r="8" fill="#f7faf8" stroke="#1c633b" stroke-width="2" />
            @break
        @case('basketball')
            <rect x="214" y="28" width="8" height="72" rx="2" fill="#144129" />
            <rect x="196" y="28" width="44" height="28" rx="3" fill="#ffffff" stroke="#144129" stroke-width="2" />
            <circle cx="200" cy="70" r="14" stroke="#1c633b" stroke-width="2" />
            <path d="M188 78c8 10 16 10 24 0" stroke="#1c633b" stroke-width="1.5" />
            <x-illustrations.person transform="translate(78 52)" shirt="#ffffff" />
            <circle cx="132" cy="46" r="12" fill="#f7faf8" stroke="#1c633b" stroke-width="2" />
            <path d="M132 34c6 6 6 16 0 24M120 46c6-6 16-6 24 0" stroke="#1c633b" stroke-width="1.5" />
            @break
        @case('tennis')
            <path d="M40 96h240" stroke="#c5e8d0" stroke-width="8" />
            <rect x="154" y="28" width="6" height="72" fill="#144129" />
            <path d="M160 40v48" stroke="#f7faf8" stroke-width="2" stroke-dasharray="3 3" />
            <path d="M78 58c16-18 28-8 28 8" stroke="#1c633b" stroke-width="3" stroke-linecap="round" />
            <ellipse cx="92" cy="62" rx="8" ry="10" stroke="#1c633b" stroke-width="2" />
            <path d="M214 58c16-18 28-8 28 8" stroke="#1c633b" stroke-width="3" stroke-linecap="round" />
            <ellipse cx="228" cy="62" rx="8" ry="10" stroke="#1c633b" stroke-width="2" />
            <circle cx="160" cy="52" r="5" fill="#f7faf8" stroke="#1c633b" stroke-width="1.5" />
            <x-illustrations.person transform="translate(48 58)" shirt="#ffffff" />
            <x-illustrations.person transform="translate(268 58)" shirt="#144129" />
            @break
        @case('padel')
            <rect x="36" y="28" width="248" height="76" rx="8" fill="#d7efe0" stroke="#1c633b" stroke-width="2" />
            <path d="M160 28v76M36 66h248" stroke="#1c633b" stroke-width="2" />
            <rect x="70" y="48" width="10" height="16" rx="4" fill="#144129" />
            <rect x="240" y="48" width="10" height="16" rx="4" fill="#ffffff" stroke="#144129" stroke-width="1.5" />
            <x-illustrations.person transform="translate(108 46)" shirt="#ffffff" />
            <x-illustrations.person transform="translate(210 46)" shirt="#97d4ad" />
            @break
        @case('hockey')
            <path d="M196 36h48v40H196z" fill="none" stroke="#144129" stroke-width="3" />
            <path d="M196 76h48" stroke="#1c633b" stroke-width="3" />
            <path d="M86 78c28-6 46-28 70-34" stroke="#144129" stroke-width="3" stroke-linecap="round" />
            <rect x="154" y="40" width="16" height="8" rx="2" fill="#122117" />
            <x-illustrations.person transform="translate(72 48)" shirt="#ffffff" />
            <path d="M0 96h320" stroke="#c5e8d0" stroke-width="8" />
            @break
        @case('volleyball')
            <rect x="150" y="24" width="6" height="80" fill="#144129" />
            <path d="M36 48h248" stroke="#f7faf8" stroke-width="3" />
            <path d="M36 48c8 8 8 16 0 24M60 48c8 8 8 16 0 24M84 48c8 8 8 16 0 24M108 48c8 8 8 16 0 24M132 48c8 8 8 16 0 24M168 48c8 8 8 16 0 24M192 48c8 8 8 16 0 24M216 48c8 8 8 16 0 24M240 48c8 8 8 16 0 24M264 48c8 8 8 16 0 24" stroke="#ffffff" stroke-width="1.5" />
            <circle cx="118" cy="36" r="10" fill="#f7faf8" stroke="#1c633b" stroke-width="2" />
            <path d="M112 32c8 4 10 10 8 16" stroke="#1c633b" stroke-width="1.5" />
            <x-illustrations.person transform="translate(64 58)" shirt="#ffffff" />
            <x-illustrations.person transform="translate(250 58)" shirt="#144129" />
            @break
        @case('table-tennis')
            <rect x="48" y="46" width="224" height="48" rx="6" fill="#e0f2f2" stroke="#125a60" stroke-width="2" />
            <path d="M160 46v48" stroke="#125a60" stroke-width="3" />
            <circle cx="118" cy="70" r="6" fill="#f7faf8" stroke="#125a60" stroke-width="2" />
            <x-illustrations.person transform="translate(28 50)" shirt="#ffffff" />
            <x-illustrations.person transform="translate(268 50)" shirt="#125a60" />
            @break
        @case('bowling')
            <path d="M70 96c40-28 140-28 180 0" stroke="#e7d3bf" stroke-width="10" stroke-linecap="round" />
            <circle cx="196" cy="58" r="7" fill="#f7faf8" stroke="#80481c" stroke-width="2" />
            <circle cx="214" cy="50" r="7" fill="#f7faf8" stroke="#80481c" stroke-width="2" />
            <circle cx="232" cy="58" r="7" fill="#f7faf8" stroke="#80481c" stroke-width="2" />
            <circle cx="96" cy="78" r="12" fill="#80481c" />
            <x-illustrations.person transform="translate(48 46)" shirt="#ffffff" />
            @break
        @case('climbing')
            <rect x="96" y="16" width="128" height="100" rx="8" fill="#e8eae4" stroke="#484e46" stroke-width="2" />
            <circle cx="132" cy="40" r="6" fill="#484e46" />
            <circle cx="176" cy="58" r="6" fill="#f7faf8" stroke="#484e46" stroke-width="2" />
            <circle cx="148" cy="82" r="6" fill="#484e46" />
            <x-illustrations.person transform="translate(150 48)" shirt="#ffffff" />
            @break
        @case('running')
            <path d="M24 96h272" stroke="#e7d3bf" stroke-width="8" stroke-linecap="round" />
            <path d="M40 96c20-16 36-16 52 0" stroke="#8c4030" stroke-width="3" stroke-linecap="round" />
            <x-illustrations.person transform="translate(150 46)" shirt="#ffffff" />
            <circle cx="210" cy="70" r="8" fill="#fae8e2" stroke="#8c4030" stroke-width="2" />
            @break
        @case('cycling')
            <circle cx="108" cy="84" r="18" stroke="#305a48" stroke-width="3" />
            <circle cx="212" cy="84" r="18" stroke="#305a48" stroke-width="3" />
            <path d="M108 84l28-28h36l40 28" stroke="#305a48" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            <x-illustrations.person transform="translate(146 36)" shirt="#ffffff" />
            @break
        @case('hiking')
            <path d="M40 100l70-52 40 28 50-40 70 64H40z" fill="#e8f0dc" stroke="#40582a" stroke-width="2" stroke-linejoin="round" />
            <x-illustrations.person transform="translate(150 48)" shirt="#ffffff" />
            <circle cx="230" cy="36" r="10" fill="#e7d3bf" />
            @break
        @default
            <circle cx="160" cy="64" r="36" fill="#d7efe0" />
            <x-illustrations.person transform="translate(132 48)" shirt="#ffffff" />
            <x-illustrations.person transform="translate(160 40)" shirt="#1c633b" />
            <x-illustrations.person transform="translate(188 48)" shirt="#97d4ad" />
            <circle cx="232" cy="86" r="8" fill="#f7faf8" stroke="#1c633b" stroke-width="2" />
    @endswitch
</svg>
