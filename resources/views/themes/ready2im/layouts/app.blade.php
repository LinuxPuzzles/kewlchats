<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ready2.im') }}</title>

        @vite(['resources/css/themes/ready2im.css', 'resources/js/app.js'])
    </head>
    <body class="r2-page antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="max-w-6xl mx-auto px-4 pt-6">
                    {{ $header }}
                </header>
            @endisset

            <main class="pb-16">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
