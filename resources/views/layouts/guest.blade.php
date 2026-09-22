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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-6 py-10">
            <a href="/" class="flex items-center gap-2">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <x-application-logo class="h-4 w-4" />
                </span>
                <span class="text-lg font-semibold tracking-tight text-slate-900">Pomodoro</span>
            </a>

            <div class="mt-6 w-full overflow-hidden rounded-2xl bg-white px-6 py-8 shadow-card ring-1 ring-slate-900/5 sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
