# Prod runbook — KewlChats (LXD) ↔ ejabberd (host)

How to wire the KewlChats Laravel app to ejabberd in the Marathon-style production
layout, where **the Laravel app runs inside an LXD container** and **ejabberd runs on the
host** (not containerised). The portal provisions the box but does **not** manage this app
after deploy, so the steps below are deliberately manual.

> Companion to `README.md` (the ejabberd config/runbook itself). This file is only the
> container↔host networking + firewall layer.

## The model (same one Marathon uses for MariaDB/Redis)

A container never reaches a host service on `127.0.0.1` — that's the container's own
loopback. It reaches the host on the **lxdbr0 gateway IP** (the host's address on the
bridge, e.g. `10.42.0.1` / `10.215.21.1`; it varies per host). Two things must hold, and
they mirror `node/redis-manual.txt` exactly:

1. **The host service listens on the bridge IP.** ejabberd already does — every listener
   in `ejabberd.yml` is `ip: "::"` (all interfaces), so **no bind change is needed** (unlike
   Redis/MariaDB, which default to loopback and had to be re-bound).
2. **UFW allows the bridge interface in.** The Marathon bootstrap sets
   `ufw default deny incoming`, so bridge→host traffic is dropped unless explicitly allowed.

Inside the container, the gateway is just the default route:

```sh
GATEWAY=$(ip route | awk '/default/{print $3}')   # e.g. 10.42.0.1
```

On the host you can read it the way the node code does:

```sh
lxc network get lxdbr0 ipv4.address   # e.g. 10.42.0.1/24  → gateway is 10.42.0.1
```

---

## 1. Container `.env` — point the API at the gateway, not loopback

The only app-level change. Default is `XMPP_API_BASE=http://127.0.0.1:5280/api`; in the
container that loopback is the container itself. Repoint at the host bridge gateway:

```dotenv
# {gateway} = host lxdbr0 address, e.g. 10.42.0.1
XMPP_API_BASE=http://{gateway}:5280/api
XMPP_API_TOKEN=<prod admin bearer token>
XMPP_DRIVER=ejabberd
XMPP_DOMAIN=kewlchats.net
XMPP_MUC_DOMAIN=conference.kewlchats.net
```

Use **plain HTTP on 5280 over the bridge**, not 5443. Bridge traffic never leaves the box,
so TLS buys nothing and self-signed-cert validation only adds pain. This matches the
existing "the API boundary is the firewall, not TLS" intent (5443's TLS is for flows that
cross a real network; the bridge isn't one).

Don't hardcode the gateway if you can avoid it — compute it at deploy time
(`lxc network get lxdbr0 ipv4.address` on the host, or `ip route` inside the container) and
write it into the container `.env`.

## 2. Web-chat WebSocket proxy (separate path — don't confuse with the API)

`XMPP_WEBSOCKET_URL=wss://kewlchats.net/ws` is **client-facing** (the browser hits it), so it
stays a public `wss://` URL terminated by the container's web server on 443 — *not* the
bridge. But the reverse-proxy block that terminates that `wss` / `/bosh` must forward to
ejabberd on the **host**, so its upstream is `http://{gateway}:5280/ws`, **not**
`127.0.0.1:5280`. Same loopback→gateway gotcha, different layer (web-server config inside
the container).

---

## 3. Host UFW — let the bridge reach the API

Interface-scoped (only the bridge, never public), mirroring the Redis runbook:

```sh
ufw allow in on lxdbr0 to any port 5280 proto tcp   # ReST API + /ws upstream
ufw reload
ufw status | grep 5280
```

## 4. Host UFW — public ports ejabberd needs open to the internet

These are real client/relay flows hitting ejabberd on the host's **public IP** (native
clients, browsers, TURN), not bridge traffic:

| Port | Proto | Why | Action |
|---|---|---|---|
| **5222** | tcp | c2s — Conversations/Monal (STARTTLS) | **open** |
| **443** | tcp | web chat `wss /ws` + BOSH, proxied by the web server | already open |
| **80** | tcp | ACME / LE renewal | already open |
| **5478** | udp | built-in STUN/TURN (`ejabberd_stun`) | **open** |
| **49152–65535** | udp | TURN relay media range (`turn_min_port`/`max_port`) | **open** |
| 5269 | tcp | s2s federation | **keep CLOSED** (federation off by design) |
| 5280 / 5443 | tcp | ReST API + web admin | **keep CLOSED to internet** — bridge/Tailscale only |

```sh
ufw allow 5222/tcp
ufw allow 5478/udp
ufw allow 49152:65535/udp
ufw reload
```

5223/tcp (direct-TLS c2s, XEP-0368) is optional and nicer on flaky mobile networks, but
it's not in the listener config — skip it unless you add the listener.

---

## 5. ejabberd.yml prod deltas (pair these with the firewall changes)

- **`turn_ipv4_address`** is the Tailscale IP `100.92.49.99` in dev → set to the server's
  **public IPv4**, or relayed media is advertised at an unroutable address and TURN calls fail.
- **`certfiles`** — uncomment and point at the website's Let's Encrypt cert (5222 STARTTLS);
  `ejabberdctl reload_config` from the LE renewal hook.
- **`hosts:` / `acl admin`** → `kewlchats.net` (the documented `.corp`→`.net` delta).
- SQL backend (MySQL) instead of Mnesia — see `README.md`.

---

## Security note (don't gloss over)

Because the API/admin listeners are `::`, **5280 and 5443 are physically listening on the
public NIC** — UFW's `default deny incoming` is the only thing keeping the bearer-token API
and web admin off the internet. Same posture as MariaDB/Redis in Marathon, so it's
consistent, but:

- Never add a blanket `ufw allow 5280` — keep it interface-scoped to `lxdbr0` (step 3).
- Never open 5269 (federation), 5280, or 5443 to the public.
- **Defence-in-depth option:** pin the 5280 listener's `ip:` to the bridge gateway address
  in `ejabberd.yml`. That removes it from the public NIC entirely — at the cost of host
  loopback `ejabberdctl`/curl debugging against the API. The rest of the fleet leans on UFW
  instead; pick one and document it.

---

## Verify from inside the container

```sh
GATEWAY=$(ip route | awk '/default/{print $3}')
curl -s -H "Authorization: Bearer $XMPP_API_TOKEN" \
  http://$GATEWAY:5280/api/status
# expect ejabberd status JSON, not "connection refused"
```

Refused → either the UFW `lxdbr0` rule is missing (step 3) or ejabberd isn't bound on the
bridge IP (it should be, via `::`). Then sanity-check a public path from off-box:

```sh
nc -vz kewlchats.net 5222     # c2s reachable
nc -vzu kewlchats.net 5478    # STUN/TURN reachable (udp)
```

## What actually changed vs dev

- **App:** `XMPP_API_BASE` (and the web server's `/ws` upstream) move from loopback to the
  bridge gateway. That's it on the app side.
- **Host:** one bridge UFW rule for the API (5280), three public UFW rules for c2s + STUN/TURN
  (5222, 5478/udp, 49152–65535/udp), plus the `turn_ipv4_address` / cert / hostname prod edits.
- ejabberd's `::` binding means there is **no re-bind step** — the thing that made Redis need a
  manual runbook doesn't apply here.
