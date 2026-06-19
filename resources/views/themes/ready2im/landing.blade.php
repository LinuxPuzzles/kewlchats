{{-- ready2.im theme — IM nostalgia as a real, scrollable, SEO-friendly landing.
     Each section is an early-2000s IM "window." Same controller data ($onlineCount, $rooms). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ready2.im — instant messaging, like it used to be</title>
    <meta name="description" content="Free instant messaging the way it used to feel: pick a name, get a buddy list, and join rooms full of people right now. Group chats, private messages and calls in your browser — no app, no ads.">
    @vite(['resources/css/themes/ready2im.css', 'resources/js/app.js'])
</head>
<body class="r2-page antialiased">

    {{-- Header --}}
    <header class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between text-white">
        <a href="{{ route('home') }}" class="text-2xl font-extrabold italic tracking-tight drop-shadow">ready2<span class="text-lime-300">.im</span></a>
        <nav class="flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="r2-btn">Open ready2.im</a>
            @else
                <a href="{{ route('login') }}" class="r2-btn">Sign On</a>
                <a href="{{ route('register') }}" class="r2-btn r2-btn--primary">Get a name</a>
            @endauth
        </nav>
    </header>

    <main class="max-w-6xl mx-auto px-4 pb-20">

        {{-- Hero: pitch window + live buddy-list window --}}
        <section class="grid lg:grid-cols-12 gap-6 items-start pt-6">

            <div class="lg:col-span-7 r2-window">
                <div class="r2-titlebar">
                    <span>🌐 ready2.im — Welcome</span>
                    <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
                </div>
                <div class="r2-winbody">
                    <h1 class="text-3xl sm:text-4xl xl:text-5xl font-extrabold tracking-tight leading-tight">
                        Instant messaging,<br><span class="text-blue-700">like it used to be.</span>
                    </h1>
                    <p class="mt-4 text-base text-slate-700 max-w-prose">
                        Pick a name, get a buddy list, and jump into rooms full of people <strong>right
                        now</strong>. Group chats, private messages, and voice &amp; video calls —
                        free, in your browser, with nothing to install. Take it to your phone whenever
                        you like.
                    </p>
                    <p class="mt-3 text-sm text-slate-500">
                        Your address looks like
                        <span class="font-mono text-blue-700">{{ 'you@'.config('xmpp.domain') }}</span>
                    </p>
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="r2-btn r2-btn--primary text-base px-6 py-2.5">Open ready2.im →</a>
                        @else
                            <a href="{{ route('register') }}" class="r2-btn r2-btn--primary text-base px-6 py-2.5">Get a name — free</a>
                            <a href="{{ route('login') }}" class="r2-btn text-base px-6 py-2.5">Sign On</a>
                        @endauth
                    </div>
                    <p class="mt-5 text-xs text-slate-400">Independent &amp; ad-free since 2001 · works in any chat app, never locked to us</p>
                </div>
            </div>

            <aside class="lg:col-span-5 r2-window">
                <div class="r2-titlebar">
                    <span>👥 Buddy List</span>
                    <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
                </div>
                <div class="r2-winbody">
                    <div class="r2-group"><span>▾</span> Rooms — {{ number_format($onlineCount) }} online</div>
                    <div class="py-1">
                        @forelse ($rooms as $room)
                            <div class="r2-buddy">
                                <span class="r2-dot {{ ($room['occupants'] ?? 0) > 0 ? 'r2-dot--online' : 'r2-dot--away' }}"></span>
                                <span class="font-medium">{{ $room['name'] }}</span>
                                <span class="ml-auto text-xs text-slate-500">{{ $room['occupants'] ?? 0 }}</span>
                            </div>
                        @empty
                            <div class="px-2 py-3 text-xs text-slate-400">Rooms are warming up — check back in a sec.</div>
                        @endforelse
                    </div>
                    <div class="r2-group mt-2"><span>▸</span> Friends</div>
                    <p class="px-2 py-3 text-xs text-slate-500">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-blue-700 underline">Open your buddy list →</a>
                        @else
                            <a href="{{ route('login') }}" class="text-blue-700 underline">Sign on</a> to see who’s around.
                        @endauth
                    </p>
                </div>
            </aside>
        </section>

        {{-- Features --}}
        <section class="pt-16">
            <h2 class="text-3xl font-extrabold text-white text-center drop-shadow">Everything you remember — minus the hassle</h2>
            <p class="mt-2 text-center text-blue-50/90 max-w-2xl mx-auto">One free account does all of it, in the browser or in a chat app on your phone.</p>

            <div class="mt-8 grid md:grid-cols-2 gap-6">

                @php
                    $features = [
                        ['icon' => '🎮', 'tag' => '#gaming', 'title' => 'Public rooms, buzzing day and night',
                         'body' => 'Wander into an open room about your game, your show, your town — say hi or just lurk. Rooms are public and full of people around the clock, the way chat rooms used to be.',
                         'mock' => ['n0scope_nate: anyone up for ranked?', 'pixelqueen: meee 🙋', 'you: adding you both', 'mr_blobby: lobby’s open 👇']],
                        ['icon' => '🔒', 'tag' => 'weekend-crew', 'title' => 'Private groups for your people',
                         'body' => 'Spin up an invite-only group for your crew, your family, or a trip — no randoms wandering in. Plan the chaos, share the photos; it stays between the people you added.',
                         'mock' => ['marcy_lou: cabin’s booked for sat 🏕️', 'you: i’ll grab snacks', 'deej: i’ll drive 🚗', 'tina_b: carpool from mine at 9?']],
                        ['icon' => '💬', 'tag' => 'maya', 'title' => 'Private messages, just you and them',
                         'body' => 'One-to-one chats with the people you care about — quick, personal, yours. With an end-to-end encrypted app, your DMs are readable only by the two of you.',
                         'mock' => ['maya: you free this weekend?', 'you: for you, always 💜']],
                        ['icon' => '📞', 'tag' => 'call', 'title' => 'Voice &amp; video, one tap away',
                         'body' => 'Hit call right inside any chat — voice or video, one friend or the whole group. No meeting links, no scheduling, no separate app to download. Just call.',
                         'mock' => ['you: hop on a video call?', 'dj_marco: calling you now 📞']],
                    ];
                @endphp

                @foreach ($features as $f)
                    <article class="r2-window">
                        <div class="r2-titlebar r2-titlebar--idle">
                            <span>{{ $f['icon'] }} {{ $f['tag'] }}</span>
                            <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
                        </div>
                        <div class="r2-winbody">
                            <h3 class="text-lg font-bold text-slate-900">{!! $f['title'] !!}</h3>
                            <p class="mt-2 text-sm text-slate-700 leading-relaxed">{{ $f['body'] }}</p>
                            <div class="mt-3 rounded border border-slate-200 bg-slate-50 p-2 text-xs text-slate-600 space-y-1">
                                @foreach ($f['mock'] as $line)
                                    @php
                                        [$who, $msg] = array_pad(explode(':', $line, 2), 2, null);
                                    @endphp
                                    <p>@if ($msg !== null)<span class="font-bold text-blue-700">{{ $who }}:</span>{{ $msg }}@else{{ $line }}@endif</p>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- How it works --}}
        <section class="pt-16">
            <div class="r2-window max-w-3xl mx-auto">
                <div class="r2-titlebar">
                    <span>🧭 Getting started</span>
                    <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
                </div>
                <div class="r2-winbody">
                    <h2 class="text-2xl font-extrabold text-slate-900">Up and running in three steps</h2>
                    <ol class="mt-4 space-y-3">
                        @php
                            $steps = [
                                ['Pick your name', 'Sign up and choose your @ address, then verify your email. That’s it.'],
                                ['Start chatting', 'Right here in your browser — nothing to install, nothing to figure out.'],
                                ['Take it with you', 'Want it on your phone too? Add a free app (Conversations on Android, Monal on iPhone) any time — totally optional.'],
                            ];
                        @endphp
                        @foreach ($steps as $i => [$t, $b])
                            <li class="flex gap-3">
                                <span class="shrink-0 w-7 h-7 grid place-items-center rounded-full bg-blue-600 text-white text-sm font-bold">{{ $i + 1 }}</span>
                                <span><span class="font-bold">{{ $t }}</span> — <span class="text-slate-700">{{ $b }}</span></span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        {{-- Final CTA --}}
        <section class="pt-16">
            <div class="r2-window max-w-2xl mx-auto text-center">
                <div class="r2-titlebar justify-center"><span>✨ ready2.im</span></div>
                <div class="r2-winbody py-10">
                    <h2 class="text-3xl font-extrabold text-slate-900">Your @ name is waiting.</h2>
                    <p class="mt-2 text-slate-600">Independent and ad-free since 2001 — still here, still free. Takes about a minute.</p>
                    <div class="mt-6">
                        @auth
                            <a href="{{ route('dashboard') }}" class="r2-btn r2-btn--primary text-base px-8 py-3">Open ready2.im →</a>
                        @else
                            <a href="{{ route('register') }}" class="r2-btn r2-btn--primary text-base px-8 py-3">Get a name — free</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-500/30 text-slate-800">
        <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
            <span>© {{ date('Y') }} ready2.im — independent &amp; ad-free since 2001</span>
            <span class="flex gap-4">
                <a href="{{ route('help') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline transition">Help</a>
                <a href="{{ route('terms') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline transition">Terms</a>
                <a href="{{ route('privacy') }}" class="font-semibold text-blue-800 hover:text-blue-900 underline transition">Privacy</a>
            </span>
        </div>
    </footer>

</body>
</html>
