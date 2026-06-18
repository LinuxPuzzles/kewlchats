<?php

namespace App\Services\Xmpp;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Real provisioner backed by ejabberd's ReST API (mod_http_api), exposed at
 * config('xmpp.api.base') and authenticated with an admin OAuth bearer token
 * (scope `ejabberd:admin`). Command/response shapes verified against ejabberd 26.4.
 *
 * See https://docs.ejabberd.im/developer/ejabberd-api/admin-api/
 */
class EjabberdApiProvisioner implements XmppProvisioner
{
    public function register(string $username, string $password): void
    {
        $resp = $this->call('register', [
            'user' => $username,
            'host' => $this->host(),
            'password' => $password,
        ]);

        if ($resp->successful()) {
            return;
        }

        // Idempotent: "already registered" is success — jobs retry, and a re-run
        // must not flip a live account to failed. (ejabberd: HTTP 409 / code 10090.)
        if ($resp->status() === 409
            || $resp->json('code') === 10090
            || str_contains((string) $resp->json('message'), 'already registered')) {
            return;
        }

        $resp->throw();
    }

    public function unregister(string $username): void
    {
        $resp = $this->call('unregister', [
            'user' => $username,
            'host' => $this->host(),
        ]);

        if ($resp->successful()) {
            return;
        }

        // Already gone is fine (idempotent teardown).
        if (str_contains(strtolower((string) $resp->json('message')), 'does not exist')
            || str_contains(strtolower((string) $resp->json('message')), 'not registered')) {
            return;
        }

        $resp->throw();
    }

    public function changePassword(string $username, string $newPassword): void
    {
        $this->call('change_password', [
            'user' => $username,
            'host' => $this->host(),
            'newpass' => $newPassword,
        ])->throw();
    }

    public function accountExists(string $username): bool
    {
        $resp = $this->call('check_account', [
            'user' => $username,
            'host' => $this->host(),
        ])->throw();

        // check_account returns 0 = exists, 1 = not registered.
        return (int) $resp->json() === 0;
    }

    public function lastActivity(string $username): ?CarbonInterface
    {
        $resp = $this->call('get_last', [
            'user' => $username,
            'host' => $this->host(),
        ]);

        if (! $resp->successful()) {
            return null;
        }

        // {"status":"...","timestamp":"ISO8601"}; "Registered but didn't login"
        // means never active -> no meaningful last activity.
        $status = (string) $resp->json('status');
        $timestamp = $resp->json('timestamp');

        if (! $timestamp || str_contains($status, "didn't login")) {
            return null;
        }

        return Carbon::parse($timestamp);
    }

    public function onlineCount(): int
    {
        // Node-wide total. NOT connected_users_number — that evaluates in the OAuth
        // token's vhost context, so each front door would only count its own domain
        // (kewlchats.net is just a vanity JID suffix; the community is one server).
        // `stats onlineusers` is a node-global stat. If it turns out to still be
        // vhost-scoped on this build, sum stats_host across the vhost list instead.
        return (int) $this->call('stats', ['name' => 'onlineusers'])->throw()->json();
    }

    public function featuredRooms(): array
    {
        $muc = (string) config('xmpp.muc_domain');
        $curated = collect(config('xmpp.featured_rooms', []))->keyBy('localpart');

        // The directory is sourced from the SHARED MUC service + the shared curated
        // config — both identical across every front door — so kewlchats.net and
        // ready2.im show the same rooms and counts (one community; the JID suffix is
        // just identity). Per-site `channels` rows can't unify across separate DBs,
        // so they're no longer the public directory source; admins still create/destroy
        // rooms on the shared MUC and they appear here live.
        //
        // List = curated showcase (always advertised, even if empty) ∪ live rooms on
        // the shared MUC, deduped, curated order first.
        $localparts = $curated->keys()
            ->merge($this->listSharedRooms($muc) ?? [])
            ->unique();

        return $localparts->map(function (string $localpart) use ($muc, $curated) {
            $meta = $curated->get($localpart, []);

            return [
                'jid' => $localpart.'@'.$muc,
                'name' => $meta['name'] ?? Str::headline($localpart),
                'description' => (string) ($meta['description'] ?? ''),
                'occupants' => $this->roomOccupants($localpart, $muc),
            ];
        })->values()->all();
    }

    /**
     * Localparts of rooms on the shared MUC service, or null if the query failed
     * (caller falls back to the curated config list). Tolerates ejabberd returning
     * either "room@service" strings or [{"room": "..."}] objects across versions.
     */
    protected function listSharedRooms(string $muc): ?array
    {
        $resp = $this->call('muc_online_rooms', ['service' => $muc]);

        if (! $resp->successful() || ! is_array($items = $resp->json())) {
            return null;
        }

        $localparts = [];
        foreach ($items as $item) {
            $jid = is_array($item) ? ($item['room'] ?? null) : $item;
            if (is_string($jid) && str_contains($jid, '@')) {
                [$localpart, $service] = explode('@', $jid, 2);
                if ($service === $muc && $localpart !== '') {
                    $localparts[] = $localpart;
                }
            }
        }

        return $localparts;
    }

    public function issueChatToken(string $username): ?array
    {
        $ttl = (int) config('xmpp.web_chat.token_ttl', 3600);

        $resp = $this->call('oauth_issue_token', [
            'jid' => $username.'@'.$this->host(),
            'ttl' => $ttl,
            'scopes' => 'sasl_auth',
        ]);

        if (! $resp->successful() || ! $resp->json('token')) {
            return null;
        }

        return [
            'token' => (string) $resp->json('token'),
            'expires_at' => Carbon::now()->addSeconds($ttl),
        ];
    }

    public function ban(string $username, string $reason): void
    {
        $this->call('ban_account', [
            'user' => $username,
            'host' => $this->host(),
            'reason' => $reason,
        ])->throw();
    }

    public function unban(string $username): void
    {
        $this->call('unban_account', [
            'user' => $username,
            'host' => $this->host(),
        ])->throw();
    }

    public function kick(string $username): void
    {
        $this->call('kick_user', [
            'user' => $username,
            'host' => $this->host(),
        ])->throw();
    }

    public function createRoom(string $localpart, string $name, string $description): void
    {
        $this->call('create_room_with_opts', [
            'room' => $localpart,
            'service' => (string) config('xmpp.muc_domain'),
            'host' => $this->host(),
            // Persistent (survives empty) + public (listed) open channel.
            'options' => [
                ['name' => 'persistent', 'value' => 'true'],
                ['name' => 'public', 'value' => 'true'],
                ['name' => 'members_only', 'value' => 'false'],
                ['name' => 'title', 'value' => $name],
                ['name' => 'description', 'value' => $description],
            ],
        ])->throw();
    }

    public function destroyRoom(string $localpart): void
    {
        $this->call('destroy_room', [
            'room' => $localpart,
            'service' => (string) config('xmpp.muc_domain'),
        ])->throw();
    }

    /**
     * Live occupant count for a room; 0 if the room isn't currently instantiated.
     */
    protected function roomOccupants(string $localpart, string $muc): int
    {
        $resp = $this->call('get_room_occupants_number', [
            'name' => $localpart,
            'service' => $muc,
        ]);

        return $resp->successful() ? (int) $resp->json() : 0;
    }

    protected function host(): string
    {
        return (string) config('xmpp.domain');
    }

    /**
     * POST a command to the ReST API. Sends a JSON object (even when empty —
     * ejabberd expects `{}`, not `[]`, for no-argument commands).
     */
    protected function call(string $command, array $params = []): Response
    {
        return $this->client()
            ->withBody(json_encode($params ?: new \stdClass), 'application/json')
            ->post($command);
    }

    /**
     * HTTP client preconfigured with the API base URL and admin bearer token.
     * The token is attached only when configured, so loopback-only setups still work.
     */
    protected function client(): PendingRequest
    {
        $request = Http::baseUrl((string) config('xmpp.api.base'))->acceptJson();

        if ($token = config('xmpp.api.token')) {
            $request = $request->withToken((string) $token);
        }

        return $request;
    }
}
