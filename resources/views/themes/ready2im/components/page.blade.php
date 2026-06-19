@props(['title' => 'ready2.im', 'max' => 'max-w-3xl'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ config('app.name', 'ready2.im') }}</title>
    @vite(['resources/css/themes/ready2im.css', 'resources/js/app.js'])
</head>
<body class="r2-page antialiased">

    <header class="{{ $max }} mx-auto px-4 py-4 flex items-center justify-between text-white">
        <a href="{{ route('home') }}"><x-brand class="text-xl" /></a>
        <a href="{{ route('home') }}" class="r2-btn">← Home</a>
    </header>

    <main class="{{ $max }} mx-auto px-4 pb-16">
        <div class="r2-window">
            <div class="r2-titlebar">
                <span>📄 {{ $title }}</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </main>

    <footer class="{{ $max }} mx-auto px-4 py-8 border-t border-slate-500/30 text-slate-800 text-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <span>© {{ date('Y') }} ready2.im — independent &amp; ad-free since 2001</span>
        <span class="flex gap-4">
            <a href="{{ route('help') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Help</a>
            <a href="{{ route('terms') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Terms</a>
            <a href="{{ route('privacy') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Privacy</a>
        </span>
    </footer>

</body>
</html>
