<?php

namespace App\Http\Middleware;

use App\Support\SiteContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the front door for this request from the Host header and activates its
 * brand/theme/mail/XMPP config (one install, multiple domains). Runs first in the
 * web group so views and controllers see the right door. A user's OWN domain still
 * comes from their row (User::jid()), not from here.
 */
class ResolveSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $site = SiteContext::forHost($request->getHost());
        SiteContext::applyWithTheme($site['domain']);

        return $next($request);
    }
}
