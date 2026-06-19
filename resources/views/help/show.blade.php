<x-page :title="$article->title" max="max-w-5xl">
    <a href="{{ route('help') }}" class="text-sm text-slate-300 hover:text-white transition">← All help</a>

    <h1 class="mt-4 text-3xl font-bold text-slate-100">{{ $article->title }}</h1>
    @if ($article->summary)
        <p class="mt-1 text-slate-400">{{ $article->summary }}</p>
    @endif

    <div class="prose-legal mt-6">
        {!! $article->html !!}
    </div>

    @if ($related->isNotEmpty())
        <div class="mt-12 border-t border-white/10 pt-6">
            <h2 class="text-lg font-semibold text-slate-100">More in {{ $article->category }}</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($related as $r)
                    <li><a href="{{ route('help.show', $r->slug) }}" class="text-fuchsia-300 hover:text-fuchsia-200 underline">{{ $r->title }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif
</x-page>
