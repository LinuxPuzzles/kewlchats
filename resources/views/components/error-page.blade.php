@props(['code' => '500', 'title' => 'Something went wrong', 'message' => ''])

{{-- Self-contained so it renders even when the app is unhappy: only CSS (no JS/Alpine),
     and the glow/brand components are pure markup. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · KewlChats</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 min-h-screen flex flex-col selection:bg-fuchsia-500/30">

    <x-glow />

    <header class="max-w-6xl w-full mx-auto px-6 py-6">
        <a href="/"><x-brand class="text-xl" /></a>
    </header>

    <main class="flex-1 flex items-center justify-center px-6">
        <div class="text-center max-w-lg">
            <div class="text-7xl sm:text-8xl font-extrabold tracking-tight bg-gradient-to-r from-fuchsia-400 via-pink-400 to-indigo-400 bg-clip-text text-transparent">{{ $code }}</div>
            <h1 class="mt-4 text-2xl font-bold text-slate-100">{{ $title }}</h1>
            <p class="mt-3 text-slate-400">{{ $message }}</p>
            <a href="/" class="mt-8 inline-block px-6 py-3 rounded-xl bg-fuchsia-500 hover:bg-fuchsia-400 text-white font-semibold shadow-lg shadow-fuchsia-500/30 transition">Back home</a>
        </div>
    </main>

</body>
</html>
