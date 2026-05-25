#!/usr/bin/env bash
# E2E: คูปอง/บาร์โค้ดกิจกรรมนักศึกษา (Phase 0–4 smoke)
# ใช้ dev login + dummy students (u59=club, u69=student)
set -euo pipefail

BASE="${BASE_URL:-http://localhost/newscience/public}"
CLUB_EMAIL="u59@live.uru.ac.th"
STUDENT_EMAIL="u69@live.uru.ac.th"
TMP="${TMPDIR:-/tmp}/barcode_e2e_$$"
mkdir -p "$TMP"
COOKIE_CLUB="$TMP/club.cookies"
COOKIE_STU="$TMP/student.cookies"
PASS=0
FAIL=0

log() { echo "[e2e] $*"; }
ok() { log "OK: $1"; PASS=$((PASS + 1)); }
bad() { log "FAIL: $1"; FAIL=$((FAIL + 1)); }

cleanup() { rm -rf "$TMP"; }
trap cleanup EXIT

curl_sess() {
  local jar="$1"
  shift
  curl -sS -L -c "$jar" -b "$jar" "$@"
}

# POST แล้วอย่าใช้ -L (curl อาจส่ง body ซ้ำ → nginx 404) — ตรวจ Location แทน
curl_post_redirect() {
  local jar="$1" url="$2"
  shift 2
  local headers
  headers=$(curl -sS -c "$jar" -b "$jar" -D - -o /dev/null -X POST "$url" "$@")
  echo "$headers" | awk 'BEGIN{IGNORECASE=1} /^Location:/ {print $2}' | tr -d '\r'
}

# --- Phase 0: API dummy ---
log "Phase 0: barcode-dummy API"
DUMMY_JSON=$(curl -sS "${BASE%/}/api/barcode-dummy?count=3")
if echo "$DUMMY_JSON" | grep -q 'BC0001'; then
  ok "barcode-dummy returns codes"
else
  bad "barcode-dummy: $DUMMY_JSON"
fi

# --- Phase 1: Club creates event, imports, adds eligible ---
log "Phase 1: club setup"
HTTP_CLUB=$(curl_sess "$COOKIE_CLUB" "${BASE%/}/dev/login-dummy-club" -o /dev/null -w "%{http_code}")
[[ "$HTTP_CLUB" =~ ^(200|302)$ ]] && ok "dev login club" || bad "dev login club (http=$HTTP_CLUB)"

TS=$(date +%s)
TITLE="E2E Coupon Test ${TS}"
EVENT_DATE=$(date +%Y-%m-%d)

STORE_OUT=$(curl_sess "$COOKIE_CLUB" \
  -X POST "${BASE%/}/student-admin/barcode-events/store" \
  -d "title=${TITLE}" \
  -d "description=Automated+e2e+test" \
  -d "event_date=${EVENT_DATE}" \
  -d "status=active" \
  -w "\n%{url_effective}")

EVENT_ID=$(echo "$STORE_OUT" | grep -oE 'barcode-events/[0-9]+' | tail -1 | grep -oE '[0-9]+' || true)
if [[ -z "${EVENT_ID:-}" ]]; then
  bad "could not parse event id from store redirect"
  echo "$STORE_OUT" | tail -5
  exit 1
fi
ok "created event id=$EVENT_ID"

IMPORT_BODY='{"barcodes":["E2E-BC-001","E2E-BC-002","E2E-BC-003"]}'
IMPORT_LOC=$(curl_post_redirect "$COOKIE_CLUB" \
  "${BASE%/}/student-admin/barcode-events/import/${EVENT_ID}" \
  --data-urlencode "json_barcodes=${IMPORT_BODY}")
if echo "$IMPORT_LOC" | grep -q "barcode-events/${EVENT_ID}"; then
  ok "imported 3 barcodes (redirect ok)"
else
  bad "import redirect: ${IMPORT_LOC:-empty}"
fi

curl_post_redirect "$COOKIE_CLUB" \
  "${BASE%/}/student-admin/barcode-events/add-eligible/${EVENT_ID}" \
  -d "by=email" \
  --data-urlencode "emails=${STUDENT_EMAIL}" >/dev/null
ELIG_DB=$(docker exec shared_mysql mysql -uroot -prootpass newscience -N -e \
  "SELECT COUNT(*) FROM barcode_event_eligibles e JOIN student_user s ON s.id=e.student_user_id \
   WHERE e.barcode_event_id=${EVENT_ID} AND s.email='${STUDENT_EMAIL}';" 2>/dev/null | tr -d ' ')
[[ "${ELIG_DB:-0}" -ge 1 ]] && ok "added eligible $STUDENT_EMAIL" || bad "eligible row missing in DB"

# DB check (via docker mysql)
DB_CNT=$(docker exec shared_mysql mysql -uroot -prootpass newscience -N -e \
  "SELECT COUNT(*) FROM barcodes WHERE barcode_event_id=${EVENT_ID};" 2>/dev/null | tr -d ' ')
[[ "${DB_CNT:-0}" -ge 3 ]] && ok "DB has $DB_CNT barcodes" || bad "DB barcode count=$DB_CNT"

# --- Phase 2: Student happy path ---
log "Phase 2: student claim"
HTTP_STU=$(curl_sess "$COOKIE_STU" "${BASE%/}/dev/login-dummy-student" -o /dev/null -w "%{http_code}")
[[ "$HTTP_STU" =~ ^(200|302)$ ]] && ok "dev login student" || bad "dev login student (http=$HTTP_STU)"

BARCODES_HTML=$(curl_sess "$COOKIE_STU" "${BASE%/}/student/barcodes")
if echo "$BARCODES_HTML" | grep -q 'เปิดคูปองรับรหัส\|คุณมีสิทธิ์'; then
  ok "barcodes list shows eligible state"
else
  bad "barcodes list missing ready_claim hint"
fi

EVENT_HTML=$(curl_sess "$COOKIE_STU" "${BASE%/}/student/barcodes/event/${EVENT_ID}")
if echo "$EVENT_HTML" | grep -q 'claim-from-event'; then
  ok "event page has claim form"
else
  bad "event page missing claim-from-event"
fi

CLAIM_LOC=$(curl_post_redirect "$COOKIE_STU" \
  "${BASE%/}/student/barcodes/claim-from-event/${EVENT_ID}")
if echo "$CLAIM_LOC" | grep -q "barcodes/event/${EVENT_ID}"; then
  ok "claim-from-event redirect ok"
else
  bad "claim redirect: ${CLAIM_LOC:-empty}"
fi

AFTER_HTML=$(curl_sess "$COOKIE_STU" "${BASE%/}/student/barcodes/event/${EVENT_ID}")
if echo "$AFTER_HTML" | grep -q 'E2E-BC-00'; then
  ok "assigned code visible on coupon page"
else
  bad "code not visible after claim"
fi

STU_ID=$(docker exec shared_mysql mysql -uroot -prootpass newscience -N -e \
  "SELECT id FROM student_user WHERE email='${STUDENT_EMAIL}' LIMIT 1;" 2>/dev/null | tr -d ' ')
ASSIGNED=$(docker exec shared_mysql mysql -uroot -prootpass newscience -N -e \
  "SELECT code FROM barcodes WHERE barcode_event_id=${EVENT_ID} AND student_user_id=${STU_ID} AND claimed_at IS NOT NULL LIMIT 1;" 2>/dev/null | tr -d ' ')
if [[ -n "${ASSIGNED:-}" ]]; then
  ok "DB assigned+claimed: $ASSIGNED"
else
  bad "DB no claimed row for student"
fi

# --- Phase 3: duplicate claim blocked ---
curl_post_redirect "$COOKIE_STU" \
  "${BASE%/}/student/barcodes/claim-from-event/${EVENT_ID}" >/dev/null
DUP_HTML=$(curl_sess "$COOKIE_STU" "${BASE%/}/student/barcodes/event/${EVENT_ID}")
if echo "$DUP_HTML" | grep -q 'รับบาร์โค้ดจากกิจกรรมนี้แล้ว\|รับสิทธิ์แล้ว'; then
  ok "duplicate claim rejected or shows opened"
else
  bad "duplicate claim message not found"
fi

# --- Phase 3b: non-eligible student (use another existing student if any) ---
OTHER_ID=$(docker exec shared_mysql mysql -uroot -prootpass newscience -N -e \
  "SELECT id FROM student_user WHERE email != '${STUDENT_EMAIL}' AND email != '${CLUB_EMAIL}' AND status='active' LIMIT 1;" 2>/dev/null | tr -d ' ')
if [[ -n "${OTHER_ID:-}" ]]; then
  COOKIE_OTHER="$TMP/other.cookies"
  curl_sess "$COOKIE_OTHER" "${BASE%/}/dev/login-as-student?id=${OTHER_ID}" -o /dev/null
  LOCK_HTML=$(curl_sess "$COOKIE_OTHER" "${BASE%/}/student/barcodes/event/${EVENT_ID}")
  if echo "$LOCK_HTML" | grep -q 'ไม่ใช่ของคุณ\|ไม่มีสิทธิ์'; then
    ok "non-eligible sees locked"
  else
    bad "non-eligible should see locked state"
  fi
else
  log "skip locked test (no other student_user)"
fi

echo ""
echo "========== SUMMARY =========="
echo "PASS: $PASS  FAIL: $FAIL  EVENT_ID: $EVENT_ID"
if [[ "$FAIL" -gt 0 ]]; then
  exit 1
fi
exit 0
