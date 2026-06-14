<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Database\Seeder;

/**
 * Dev convenience: a ready-to-use, already-verified, fully-provisioned account so
 * you can log in (and chat) immediately — no chasing a verification email.
 *
 * Requires the local ejabberd to be running (it registers the real XMPP account
 * with the known password). NOT for production. Run explicitly:
 *
 *   php artisan db:seed --class=DevUserSeeder
 *
 * Then sign in at kewlchats.corp with  andy@kewlchats.net / password123
 * (chat JID: andy@kewlchats.net, same password).
 */
class DevUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'andy@andyjames.org';
        $username = 'andy';
        $password = 'password123';

        $xmpp = app(XmppProvisioner::class);

        // Idempotent re-seed: drop any prior copy matched by username OR email
        // (the User `deleting` hook unregisters the JID), then make sure the XMPP
        // side is gone too.
        User::where('xmpp_username', $username)->orWhere('email', $email)->get()->each->delete();
        $xmpp->unregister($username);

        $user = User::create([
            'name' => 'Andy',
            'email' => $email,
            'password' => $password,        // hashed by the model cast
            'xmpp_username' => $username,
        ]);

        // status / verified / admin aren't mass-assignable, so set them directly.
        $user->forceFill([
            'xmpp_status' => 'active',
            'email_verified_at' => now(),
            'xmpp_provisioned_at' => now(),
            'is_admin' => true,
        ])->save();

        // Provision the real ejabberd account with the known password.
        $xmpp->register($username, $password);

        $this->command->info("Seeded {$email} / {$password}  (JID {$username}@".config('xmpp.domain').')');
    }
}
