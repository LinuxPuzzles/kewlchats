<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Rules\XmppUsername;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Bootstraps an admin: promotes an existing user, or (with --create) provisions a
 * brand-new verified + admin account. This is how the first admin is made in prod.
 *
 *   php artisan kewlchats:make-admin you@example.com
 *   php artisan kewlchats:make-admin you@example.com --create
 */
class MakeAdmin extends Command
{
    protected $signature = 'kewlchats:make-admin {email} {--create : Create the user if it does not exist}';

    protected $description = 'Promote a user to admin (or create the first admin).';

    public function handle(XmppProvisioner $xmpp): int
    {
        $email = (string) $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            if (! $this->option('create')) {
                $this->error("No user with email {$email}. Pass --create to provision one.");

                return self::FAILURE;
            }

            $user = $this->createUser($email, $xmpp);

            if (! $user) {
                return self::FAILURE;
            }
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("{$email} is now an admin (JID ".($user->jid() ?? 'n/a').').');

        return self::SUCCESS;
    }

    private function createUser(string $email, XmppProvisioner $xmpp): ?User
    {
        $name = (string) $this->ask('Display name', 'Admin');
        $username = strtolower(trim((string) $this->ask('XMPP username (localpart)')));
        $password = (string) $this->secret('Password');
        $domain = (string) $this->ask('Home domain (JID suffix)', (string) config('sites.primary', config('xmpp.domain')));

        $validator = Validator::make(
            ['username' => $username, 'password' => $password],
            ['username' => ['required', new XmppUsername], 'password' => ['required', 'min:8']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return null;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,          // hashed by the model cast
            'xmpp_username' => $username,
            'domain' => $domain,
        ]);

        $user->forceFill([
            'xmpp_status' => 'active',
            'email_verified_at' => now(),     // skip verification for a bootstrapped admin
            'xmpp_provisioned_at' => now(),
        ])->save();

        // Provision the real XMPP account with the chosen password.
        $xmpp->register($username, $password, $domain);

        $this->info("Provisioned {$email}.");

        return $user;
    }
}
