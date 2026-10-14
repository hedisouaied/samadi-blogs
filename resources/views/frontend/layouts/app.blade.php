<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('frontend.partials.head')
</head>
@php
    $siteName = setting('general.site_name', config('app.name'));
@endphp
<body class="min-h-screen bg-bg font-sans text-ink antialiased">

    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-5 focus:py-3 focus:text-sm focus:text-bg">
        Skip to content
    </a>

    @yield('progress')

    @include('frontend.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('frontend.partials.footer')
    @include('frontend.partials.mobile-nav')
    @include('frontend.partials.search-overlay')
    <x-frontend.back-to-top />
    @include('frontend.partials.notifications')

    @stack('scripts')
</body>
</html>
