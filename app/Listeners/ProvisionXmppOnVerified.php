<?php

namespace App\Listeners;

use App\Jobs\ProvisionXmppAccount;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

/**
 * When a user verifies their email, kick off creation of their XMPP account.
 * Auto-discovered via the Verified type-hint on handle().
 */
class ProvisionXmppOnVerified
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user instanceof User) {
            ProvisionXmppAccount::dispatch($user->id);
        }
    }
}
