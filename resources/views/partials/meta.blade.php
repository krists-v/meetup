<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? config('app.name', 'Varza') }}</title>
<meta name="description" content="Varza gets a small crew onto the same pitch. Pick a sport, settle a time, and play.">
<meta name="theme-color" content="#1c633b">
<meta property="og:site_name" content="Varza">
<meta property="og:title" content="{{ $title ?? config('app.name', 'Varza') }}">
<meta property="og:description" content="Get the crew together. Pick a time, lock the pitch, play.">
<meta property="og:type" content="website">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
