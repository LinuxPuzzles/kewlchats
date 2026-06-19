<x-page title="Help" max="max-w-5xl">
    <h1 class="text-3xl font-bold text-slate-100">Help &amp; Guide</h1>
    <p class="mt-2 text-slate-400">Short answers to the everyday stuff — adding friends, joining rooms, calls and more.</p>

    <form method="GET" action="{{ route('help') }}" class="mt-6 flex gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search help…"
               class="flex-1 rounded-lg border-white/10 bg-white/5 text-slate-100 placeholder-slate-500 focus:border-fuchsia-400 focus:ring-fuchsia-400">
        <button class="px-4 py-2 rounded-lg bg-fuchsia-500 hover:bg-fuchsia-400 text-white font-semibold transition">Search</button>
    </form>

    @isset($results)
        <div class="mt-8">
            <p class="text-sm text-slate-400">{{ $results->count() }} {{ $results->count() === 1 ? 'result' : 'results' }} for &ldquo;{{ $q }}&rdquo; · <a href="{{ route('help') }}" class="text-fuchsia-300 hover:text-fuchsia-200 underline">browse all</a></p>
            <div class="mt-4 space-y-3">
                @forelse ($results as $a)
                    <a href="{{ route('help.show', $a->slug) }}" class="block border border-white/10 rounded-xl p-4 hover:border-fuchsia-400/40 hover:bg-white/5 transition">
                        <div class="font-semibold text-slate-100">{{ $a->title }}</div>
                        <div class="text-sm text-slate-400 mt-1">{!! $a->excerpt($q) !!}</div>
                        <div class="text-xs text-slate-500 mt-2">{{ $a->category }}</div>
                    </a>
                @empty
                    <p class="text-slate-400">No matches. Try different words, or <a href="{{ route('help') }}" class="text-fuchsia-300 underline">browse all topics</a>.</p>
                @endforelse
            </div>
        </div>
    @else
        @foreach ($categories as $category => $articles)
            <section class="mt-10">
                <h2 class="text-xl font-bold text-slate-100">{{ $category }}</h2>
                <div class="mt-4 grid sm:grid-cols-2 gap-4">
                    @foreach ($articles as $a)
                        <a href="{{ route('help.show', $a->slug) }}" class="block border border-white/10 rounded-xl p-4 hover:border-fuchsia-400/40 hover:bg-white/5 transition">
                            <div class="font-semibold text-slate-100">{{ $a->title }}</div>
                            <div class="text-sm text-slate-400 mt-1">{{ $a->summary }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endisset
</x-page>
