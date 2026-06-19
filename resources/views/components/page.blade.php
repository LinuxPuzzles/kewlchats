@props(['title' => 'KewlChats', 'max' => 'max-w-3xl'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — KewlChats</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 selection:bg-fuchsia-500/30">

    <x-glow />

    <header class="{{ $max }} mx-auto px-6 py-6 flex items-center justify-between">
        <a href="{{ route('home') }}"><x-brand class="text-xl" /></a>
        <a href="{{ route('home') }}" class="text-sm text-slate-300 hover:text-white transition">← Back home</a>
    </header>

    <main class="{{ $max }} mx-auto px-6 pb-24">
        {{ $slot }}
    </main>

    <footer class="border-t border-white/10">
        <div class="{{ $max }} mx-auto px-6 py-8 text-sm text-slate-400 flex flex-col sm:flex-row gap-3 justify-between">
            <span>© {{ date('Y') }} KewlChats — independent &amp; ad-free since 2001</span>
            <span class="flex gap-4">
                <a href="{{ route('help') }}" class="hover:text-slate-200 transition">Help</a>
                <a href="{{ route('terms') }}" class="hover:text-slate-200 transition">Terms</a>
                <a href="{{ route('privacy') }}" class="hover:text-slate-200 transition">Privacy</a>
            </span>
        </div>
    </footer>

</body>
</html>
