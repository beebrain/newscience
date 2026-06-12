#!/usr/bin/env bash
# git pull บน win-kc (production) ผ่าน Tailscale SSH
#
# Usage:
#   FTP_PASS='Admin@SCI@URU' ./scripts/git-pull-win-kc.sh
#   # หรือ export SSHPASS แทน FTP_PASS
#
set -euo pipefail

HOST="${WIN_KC_HOST:-100.74.66.65}"
USER="${WIN_KC_USER:-Administrator}"
REPO="${WIN_KC_REPO:-C:/inetpub/newscience}"
PASS="${SSHPASS:-${FTP_PASS:-${WIN_KC_PASS:-}}}"

if ! command -v tailscale >/dev/null 2>&1; then
  echo "ไม่พบ tailscale CLI" >&2
  exit 1
fi

if ! tailscale status 2>/dev/null | grep -q '100.74.66.65'; then
  echo "win-kc (100.74.66.65) ไม่อยู่ใน tailnet — เปิด Tailscale ก่อน" >&2
  exit 1
fi

mkdir -p ~/.ssh
ssh-keyscan -t ed25519,rsa -H "$HOST" 2>/dev/null >> ~/.ssh/known_hosts || true

SSH_OPTS=(
  -F /dev/null
  -o StrictHostKeyChecking=accept-new
  -o UserKnownHostsFile="${HOME}/.ssh/known_hosts"
  -o ProxyCommand="tailscale nc %h 22"
  -o ConnectTimeout=25
)

SSH_CMD=()
if [[ -n "$PASS" ]]; then
  export SSHPASS="$PASS"
  SSH_CMD=(sshpass -e ssh "${SSH_OPTS[@]}" -o PubkeyAuthentication=no -o PreferredAuthentications=password,keyboard-interactive)
else
  SSH_CMD=(ssh "${SSH_OPTS[@]}")
fi

REMOTE_CMD="cd /d ${REPO//\//\\\\} && git rev-parse --short HEAD && git pull origin master && git rev-parse --short HEAD && git log -1 --oneline"

echo "=== git pull บน ${USER}@${HOST} (${REPO}) ผ่าน tailscale nc ==="
"${SSH_CMD[@]}" "${USER}@${HOST}" "${REMOTE_CMD}"

echo "=== cache + migrate (ถ้ามี php) ==="
"${SSH_CMD[@]}" "${USER}@${HOST}" "cd /d ${REPO//\//\\\\} && (php spark cache:clear 2>nul || echo skip-cache) && (php spark migrate 2>nul || echo skip-migrate) && (php scripts/run_add_barcode_events_join_code.php 2>nul || echo skip-join-code)" || true

echo "=== done ==="
