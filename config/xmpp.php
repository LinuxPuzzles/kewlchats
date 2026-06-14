<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provisioner driver
    |--------------------------------------------------------------------------
    |
    | Which XmppProvisioner implementation to use. "mock" returns canned data
    | and never touches a real server (Phase 1). "ejabberd" talks to a real
    | ejabberd ReST API and will be implemented once a server exists.
    |
    */

    'driver' => env('XMPP_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Domains
    |--------------------------------------------------------------------------
    |
    | The XMPP host (JID = localpart@domain) and the MUC service host used for
    | group chat rooms. Never hardcode these in views/services.
    |
    */

    'domain' => env('XMPP_DOMAIN', 'kewlchats.net'),

    'muc_domain' => env('XMPP_MUC_DOMAIN', 'conference.kewlchats.net'),

    /*
    |--------------------------------------------------------------------------
    | Featured rooms (curated)
    |--------------------------------------------------------------------------
    |
    | An editorial list of rooms to showcase on the landing page and dashboard.
    | The ejabberd driver enriches each with a live occupant count; rooms
    | materialise on first join, so an idle room simply shows 0.
    |
    */

    'featured_rooms' => [
        ['localpart' => 'lounge', 'name' => 'The Lounge', 'description' => 'General hangout — say hi, talk about anything.'],
        ['localpart' => 'tech', 'name' => 'Tech Talk', 'description' => 'Gadgets, code, self-hosting and all things nerdy.'],
        ['localpart' => 'music', 'name' => 'Now Playing', 'description' => 'Share what you are listening to right now.'],
        ['localpart' => 'gaming', 'name' => 'Game Night', 'description' => 'Find people to play with, voice chat welcome.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Web chat (Converse.js)
    |--------------------------------------------------------------------------
    |
    | The embedded browser client connects to ejabberd over WebSocket (preferred)
    | or BOSH, proxied on the kewlchats.net web host. `token_ttl` is how long a
    | minted web-chat auth token stays valid — the browser fetches a fresh one per
    | session from /chat/token, so the user's password is never re-entered. These
    | endpoints are wired up for real in Phase 2; the URLs below are placeholders.
    |
    */

    'web_chat' => [
        'websocket_url' => env('XMPP_WEBSOCKET_URL', 'wss://kewlchats.net/ws'),
        'bosh_url' => env('XMPP_BOSH_URL', 'https://kewlchats.net/bosh'),
        // Short-lived: the browser refetches per session, so keep the SASL token's
        // window small (10 min) to limit the value of a leaked one.
        'token_ttl' => (int) env('XMPP_CHAT_TOKEN_TTL', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | ejabberd ReST API (used by the "ejabberd" driver only)
    |--------------------------------------------------------------------------
    */

    'api' => [
        'base' => env('XMPP_API_BASE', 'http://127.0.0.1:5443/api'),
        'token' => env('XMPP_API_TOKEN'),
    ],

];
