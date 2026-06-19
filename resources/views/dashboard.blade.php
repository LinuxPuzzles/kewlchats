<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Your KewlChats account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Provisioning status banner --}}
            @if ($jid && auth()->user()->xmpp_status !== 'active')
                <div class="rounded-2xl p-4 text-sm border
                    @if(auth()->user()->xmpp_status === 'failed') bg-red-500/10 text-red-200 border-red-400/20
                    @else bg-amber-500/10 text-amber-200 border-amber-400/20 @endif">
                    @if (auth()->user()->xmpp_status === 'failed')
                        We hit a snag setting up your chat account. Our team has been notified — please try again later.
                    @elseif (! auth()->user()->hasVerifiedEmail())
                        <strong>Almost there!</strong> Verify your email to activate your chat account. Check your inbox for the link.
                    @else
                        <strong>Setting things up…</strong> Your chat account is being created. This usually only takes a moment — refresh shortly.
                    @endif
                </div>
            @endif

            {{-- Password drift: a reset didn't fully reach the chat server --}}
            @if (auth()->user()->isDesynced() && auth()->user()->xmpp_desync_reason === 'password')
                <div class="rounded-2xl p-4 text-sm border bg-amber-500/10 text-amber-200 border-amber-400/20">
                    <strong>Heads up:</strong> your last password change didn't finish updating your chat account.
                    Please <a href="{{ route('password.request') }}" class="underline hover:text-amber-100">reset it again</a> to get back in sync.
                </div>
            @endif

            {{-- Identity + QR --}}
            <div class="bg-white/5 border border-white/10 overflow-hidden shadow-sm rounded-2xl p-6">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-slate-100">Your chat address</h3>
                        <p class="text-sm text-slate-400 mt-1">Use this with the password you signed up with.</p>

                        <div class="mt-4" x-data="{ copied: false }">
                            <div class="flex items-stretch max-w-md">
                                <code class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-l-lg text-fuchsia-300 font-mono text-sm md:text-base break-all">{{ $jid ?? '—' }}</code>
                                @if ($jid)
                                    <button type="button"
                                            class="px-4 bg-fuchsia-500 hover:bg-fuchsia-400 text-white text-sm rounded-r-lg"
                                            @click="navigator.clipboard.writeText(@js($jid)); copied = true; setTimeout(() => copied = false, 1500)">
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" x-cloak>Copied!</span>
                                    </button>
                                @endif
                            </div>
                            <p class="mt-3 text-xs text-slate-500">
                                🔒 For your security we never show your password here — it's the one you chose at sign-up.
                            </p>

                            <a href="{{ route('chat') }}"
                               class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-fuchsia-500 hover:bg-fuchsia-400 text-white text-sm font-medium transition">
                                💬 Chat in your browser
                            </a>
                            <p class="mt-2 text-xs text-slate-500">No app needed — start chatting right here.</p>
                        </div>
                    </div>

                    @if ($jidQr)
                        <div class="shrink-0 text-center">
                            {{-- White tile so the QR stays scannable against the dark UI --}}
                            <div class="inline-block p-3 bg-white rounded-xl">
                                {!! $jidQr !!}
                            </div>
                            <p class="mt-2 text-xs text-slate-400 max-w-[220px]">Scan in your app to fill in your address automatically.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-white/5 border border-white/10 shadow-sm rounded-2xl p-5">
                    <div class="text-3xl font-bold text-slate-100">{{ number_format($onlineCount) }}</div>
                    <div class="text-sm text-slate-400 mt-1">people online now</div>
                </div>
                <div class="bg-white/5 border border-white/10 shadow-sm rounded-2xl p-5">
                    <div class="text-3xl font-bold text-slate-100">
                        {{ $lastActivity ? $lastActivity->diffForHumans(short: true) : '—' }}
                    </div>
                    <div class="text-sm text-slate-400 mt-1">your last activity</div>
                </div>
                <div class="bg-white/5 border border-white/10 shadow-sm rounded-2xl p-5 col-span-2 sm:col-span-1">
                    <div class="text-3xl font-bold text-emerald-400">
                        {{ auth()->user()->xmppIsActive() ? 'Active' : 'Pending' }}
                    </div>
                    <div class="text-sm text-slate-400 mt-1">account status</div>
                </div>
            </div>

            {{-- Setup guide --}}
            <div class="bg-white/5 border border-white/10 overflow-hidden shadow-sm rounded-2xl p-6" x-data="{ tab: 'android' }">
                <h3 class="text-lg font-semibold text-slate-100">Set up your chat app</h3>
                <p class="text-sm text-slate-400 mt-1">Pick your phone, install a free app, and sign in with your address and password.</p>

                <div class="mt-4 flex gap-2">
                    <button type="button" @click="tab='android'"
                            :class="tab==='android' ? 'bg-fuchsia-500 text-white' : 'bg-white/10 text-slate-300'"
                            class="px-4 py-2 rounded-lg text-sm font-medium">Android</button>
                    <button type="button" @click="tab='ios'"
                            :class="tab==='ios' ? 'bg-fuchsia-500 text-white' : 'bg-white/10 text-slate-300'"
                            class="px-4 py-2 rounded-lg text-sm font-medium">iPhone / iPad</button>
                </div>

                <div x-show="tab==='android'" class="mt-4">
                    <ol class="list-decimal list-inside space-y-2 text-sm text-slate-300">
                        <li>Install <strong>Conversations</strong> from the Play Store (or F-Droid).</li>
                        <li>Open it and tap <strong>“I already have an account.”</strong></li>
                        <li>Enter your address <code class="text-fuchsia-300">{{ $jid }}</code> and the password you chose here.</li>
                        <li>Tap <strong>Next</strong> — you're in! Start a chat or join a room below.</li>
                    </ol>
                </div>

                <div x-show="tab==='ios'" x-cloak class="mt-4">
                    <ol class="list-decimal list-inside space-y-2 text-sm text-slate-300">
                        <li>Install <strong>Monal</strong> from the App Store.</li>
                        <li>Open it and tap <strong>“Add account.”</strong></li>
                        <li>Enter your address <code class="text-fuchsia-300">{{ $jid }}</code> and the password you chose here.</li>
                        <li>Tap <strong>Sign in</strong> — you're in! Start a chat or join a room below.</li>
                    </ol>
                </div>

                <div class="mt-5 rounded-xl bg-fuchsia-500/10 border border-fuchsia-400/20 p-4 text-sm text-fuchsia-100">
                    🎙️ <strong>Voice &amp; video calls</strong> work too — once you're signed in, open a chat and tap the call icon.
                </div>
            </div>

            {{-- Featured rooms --}}
            <div class="bg-white/5 border border-white/10 overflow-hidden shadow-sm rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-slate-100">Featured rooms</h3>
                <p class="text-sm text-slate-400 mt-1">Public group chats — tap one to open it in your app and jump in.
                    New here? <a href="{{ route('help') }}" class="text-fuchsia-300 hover:text-fuchsia-200 underline">Read the guide</a> on adding friends &amp; joining rooms.</p>

                <div class="mt-4 grid sm:grid-cols-2 gap-4">
                    @foreach ($rooms as $room)
                        <a href="xmpp:{{ $room['jid'] }}?join"
                           class="block border border-white/10 rounded-xl p-4 hover:border-fuchsia-400/40 hover:bg-white/5 transition">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-100">{{ $room['name'] }}</span>
                                <span class="text-xs text-emerald-400">● {{ $room['occupants'] }} online</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-1">{{ $room['description'] }}</p>
                            <code class="text-xs text-fuchsia-300 mt-2 inline-block break-all">{{ $room['jid'] }}</code>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
