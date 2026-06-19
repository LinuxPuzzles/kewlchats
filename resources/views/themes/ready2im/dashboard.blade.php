<x-app-layout>
    <x-slot name="header">
        <h2 class="text-white font-extrabold text-xl tracking-tight drop-shadow">
            {{ __('Your ready2.im account') }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 pt-6 space-y-6">

        {{-- Provisioning status banner --}}
        @if ($jid && auth()->user()->xmpp_status !== 'active')
            <div class="rounded-lg p-4 text-sm border
                @if(auth()->user()->xmpp_status === 'failed') bg-red-100 text-red-800 border-red-300
                @else bg-amber-100 text-amber-800 border-amber-300 @endif">
                @if (auth()->user()->xmpp_status === 'failed')
                    We hit a snag setting up your chat account. Our team has been notified — please try again later.
                @elseif (! auth()->user()->hasVerifiedEmail())
                    <strong>Almost there!</strong> Verify your email to activate your chat account. Check your inbox for the link.
                @else
                    <strong>Setting things up…</strong> Your chat account is being created — refresh shortly.
                @endif
            </div>
        @endif

        @if (auth()->user()->isDesynced() && auth()->user()->xmpp_desync_reason === 'password')
            <div class="rounded-lg p-4 text-sm border bg-amber-100 text-amber-800 border-amber-300">
                <strong>Heads up:</strong> your last password change didn't finish updating your chat account.
                Please <a href="{{ route('password.request') }}" class="underline">reset it again</a> to get back in sync.
            </div>
        @endif

        {{-- Identity + QR --}}
        <section class="r2-window">
            <div class="r2-titlebar">
                <span>👤 Your chat address</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex-1">
                        <p class="text-sm text-slate-600">Sign in to any chat app with this address and the password you chose at sign-up.</p>

                        <div class="mt-4" x-data="{ copied: false }">
                            <div class="flex items-stretch max-w-md">
                                <code class="flex-1 px-4 py-3 bg-slate-100 border border-slate-300 rounded-l text-blue-700 font-mono text-sm md:text-base break-all">{{ $jid ?? '—' }}</code>
                                @if ($jid)
                                    <button type="button" class="r2-btn rounded-l-none"
                                            @click="navigator.clipboard.writeText(@js($jid)); copied = true; setTimeout(() => copied = false, 1500)">
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" x-cloak>Copied!</span>
                                    </button>
                                @endif
                            </div>
                            <p class="mt-3 text-xs text-slate-500">🔒 We never show your password — it's the one you chose at sign-up.</p>

                            <a href="{{ route('chat') }}" class="r2-btn r2-btn--primary mt-4">💬 Chat in your browser</a>
                            <p class="mt-2 text-xs text-slate-500">No app needed — start chatting right here.</p>
                        </div>
                    </div>

                    @if ($jidQr)
                        <div class="shrink-0 text-center">
                            <div class="inline-block p-3 bg-white border border-slate-300 rounded">
                                {!! $jidQr !!}
                            </div>
                            <p class="mt-2 text-xs text-slate-500 max-w-[220px]">Scan in your app to fill in your address automatically.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="r2-window"><div class="r2-winbody text-center">
                <div class="text-3xl font-extrabold text-slate-900">{{ number_format($onlineCount) }}</div>
                <div class="text-sm text-slate-500 mt-1">people online now</div>
            </div></div>
            <div class="r2-window"><div class="r2-winbody text-center">
                <div class="text-2xl font-extrabold text-slate-900">{{ $lastActivity ? $lastActivity->diffForHumans(short: true) : '—' }}</div>
                <div class="text-sm text-slate-500 mt-1">your last activity</div>
            </div></div>
            <div class="r2-window col-span-2 sm:col-span-1"><div class="r2-winbody text-center">
                <div class="text-2xl font-extrabold {{ auth()->user()->xmppIsActive() ? 'text-green-700' : 'text-amber-600' }}">
                    {{ auth()->user()->xmppIsActive() ? 'Online' : 'Pending' }}
                </div>
                <div class="text-sm text-slate-500 mt-1">account status</div>
            </div></div>
        </div>

        {{-- Setup guide --}}
        <section class="r2-window" x-data="{ tab: 'android' }">
            <div class="r2-titlebar">
                <span>🧭 Set up your chat app</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <p class="text-sm text-slate-600">Pick your phone, install a chat app, and sign in with your address and password.</p>

                <div class="mt-4 flex gap-2">
                    <button type="button" @click="tab='android'" :class="tab==='android' ? 'r2-btn--primary' : ''" class="r2-btn">Android</button>
                    <button type="button" @click="tab='ios'" :class="tab==='ios' ? 'r2-btn--primary' : ''" class="r2-btn">iPhone / iPad</button>
                </div>

                <div x-show="tab==='android'" class="mt-4">
                    <ol class="list-decimal list-inside space-y-2 text-sm text-slate-700">
                        <li>Install <strong>Conversations</strong> from the Play Store (or F-Droid).</li>
                        <li>Open it and tap <strong>"I already have an account."</strong></li>
                        <li>Enter <code class="text-blue-700">{{ $jid }}</code> and the password you chose here.</li>
                        <li>Tap <strong>Next</strong> — you're in!</li>
                    </ol>
                </div>
                <div x-show="tab==='ios'" x-cloak class="mt-4">
                    <ol class="list-decimal list-inside space-y-2 text-sm text-slate-700">
                        <li>Install <strong>Monal</strong> from the App Store.</li>
                        <li>Open it and tap <strong>"Add account."</strong></li>
                        <li>Enter <code class="text-blue-700">{{ $jid }}</code> and the password you chose here.</li>
                        <li>Tap <strong>Sign in</strong> — you're in!</li>
                    </ol>
                </div>

                <div class="mt-5 rounded border border-blue-300 bg-blue-50 p-4 text-sm text-blue-900">
                    🎙️ <strong>Voice &amp; video calls</strong> work too — open a chat and tap the call icon.
                </div>
            </div>
        </section>

        {{-- Featured rooms --}}
        <section class="r2-window">
            <div class="r2-titlebar">
                <span>👥 Featured rooms</span>
                <span class="r2-winbtns" aria-hidden="true"><span class="r2-winbtn">_</span><span class="r2-winbtn">▢</span><span class="r2-winbtn r2-winbtn--close">✕</span></span>
            </div>
            <div class="r2-winbody p-6">
                <p class="text-sm text-slate-600">Public group chats — tap one to open it in your app and jump in.
                    New here? <a href="{{ route('help') }}" class="text-blue-700 underline">Read the guide</a> on adding friends &amp; joining rooms.</p>
                <div class="mt-4 grid sm:grid-cols-2 gap-3">
                    @foreach ($rooms as $room)
                        <a href="xmpp:{{ $room['jid'] }}?join" class="block border border-slate-300 rounded p-3 hover:border-blue-400 hover:bg-blue-50 transition">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">{{ $room['name'] }}</span>
                                <span class="text-xs text-green-700"><span class="r2-dot r2-dot--online"></span> {{ $room['occupants'] }}</span>
                            </div>
                            <p class="text-sm text-slate-600 mt-1">{{ $room['description'] }}</p>
                            <code class="text-xs text-blue-700 mt-2 inline-block break-all">{{ $room['jid'] }}</code>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

    </div>
</x-app-layout>
