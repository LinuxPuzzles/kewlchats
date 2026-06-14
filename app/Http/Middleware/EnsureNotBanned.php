<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force-logs-out any authenticated user who has been banned, so a ban applied
 * mid-session takes effect on their next request. (Login itself is also rejected
 * in LoginRequest, but this covers already-active sessions.)
 */
class EnsureNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if (($user = $request->user()) && $user->isBanned()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended.',
            ]);
        }

        return $next($request);
    }
}
