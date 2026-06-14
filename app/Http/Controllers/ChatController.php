<?php

namespace App\Http\Controllers;

use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Browser chat (Converse.js) — the zero-install on-ramp.
 *
 * The page provides the spot where the embedded client lives; the token endpoint
 * mints a short-lived XMPP credential for the logged-in user so they never have to
 * re-type the password (which Laravel only holds hashed anyway). Wired to the mock
 * in Phase 1; against a real ejabberd + Converse.js in Phase 2.
 */
class ChatController extends Controller
{
    public function __construct(private readonly XmppProvisioner $xmpp)
    {
    }

    /**
     * The web-chat page where Converse.js will mount.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return view('chat', [
            'jid' => $user->jid(),
            'active' => $user->xmppIsActive(),
            'websocketUrl' => config('xmpp.web_chat.websocket_url'),
        ]);
    }

    /**
     * Mint a short-lived XMPP auth token for the logged-in user. Shaped as a
     * Converse.js `credentials_url` response ({ jid, password }) — the token rides
     * in the password field and ejabberd validates it via SASL X-OAUTH2.
     */
    public function token(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->xmppIsActive()) {
            return response()->json(['message' => 'Your chat account is not ready yet.'], 409);
        }

        $token = $this->xmpp->issueChatToken($user->xmpp_username);

        if ($token === null) {
            return response()->json(['message' => 'Could not start a chat session.'], 502);
        }

        return response()->json([
            'jid' => $user->jid(),
            'password' => $token['token'],
            'expires_at' => $token['expires_at']->toIso8601String(),
        ]);
    }
}
