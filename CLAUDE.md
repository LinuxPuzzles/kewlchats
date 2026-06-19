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

- **Federation closed at launch, EXCEPT outbound s2s to push gateways.** Long-term ethos is
  open federation ("make all XMPP apps work, like all XMPP does"), but launch closed and
  open later: closed→open is non-breaking (users *gain* reach), open→closed breaks existing
  cross-server contacts. General federation stays off until abuse/spam controls exist.
  **The one exception is mandatory:** XEP-0357 mobile push is *delivered* by ejabberd connecting
  out to the apps' push app-servers (Monal: `eu/us.prod.push.monal-im.org`) — that's s2s. iOS (Monal) can't hold a
  background socket, so **no push = no notifications**, and you can't self-host the gateway (it
  needs the app vendor's APNs cert). So **s2s is enabled in BOTH directions but `s2s_access`-gated to a
  `push_servers` allowlist** (`s2s_push_domains` in group_vars) — Monal's appserver answers via a
  reverse/dialback connection, so the inbound `5269` listener (firewalled open, `mod_s2s_dialback` on)
  is required too; every non-allowlisted server is rejected at the stream layer, so general federation
  stays off. Symptom when this is wrong: Monal's tester is all-green (registration) but its debug Ping
  fails `STARTTLS is disabled / remote-server-timeout` (delivery). Conversations (Android) keeps its
  own persistent connection and needs none of this.
- **Message archive (MAM ON, full retention — no trim).** *Twice revised:* original stance was
  "MAM off / zero archive"; then "MAM on, trimmed to 7 days"; **now MAM on with no retention
  limit** (the daily `delete_old_mam_messages` timer is removed). The reasoning behind the trim
  was "minimize what we could be compelled to hand over." That was solving the wrong story. We
  don't sell, use, read, or care about the data — most of it is OMEMO ciphertext we can't read
  anyway; public-channel content is the main plaintext. The posture isn't "we keep nothing," it's
  **"the house doesn't monitor you, but it has windows"**: we aren't watching, but the messages
  exist and *do something stupid and there can be consequences*. So we keep the archive (better
  UX — real scrollback, multi-device sync, client testing) and stop pretending short retention is
  the privacy win. (Disk is a non-issue — text on a 480 GB SSD; revisit only if it ever grows large.)
- **HTTP File Upload (XEP-0363 — ON, but media is ephemeral).** `mod_http_upload` +
  `mod_http_upload_quota` (in `ejabberd.yml.j2`): without it clients (Monal/Conversations/web) can't
  share images/files at all. **Deliberately the *opposite* retention posture from MAM:** text history
  is kept forever (cheap), but **media auto-expires (~30 days)** because files are the heavy, sensitive
  part — the real storage *and* legal-exposure lever. Sane caps live in `group_vars`
  (`upload_max_size` 25 MiB/file, `upload_soft/hard_quota_mb` 200/250 MiB per user, `upload_max_days`
  30). Served on the loopback `:5280` listener (`/upload` handler) and exposed by nginx as
  `https://<domain>/upload` (its own `location` with `client_max_body_size` > max_size); `put_url`
  uses `@HOST@` so each vhost hands out its own URL. With OMEMO the file is encrypted client-side →
  we store ciphertext. When a file expires its link dies but the message stays in MAM. Docroot
  `ejabberd_upload_dir` (`/opt/ejabberd/upload`, created owned by `ejabberd`).
- **Smooth multi-device:** Carbons (`mod_carbons`) ON so simultaneous phone+laptop both see
  live messages; short-lived **offline spool** (`mod_offline`); MUC room history small
  (`history_size: 20`, room `mam: true`). With full MAM a device that was *off* syncs scrollback
  with no window cap.
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
- **Rooms carry history (was: "ephemeral-on-join").** *Obsolete since MAM went full-retention:* the
  old UX rule framed empty-on-join as a feature ("walking into a party, not a chat log") because
  there was no archive. Rooms now have scrollback (MAM + `history_size`), so **don't claim "no
  history" anywhere in the UI** — that's now false (it was on the chat page; removed). Keep the
  welcome warm but accurate; don't trumpet retention either (per the privacy posture, the storage is
  disclosed in the Privacy Policy, not advertised on the chat/landing pages).

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
- **MAM (full retention) is still light.** Text archive write load is tiny and the 480 GB SSD
  makes MySQL latency a non-issue; with no trim the `mam` table grows unbounded, but text at this
  scale won't trouble the disk for a very long time (revisit only if it ever gets large).
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
- **Prod deploy is now Ansible** (`deploy/ansible/`), not the Marathon portal/LXD model — a
  single dedicated Ubuntu 24.04 box runs Laravel (nginx + PHP 8.5-FPM) + ejabberd (MySQL, MAM)
  + MySQL + Redis. certbot+Bunny DNS-01 issues **wildcard** certs (covering `conference`/`pubsub`)
  shared by nginx and ejabberd; nginx proxies `wss://…/ws` to ejabberd's loopback HTTP listener
  (meets the secure-context requirement). The ejabberd prod config is generated from
  `roles/ejabberd/templates/ejabberd.yml.j2` (no IPv6, loopback API, SQL backend, MAM full-retention, TURN).
  See `deploy/ansible/README.md`. Still TODO: actually run it against the box and finalize DNS.
- **One install, multiple front doors (NOT a fork — one community forever).** The box hosts a
  `sites` list — currently `kewlchats.net` and `ready2.im` (the latter with
  `www`/`ready2im.{com,net,org}` aliases that 301 to it). It's **ONE Laravel install / ONE DB**
  that both domains point at via nginx — not two deploys. The browsed Host picks the brand/theme
  skin per request (`App\Support\SiteContext` + the `ResolveSite` middleware, registry in
  `config/sites.php`), while a user's home domain / JID suffix lives on their **`users.domain`**
  row (set at the door they signed up at, permanent). **Email = login identity** (one account);
  logging in at either door shows your real JID (e.g. `andy@kewlchats.net`) no matter which skin
  you're viewing — *theme = the door, data = the account*. One DB makes the localpart globally
  unique (`xmpp_username` unique index), so there's no duplicate `andy` and the signup race is
  structurally impossible. ejabberd is still a **virtual host per domain** on the node; local
  vhosts route to each other *internally* — cross-domain chat (`a@kewlchats.net` ↔ `b@ready2.im`)
  and shared MUC work with **`s2s` still off** (s2s is allowlisted only to the push gateways).
  - **One shared MUC, not per-vhost.** ejabberd's `mod_muc` is per-vhost and by default would
    auto-create `conference.<vhost>` for *every* site (so a `@kewlchats.net` client discovers
    `conference.kewlchats.net`). We want a single community room host (`xmpp_muc_domain` =
    `conference.ready2.im`), so in `ejabberd.yml.j2` `mod_muc`/`mod_muc_admin` are loaded via
    **`append_host_config`** on **`muc_vhost` (ready2.im) only**; every other vhost gets
    `mod_disco: extra_domains: [conference.ready2.im]` so its clients still discover the shared
    service. Cross-vhost joins route internally (no s2s). Symptom if this regresses: a native client
    (e.g. Monal) lists `conference.<its-own-domain>` instead of `conference.ready2.im`.
    - **`append_host_config`, never `host_config`, for per-vhost modules.** `host_config` *replaces*
      a vhost's entire `modules:` list — using it here silently dropped `mod_admin_extra`
      (`check_account`), `mod_mam`, `mod_roster`, `mod_register`, etc. on both vhosts, which surfaced
      as the ReST API returning `404 {"code":40,"Endpoint not found"}` on `check_account` during
      signup (and would have broken archiving/rosters too). `append_host_config` *merges* onto the
      global modules. A 404 "Endpoint not found" from `mod_http_api` = command/module not loaded
      (NOT auth — auth failures are 401/403, and loopback grants node-admin regardless of token).
- **Per-door theming (Host-resolved).** The `ResolveSite` middleware reads the Host, looks the door
  up in `config/sites.php`, and (via `SiteContext::applyWithTheme`) prepends
  `resources/views/themes/{theme}` to the view finder **and** sets `app.name`/mail config for the
  request, so any view under the theme dir overrides the base (and falls back to base when absent).
  (Hosts not in the registry — dev's `kewlchats.corp`, test hosts — fall back to the `.env` values.)
  **Base = the default/canonical look (KewlChats);
  `themes/` = divergent skins.** KewlChats deliberately has *no* theme files (empty
  `themes/kewlchats/`, kept only as a hook) — it renders entirely from base, so there is nothing
  to "move." **ready2.im** is fully themed (early-2000s IM "windows" look): landing, all six auth
  pages, dashboard, chat, profile, legal (`<x-page>`), and admin — plus its own Tailwind bundle
  `resources/css/themes/ready2im.css` (added to `vite.config.js`, built by `npm run build`).
  Theme overrides must keep the functional contract: themed layouts re-`@vite` the theme CSS +
  `app.js`, the guest layout keeps `@unbotableJs`, auth forms keep the `@unbotable*` directives,
  and chat keeps `<x-converse-chat/>`. Form-component overrides keep the **exact same `@props`**
  and merge retro defaults via `$attributes->merge`, so call-sites are unchanged. See
  `resources/views/themes/README.md`.
- **Email is themed differently — it can't ride `SITE_THEME`.** Markdown mail resolves via the
  `mail::` namespace (hint paths), not the global view-finder prepend, so theme overrides under
  `themes/` do **not** reach mail. Instead: the shared `vendor/mail/html/header.blade.php` +
  `footer.blade.php` are **brand-aware** (`config('app.name')`), and the accent comes from a
  per-domain **mail CSS theme** (`config('mail.markdown.theme')`; CSS at
  `resources/views/vendor/mail/html/themes/<name>.css`). Since a queue worker has no Host, the
  `VerifyEmail`/`ResetPassword` `toMailUsing` closures in `AppServiceProvider` call
  `SiteContext::apply($notifiable->domain)` — activating the **recipient's** door (brand, From,
  mail theme) from their row, so mail is correctly branded no matter which door triggered the send.
- **Voice/video:** the Ansible `ejabberd.yml.j2` already includes the `ejabberd_stun` listener
  (UDP 5478) + `mod_stun_disco` with `use_turn: true`, the public `turn_ipv4_address`, and the
  relay port range (firewalled open). No coturn. Plus real MUC directory / presence polish.
