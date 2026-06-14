<?php

namespace App\Services\Xmpp;

use Carbon\CarbonInterface;

/**
 * Contract for everything KewlChats needs from the underlying XMPP server.
 *
 * Phase 1 runs against {@see MockXmppProvisioner}. A real
 * {@see EjabberdApiProvisioner} backed by ejabberd's ReST API is swapped in
 * later with no changes to the rest of the app.
 */
interface XmppProvisioner
{
    /**
     * Create an account for the given localpart with the given plaintext
     * password. Implementations MUST be idempotent: an existing account with a
     * matching localpart should not be treated as a hard failure.
     */
    public function register(string $username, string $password): void;

    /**
     * Permanently remove an account.
     */
    public function unregister(string $username): void;

    /**
     * Change an existing account's password.
     */
    public function changePassword(string $username, string $newPassword): void;

    /**
     * Whether an account already exists for the given localpart.
     */
    public function accountExists(string $username): bool;

    /**
     * The account's last activity timestamp, or null if never seen / unknown.
     */
    public function lastActivity(string $username): ?CarbonInterface;

    /**
     * Number of currently connected users across the server.
     */
    public function onlineCount(): int;

    /**
     * Featured group-chat rooms to showcase.
     *
     * @return array<int, array{jid: string, name: string, description: string, occupants: int}>
     */
    public function featuredRooms(): array;

    /**
     * Mint a short-lived auth token the browser chat client (Converse.js) can use
     * to log into the XMPP server WITHOUT the user re-entering their password.
     *
     * KewlChats owns identity, so it issues the token for an already-authenticated
     * web session; ejabberd validates it via SASL X-OAUTH2. This keeps the
     * one-password model intact — Laravel only ever holds the hash, never replays
     * the plaintext to the browser. Returns the token + its absolute expiry, or
     * null if one can't be issued.
     *
     * @return array{token: string, expires_at: CarbonInterface}|null
     */
    public function issueChatToken(string $username): ?array;

    /**
     * Ban an account: kick its live sessions and block login, while keeping the
     * account (reversible). KewlChats mirrors this with a website-side ban.
     */
    public function ban(string $username, string $reason): void;

    /**
     * Lift a ban.
     */
    public function unban(string $username): void;

    /**
     * Force-disconnect a user's live sessions, without banning (a soft moderation nudge).
     */
    public function kick(string $username): void;

    /**
     * Create a persistent, public group-chat room (channel) that survives being empty.
     */
    public function createRoom(string $localpart, string $name, string $description): void;

    /**
     * Permanently remove a room.
     */
    public function destroyRoom(string $localpart): void;
}
