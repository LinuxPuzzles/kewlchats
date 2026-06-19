<?php

namespace App\Jobs;

use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Removes an XMPP account after the corresponding KewlChats user is deleted.
 * Takes the bare localpart because the user row is already gone by dispatch time.
 */
class UnregisterXmppAccount implements ShouldQueue
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
        $xmpp->unregister($this->username, $this->domain);
    }

    public function failed(Throwable $e): void
    {
        Log::error('[xmpp] unregister failed', [
            'username' => $this->username,
            'error' => $e->getMessage(),
        ]);
    }
}
