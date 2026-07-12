@php
    $appName = config('app.name', 'Sourcefolk');
    $pageTitle = filled($title ?? null)
        ? $title.' — '.$appName
        : $appName.' — Everything happening in Laravel';
    $defaultDescription = 'A community front page for the work, writing, packages, projects, and people moving Laravel forward.';
    $metaDescription = (string) str(strip_tags((string) ($description ?? $defaultDescription)))
        ->squish()
        ->limit(160, '');
    $canonicalUrl = $canonical ?? request()->url();
    $socialImageUrl = $socialImage ?? asset('social/sourcefolk-card.png');
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="application-name" content="{{ $appName }}" />
<meta name="apple-mobile-web-app-title" content="{{ $appName }}" />
<meta name="description" content="{{ $metaDescription }}" />
<meta name="theme-color" content="#090b0f" />

<title>{{ $pageTitle }}</title>
<link rel="canonical" href="{{ $canonicalUrl }}" />

<meta property="og:site_name" content="{{ $appName }}" />
<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $pageTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:image" content="{{ $socialImageUrl }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="Sourcefolk — everything happening in Laravel, woven together" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $pageTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $socialImageUrl }}" />
<meta name="twitter:image:alt" content="Sourcefolk — everything happening in Laravel, woven together" />

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" />
<link rel="manifest" href="{{ asset('site.webmanifest') }}" />

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
