<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- No-FOUC theme bootstrap (runs pre-paint, before Vite/JS) -->
        <script>
            (function() {
                const stored = localStorage.getItem('theme');
                const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text-strong dark:text-text-strong-dark antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-surface dark:bg-surface-dark relative">
            <div class="absolute top-4 right-4">
                <x-theme-toggle />
            </div>
            <div class="mb-6 text-center">
                <a href="/">
                    <div class="flex items-center justify-center gap-2">
                        <x-application-logo class="w-10 h-10 fill-current text-indigo-600" />
                        <span class="text-2xl font-bold text-text-strong dark:text-text-strong-dark">SewaKost</span>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 bg-surface-raised dark:bg-surface-raised-dark shadow-xl rounded-xl overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
