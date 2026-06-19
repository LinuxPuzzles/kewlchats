<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lifts an XMPP ban once a KewlChats user is unbanned.
 */
class UnbanXmppAccount implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(public string $username, public string $domain)
    {
    }

    public function handle(XmppProvisioner $xmpp): void
    {
        $xmpp->unban($this->username, $this->domain);

        User::where('xmpp_username', $this->username)
            ->whereNotNull('xmpp_desynced_at')
            ->update(['xmpp_desynced_at' => null, 'xmpp_desync_reason' => null]);
    }

    public function failed(Throwable $e): void
    {
        Log::critical('[xmpp] unban DESYNC — XMPP may still be banned', [
            'username' => $this->username,
            'error' => $e->getMessage(),
        ]);

        User::where('xmpp_username', $this->username)->update([
            'xmpp_desynced_at' => now(),
            'xmpp_desync_reason' => 'unban',
        ]);
    }
}
