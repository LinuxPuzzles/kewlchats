# ejabberd — runbook

The XMPP server behind KewlChats. This `ejabberd.yml` is the **dev source of truth**
(symlinked to the live config path on the Mac mini). It encodes the locked decisions
(federation off, carbons on, short offline spool, in-band registration denied, small MUC
history). NOTE: dev still has `mod_mam` *off*; **prod runs MAM with full retention (no trim)** — see below.

- **Dev (Mac mini):** Homebrew, Mnesia store, this file.
- **Prod:** provisioned by **Ansible** (`deploy/ansible/`), not by hand and not via the old
  Marathon/LXD model. A single Ubuntu 24.04 box; the prod ejabberd config is *generated* from
  `deploy/ansible/roles/ejabberd/templates/ejabberd.yml.j2` (Process-One package, MySQL store,
  Let's Encrypt wildcard cert, no IPv6, loopback-only ReST API, full-retention MAM, built-in STUN/TURN).
  Don't hand-edit prod config — change the template. (The retired `PROD-LXD.md` has been removed.)

## Dev (Homebrew) layout

| Thing | Path |
|---|---|
| `ejabberdctl` | `/opt/homebrew/opt/ejabberd/sbin/ejabberdctl` |
| Config (symlink → this repo) | `/opt/homebrew/etc/ejabberd/ejabberd.yml` |
| `HOME` / spool (Mnesia) | `/opt/homebrew/var/lib/ejabberd` |
| Logs | `/opt/homebrew/var/log/ejabberd/` |

**`ejabberdctl` needs `HOME` set** to the spool dir (brew caveat), or it can't find the node:

```sh
export HOME=/opt/homebrew/var/lib/ejabberd
EJCTL=/opt/homebrew/opt/ejabberd/sbin/ejabberdctl
```

## First-time setup

```sh
brew install ejabberd

# symlink our config in (back up the brew default first)
ETC=/opt/homebrew/etc/ejabberd
cp "$ETC/ejabberd.yml" "$ETC/ejabberd.yml.brew-default"
ln -sf "$PWD/ops/ejabberd/ejabberd.yml" "$ETC/ejabberd.yml"

export HOME=/opt/homebrew/var/lib/ejabberd
EJCTL=/opt/homebrew/opt/ejabberd/sbin/ejabberdctl

$EJCTL start && $EJCTL started

# admin account (also the ReST API identity) — dev vhost is kewlchats.corp
$EJCTL register admin kewlchats.corp '<admin-password>'

# mint a long-lived admin ReST token (scope ejabberd:admin) -> .env XMPP_API_TOKEN
$EJCTL oauth_issue_token admin@kewlchats.corp 31536000 ejabberd:admin
```

Then set in the app `.env`:

```
XMPP_DRIVER=ejabberd
XMPP_DOMAIN=kewlchats.corp
XMPP_MUC_DOMAIN=conference.kewlchats.corp
XMPP_API_BASE=http://127.0.0.1:5280/api        # loopback, server-side
XMPP_API_TOKEN=<token from oauth_issue_token>
XMPP_WEBSOCKET_URL=ws://kewlchats.corp:5280/ws  # client-facing (resolves over Tailscale)
```

## Daily ops

```sh
$EJCTL start | stop | restart | status
$EJCTL registered_users kewlchats.corp
$EJCTL connected_users
tail -f /opt/homebrew/var/log/ejabberd/ejabberd.log
```

## Dev reset (the "nuke", in place of `docker down -v`)

```sh
$EJCTL stop
rm -rf /opt/homebrew/var/lib/ejabberd/Mnesia.*    # wipe all accounts/rooms
$EJCTL start && $EJCTL started
$EJCTL register admin kewlchats.corp '<admin-password>'  # re-seed admin + token
```

After a reset, also re-mint the token + reseed the app user:
`$EJCTL oauth_issue_token admin@kewlchats.corp 31536000 ejabberd:admin` → `.env`, then
`php artisan db:seed --class=DevUserSeeder`.

## DNS for native clients (dev, `.corp`)

XMPP apps look up `_xmpp-client._tcp.<domain>` (SRV) **before** connecting. Herd's dnsmasq
(`~/Library/Application Support/Herd/config/dnsmasq/dnsmasq.conf`) only synthesised **A**
records for `.corp`, so SRV queries forwarded upstream and **timed out**, hanging the apps.
Fix (added to that dnsmasq.conf — re-add if Herd regenerates it, then `herd restart`):

```
local=/corp/                                                      # authoritative for .corp; no upstream forward
srv-host=_xmpp-client._tcp.kewlchats.corp,kewlchats.corp,5222,0,5 # XMPP STARTTLS endpoint
```

dnsmasq already listens on the Tailscale IP, so this resolves for every tailnet device.
Result: Conversations/Monal connect to `name@kewlchats.corp` with **no host override** (just
accept the self-signed cert once). Prod (`kewlchats.net`) gets these SRV records in real DNS.

## Prod: DNS, ports & the wss proxy (`kewlchats.net`)

**DNS records.** One host serves web + XMPP + wss (same public IP).

| Record | Value | Why |
|---|---|---|
| `kewlchats.net` **A** (+ AAAA) | public IP | web, XMPP host, `wss` endpoint — all the same box |
| `_xmpp-client._tcp.kewlchats.net` **SRV** | `0 5 5222 kewlchats.net.` | native apps (Conversations/Monal) connect with no host override |
| `_xmpps-client._tcp.kewlchats.net` **SRV** | `0 5 5223 kewlchats.net.` | *optional* — only if you add a direct-TLS c2s port (XEP-0368) |
| `kewlchats.net` **CAA** | `0 issue "letsencrypt.org"` | *optional* — restrict cert issuance |

- **NO `_xmpp-server._tcp` (s2s) record** — federation is off at launch. Add it only when you enable `s2s`.
- **NO TURN/STUN DNS record** — ejabberd advertises STUN/TURN to clients over the stream via
  `mod_stun_disco` (XEP-0215). Just set the public `turn_ipv4_address` and open the UDP ports.
- **MUC/pubsub subdomains** (`conference.`, `pubsub.`) need **no public DNS** while federation is
  off — they're internal components resolved by the server, not DNS.
- **Email** (verification/reset mail + the `abuse@`/`support@`/`privacy@` addresses the legal pages
  cite): **SPF + DKIM + DMARC** (TXT, per your mail provider) for *sending*, and an **MX** record +
  mailbox/forwarding for *receiving* at those addresses.

**Firewall / ports.** TCP **443** (web + wss + ACME), **5222** (c2s STARTTLS); UDP **5478** +
**49152–65535** (STUN/TURN relay). Not 5269 (s2s, off).

**wss reverse proxy** (browser needs `wss://` from an HTTPS page; terminate TLS at nginx, proxy
to ejabberd's plain loopback ws):

```nginx
# wss://kewlchats.net/ws  ->  ejabberd plain ws on 127.0.0.1:5280
location /ws {
    proxy_pass http://127.0.0.1:5280/ws;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_read_timeout 3600s;   # keep idle XMPP sockets alive
}
```

Then in prod `.env`: `XMPP_WEBSOCKET_URL=wss://kewlchats.net/ws` (and the site on HTTPS, which also
satisfies Converse's `crypto.subtle` secure-context requirement).

### TLS certificate (reuse the website's — don't issue a second)

Thanks to the wss proxy, ejabberd's **only** TLS consumer is **c2s on 5222** (native apps doing
STARTTLS). The browser/wss path is terminated by nginx with the website cert, so ejabberd needs no
cert for web chat; TURN needs none either (UDP relay; WebRTC media is DTLS-SRTP end-to-end).

c2s serves `kewlchats.net` — the same hostname as the website — so it uses the **same Let's Encrypt
cert Marathon already issues**. Point ejabberd at those files:

```yaml
certfiles:
  - /path/to/kewlchats.net/fullchain.pem
  - /path/to/kewlchats.net/privkey.pem
```

Two operational bits:
- **Read access:** ejabberd runs as its own user; it must be able to *read* the cert + key (group
  perms, or copy into an ejabberd-readable dir on deploy).
- **Reload on renewal:** ejabberd caches certs in memory. After LE renews (~60d) run
  `ejabberdctl reload_config` so it picks up the new cert — wire it into Marathon's renewal hook,
  next to wherever it reloads nginx.

**Do not** use ejabberd's built-in ACME (`/.well-known/acme-challenge: ejabberd_acme`) here —
Marathon already owns ACME for `kewlchats.net`, and two issuers contesting the same domain's HTTP-01
challenge will collide. Reuse the cert.

## Notes / gotchas

- **API auth uses the bearer token, not loopback.** The HTTP listener binds `::`, so IPv4
  loopback arrives as `::ffff:127.0.0.1` and does **not** match the `loopback` ACL — the
  token (scope `ejabberd:admin`, matching `acl admin`) is what authorises ReST calls.
- **`register` idempotency:** ejabberd returns HTTP 409 / code 10090 "already registered";
  the provisioner treats that as success (jobs retry).
- **Featured rooms** are a curated list in `config/xmpp.php`; they materialise on first
  join, so idle rooms show 0 occupants. Empty non-persistent rooms are GC'd — that's fine.
- **Phone / native-app testing (Tailscale):** dev vhost `kewlchats.corp` resolves across the
  tailnet, and ejabberd binds `::`, so Conversations/Monal on a Tailscale device connect to
  `name@kewlchats.corp` with no host override. The self-signed cert needs a one-time accept/pin.
- **Prod delta:** set `hosts` + `acl admin` to `kewlchats.net`, fill `certfiles` (Let's
  Encrypt), configure an SQL/MySQL backend (`default_db: sql` + `sql_*`), and front the
  HTTP/WebSocket with TLS.
