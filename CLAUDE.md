# CLAUDE.md — KewlChats

Friendly web front door for an **XMPP/ejabberd** chat service on **kewlchats.net**.
A non-technical person signs up here, verifies their email, and gets a dashboard that
hands them their chat address + a step-by-step guide to set up a mobile XMPP client
(Conversations on Android, Monal on iOS). KewlChats owns identity (signup, verification,
rate limiting, bot protection, password resets) and provisions/manages the underlying
XMPP account on their behalf.

> Not part of the Marathon Hosting system. It only shares the `/Users/andy/Herd/` root
> (and that root `CLAUDE.md`) by virtue of living under Herd. Treat it as standalone.

## Stack & environment

- **Laravel 13** + **Breeze** (Blade + Tailwind + Alpine.js).
- **SQLite** in dev (`database/database.sqlite`). ejabberd will use MySQL in prod — that's
  ejabberd's own store, unrelated to this app's DB.
- Served by **Laravel Herd** at **http://kewlchats.corp** (Herd's TLD here is `.corp`,
  not `.test`; the site is not TLS-secured in dev).
- Dev mail uses `MAIL_MAILER=log` — verification/reset links land in
  `storage/logs/laravel.log`. NOTE: the logged HTML encodes `&` as `&amp;`; copy the link
  decoded or signed-URL validation fails (see Gotchas).
- `QUEUE_CONNECTION=sync` in dev so provisioning jobs run inline without a worker.

## Core design (Phase 1 — XMPP is mocked, no real server yet)

- **One password.** The password chosen at signup is BOTH the dashboard login and the
  XMPP password. It is **never displayed**. The dashboard shows the JID
  (`username@kewlchats.net`) and a QR that pre-fills the **JID only** (no XMPP client
  reliably accepts a password via QR).
- **Username = JID localpart**, chosen at signup with a live `you@kewlchats.net` preview.
  Permanent (XMPP has no rename). Validated by `app/Rules/XmppUsername.php`
  (format + reserved-name blocklist + uniqueness via the provisioner).
- **Provision on email-verify.** Laravel hashes the login password (unrecoverable), but
  ejabberd needs the plaintext. So signup also stashes the password **encrypted** in
  `users.xmpp_pending_secret` (model `encrypted` cast). When the `Verified` event fires,
  `ProvisionXmppAccount` registers the account with it, then **wipes the stash**. Plaintext
  otherwise only exists transiently in-request during a password reset.
- **App signups are disabled server-side.** In prod, ejabberd's in-band registration is
  turned off (`mod_register` access denied), so this website is the only way to get an
  account.

## Key components

| Concern | Where |
|---|---|
| XMPP integration seam | `app/Services/Xmpp/XmppProvisioner.php` (interface) |
| Phase 1 implementation | `MockXmppProvisioner` (logs calls, canned stats/rooms, DB-backed uniqueness) |
| Phase 2 implementation | `EjabberdApiProvisioner` (**implemented**; validated against local ejabberd 26.4) |
| Driver binding | `AppServiceProvider::register()` via `config('xmpp.driver')` (`XMPP_DRIVER`) |
| Domains / API config | `config/xmpp.php` — never hardcode the domain/MUC host |
| Provision on verify | `app/Listeners/ProvisionXmppOnVerified.php` → `app/Jobs/ProvisionXmppAccount.php` |
| Password sync | `app/Jobs/SyncXmppPassword.php` (from reset + in-dashboard change) |
| Account teardown | `app/Jobs/UnregisterXmppAccount.php` (User `deleting` hook) |
| Signup customizations | `app/Http/Controllers/Auth/RegisteredUserController.php` (username, stash, honeypot, Turnstile) |
| Bot protection | honeypot `website` field + `app/Rules/Turnstile.php` (bypassed when no key set) |
| Dashboard | `app/Http/Controllers/DashboardController.php` + `resources/views/dashboard.blade.php` |
| Landing page | `app/Http/Controllers/LandingController.php` + `resources/views/landing.blade.php` |

All provisioning runs through queued jobs (retries + backoff) with drift tracked via
`users.xmpp_status` (`pending | active | failed | disabled`), so a slow/down ejabberd
never blocks a web request.

## ejabberd ReST API (for the Phase 2 driver)

`mod_http_api` exposes `/api/<command>`; admin OAuth bearer token. Commands used:
`register`, `unregister`, `change_password`, `check_account`, `get_last`,
`connected_users_number`, plus MUC queries. `register` must be treated as **idempotent**
("already registered" = success) because jobs retry. Docs:
https://docs.ejabberd.im/developer/ejabberd-api/admin-api/

## Gotchas

- **`@{{ ... }}` in Blade is the literal-escape syntax** and will NOT interpolate. To print
  an address use `{{ 'you@'.config('xmpp.domain') }}`, not `you@{{ config('xmpp.domain') }}`.
- **Signed-URL "Invalid signature" in dev**: the `log` mailer records the HTML body where
  `&` is `&amp;`. Pasting that raw splits the query string and drops the `signature` param.
  Decode it first, e.g.:
  `grep -oE 'http://[^ "]*verify-email[^ "<]*' storage/logs/laravel.log | tail -1 | sed 's/&amp;/\&/g'`
- **Never render the password.** The dashboard intentionally shows only the JID. The
  `xmpp_pending_secret` stash and the hashed password must never reach a view.
- **Provision only after verification** — never call `register` during signup (avoids spam
  accounts for unverified emails).

## Tests

`php artisan test`. XMPP behaviour is covered against the mock in
`tests/Feature/XmppProvisioningTest.php` and `tests/Feature/UsernameValidationTest.php`
(registration stashes but does not provision pre-verify; verify provisions + wipes stash;
password change/reset sync; username rules; dashboard never leaks the password).

## Phase 2 design decisions (locked — drive the ejabberd config)

These are product/privacy decisions, not derivable from code. The whole posture is
**"be the boring, low-value target"**: legal exposure is set by *what we retain*, not by
features, so we retain almost nothing. The vibe is intentionally IRC-like — ephemeral,
"you had to be there."

- **Federation OFF at launch (closed/walled-garden, `s2s` disabled).** Long-term ethos is
  open federation ("make all XMPP apps work, like all XMPP does"), but launch closed and
  open later: closed→open is non-breaking (users *gain* reach), open→closed breaks existing
  cross-server contacts. Don't enable `s2s` until abuse/spam controls + a written retention
  policy exist.
- **No message archive (MAM / `mod_mam` off).** We do not keep message history server-side.
  This is the single most important knob for the "we don't have useful data" posture.
- **Smooth multi-device without an archive:** Carbons (`mod_carbons`) ON so simultaneous
  phone+laptop both see live messages; short-lived **offline spool** (`mod_offline`) so a
  message sent while you're offline still arrives once, then is gone; MUC room history
  near-zero. Accepted tradeoff: a device that was *off* can't sync past scrollback — by design.
- **Minimal logging / retention:** keep IP/connection-log retention minimal (metadata is what
  most requests actually want). Content, where clients use OMEMO, is ciphertext we can't read —
  but we don't *promise* E2E in the UI (we don't control the clients; it's not always on).
- **Web chat via Converse.js (self-hosted).** Embed a self-hosted [Converse.js](https://conversejs.org/)
  client for **zero-install onboarding**: signup → verify → *chatting in the browser*, with the
  native app (Conversations/Monal) reframed as the *upgrade*, not the barrier. This is KewlChats
  being true to its roots — web chat was always why it was easy ("couldn't figure out mIRC? just
  web chat"). Auth via **token/OAuth SSO**: Laravel owns identity and mints a short-lived ejabberd
  token (`X-OAUTH2` SASL) for the logged-in session, so the one password is **never re-asked or
  re-shown** (consistent with the one-password model — Laravel only holds the hash). Self-host the
  assets (no CDN — wrong look for a no-tracking project); proxy a `/ws` WebSocket endpoint to
  ejabberd. Caveat: **OMEMO-on-web is weaker** than the native clients → web chat is the
  *convenience* path, the app is the *stronger-privacy* path; don't let the UI imply they're equal.
- **Ephemeral-on-join is a feature, not a gap — but set the expectation.** An empty room on join
  is the original IRC model (join and go forward), and on-brand, NOT a loss. But a newcomer who
  didn't grow up on IRC reads "empty" as "broken." UX rule: frame it warmly and once as a **live
  room happening now** (mental model: *walking into a party, not opening a chat log*), present-tense
  and forward-facing (e.g. "You're in. This is a live room — you'll see everything from right now").
  **Never** render it as an error / "No messages" / "History unavailable" state.

> Not legal advice — jurisdiction, "communications provider" status, and lawful-intercept
> duties need a real lawyer. The above is the technical/retention shape that keeps what we
> can be compelled to hand over as small as possible.

## Phase 2 — capacity & ops (the box this runs on)

Target server is a single **decommissioned-but-healthy** machine (EOL'd in a fleet upgrade
to EPYC 7413 boxes — *not* faulty), left online for this project:

- **CPU:** Intel Xeon E3-1270 v3 — 4 cores / 8 threads @ 3.5 GHz (Haswell). Strong
  single-thread; has **AES-NI** (`aes`/`pclmulqdq`/`avx2`) so TLS is hardware-accelerated.
- **RAM:** 32 GB ECC — **platform ceiling, can't expand** (Haswell E3 max).
- **Disk:** 480 GB SSD (huge for a no-archive workload).
- **Bandwidth:** 10 TB/month (~30 Mbps sustained average).
- **Single box, no HA** — acceptable trade for a free side service; name it, don't be surprised.

Binding constraints, in order: **bandwidth → TURN relay packets/sec → file-descriptor/config
tuning → CPU/RAM.** The hardware is *not* the limit at this scale.

- **Calls = TURN relay bandwidth.** Jingle media is P2P (WebRTC); ejabberd only does signaling
  (KB per call). The server carries media *only* when P2P fails and the stream relays through
  **ejabberd's built-in TURN** (`ejabberd_stun` + `mod_stun_disco` — no separate coturn needed;
  coturn is the Matrix/Synapse default because Synapse has no built-in TURN) — roughly 10–30% of
  calls. Rough relayed egress: voice ~80 kbps, video ~2 Mbps. Against 10 TB/mo, **voice is
  effectively free; video is the only slice that can move the needle** (~a few million relayed
  video-minutes/mo at ~20% relay). Group video (mesh) multiplies per-leg → needs an SFU before
  it's a real feature.
- **Don't run an open relay.** ejabberd's `mod_stun_disco` hands clients short-lived TURN
  credentials tied to their XMPP auth (XEP-0215), so it's authenticated by default — set a defined
  relay UDP port range (e.g. 49152–65535) and the server's public `turn_ipv4_address`.
- **MAM-off keeps load tiny.** No archive = almost no write load; DB/disk stay small. The
  SSD makes MySQL latency a non-issue.
- **Raise ulimits.** Default `nofile 1024` caps you at ~1k connections regardless of the
  32 GB. Raise `nofile` + Erlang max ports to 100k+. (#1 real-world gotcha.)
- **Concurrency headroom.** ejabberd (Erlang) handles tens of thousands of concurrent
  connections on this box easily. Weak spot is a spiky reconnect storm coinciding with relay
  + DB load — 4c/8t absorbs that less gracefully than a big box, but fine at this scale.
- **Old-CPU caveat.** Every speculative-exec mitigation is on (Meltdown/Spectre/L1TF/MDS/…);
  a few % perf cost and no future microcode. Fine for a public chat server not running
  untrusted local code.

## Security posture (Phase 4 — review actioned)

The defensive review in `security-review.txt` was implemented. Controls + **hard prod gates**:
- **Turnstile fails CLOSED in prod** (`app/Rules/Turnstile.php`): a missing `TURNSTILE_SECRET`
  blocks signup in `production` (local/testing still skip). **`TURNSTILE_SECRET` is a launch gate.**
- **`APP_DEBUG=false` in prod — non-negotiable.** `APP_KEY` is the only thing protecting the
  encrypted `xmpp_pending_secret`; a debug page leaks it.
- **wss-only** web chat in prod — the X-OAUTH2 token is a SASL bearer; never on plain `ws://`.
- **Throttles:** register `8,1`, forgot-password `5,1`, `/chat/token` `30,1` (+ login's existing
  email|ip limiter). Generic password-reset responses (no membership enumeration).
- **`password.confirm`** gates destructive admin actions (ban/kick/reset/channel store+destroy) —
  the best compensating control given no XMPP-layer MFA.
- **Sync drift:** a terminally-failed XMPP sync/ban job sets `users.xmpp_desynced_at` (+
  `xmpp_desync_reason`) and `Log::critical`. **Run `php artisan kewlchats:reconcile` after any
  ejabberd outage** — it re-drives ban/unban drift; password drift can't be re-pushed (no
  plaintext) so the user gets a "reset again" dashboard banner. Secret/status are not mass-assignable.

## Local ejabberd (dev)

Dev now runs a **real ejabberd** (Homebrew, ejabberd 26.4, Mnesia). Config source-of-truth +
runbook live in **`ops/ejabberd/`** (symlinked to `/opt/homebrew/etc/ejabberd/ejabberd.yml`).
`.env` has `XMPP_DRIVER=ejabberd`, API on `http://127.0.0.1:5280/api` with an admin OAuth
bearer token (scope `ejabberd:admin`). To start/reset, see `ops/ejabberd/README.md`. Flip
`XMPP_DRIVER=mock` to work without it. Tests always use the mock (forced in `phpunit.xml`).

**Dev vhost is `kewlchats.corp`** (not `.net`): it resolves over Tailscale, so phone apps
(Conversations/Monal) can connect to `name@kewlchats.corp` directly — real native-app testing.
ejabberd binds `::`, so it's reachable on the mini's Tailscale IP; the self-signed cert just
needs a one-time accept/pin in the client. The API base stays loopback; only the client-facing
bits (JID domain, MUC, WebSocket) are `.corp`. **Prod uses `kewlchats.net`** — a documented env
delta in the `ejabberd.yml` `hosts`/`acl admin` lines (alongside certs + SQL backend).

Dev login: seed a ready, verified, provisioned account with
`php artisan db:seed --class=DevUserSeeder` → sign in at `kewlchats.corp` with
`andy@andyjames.org` / `password123` (JID `andy@kewlchats.corp`, same password). See
`database/seeders/DevUserSeeder.php`.

Gotcha baked into the config: the HTTP listener binds `::`, so IPv4 loopback arrives as
`::ffff:127.0.0.1` and won't match the `loopback` ACL — **ReST auth uses the bearer token**,
not loopback. `register` returns HTTP 409 / code 10090 on "already registered" → treated as
success (idempotent).

## Phase 2 status

**Done (2a):** local ejabberd stood up to the locked decisions; `EjabberdApiProvisioner`
implemented and validated end-to-end (signup doesn't provision pre-verify; verify creates a
real account + wipes the stash; reset syncs password; delete unregisters). Showcase reads
(`onlineCount`/`featuredRooms`/`lastActivity`) are `rescue()`-guarded so a down ejabberd never
500s a page.

**Done (2b — web chat):** self-hosted **Converse.js** (`public/vendor/converse/`, published via
`npm run converse:publish`, wired into `npm run build`, gitignored) embedded in `/chat`. It logs
in with the minted **X-OAUTH2** token from `/chat/token` — no password re-entry.
- **The trick:** ejabberd also offers SCRAM/PLAIN, which Strophe prefers and which can't work
  (token ≠ password, and SCRAM needs Web Crypto). A small whitelisted Converse plugin restricts
  the connection to X-OAUTH2 on `connectionInitialized` via
  `conn.registerSASLMechanisms([xoauth2.constructor])` — note it wants mechanism **classes**, not
  instances. (`disableSASLMechanism` doesn't exist in this Strophe build.)
- **Verified headlessly:** `npm run webchat:verify` (`scripts/webchat-verify.mjs`, Puppeteer) logs
  in as the seeded `andy`, and asserts `<auth mechanism="X-OAUTH2">` → `<success/>` → resource
  bind + carbons. Great regression guard.
- **Secure-context caveat:** Converse needs `window.crypto.subtle` (SCRAM/caps hashing), which
  browsers only expose over **HTTPS or localhost**. Plain `http://kewlchats.corp` is *not* a secure
  context, so web chat crashes **post-auth** there. Prod (HTTPS) is fine; the harness fakes it with
  Chrome's `--unsafely-treat-insecure-origin-as-secure`. For local interactive testing, launch a
  browser with that flag, or serve dev over HTTPS + a `wss` proxy to ejabberd's `/ws`.

**Done (Phase 3 — admin & moderation):** admin role + moderation UI, all ejabberd actions via the
ReST API (never `ejabberdctl`).
- **Admin role:** `users.is_admin`; gate `Gate::define('admin', …)`; routes behind `can:admin`;
  `@can('admin')` nav link. Bootstrap with `php artisan kewlchats:make-admin {email} [--create]`
  (`DevUserSeeder` flips `andy` admin).
- **Ban = both sides.** `users.banned_at`/`ban_reason` block website login (`LoginRequest` +
  `EnsureNotBanned` middleware on the web group) and `xmpp_status='disabled'`; `BanXmppAccount` job
  calls ejabberd `ban_account` (kicks sessions + blocks XMPP login, account kept). Unban reverses.
  Admins can't be banned.
- **Persistent public channels.** `channels` table + `ChannelController`; provisioner
  `createRoom()` → `create_room_with_opts` (persistent+public, survives empty). `featuredRooms()`
  now reads `channels` (falls back to the `config('xmpp.featured_rooms')` list when empty).
- **Sync boundary:** Laravel is the **control plane** — do moderation through the KewlChats admin
  UI so both sides stay consistent. A ban made directly in ejabberd's own web admin won't reflect
  back to Laravel (no push). (Future: a reconcile command.)
- **More admin actions:** **kick** live sessions (`kick_user`, synchronous — immediate),
  **send password-reset** (reuses the reset→sync flow; no XMPP-specific step), and a **live stats**
  header (online from ejabberd's `onlineCount()`; registered/banned/channels from the DB).
- New provisioner methods: `ban`/`unban`/`kick`/`createRoom`/`destroyRoom` (interface + mock +
  ejabberd). Verified against live ejabberd; covered by `tests/Feature/AdminModerationTest.php`.

**Remaining:**
- **Prod:** deploy ejabberd on the Xeon (MySQL, Let's Encrypt, same `ejabberd.yml`); front the
  WebSocket with TLS (`wss`) behind the web server so the secure-context requirement is met.
- **Voice/video:** re-enable ejabberd's built-in STUN/TURN — the `ejabberd_stun` listener (UDP
  5478) + `mod_stun_disco`, with `use_turn: true`, the public `turn_ipv4_address`, and a relay
  port range. (Both were trimmed from `ops/ejabberd/ejabberd.yml`; the brew default had them.)
  No coturn. Plus real MUC directory / presence polish.
