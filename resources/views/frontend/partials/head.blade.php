@php
    $seo = $seo ?? \App\Support\Seo::make();
    $siteName = $seo['site_name'] ?? setting('general.site_name', config('app.name'));
    $favicon = setting('general.favicon_path')
        ? \Illuminate\Support\Facades\Storage::disk(config('filament.default_filesystem_disk', 'public'))->url(setting('general.favicon_path'))
        : null;
    $initial = mb_strtoupper(mb_substr((string) $siteName, 0, 1));
    $inlineFavicon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%237c2233'/%3E%3Ctext x='16' y='22' font-family='Georgia,serif' font-size='18' fill='%23fdf6ec' text-anchor='middle'%3E".rawurlencode($initial).'%3C/text%3E%3C/svg%3E';
@endphp

{{-- Theme must resolve before first paint to avoid a flash of the wrong palette. --}}
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        } catch (e) {}
    })();
</script>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $seo['full_title'] }}</title>

@if (! empty($seo['description']))
    <meta name="description" content="{{ $seo['description'] }}">
@endif
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">

@if (! empty($seo['prev']))
    <link rel="prev" href="{{ $seo['prev'] }}">
@endif
@if (! empty($seo['next']))
    <link rel="next" href="{{ $seo['next'] }}">
@endif

<meta name="theme-color" content="#f6f2e9" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#100f0d" media="(prefers-color-scheme: dark)">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('-', '_', $seo['locale']) }}">
@if ($seo['image'])
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta property="og:image:alt" content="{{ $seo['image_alt'] }}">
@endif

@if ($seo['type'] === 'article')
    @if ($seo['published_at'])
        <meta property="article:published_time" content="{{ \Illuminate\Support\Carbon::parse($seo['published_at'])->toIso8601String() }}">
    @endif
    @if ($seo['updated_at'])
        <meta property="article:modified_time" content="{{ \Illuminate\Support\Carbon::parse($seo['updated_at'])->toIso8601String() }}">
    @endif
    @if ($seo['author'])
        <meta property="article:author" content="{{ $seo['author'] }}">
    @endif
    @if ($seo['section'])
        <meta property="article:section" content="{{ $seo['section'] }}">
    @endif
    @foreach (($seo['tags'] ?? []) as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if ($seo['image'])
    <meta name="twitter:image" content="{{ $seo['image'] }}">
@endif

<link rel="alternate" type="application/rss+xml" title="{{ $siteName }} — Feed" href="{{ route('feed') }}">
<link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">

<link rel="icon" href="{{ $favicon ?: $inlineFavicon }}" type="image/svg+xml">
@if ($favicon)
    <link rel="apple-touch-icon" href="{{ $favicon }}">
@endif

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])

@foreach (($seo['schema'] ?? []) as $schema)
    @if (! empty($schema))
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endforeach

@stack('head')
