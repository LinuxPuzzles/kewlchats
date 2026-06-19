<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Bans an XMPP account (kicks sessions + blocks login) once a KewlChats user is
 * banned. The website-side ban is applied synchronously; this mirrors it to ejabberd.
 */
class BanXmppAccount implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(public string $username, public string $reason, public string $domain)
    {
    }

    public function handle(XmppProvisioner $xmpp): void
    {
        $xmpp->ban($this->username, $this->reason, $this->domain);

        User::where('xmpp_username', $this->username)
            ->whereNotNull('xmpp_desynced_at')
            ->update(['xmpp_desynced_at' => null, 'xmpp_desync_reason' => null]);
    }

    public function failed(Throwable $e): void
    {
        // Critical: user banned in KewlChats but XMPP login may still work.
        Log::critical('[xmpp] ban DESYNC — XMPP login may still work', [
            'username' => $this->username,
            'error' => $e->getMessage(),
        ]);

        User::where('xmpp_username', $this->username)->update([
            'xmpp_desynced_at' => now(),
            'xmpp_desync_reason' => 'ban',
        ]);
    }
}
