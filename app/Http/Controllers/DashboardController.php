<?php

namespace App\Http\Controllers;

use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{
    public function __invoke(Request $request, XmppProvisioner $xmpp): View
    {
        $user = $request->user();
        $jid = $user->jid();

        // QR encodes the JID only (no client reliably accepts a password by QR);
        // it pre-fills the address in Conversations/Monal. SVG needs no imagick/GD.
        $jidQr = $jid
            ? QrCode::format('svg')->size(220)->margin(1)->generate("xmpp:{$jid}")
            : null;

        // Best-effort showcase reads: cache the global stats and degrade gracefully
        // so a slow/down ejabberd never breaks the dashboard. (Write path still retries.)
        $onlineCount = rescue(fn () => Cache::remember('xmpp.online_count', now()->addMinute(), fn () => $xmpp->onlineCount()), 0, false);
        $lastActivity = $user->xmppIsActive() && $user->xmpp_username
            ? rescue(fn () => $xmpp->lastActivity($user->xmpp_username), null, false)
            : null;

        return view('dashboard', [
            'jid' => $jid,
            'jidQr' => $jidQr,
            'onlineCount' => $onlineCount,
            'lastActivity' => $lastActivity,
            'rooms' => rescue(fn () => Cache::remember('xmpp.featured_rooms', now()->addMinute(), fn () => $xmpp->featuredRooms()), [], false),
        ]);
    }
}
