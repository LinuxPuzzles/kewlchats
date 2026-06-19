@props(['title' => null, 'description' => null])
@php
    // The active front door (set by ResolveSite from the Host; falls back to .env).
    $site = \App\Support\SiteContext::current();
    $brand = $site['brand'] ?? config('app.name', 'KewlChats');
    $ogTitle = $title ?? $brand;
    $ogDescription = $description ?? ($site['description'] ?? null);
    $ogImage = ! empty($site['og_image']) ? url($site['og_image']) : null;
    $icon = $site['icon'] ?? null;
    $iconLarge = $site['icon_large'] ?? null;
@endphp
{{-- Favicon (per door). Icons live in public/icons/. --}}
@if ($icon)
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $icon }}">
@endif
@if ($iconLarge)
    <link rel="icon" type="image/png" sizes="512x512" href="{{ $iconLarge }}">
    <link rel="apple-touch-icon" href="{{ $iconLarge }}">
@endif

{{-- Open Graph / Twitter link preview (per door). --}}
<meta property="og:site_name" content="{{ $brand }}">
<meta property="og:title" content="{{ $ogTitle }}">
@if ($ogDescription)
    <meta property="og:description" content="{{ $ogDescription }}">
@endif
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endif
<meta name="twitter:card" content="summary_large_image">
