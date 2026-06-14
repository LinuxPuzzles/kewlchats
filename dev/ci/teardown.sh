#!/bin/bash
# Tear down all resources for a CI run.
# Runs unconditionally (if: always()) so partial failures don't leave orphans.
set -uo pipefail

RUN_ID="${RUN_ID:-${GITHUB_RUN_ID:-}}"

if [ -z "${RUN_ID}" ]; then
    echo "WARNING: RUN_ID not set, cannot identify resources to clean up" >&2
    exit 0
fi

APP_NAME="kewlchats-${RUN_ID}"
HERD_HOME="${HOME}/Herd"
DB_FILE="/tmp/kewlchats-ci-${RUN_ID}.sqlite"

echo "==> [CI] Teardown for run: ${RUN_ID}"

# ── Herd symlink ──────────────────────────────────────────────────────────────
if [ -L "${HERD_HOME}/${APP_NAME}" ]; then
    rm -f "${HERD_HOME}/${APP_NAME}"
    echo "    Removed Herd symlink: ${APP_NAME}"
fi

# ── SQLite database ───────────────────────────────────────────────────────────
if [ -f "${DB_FILE}" ]; then
    rm -f "${DB_FILE}"
    echo "    Removed database: ${DB_FILE}"
fi

echo "==> Teardown complete"
