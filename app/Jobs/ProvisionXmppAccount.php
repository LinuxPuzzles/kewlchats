<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Creates the user's XMPP account once their email is verified, using the
 * password they chose at signup (stashed encrypted in xmpp_pending_secret).
 * On success the stash is wiped so the plaintext never lingers.
 *
 * Runs on the queue so a slow/unavailable ejabberd never blocks the web
 * request; retried with backoff, with drift tracked via xmpp_status.
 */
class ProvisionXmppAccount implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(public int $userId)
    {
    }

    public function handle(XmppProvisioner $xmpp): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        // Already provisioned (e.g. a duplicate/retried event) — nothing to do.
        if ($user->xmpp_status === 'active') {
            return;
        }

        $secret = $user->xmpp_pending_secret;

        if (! $user->xmpp_username || ! $secret) {
            Log::warning('[xmpp] provision skipped: missing username or secret', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $xmpp->register($user->xmpp_username, $secret);

        $user->forceFill([
            'xmpp_status' => 'active',
            'xmpp_provisioned_at' => now(),
            'xmpp_pending_secret' => null,
        ])->save();
    }

    public function failed(Throwable $e): void
    {
        if ($user = User::find($this->userId)) {
            $user->forceFill(['xmpp_status' => 'failed'])->save();
        }

        Log::error('[xmpp] provisioning failed', [
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);
    }
}
