<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,follow">
    <title>@yield('title', 'Something went wrong') &middot; {{ config('app.name') }}</title>
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
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-bg font-sans text-ink antialiased">
    <header class="shell flex h-20 items-center justify-between">
        <a href="{{ url('/') }}" class="font-display text-2xl tracking-tight text-ink">{{ config('app.name') }}</a>
        <a href="{{ url('/blog') }}" class="text-sm text-ink-soft link-underline">The journal</a>
    </header>

    <main class="shell flex flex-1 items-center justify-center py-16">
        <div class="text-center">
            @yield('content')
        </div>
    </main>

    <footer class="shell py-10 text-center text-xs uppercase tracking-[0.2em] text-muted">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>
</body>
</html>
