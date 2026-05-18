#!/usr/bin/env bash
# ทดสอบหน้าแรก + API ข่าว กับสแต็กที่รันอยู่แล้ว (ไม่ใช้ docker compose ในโปรเจกต์)
# ตั้ง BASE_URL ให้ตรงกับ nginx — ค่าเริ่มต้นตาม app/Config/App.php
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost/newscience/public/}"
BASE_URL="${BASE_URL%/}/"

echo "==> Testing newScience (external stack) at ${BASE_URL}"
echo "    (override with: BASE_URL=https://your-host/ ./scripts/stack-test.sh)"

fail() {
  echo "FAIL: $1" >&2
  exit 1
}

pass() {
  echo "OK: $1"
}

# 1) Homepage responds
HTTP_HOME=$(curl -sS -o /tmp/newscience-home.html -w "%{http_code}" "${BASE_URL}" || echo "000")
[[ "$HTTP_HOME" == "200" ]] || fail "Homepage HTTP ${HTTP_HOME} (expected 200). ตั้ง BASE_URL ให้ตรงกับ vhost ของสแต็ก"
grep -q "ข่าวประชาสัมพันธ์" /tmp/newscience-home.html || fail "Homepage missing Campus News section title"
pass "Homepage loads (HTTP 200)"

# 2) News API by tag (Campus News section)
JSON_TAG=$(curl -sS "${BASE_URL}api/news/tag/general?limit=6")
echo "$JSON_TAG" | grep -q '"success":true' || fail "api/news/tag/general not success: ${JSON_TAG}"
echo "$JSON_TAG" | grep -q '"data":\[' || fail "api/news/tag/general missing data array"
COUNT=$(echo "$JSON_TAG" | grep -o '"id"' | wc -l | tr -d ' ')
[[ "${COUNT:-0}" -gt 0 ]] || fail "api/news/tag/general returned no articles"
pass "api/news/tag/general returned ${COUNT} article(s)"

# 3) News API list fallback
JSON_LIST=$(curl -sS "${BASE_URL}api/news?limit=6")
echo "$JSON_LIST" | grep -q '"success":true' || fail "api/news not success: ${JSON_LIST}"
pass "api/news list endpoint works"

# 4) Research news API
JSON_RES=$(curl -sS "${BASE_URL}api/news/research?limit=6")
echo "$JSON_RES" | grep -q '"success":true' || fail "api/news/research not success: ${JSON_RES}"
pass "api/news/research endpoint works"

if grep -q "เกิดข้อผิดพลาดในการโหลดข่าว" /tmp/newscience-home.html; then
  echo "NOTE: Error text in initial HTML — ตรวจในเบราว์เซอร์หลังโหลด AJAX"
fi

echo ""
echo "All stack checks passed."
echo "Open: ${BASE_URL}"
