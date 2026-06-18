<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Web chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Live-room disclaimer: frame the no-history model as a feature, present-tense. --}}
            <div class="rounded-2xl bg-fuchsia-500/10 border border-fuchsia-400/20 p-5 text-sm">
                <p class="font-semibold text-fuchsia-100">👋 This is live chat — happening right now.</p>
                <p class="mt-1 text-fuchsia-100/80">
                    KewlChats doesn’t keep a history. You’ll see what’s said from the moment you arrive —
                    like walking into a room full of people, not opening a transcript. Say hi and jump in.
                </p>
            </div>

            @if (auth()->user()->xmppIsActive())
                {{-- Self-hosted Converse.js (no CDN). It logs in with a short-lived X-OAUTH2 token
                     fetched from /chat/token, so the password is never re-entered. --}}
                <link rel="stylesheet" href="/vendor/converse/converse.min.css">
                {{-- Embedded Converse sets its root to height:100%, which only resolves
                     against a parent with a DEFINITE height — min-height doesn't count,
                     so the fully-rendered UI collapses to 0px (blank). Give it a real
                     height; converse-root then fills it. --}}
                <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden h-[600px]">
                    <converse-root class="converse-embedded" style="display:block; height:100%;"></converse-root>
                </div>
                <script src="/vendor/converse/converse.min.js"></script>
                <script>
                    // KewlChats: force the X-OAUTH2 SASL mechanism. We log Converse in with a
                    // short-lived token (not a password), so ejabberd's password-based mechanisms
                    // (SCRAM/PLAIN) must be ruled out — Strophe would otherwise prefer SCRAM, which
                    // fails (token != password) and needs Web Crypto. Restrict the connection to
                    // X-OAUTH2 before authentication begins.
                    converse.plugins.add('kewlchats-xoauth2', {
                        initialize() {
                            const api = this._converse.api;
                            api.listen.on('connectionInitialized', () => {
                                try {
                                    const conn = api.connection.get();
                                    const mechs = conn && conn.mechanisms ? Object.values(conn.mechanisms) : [];
                                    const xoauth2 = mechs.filter(m => (m && (m.mechname || m.name)) === 'X-OAUTH2');
                                    if (conn && typeof conn.registerSASLMechanisms === 'function' && xoauth2.length) {
                                        // registerSASLMechanisms wants mechanism classes, not instances.
                                        conn.registerSASLMechanisms(xoauth2.map(m => m.constructor));
                                    } else {
                                        console.error('[kewlchats] could not restrict SASL to X-OAUTH2');
                                    }
                                } catch (e) {
                                    console.error('[kewlchats] X-OAUTH2 plugin error', e);
                                }
                            });
                        }
                    });

                    converse.initialize({
                        assets_path: '/vendor/converse/',
                        view_mode: 'embedded',
                        authentication: 'login',
                        auto_login: true,
                        jid: @js(auth()->user()->jid()),
                        credentials_url: @js(route('chat.token')),
                        websocket_url: @js(config('xmpp.web_chat.websocket_url')),
                        discover_connection_methods: false,
                        whitelisted_plugins: ['kewlchats-xoauth2'],
                        auto_reconnect: true,
                        @if (config('xmpp.web_chat.landing_room'))
                        // Drop straight into the shared live lounge on load — "walk into
                        // a room full of people," the whole KewlChats model. Shared by all
                        // front-end sites so everyone lands together.
                        auto_join_rooms: [@js(config('xmpp.web_chat.landing_room'))],
                        // Use the account's localpart as the MUC nick (their username IS
                        // their identity) so auto-join completes without a nickname prompt
                        // — otherwise the embedded view just sits blank waiting for input.
                        muc_nickname_from_jid: true,
                        @endif
                        // Disable XEP-0198 Stream Management. Its cross-reload *resume*
                        // is buggy here: after a successful <resume> Converse keeps a
                        // freshly-minted resource instead of the resumed one, then stamps
                        // stanzas with a 'from' that no longer matches the session →
                        // ejabberd returns <invalid-from/> and drops the stream (blank
                        // page). We're an ephemeral, no-history "live room" anyway, so a
                        // clean fresh bind per load is the right model — nothing to resume.
                        enable_smacks: false,
                        allow_logout: false,
                        allow_registration: false,
                        muc_domain: @js(config('xmpp.muc_domain')),
                    });
                </script>
            @else
                <div class="bg-white/5 border border-white/10 rounded-2xl min-h-[320px] flex items-center justify-center p-10 text-center">
                    <div class="max-w-sm">
                        <div class="text-4xl">⏳</div>
                        <h3 class="mt-3 text-lg font-semibold text-slate-100">Setting up your account</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Your chat account isn’t active yet. Once it’s ready you’ll be able to chat right here.
                        </p>
                    </div>
                </div>
            @endif

            <p class="text-xs text-slate-500 text-center">
                Prefer your phone? Conversations (Android) and Monal (iPhone) use the same account — set them up from your
                <a href="{{ route('dashboard') }}" class="underline hover:text-slate-300">dashboard</a>.
            </p>
        </div>
    </div>
</x-app-layout>
