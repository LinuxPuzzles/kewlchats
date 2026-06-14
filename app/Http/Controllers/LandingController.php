<?php

namespace App\Http\Controllers;

use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(XmppProvisioner $xmpp): View
    {
        // Best-effort showcase reads: a slow/down ejabberd must never 500 the public
        // landing page, so degrade to sane fallbacks. (The write path still retries.)
        return view('landing', [
            'onlineCount' => rescue(fn () => Cache::remember('xmpp.online_count', now()->addMinute(), fn () => $xmpp->onlineCount()), 0, false),
            'rooms' => rescue(fn () => Cache::remember('xmpp.featured_rooms', now()->addMinute(), fn () => $xmpp->featuredRooms()), [], false),
        ]);
    }
}
