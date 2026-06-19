@props(['title' => 'ready2.im'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-site-meta />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ready2.im') }}</title>

        @vite(['resources/css/themes/ready2im.css', 'resources/js/app.js'])

        {{-- Unbotable: loads unbotable.js + sets window.UNBOTABLE_URL before forms wire up. --}}
        @unbotableJs
    </head>
    <body class="r2-page antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 px-4">
            <a href="{{ route('home') }}" class="text-white drop-shadow mb-2">
                <x-brand class="text-3xl" />
            </a>

            <div class="w-full sm:max-w-md mt-4 r2-window">
                <div class="r2-titlebar">
                    <span>{{ $title }}</span>
                    <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
                </div>
                <div class="r2-winbody p-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
