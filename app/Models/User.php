<?php

namespace App\Models;

use App\Jobs\UnregisterXmppAccount;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// xmpp_pending_secret (a secret) and xmpp_status are intentionally NOT fillable —
// set them via forceFill so no future endpoint can mass-assign them.
#[Fillable(['name', 'email', 'password', 'xmpp_username'])]
#[Hidden(['password', 'remember_token', 'xmpp_pending_secret'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * When a user is deleted, tear down their XMPP account too.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->xmpp_username) {
                UnregisterXmppAccount::dispatch($user->xmpp_username);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Transparently encrypted at rest; only ever set between signup and
            // email verification, then nulled out once provisioned.
            'xmpp_pending_secret' => 'encrypted',
            'xmpp_provisioned_at' => 'datetime',
            'is_admin' => 'boolean',
            'banned_at' => 'datetime',
            'xmpp_desynced_at' => 'datetime',
        ];
    }

    /**
     * The user's full XMPP address, e.g. "alice@kewlchats.net".
     */
    public function jid(): ?string
    {
        return $this->xmpp_username
            ? $this->xmpp_username.'@'.config('xmpp.domain')
            : null;
    }

    /**
     * Whether the XMPP account has been provisioned and is ready to use.
     */
    public function xmppIsActive(): bool
    {
        return $this->xmpp_status === 'active';
    }

    /**
     * Whether this user can reach the admin area.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Whether this user is banned (blocks website login + XMPP).
     */
    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Whether this account's XMPP state drifted from KewlChats (a sync job failed).
     */
    public function isDesynced(): bool
    {
        return $this->xmpp_desynced_at !== null;
    }
}
