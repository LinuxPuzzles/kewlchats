#!/bin/bash
# Stand up a test KewlChats instance for CI.
# Creates a Herd symlink → .corp vhost, writes a per-run .env, installs deps,
# builds assets (incl. the self-hosted Converse.js bundle), migrates the DB, and
# exports env vars for subsequent steps.
#
# The test suite forces XMPP_DRIVER=mock + an in-memory SQLite DB via phpunit.xml,
# so `php artisan test` is self-contained — it does not touch a real ejabberd. The
# standing .corp instance is a bonus for interactive/integration checks; we keep the
# driver on mock here too so nothing in setup reaches for a server that isn't there.
set -euo pipefail

RUN_ID="${RUN_ID:-${GITHUB_RUN_ID:-$(date +%s)}}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WORKSPACE="${GITHUB_WORKSPACE:-$(cd "${SCRIPT_DIR}/../.." && pwd)}"
HERD_HOME="${HOME}/Herd"
APP_NAME="kewlchats-${RUN_ID}"
APP_URL="http://${APP_NAME}.corp"
DB_FILE="/tmp/kewlchats-ci-${RUN_ID}.sqlite"

echo "==> [CI] Setting up KewlChats: ${APP_NAME}"
echo "    Workspace: ${WORKSPACE}"
echo "    App URL:   ${APP_URL}"
echo "    Database:  ${DB_FILE}"

# ── Herd vhost (symlink → instant .corp domain) ─────────────────────────────
ln -sf "${WORKSPACE}" "${HERD_HOME}/${APP_NAME}"
echo "    Herd symlink created: ${HERD_HOME}/${APP_NAME}"

# ── SQLite database file ──────────────────────────────────────────────────────
touch "${DB_FILE}"

# ── .env ─────────────────────────────────────────────────────────────────────
cat > "${WORKSPACE}/.env" <<EOF
APP_NAME="KewlChats [CI ${RUN_ID}]"
APP_ENV=testing
APP_KEY=
APP_DEBUG=true
APP_URL=${APP_URL}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

BCRYPT_ROUNDS=4

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=${DB_FILE}

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=sync

# XMPP: mock in CI — the suite forces it anyway, and there's no ejabberd here.
XMPP_DRIVER=mock
XMPP_DOMAIN=kewlchats.corp
XMPP_MUC_DOMAIN=conference.kewlchats.corp

# Bot protection: observe-only so tokenless test POSTs to auth routes never block.
UNBOTABLE_URL=https://unbotable.com
UNBOTABLE_ON_BLOCK=log_only

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@kewlchats.net"
MAIL_FROM_NAME="KewlChats"

VITE_APP_NAME="KewlChats"
EOF

# ── Dependencies ──────────────────────────────────────────────────────────────
echo "==> Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --quiet

echo "==> Installing Node dependencies..."
npm ci --silent

echo "==> Building frontend assets (publishes Converse.js, then vite build)..."
npm run build

# ── Laravel setup ─────────────────────────────────────────────────────────────
php artisan key:generate --quiet
php artisan migrate --force --quiet
echo "    Database migrated"

# ── Export for subsequent steps ───────────────────────────────────────────────
{
    echo "APP_NAME=${APP_NAME}"
    echo "APP_URL=${APP_URL}"
    echo "DB_FILE=${DB_FILE}"
    echo "RUN_ID=${RUN_ID}"
} >> "${GITHUB_ENV}"

echo "==> KewlChats ready at ${APP_URL}"
