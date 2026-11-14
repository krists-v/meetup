@props(['href' => '/'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-control focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pitch-600 focus-visible:ring-offset-2']) }}>
    <svg class="h-8 w-8" viewBox="0 0 32 32" aria-hidden="true">
        <rect width="32" height="32" rx="8" fill="#1c633b" />
        <rect x="6" y="7" width="20" height="18" rx="2" fill="none" stroke="#e3f4e8" stroke-width="1.5" />
        <circle cx="16" cy="16" r="3.25" fill="none" stroke="#e3f4e8" stroke-width="1.5" />
        <path d="M16 7v18M6 16h20" stroke="#e3f4e8" stroke-width="1.5" />
    </svg>
    <span class="text-lg font-semibold tracking-tight text-ink">Varza</span>
</a>
