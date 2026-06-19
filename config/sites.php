<?php

/*
|--------------------------------------------------------------------------
| Front doors (one community, multiple domains, ONE install)
|--------------------------------------------------------------------------
|
| Each entry is a "door": the browsed Host selects its brand/theme/mail skin
| per request, while a user's own domain (their JID suffix) lives on their
| row. Non-secret skin data is inline here (these two doors are permanent);
| the ejabberd ReST API is loopback node-admin, so one XMPP_API_TOKEN covers
| every vhost — no per-door token needed.
|
| A Host not listed here (e.g. the dev domain kewlchats.corp, or test hosts)
| falls back to the plain .env values via SiteContext::fromEnv(), so local/dev
| keeps working as a single tenant with no registry entry.
|
*/

return [

    'primary' => env('SITE_PRIMARY_DOMAIN', env('XMPP_DOMAIN', 'kewlchats.net')),

    'sites' => [
        'kewlchats.net' => [
            'domain' => 'kewlchats.net',
            'brand' => 'KewlChats',
            'theme' => 'kewlchats',
            'mail_theme' => 'default',
            'mail_from' => 'hello@kewlchats.net',
        ],
        'ready2.im' => [
            'domain' => 'ready2.im',
            'brand' => 'ready2.im',
            'theme' => 'ready2im',
            'mail_theme' => 'ready2im',
            'mail_from' => 'hello@ready2.im',
        ],
    ],

];
