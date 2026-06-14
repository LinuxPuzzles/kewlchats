<?php

namespace App\Services\Xmpp;

use App\Models\Channel;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Phase 1 provisioner: never touches a real server. It logs every call (so the
 * flow is observable end-to-end) and returns plausible canned data. Account
 * existence is checked against the local database so username uniqueness
 * behaves realistically during signup.
 */
class MockXmppProvisioner implements XmppProvisioner
{
    public function register(string $username, string $password): void
    {
        Log::info('[xmpp:mock] register', ['username' => $username]);
    }

    public function unregister(string $username): void
    {
        Log::info('[xmpp:mock] unregister', ['username' => $username]);
    }

    public function changePassword(string $username, string $newPassword): void
    {
        Log::info('[xmpp:mock] change_password', ['username' => $username]);
    }

    public function accountExists(string $username): bool
    {
        return User::where('xmpp_username', $username)->exists();
    }

    public function lastActivity(string $username): ?CarbonInterface
    {
        // Pretend the user was last seen a little while ago.
        return Carbon::now()->subMinutes(7);
    }

    public function onlineCount(): int
    {
        // Stable-ish made-up number so the dashboard looks alive.
        return 42;
    }

    public function issueChatToken(string $username): ?array
    {
        // No real server to mint against in Phase 1. Hand back a throwaway token so
        // the web-chat plumbing (endpoint -> Converse.js credentials) is exercisable
        // end-to-end; it authenticates against nothing until ejabberd exists.
        Log::info('[xmpp:mock] issue_chat_token', ['username' => $username]);

        return [
            'token' => 'mock-'.bin2hex(random_bytes(16)),
            'expires_at' => Carbon::now()->addSeconds((int) config('xmpp.web_chat.token_ttl', 3600)),
        ];
    }

    public function ban(string $username, string $reason): void
    {
        Log::info('[xmpp:mock] ban_account', ['username' => $username, 'reason' => $reason]);
    }

    public function unban(string $username): void
    {
        Log::info('[xmpp:mock] unban_account', ['username' => $username]);
    }

    public function kick(string $username): void
    {
        Log::info('[xmpp:mock] kick_user', ['username' => $username]);
    }

    public function createRoom(string $localpart, string $name, string $description): void
    {
        Log::info('[xmpp:mock] create_room_with_opts', ['room' => $localpart, 'name' => $name]);
    }

    public function destroyRoom(string $localpart): void
    {
        Log::info('[xmpp:mock] destroy_room', ['room' => $localpart]);
    }

    public function featuredRooms(): array
    {
        $muc = config('xmpp.muc_domain');

        // Prefer admin-created channels (canned occupant counts in the mock).
        $channels = Channel::orderBy('name')->get();
        if ($channels->isNotEmpty()) {
            return $channels->map(fn (Channel $c) => [
                'jid' => $c->jid(),
                'name' => $c->name,
                'description' => (string) $c->description,
                'occupants' => random_int(3, 40),
            ])->all();
        }

        return [
            [
                'jid' => "lounge@{$muc}",
                'name' => 'The Lounge',
                'description' => 'General hangout — say hi, talk about anything.',
                'occupants' => 28,
            ],
            [
                'jid' => "tech@{$muc}",
                'name' => 'Tech Talk',
                'description' => 'Gadgets, code, self-hosting and all things nerdy.',
                'occupants' => 17,
            ],
            [
                'jid' => "music@{$muc}",
                'name' => 'Now Playing',
                'description' => 'Share what you are listening to right now.',
                'occupants' => 11,
            ],
            [
                'jid' => "gaming@{$muc}",
                'name' => 'Game Night',
                'description' => 'Find people to play with, voice chat welcome.',
                'occupants' => 23,
            ],
        ];
    }
}
