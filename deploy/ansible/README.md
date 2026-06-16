# KewlChats — Ansible deployment

Provisions a **single dedicated Ubuntu 24.04 box** running the whole stack:
Laravel (nginx + PHP 8.5-FPM) · ejabberd (MySQL backend, MAM) · MySQL · Redis,
with Let's Encrypt managed by us (wildcard certs shared by nginx and ejabberd).

The playbook is **idempotent** — run it as often as you like to converge the box
back to the desired state.

> This replaces the old LXD / Marathon-portal model. Everything lives on one box,
> so ejabberd's ReST API binds loopback and Laravel reaches it at
> `http://127.0.0.1:5280/api`.

## Two sites, one codebase, one ejabberd

The same Laravel code is deployed **once per site** (see the `sites` list in
`group_vars/all/main.yml`) — separate checkout, DB, `.env`, queue worker and nginx
vhost each:

| Site | Canonical | Aliases (301 → canonical) | XMPP vhost |
|---|---|---|---|
| `kewlchats` | `kewlchats.net` | `www.kewlchats.net` | `kewlchats.net` |
| `ready2im` | `ready2.im` | `www.ready2.im`, `ready2im.{com,net,org}` (+`www.`) | `ready2.im` |

Each site's domain is also an **ejabberd virtual host** on the *same* node, each
with its own admin (`admin@<domain>`) and API token. The two vhosts **route to each
other internally** — `alice@kewlchats.net` and `bob@ready2.im` can chat (and share
MUC rooms) **without `s2s`/federation enabled**; `s2s` is only for *other* servers.
They are intentionally *not* isolated from one another.

To add or rename a site later, edit `sites` (+ `certificates`) and re-run — the app,
worker, nginx and ejabberd roles all loop over the list.

## What you provide before the first run

1. **Vars** — copy the example and fill it in (gitignored):
   ```bash
   cp provided.example.yml group_vars/all/provided.yml
   $EDITOR group_vars/all/provided.yml      # bunny_api_key, letsencrypt_email,
                                            # postmark_token, git_repo_url, ...
   ```
2. **Deploy key** — drop the repo's read-only deploy private key in place, and add
   its public half as a deploy key on the git host:
   ```bash
   cp /path/to/id_ed25519 files/deploy_key
   chmod 600 files/deploy_key
   ```
3. **Inventory** — set your server's address in `inventory.ini`.

Everything else (APP_KEY, DB/Redis passwords, the ejabberd admin password, and the
ejabberd API token) is **generated on the box** on the first run and persisted
under `/root/.kewlchats/`. The ejabberd admin password is also mirrored to
`/root/kewlchats-ejabberd-admin.txt`.

### DNS & mail

Point all canonical + alias domains at the box (A records): `kewlchats.net`,
`ready2.im`, and the `ready2im.{com,net,org}` apexes (+ their `www`). `conference.`
and `pubsub.` are covered by the per-domain wildcard certs and don't need public DNS
while federation is off. Certs are issued via **Bunny DNS-01** — one Bunny account
API key covers every zone in the account, so no inbound HTTP is needed.

For verification/reset email, each site's from-domain (`kewlchats.net`, `ready2.im`)
must be a **verified sender domain in Postmark** (the one server token sends for all
verified domains).

## Run it

```bash
ansible-galaxy collection install -r requirements.yml   # one-time
ansible-playbook site.yml --check                        # dry run
ansible-playbook site.yml                                # converge
```

Useful tags:

```bash
ansible-playbook site.yml --tags app        # redeploy just the Laravel app
ansible-playbook site.yml --tags nginx
ansible-playbook site.yml --tags ejabberd
ansible-playbook site.yml --tags tls        # certbot
```

## Role order (and why)

`common → tailscale → firewall → secrets → mysql → redis → php → nodejs → certbot
→ ejabberd → app → queue_worker → nginx → monit`

- **tailscale** runs before **firewall** so the `tailscale0` interface exists when
  the firewall trusts it (skipped unless `tailscale_authkey` is set).
- **secrets** runs before MySQL/Redis (they need the generated passwords).
- **certbot** uses DNS-01 so it needs no web server — certs exist before the
  services that consume them.
- **ejabberd** runs before **app** because it mints the XMPP API token the app's
  `.env` needs.

## Re-running, idempotency & drift

Ansible is stateless about history — it converges *declared* state and has no memory
of what it did last time. So the playbook is built around three categories:

1. **Declared, re-asserted every run** — config files (nginx/ejabberd/php/.env),
   packages, systemd units, ufw rules. Re-running reverts hand-edits back to the
   templated truth. A `--check --diff` run shows exactly what drifted **without
   changing anything** — this is your drift detector:
   ```bash
   ansible-playbook site.yml --check --diff
   ```
2. **Generate-once, deliberately NOT redone** (guarded by `creates`/`when`), because
   redoing them would be destructive:
   - **Secrets** (`/root/.kewlchats/*`) — regenerating would rotate passwords out
     from under MySQL/ejabberd. These files are the **source of truth**; back them
     up, don't hand-delete them (deleting one mid-stream can desync a service).
   - **Let's Encrypt certs** — re-issuing every run would burn LE rate limits;
     renewal is the systemd timer's job, not the playbook's.
   - **ejabberd admin accounts + API tokens, the SQL schema import** — minted/loaded
     once; "already registered" is treated as success.
3. **Undeclared — Ansible can't see it.** A user banned directly in ejabberd's web
   admin, a room created by hand, MAM rows: the playbook has no opinion on these.
   That's **XMPP control-plane drift**, and the answer is the `kewlchats:reconcile`
   timer (Laravel is authoritative and re-drives ban/unban state daily).

A clean second run should report **~0 changed** (build steps are gated on the git
checkout actually changing, or a missing build artifact).

## Tailscale (optional gate)

Set `tailscale_authkey` in `provided.yml` and the box joins your tailnet, after
which **SSH and the monit web UI are reachable only over the tailnet** — public
port 22 is closed and the firewall trusts the `tailscale0` interface. Without a key,
the role is skipped and SSH stays on its public/CIDR rules.

> First run from the box's public IP (it's not on the tailnet yet). That run brings
> Tailscale up and closes public SSH, so for every run after, point `inventory.ini`
> at the box's Tailscale hostname/IP. Use a **reusable** auth key if you want
> unattended re-runs.

## Security shape

- ufw default-deny inbound; only `80, 443, 5222/tcp, 5478/udp` and the TURN relay
  UDP range are public. **SSH is tailnet-only when Tailscale is enabled** (else
  `22/tcp`, optionally CIDR-restricted). The ejabberd API (5280), MySQL (3306) and
  Redis (6379) bind to **loopback**; the monit UI (2812) is tailnet-only.
- TLS 1.2/1.3, HSTS, security headers, dotfile/secret-extension denies in nginx.
- PHP hardened (`disable_functions`, `expose_php=Off`, OPcache); fail2ban on SSH +
  nginx auth; SSH key-only.

## Monitoring

**monit** (alert-only — systemd owns restarts) emails `monit_alert_email` via Postmark
when something's wrong: core services down (nginx/php-fpm/mysql/redis/ejabberd), a
per-site queue worker down, a local listener not answering (5222/5280/3306/6379),
public HTTPS unreachable, a TLS cert within 10 days of expiry, or disk/load/memory
over threshold. Set `monit_alert_email` in `provided.yml`; the from-address
(`monit@kewlchats.net`) must be a verified Postmark sender. Check state on the box
with `monit status`.

## Post-run checks

See the "Verification" section of the implementation plan, e.g.:

```bash
systemctl status ejabberd nginx php8.5-fpm redis-server mysql \
  kewlchats-worker ready2im-worker
echo | openssl s_client -connect ready2.im:443 -servername conference.ready2.im 2>/dev/null \
  | openssl x509 -noout -text | grep -A1 'Subject Alternative Name'
# Cross-domain chat works without s2s — verify both vhosts are up:
ejabberdctl registered_vhosts            # -> kewlchats.net, ready2.im
```
