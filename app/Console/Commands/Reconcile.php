<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Console\Command;

/**
 * Re-asserts ejabberd state for accounts that drifted (a sync/ban job failed
 * terminally, setting users.xmpp_desynced_at). Run after an ejabberd outage.
 *
 * Ban/unban drift is re-driven from KewlChats' authoritative state. Password drift
 * can't be — we no longer hold the plaintext — so it's only reported (the user must
 * reset again, which re-syncs and clears the flag).
 *
 *   php artisan kewlchats:reconcile
 */
class Reconcile extends Command
{
    protected $signature = 'kewlchats:reconcile';

    protected $description = 'Re-drive XMPP state for accounts that drifted from KewlChats.';

    public function handle(XmppProvisioner $xmpp): int
    {
        $drifted = User::whereNotNull('xmpp_desynced_at')->whereNotNull('xmpp_username')->get();

        if ($drifted->isEmpty()) {
            $this->info('Nothing to reconcile — no drifted accounts.');

            return self::SUCCESS;
        }

        $healed = 0;
        $passwordDrift = [];

        foreach ($drifted as $user) {
            $reason = $user->xmpp_desync_reason;
            try {
                switch ($reason) {
                    case 'ban':
                        $xmpp->ban($user->xmpp_username, $user->ban_reason ?? 'Reconciled.');
                        break;
                    case 'unban':
                        $xmpp->unban($user->xmpp_username);
                        break;
                    default: // 'password' or unknown — not re-drivable
                        $passwordDrift[] = $user->email;

                        continue 2;
                }

                $user->forceFill(['xmpp_desynced_at' => null, 'xmpp_desync_reason' => null])->save();
                $healed++;
                $this->line("  ✓ {$user->xmpp_username} ({$reason})");
            } catch (\Throwable $e) {
                $this->warn("  ! {$user->xmpp_username}: {$e->getMessage()}");
            }
        }

        $this->info("Reconciled {$healed} ban/unban drift(s).");

        if ($passwordDrift !== []) {
            $this->warn(count($passwordDrift).' account(s) have password drift (they must reset to fix): '.implode(', ', $passwordDrift));
        }

        return self::SUCCESS;
    }
}
