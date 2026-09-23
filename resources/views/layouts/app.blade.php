<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Pomodoro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- PWA -->
        @include('partials.pwa')

        <!-- Theme: resolve before first paint to avoid flashing the wrong scheme -->
        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('pomodoro-theme-v1') || 'system';
                    var mode = ['light', 'dark', 'system'].indexOf(stored) !== -1 ? stored : 'system';
                    var dark = mode === 'dark'
                        || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    if (dark) {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {
                    // storage unavailable: fall back to light
                }
            })();
        </script>
        <style>html { color-scheme: light dark; }</style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased dark:text-slate-200">
        <div class="min-h-screen overflow-x-clip bg-slate-100 dark:bg-slate-950">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="min-w-0">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
