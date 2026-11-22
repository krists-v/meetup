@php
    $previewTitle = $title ?? 'Ejam';
    $previewDescription = $description ?? \App\Support\LinkPreview::page()->description;
    $previewImage = $image ?? \App\Support\LinkPreview::image('default');
    $previewUrl = $url ?? url()->current();
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $previewTitle }}</title>
<meta name="description" content="{{ $previewDescription }}">
<meta name="theme-color" content="#F5F5F7">
<meta property="og:site_name" content="Ejam">
<meta property="og:title" content="{{ $previewTitle }}">
<meta property="og:description" content="{{ $previewDescription }}">
<meta property="og:image" content="{{ $previewImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:url" content="{{ $previewUrl }}">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $previewTitle }}">
<meta name="twitter:description" content="{{ $previewDescription }}">
<meta name="twitter:image" content="{{ $previewImage }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
