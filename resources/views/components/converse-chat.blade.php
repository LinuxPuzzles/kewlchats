{{--
    Shared functional Converse.js embed. Themes own the chat page's chrome/copy
    (chat.blade.php) but drop in <x-converse-chat /> so this must-not-diverge JS
    (X-OAUTH2 login, shared-lounge auto-join, the SM/location/layout fixes) stays
    single-source. Reads the user JID + config itself — no props needed.

    Self-hosted (no CDN). Logs in with a short-lived X-OAUTH2 token from /chat/token,
    so the password is never re-entered.
--}}
<link rel="stylesheet" href="/vendor/converse/converse.min.css">
<style>
    /* No "share location" button (typing a geo: URI still works). */
    converse-location-button { display: none !important; }
    /* Embed is narrow: drop the drag-resize gutter so chat (col-8) + occupants
       (col-4) sum to 100% instead of overflowing and clipping the list on the right. */
    .converse-embedded converse-split-resize { display: none !important; }
</style>
{{-- Embedded Converse sets its root to height:100%, which only resolves against a
     parent with a DEFINITE height — min-height doesn't count, so the fully-rendered
     UI collapses to 0px (blank). Give it a real height; converse-root then fills it. --}}
<div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden h-[600px]">
    <converse-root class="converse-embedded" style="display:block; height:100%;"></converse-root>
</div>
<script src="/vendor/converse/converse.min.js"></script>
<script>
    // Force the X-OAUTH2 SASL mechanism. We log Converse in with a short-lived token
    // (not a password), so ejabberd's password-based mechanisms (SCRAM/PLAIN) must be
    // ruled out — Strophe would otherwise prefer SCRAM, which fails (token != password)
    // and needs Web Crypto. Restrict the connection to X-OAUTH2 before auth begins.
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
        // Drop straight into the shared live lounge on load — "walk into a room full
        // of people." Shared by all front-end sites so everyone lands together.
        auto_join_rooms: [@js(config('xmpp.web_chat.landing_room'))],
        // Use the account's localpart as the MUC nick (their username IS their identity)
        // so auto-join completes without a nickname prompt — else the view sits blank.
        muc_nickname_from_jid: true,
        @endif
        // Disable XEP-0198 Stream Management. Its cross-reload *resume* is buggy here:
        // after a successful <resume> Converse keeps a freshly-minted resource instead
        // of the resumed one, then stamps stanzas with a 'from' that no longer matches
        // the session → ejabberd returns <invalid-from/> and drops the stream (blank
        // page). A clean fresh bind per load avoids it; MAM still backfills history.
        enable_smacks: false,
        allow_logout: false,
        allow_registration: false,
        muc_domain: @js(config('xmpp.muc_domain')),
    });
</script>
