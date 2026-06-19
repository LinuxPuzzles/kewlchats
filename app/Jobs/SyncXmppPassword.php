<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pushes a password change through to the user's XMPP account after they reset
 * or change it in KewlChats. The new plaintext is passed straight into the job
 * (the only moment it exists) and never persisted by us.
 */
class SyncXmppPassword implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(public int $userId, public string $newPassword)
    {
    }

    public function handle(XmppProvisioner $xmpp): void
    {
        $user = User::find($this->userId);

        // Only sync accounts that have actually been provisioned.
        if (! $user || ! $user->xmpp_username || $user->xmpp_status !== 'active') {
            return;
        }

        $xmpp->changePassword($user->xmpp_username, $this->newPassword, $user->domain);

        // Recovered — a prior reset that drifted is now back in sync.
        if ($user->xmpp_desynced_at) {
            $user->forceFill(['xmpp_desynced_at' => null, 'xmpp_desync_reason' => null])->save();
        }
    }

    public function failed(Throwable $e): void
    {
        // Critical: the website password changed but XMPP didn't — the OLD chat
        // credential may still be valid. Flag it so it's visible/re-drivable.
        Log::critical('[xmpp] password sync DESYNC — old chat credential may still work', [
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);

        User::whereKey($this->userId)->update([
            'xmpp_desynced_at' => now(),
            'xmpp_desync_reason' => 'password',
        ]);
    }
}
