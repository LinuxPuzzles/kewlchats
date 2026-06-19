@php
    $tt = '<span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site-meta />
    <title>Help &amp; Guide — {{ config('app.name', 'ready2.im') }}</title>
    <meta name="description" content="How to add friends, join rooms, start group chats and make calls on ready2.im.">
    @vite(['resources/css/themes/ready2im.css', 'resources/js/app.js'])
</head>
<body class="r2-page antialiased">

    <header class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between text-white">
        <a href="{{ route('home') }}"><x-brand class="text-xl" /></a>
        <a href="{{ route('home') }}" class="r2-btn">← Home</a>
    </header>

    <main class="max-w-5xl mx-auto px-4 pb-16 space-y-6">

        <section class="r2-window">
            <div class="r2-titlebar"><span>🛟 Help &amp; Guide</span>{!! $tt !!}</div>
            <div class="r2-winbody p-6">
                <p class="text-slate-700">Short answers to the everyday stuff — adding friends, joining rooms, calls and more.</p>
                <form method="GET" action="{{ route('help') }}" class="mt-4 flex gap-2">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search help…" class="r2-field flex-1">
                    <button class="r2-btn r2-btn--primary">Search</button>
                </form>
            </div>
        </section>

        @isset($results)
            <section class="r2-window">
                <div class="r2-titlebar r2-titlebar--idle"><span>🔍 {{ $results->count() }} {{ $results->count() === 1 ? 'result' : 'results' }} for “{{ $q }}”</span>{!! $tt !!}</div>
                <div class="r2-winbody p-2">
                    @forelse ($results as $a)
                        <a href="{{ route('help.show', $a->slug) }}" class="r2-buddy">
                            <span aria-hidden="true">📄</span>
                            <span class="min-w-0">
                                <span class="font-medium text-slate-900">{{ $a->title }}</span>
                                <span class="block text-xs text-slate-500">{!! $a->excerpt($q, 110) !!}</span>
                            </span>
                        </a>
                    @empty
                        <p class="px-3 py-4 text-sm text-slate-500">No matches. Try different words, or <a href="{{ route('help') }}" class="text-blue-700 underline">browse all topics</a>.</p>
                    @endforelse
                </div>
            </section>
        @else
            @foreach ($categories as $category => $articles)
                <section class="r2-window">
                    <div class="r2-titlebar"><span>📂 {{ $category }}</span>{!! $tt !!}</div>
                    <div class="r2-winbody p-2">
                        @foreach ($articles as $a)
                            <a href="{{ route('help.show', $a->slug) }}" class="r2-buddy">
                                <span class="r2-dot r2-dot--online"></span>
                                <span class="min-w-0">
                                    <span class="font-medium text-slate-900">{{ $a->title }}</span>
                                    <span class="block text-xs text-slate-500">{{ $a->summary }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endisset
    </main>

    <footer class="max-w-5xl mx-auto px-4 py-8 border-t border-slate-500/30 text-slate-800 text-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <span>© {{ date('Y') }} ready2.im · instant messaging, like it used to be</span>
        <span class="flex gap-4">
            <a href="{{ route('help') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Help</a>
            <a href="{{ route('terms') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Terms</a>
            <a href="{{ route('privacy') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Privacy</a>
        </span>
    </footer>

</body>
</html>
