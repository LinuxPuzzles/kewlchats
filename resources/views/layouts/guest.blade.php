<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-site-meta />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Unbotable: loads unbotable.js + sets window.UNBOTABLE_URL before the page wires its forms. --}}
        @unbotableJs
    </head>
    <body class="font-sans text-slate-100 antialiased bg-slate-950">
        <x-glow />

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <div>
                <a href="{{ route('home') }}">
                    <x-brand class="text-3xl" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white/5 border border-white/10 backdrop-blur shadow-xl overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
