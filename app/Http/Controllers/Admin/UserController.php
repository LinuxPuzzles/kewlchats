<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BanXmppAccount;
use App\Jobs\UnbanXmppAccount;
use App\Models\Channel;
use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly XmppProvisioner $xmpp)
    {
    }

    public function index(): View
    {
        return view('admin.users', [
            'users' => User::orderByDesc('created_at')->paginate(30),
            'stats' => [
                // Live from ejabberd (guarded so a hiccup never breaks the page); the rest from our DB.
                'online' => rescue(fn () => $this->xmpp->onlineCount(), 0, false),
                'registered' => User::whereNotNull('xmpp_username')->count(),
                'banned' => User::whereNotNull('banned_at')->count(),
                'channels' => Channel::count(),
            ],
        ]);
    }

    /**
     * Ban a user: blocks website login (banned_at) AND XMPP (ban_account, async).
     */
    public function ban(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['ban' => "You can't ban an admin."]);
        }

        $reason = (string) ($request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ])['reason'] ?? 'Violated the acceptable use policy.');

        $user->forceFill([
            'banned_at' => now(),
            'ban_reason' => $reason,
            'xmpp_status' => 'disabled',
        ])->save();

        if ($user->xmpp_username) {
            BanXmppAccount::dispatch($user->xmpp_username, $reason, $user->domain);
        }

        return back()->with('status', "Banned {$user->email}.");
    }

    public function unban(User $user): RedirectResponse
    {
        $user->forceFill([
            'banned_at' => null,
            'ban_reason' => null,
            'xmpp_status' => 'active',
        ])->save();

        if ($user->xmpp_username) {
            UnbanXmppAccount::dispatch($user->xmpp_username, $user->domain);
        }

        return back()->with('status', "Unbanned {$user->email}.");
    }

    /**
     * Force-disconnect the user's live chat sessions (without banning). Immediate,
     * so it's a synchronous API call rather than a queued job.
     */
    public function kick(User $user): RedirectResponse
    {
        if (! $user->xmpp_username) {
            return back()->withErrors(['kick' => 'That user has no chat account.']);
        }

        try {
            $this->xmpp->kick($user->xmpp_username, $user->domain);
        } catch (\Throwable $e) {
            return back()->withErrors(['kick' => 'Could not reach the chat server.']);
        }

        return back()->with('status', "Disconnected {$user->email}'s chat sessions.");
    }

    /**
     * Send the user a password-reset link. The existing reset flow re-syncs the new
     * password to ejabberd, so no XMPP-specific action is needed here.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);

        return back()->with('status', "Sent a password-reset link to {$user->email}.");
    }
}
