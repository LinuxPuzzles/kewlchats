@php
    $tt = '<span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->title }} — {{ config('app.name', 'ready2.im') }}</title>
    <meta name="description" content="{{ $article->summary }}">
    @vite(['resources/css/themes/ready2im.css', 'resources/js/app.js'])
</head>
<body class="r2-page antialiased">

    <header class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between text-white">
        <a href="{{ route('home') }}"><x-brand class="text-xl" /></a>
        <a href="{{ route('help') }}" class="r2-btn">← All help</a>
    </header>

    <main class="max-w-5xl mx-auto px-4 pb-16 space-y-6">
        <article class="r2-window">
            <div class="r2-titlebar"><span>📄 {{ $article->title }}</span>{!! $tt !!}</div>
            <div class="r2-winbody p-6">
                @if ($article->summary)
                    <p class="text-slate-500 -mt-1 mb-3">{{ $article->summary }}</p>
                @endif
                <div class="prose-legal">
                    {!! $article->html !!}
                </div>
            </div>
        </article>

        @if ($related->isNotEmpty())
            <section class="r2-window">
                <div class="r2-titlebar r2-titlebar--idle"><span>📂 More in {{ $article->category }}</span>{!! $tt !!}</div>
                <div class="r2-winbody p-2">
                    @foreach ($related as $r)
                        <a href="{{ route('help.show', $r->slug) }}" class="r2-buddy">
                            <span class="r2-dot r2-dot--online"></span>
                            <span class="font-medium text-slate-900">{{ $r->title }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <footer class="max-w-5xl mx-auto px-4 py-8 border-t border-slate-500/30 text-slate-800 text-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <span>© {{ date('Y') }} ready2.im — independent &amp; ad-free since 2001</span>
        <span class="flex gap-4">
            <a href="{{ route('help') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Help</a>
            <a href="{{ route('terms') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Terms</a>
            <a href="{{ route('privacy') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline">Privacy</a>
        </span>
    </footer>

</body>
</html>
