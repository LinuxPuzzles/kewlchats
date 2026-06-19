<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site-meta />
    <title>KewlChats — your own place to chat with friends</title>
    <meta name="description" content="Free group chats, private messages, and calls for you and your friends. No ads, no snooping, no catch.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 selection:bg-fuchsia-500/30">

    {{-- Glow backdrop --}}
    <x-glow />

    {{-- Nav --}}
    <header class="max-w-6xl mx-auto px-6 py-6 flex items-center justify-between">
        <a href="{{ route('home') }}">
            <x-brand class="text-xl" />
        </a>
        <nav class="flex items-center gap-3 text-sm">
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg hover:bg-white/10 transition">Log in</a>
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-fuchsia-500 hover:bg-fuchsia-400 text-white font-semibold transition">Get your @ name</a>
            @endauth
        </nav>
    </header>

    {{-- Hero --}}
    <section class="max-w-6xl mx-auto px-6 pt-16 pb-20">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">

            {{-- Left: the pitch --}}
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-slate-300 mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ number_format($onlineCount) }} people chatting right now
                </div>

                <h1 class="text-5xl sm:text-6xl xl:text-7xl font-extrabold tracking-tight leading-[1.05]">
                    Your friends. Your rooms.<br>
                    <span class="bg-gradient-to-r from-fuchsia-400 via-pink-400 to-indigo-400 bg-clip-text text-transparent">
                        No catch.
                    </span>
                </h1>

                <p class="mt-6 max-w-xl mx-auto lg:mx-0 text-lg text-slate-300">
                    Text your group, drop into a room, or hop on a call — start right in your
                    browser, no app to install. Take it to your phone whenever you want. Free.
                    No ads. No catch.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}"
                       class="px-8 py-4 rounded-xl bg-fuchsia-500 hover:bg-fuchsia-400 text-white text-lg font-semibold shadow-lg shadow-fuchsia-500/30 transition">
                        Grab your free name
                    </a>
                    <a href="#how"
                       class="px-8 py-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-lg font-medium transition">
                        See how it works
                    </a>
                </div>
                <p class="mt-4 text-sm text-slate-400">You'll get an address like <span class="text-fuchsia-300 font-mono">{{ 'you@'.config('xmpp.domain') }}</span></p>
            </div>

            {{-- Right: a generic chat illustration (the idea, not a real app) --}}
            <x-chat-frame>
                {{-- room header --}}
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🎬</span>
                        <div>
                            <div class="text-sm font-semibold text-slate-100">movie-night</div>
                            <div class="text-xs text-emerald-400">● 12 online</div>
                        </div>
                    </div>
                    <span class="w-8 h-8 rounded-full bg-fuchsia-500/20 border border-fuchsia-400/30 flex items-center justify-center" title="voice & video">📞</span>
                </div>

                {{-- messages --}}
                <div class="space-y-3 py-4">
                    <div class="flex items-end gap-2">
                        <span class="w-6 h-6 shrink-0 rounded-full bg-indigo-500/30 flex items-center justify-center text-xs">🦊</span>
                        <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">anyone around tonight? 👀</div>
                    </div>
                    <div class="flex items-end gap-2 justify-end">
                        <div class="max-w-[75%] rounded-2xl rounded-br-sm bg-fuchsia-500 px-3 py-2 text-sm text-white">always 😎</div>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="w-6 h-6 shrink-0 rounded-full bg-emerald-500/30 flex items-center justify-center text-xs">🐢</span>
                        <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">starting the movie in 5 🍿 jump in</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 shrink-0 rounded-full bg-pink-500/30 flex items-center justify-center text-xs">🐱</span>
                        <div class="rounded-2xl rounded-bl-sm bg-white/10 px-3 py-3 flex gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce [animation-delay:0.15s]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce [animation-delay:0.3s]"></span>
                        </div>
                    </div>
                </div>

                {{-- input --}}
                <div class="flex items-center gap-2 pt-3 border-t border-white/10">
                    <div class="flex-1 rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-slate-500">Message movie-night…</div>
                    <span class="w-8 h-8 shrink-0 rounded-full bg-fuchsia-500 flex items-center justify-center text-white">↑</span>
                </div>
            </x-chat-frame>
        </div>

        {{-- It's-yours reassurance strip --}}
        <p class="mt-12 max-w-2xl mx-auto text-center text-sm text-slate-400 leading-relaxed">
            Free, and actually yours. We don't sell your info or track you.
            Public rooms are public — everything else stays between you and your friends.
        </p>
    </section>

    {{-- What you can do --}}
    <section class="max-w-6xl mx-auto px-6 pt-8 pb-24">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold">Four ways to hang out</h2>
            <p class="mt-3 text-slate-300">
                One account does all of it — and so do both free apps we recommend.
                No juggling, no “which app was that on again?”
            </p>
        </div>

        <div class="mt-24 lg:mt-32 space-y-28 lg:space-y-36">

            {{-- 1. Public channels (text left, image right) --}}
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
                <div class="text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-fuchsia-500/10 border border-fuchsia-400/20 text-xs text-fuchsia-200">Public channels</span>
                    <h3 class="mt-4 text-2xl font-bold">Open rooms for whatever you’re into</h3>
                    <p class="mt-3 text-slate-300 leading-relaxed max-w-md mx-auto lg:mx-0">
                        Wander into a room about your game, your show, your town — say hi or just
                        lurk. They’re open to anyone, busy day and night. Like an old chat room,
                        alive and full of people.
                    </p>
                </div>
                <x-chat-frame tilt="rotate-1">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎮</span>
                            <div>
                                <div class="text-sm font-semibold text-slate-100">gaming</div>
                                <div class="text-xs text-emerald-400">● 218 online</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-white/10 text-[10px] text-slate-300">🌐 Public</span>
                    </div>
                    <div class="space-y-3 py-4">
                        <div class="flex items-end gap-2">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-indigo-500/30 flex items-center justify-center text-xs">🦊</span>
                            <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">anyone up for a ranked match?</div>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-amber-500/30 flex items-center justify-center text-xs">🐼</span>
                            <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">me! adding you now</div>
                        </div>
                        <div class="flex items-end gap-2 justify-end">
                            <div class="max-w-[75%] rounded-2xl rounded-br-sm bg-fuchsia-500 px-3 py-2 text-sm text-white">lobby’s open 👇</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-white/10">
                        <div class="flex-1 rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-slate-500">Message gaming…</div>
                        <span class="w-8 h-8 shrink-0 rounded-full bg-fuchsia-500 flex items-center justify-center text-white">↑</span>
                    </div>
                </x-chat-frame>
            </div>

            {{-- 2. Private groups (image left, text right) --}}
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
                <x-chat-frame tilt="-rotate-1">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔒</span>
                            <div>
                                <div class="text-sm font-semibold text-slate-100">weekend-crew</div>
                                <div class="text-xs text-slate-400">5 members · invite only</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-white/10 text-[10px] text-slate-300">🔒 Private</span>
                    </div>
                    <div class="space-y-3 py-4">
                        <div class="flex items-end gap-2">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-emerald-500/30 flex items-center justify-center text-xs">🐢</span>
                            <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">cabin’s booked for sat 🏕️</div>
                        </div>
                        <div class="flex items-end gap-2 justify-end">
                            <div class="max-w-[75%] rounded-2xl rounded-br-sm bg-fuchsia-500 px-3 py-2 text-sm text-white">i’ll grab snacks</div>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-pink-500/30 flex items-center justify-center text-xs">🐱</span>
                            <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">carpool from mine at 9?</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-white/10">
                        <div class="flex-1 rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-slate-500">Message weekend-crew…</div>
                        <span class="w-8 h-8 shrink-0 rounded-full bg-fuchsia-500 flex items-center justify-center text-white">↑</span>
                    </div>
                </x-chat-frame>
                <div class="text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-fuchsia-500/10 border border-fuchsia-400/20 text-xs text-fuchsia-200">Private groups</span>
                    <h3 class="mt-4 text-2xl font-bold">A room just for your people</h3>
                    <p class="mt-3 text-slate-300 leading-relaxed max-w-md mx-auto lg:mx-0">
                        Make a group for your crew, your family, your group trip — invite-only, no
                        randoms wandering in. Plan the chaos and share the photos. It stays between
                        the people you added.
                    </p>
                </div>
            </div>

            {{-- 3. Private messages (text left, image right) --}}
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
                <div class="text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-fuchsia-500/10 border border-fuchsia-400/20 text-xs text-fuchsia-200">Private messages</span>
                    <h3 class="mt-4 text-2xl font-bold">Just you and them</h3>
                    <p class="mt-3 text-slate-300 leading-relaxed max-w-md mx-auto lg:mx-0">
                        One-to-one chats with the people you care about — quick, personal, and yours.
                        Your private messages stay between the two of you. Not ours to read, not ours
                        to sell.
                    </p>
                </div>
                <x-chat-frame tilt="rotate-1">
                    <div class="flex items-center gap-2 pb-3 border-b border-white/10">
                        <span class="w-8 h-8 shrink-0 rounded-full bg-indigo-500/30 flex items-center justify-center text-sm">🦊</span>
                        <div>
                            <div class="text-sm font-semibold text-slate-100">maya</div>
                            <div class="text-xs text-emerald-400">● online</div>
                        </div>
                    </div>
                    <div class="space-y-3 py-4">
                        <div class="flex items-end gap-2">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-indigo-500/30 flex items-center justify-center text-xs">🦊</span>
                            <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">you free this weekend?</div>
                        </div>
                        <div class="flex items-end gap-2 justify-end">
                            <div class="max-w-[75%] rounded-2xl rounded-br-sm bg-fuchsia-500 px-3 py-2 text-sm text-white">for you, always 💜</div>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-indigo-500/30 flex items-center justify-center text-xs">🦊</span>
                            <div class="max-w-[75%] rounded-2xl rounded-bl-sm bg-white/10 px-3 py-2 text-sm text-slate-100">coffee sat morning then ☕</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-white/10">
                        <div class="flex-1 rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-slate-500">Message maya…</div>
                        <span class="w-8 h-8 shrink-0 rounded-full bg-fuchsia-500 flex items-center justify-center text-white">↑</span>
                    </div>
                </x-chat-frame>
            </div>

            {{-- 4. Voice & video (image left, text right) --}}
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
                <x-chat-frame tilt="-rotate-1">
                    {{-- mini video tiles --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div class="aspect-square rounded-2xl bg-gradient-to-br from-indigo-500/30 to-fuchsia-500/20 flex items-center justify-center text-4xl">🦊</div>
                        <div class="aspect-square rounded-2xl bg-gradient-to-br from-emerald-500/30 to-cyan-500/20 flex items-center justify-center text-4xl">🐢</div>
                    </div>
                    <div class="text-center py-4">
                        <div class="text-sm font-semibold text-slate-100">maya &amp; you</div>
                        <div class="text-xs text-emerald-400">● 02:14 · connected</div>
                    </div>
                    {{-- call controls --}}
                    <div class="flex items-center justify-center gap-3 pt-2">
                        <span class="w-11 h-11 rounded-full bg-white/10 border border-white/10 flex items-center justify-center">🎙️</span>
                        <span class="w-11 h-11 rounded-full bg-white/10 border border-white/10 flex items-center justify-center">📷</span>
                        <span class="w-11 h-11 rounded-full bg-rose-500 flex items-center justify-center text-white rotate-[135deg]">📞</span>
                    </div>
                </x-chat-frame>
                <div class="text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-fuchsia-500/10 border border-fuchsia-400/20 text-xs text-fuchsia-200">Voice &amp; video</span>
                    <h3 class="mt-4 text-2xl font-bold">Hear their voice, see their face</h3>
                    <p class="mt-3 text-slate-300 leading-relaxed max-w-md mx-auto lg:mx-0">
                        Tap the call button right inside any chat — voice or video, one person or the
                        group. No meeting links, no scheduling, no separate app to download. Just call.
                    </p>
                </div>
            </div>

        </div>
    </section>

    {{-- How it works --}}
    <section id="how" class="max-w-6xl mx-auto px-6 pb-24">
        <h2 class="text-3xl sm:text-4xl font-bold text-center">Up and running in 3 steps</h2>
        <div class="mt-12 grid md:grid-cols-3 gap-6">
            @php
                $steps = [
                    ['1', 'Pick your name', 'Sign up and choose your @ address. Verify your email — that\'s it.'],
                    ['2', 'Start chatting', 'Right here in your browser. Nothing to install, nothing to figure out.'],
                    ['3', 'Take it with you', 'Want it on your phone too? Add a free app whenever you like — totally optional.'],
                ];
            @endphp
            @foreach ($steps as [$n, $title, $body])
                <div class="relative rounded-2xl bg-gradient-to-b from-white/[0.08] to-transparent border border-white/10 p-6">
                    <div class="w-10 h-10 rounded-full bg-fuchsia-500 flex items-center justify-center font-bold">{{ $n }}</div>
                    <h3 class="mt-4 text-lg font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-slate-300 text-sm">{{ $body }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-10 flex flex-col items-center gap-3 text-sm">
            <span class="text-slate-400">Prefer your phone? It works there too —</span>
            <div class="flex flex-wrap items-center justify-center gap-4 text-slate-300">
                <span class="px-3 py-2 rounded-lg bg-white/5 border border-white/10">🤖 Conversations · Android</span>
                <span class="px-3 py-2 rounded-lg bg-white/5 border border-white/10"> Monal · iPhone &amp; iPad</span>
            </div>
        </div>
    </section>

    {{-- Rooms teaser --}}
    <section class="max-w-6xl mx-auto px-6 pb-24">
        <div class="flex items-end justify-between">
            <h2 class="text-3xl sm:text-4xl font-bold">Rooms buzzing right now</h2>
            <a href="{{ route('register') }}" class="hidden sm:inline text-fuchsia-300 hover:text-fuchsia-200 text-sm">Join them →</a>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($rooms as $room)
                <div class="rounded-2xl bg-white/5 border border-white/10 p-5 hover:border-fuchsia-400/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold">{{ $room['name'] }}</span>
                        <span class="text-xs text-emerald-400">● {{ $room['occupants'] }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-400">{{ $room['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="max-w-4xl mx-auto px-6 pb-28 text-center">
        <div class="rounded-3xl bg-gradient-to-br from-fuchsia-600/30 to-indigo-600/30 border border-white/10 p-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold">Your @ name is waiting.</h2>
            <p class="mt-3 text-slate-300">Independent and ad-free since 2001 — still here, still free. Takes about a minute.</p>
            <a href="{{ route('register') }}"
               class="mt-8 inline-block px-8 py-4 rounded-xl bg-fuchsia-500 hover:bg-fuchsia-400 text-white text-lg font-semibold shadow-lg shadow-fuchsia-500/30 transition">
                Create my account
            </a>
        </div>
    </section>

    <footer class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-400">
            <span>© {{ date('Y') }} KewlChats</span>
            <span class="text-center sm:text-right">Independent &amp; ad-free since 2001. Your account works in any app you like — never locked to us.</span>
            <span class="flex gap-4">
                <a href="{{ route('help') }}" class="hover:text-slate-200 transition">Help</a>
                <a href="{{ route('terms') }}" class="hover:text-slate-200 transition">Terms</a>
                <a href="{{ route('privacy') }}" class="hover:text-slate-200 transition">Privacy</a>
            </span>
        </div>
    </footer>

</body>
</html>
