#!/usr/bin/env bash
#
# Sync ข้อมูลจาก Server ลง Local (newScience)
#
# ตั้งค่าใน .env:
#   database.server.*  — MySQL บน server (ต้นทาง)
#   database.default.*   — MySQL local (ปลายทาง)
#
# ตัวเลือกไฟล์ (ไม่บังคับ):
#   sync.server.ssh              — เช่น user@49.231.30.18
#   sync.server.remote_uploads   — path บน server เช่น /var/www/newscience/public/uploads/
#   sync.local.uploads           — path local (default: public/uploads)
#
# ตัวอย่าง:
#   ./scripts/sync_server_to_local.sh
#   ./scripts/sync_server_to_local.sh --yes
#   ./scripts/sync_server_to_local.sh --method=php
#   ./scripts/sync_server_to_local.sh --tables=news,personnel,user
#   ./scripts/sync_server_to_local.sh --db-only
#   ./scripts/sync_server_to_local.sh --files-only
#   ./scripts/sync_server_to_local.sh --dump-only
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

METHOD="auto"
DB_ONLY=false
FILES_ONLY=false
YES=false
DUMP_ONLY=false
TABLES=""
EXTRA_PHP_ARGS=()

usage() {
  sed -n '2,22p' "$0" | sed 's/^# \{0,1\}//'
  exit "${1:-0}"
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    -h|--help) usage 0 ;;
    -y|--yes) YES=true; shift ;;
    --method=*) METHOD="${1#*=}"; shift ;;
    --method) METHOD="$2"; shift 2 ;;
    --db-only) DB_ONLY=true; shift ;;
    --files-only) FILES_ONLY=true; shift ;;
    --dump-only) DUMP_ONLY=true; shift ;;
    --tables=*) TABLES="${1#*=}"; shift ;;
    --tables) TABLES="$2"; shift 2 ;;
    --*) echo "Unknown option: $1" >&2; usage 1 ;;
    *) echo "Unknown argument: $1" >&2; usage 1 ;;
  esac
done

if [[ -n "$TABLES" ]]; then
  EXTRA_PHP_ARGS+=(--tables="$TABLES")
fi

if [[ ! -f .env ]]; then
  echo "Error: .env not found at $ROOT/.env" >&2
  echo "Copy env.stack.example → .env and set database.server.* and database.default.*" >&2
  exit 1
fi

read_env() {
  local key="$1"
  local line val
  line="$(grep -E "^[[:space:]]*${key}[[:space:]]*=" .env 2>/dev/null | tail -1 || true)"
  if [[ -z "$line" ]]; then
    echo ""
    return 0
  fi
  val="${line#*=}"
  val="$(echo "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^["'\'']//' -e 's/["'\'']$//')"
  echo "$val"
}

require_db_env() {
  local missing=0
  for k in hostname database username; do
    local v
    v="$(read_env "database.server.${k}")"
    if [[ -z "$v" ]]; then
      echo "Error: database.server.${k} is not set in .env" >&2
      missing=1
    fi
    v="$(read_env "database.default.${k}")"
    if [[ -z "$v" ]]; then
      echo "Error: database.default.${k} is not set in .env" >&2
      missing=1
    fi
  done
  [[ "$missing" -eq 0 ]] || exit 1
}

find_php() {
  if command -v php >/dev/null 2>&1; then
    command -v php
    return 0
  fi
  for p in /usr/bin/php /usr/local/bin/php /opt/homebrew/bin/php; do
    if [[ -x "$p" ]]; then
      echo "$p"
      return 0
    fi
  done
  return 1
}

confirm_wipe() {
  if [[ "$YES" == true ]]; then
    return 0
  fi
  local srv_db loc_db
  srv_db="$(read_env database.server.database)"
  loc_db="$(read_env database.default.database)"
  echo "This will REPLACE all data in local database: ${loc_db}"
  echo "Source: $(read_env database.server.hostname) / ${srv_db}"
  read -r -p "Continue? [y/N] " ans
  ans="$(printf '%s' "$ans" | tr '[:upper:]' '[:lower:]')"
  [[ "$ans" == "y" || "$ans" == "yes" ]]
}

sync_db_dump() {
  local php_bin spark_args=()
  php_bin="$(find_php)" || { echo "php not found in PATH" >&2; return 1; }
  if [[ "$DUMP_ONLY" == true ]]; then
    spark_args+=(--dump-only)
  fi
  "$php_bin" spark db:clone-to-local "${spark_args[@]}"
}

sync_db_php() {
  local php_bin
  php_bin="$(find_php)" || { echo "php not found in PATH" >&2; return 1; }
  "$php_bin" scripts/sync_server_to_local.php "${EXTRA_PHP_ARGS[@]}"
}

sync_db() {
  require_db_env
  confirm_wipe || { echo "Aborted."; exit 0; }

  local srv_host srv_port srv_db loc_host loc_port loc_db
  srv_host="$(read_env database.server.hostname)"
  srv_port="$(read_env database.server.port)"
  srv_db="$(read_env database.server.database)"
  loc_host="$(read_env database.default.hostname)"
  loc_port="$(read_env database.default.port)"
  loc_db="$(read_env database.default.database)"
  [[ -n "$srv_port" ]] || srv_port=3306
  [[ -n "$loc_port" ]] || loc_port=3306

  echo "=== Database: server → local ==="
  echo "Server: ${srv_host}:${srv_port} / ${srv_db}"
  echo "Local:  ${loc_host}:${loc_port} / ${loc_db}"
  echo ""

  if [[ "$METHOD" == "php" ]]; then
    sync_db_php
    return
  fi

  if [[ "$METHOD" == "dump" ]]; then
    sync_db_dump
    return
  fi

  # auto: mysqldump via spark, fallback to PHP row copy
  if sync_db_dump 2>/dev/null; then
    return
  fi
  echo ""
  echo "Note: db:clone-to-local failed (often missing mysqldump). Falling back to PHP sync..."
  echo ""
  sync_db_php
}

sync_uploads() {
  local ssh_host remote local_path
  ssh_host="$(read_env sync.server.ssh)"
  remote="$(read_env sync.server.remote_uploads)"
  local_path="$(read_env sync.local.uploads)"
  [[ -n "$local_path" ]] || local_path="public/uploads"

  if [[ -z "$ssh_host" || -z "$remote" ]]; then
    echo "Skip files: set sync.server.ssh and sync.server.remote_uploads in .env to enable rsync"
    return 0
  fi

  if ! command -v rsync >/dev/null 2>&1; then
    echo "Error: rsync not installed" >&2
    return 1
  fi

  mkdir -p "$local_path"
  remote="${remote%/}/"
  echo "=== Files: rsync ${ssh_host}:${remote} → ${local_path}/ ==="
  rsync -avz --progress -e ssh "${ssh_host}:${remote}" "${local_path}/"
}

# --- main ---
if [[ "$FILES_ONLY" != true ]]; then
  sync_db
fi

if [[ "$DB_ONLY" != true ]]; then
  sync_uploads
fi

echo ""
echo "Sync finished."
