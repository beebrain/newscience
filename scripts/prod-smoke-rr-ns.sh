#!/usr/bin/env bash
# Production smoke: RR API + NS CLI (reads RESEARCH_* from .env)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f .env ]]; then
  while IFS= read -r line; do
    if [[ "$line" =~ ^RESEARCH_API_BASE_URL ]]; then RR_BASE="${line#*=}"; RR_BASE="${RR_BASE//\"/}"; RR_BASE="${RR_BASE//\'/}"; RR_BASE="${RR_BASE#"${RR_BASE%%[![:space:]]*}"}"; RR_BASE="${RR_BASE%"${RR_BASE##*[![:space:]]}"}"; fi
    if [[ "$line" =~ ^RESEARCH_API_KEY ]]; then API_KEY="${line#*=}"; API_KEY="${API_KEY//\"/}"; API_KEY="${API_KEY//\'/}"; API_KEY="${API_KEY#"${API_KEY%%[![:space:]]*}"}"; API_KEY="${API_KEY%"${API_KEY##*[![:space:]]}"}"; fi
    if [[ "$line" =~ ^RESEARCH_SYNC_HMAC_SECRET ]]; then HMAC_SECRET="${line#*=}"; HMAC_SECRET="${HMAC_SECRET//\"/}"; HMAC_SECRET="${HMAC_SECRET//\'/}"; HMAC_SECRET="${HMAC_SECRET#"${HMAC_SECRET%%[![:space:]]*}"}"; HMAC_SECRET="${HMAC_SECRET%"${HMAC_SECRET##*[![:space:]]}"}"; fi
  done < <(grep -E '^RESEARCH_API_|^RESEARCH_SYNC_' .env)
fi
PHP_RUN=(php)
if docker ps --format '{{.Names}}' 2>/dev/null | grep -q '^shared_php$'; then
  PHP_RUN=(docker exec -i shared_php php)
fi

RR_BASE="${RR_BASE:-https://research.academic.uru.ac.th/public/index.php}"
RR_BASE="${RR_BASE%/}"
API_KEY="${API_KEY:-}"
HMAC_SECRET="${HMAC_SECRET:-}"
TEST_EMAIL="${SMOKE_EMAIL:-pisit.nak@live.uru.ac.th}"
COAUTHOR_EMAIL="${SMOKE_COAUTHOR_EMAIL:-chan11262@live.uru.ac.th}"
NS_PUBLIC="${NS_PUBLIC_URL:-https://sci.uru.ac.th}"

pass=0
fail=0
skip=0

ok() { echo "  PASS: $1"; pass=$((pass + 1)); }
bad() { echo "  FAIL: $1"; fail=$((fail + 1)); }
skp() { echo "  SKIP: $1"; skip=$((skip + 1)); }

http_code() {
  curl -sS -o /dev/null -w "%{http_code}" "$@"
}

rr_get() {
  local path="$1" email="$2"
  "${PHP_RUN[@]}" -r "
    \$base = '$RR_BASE';
    \$key = '$API_KEY';
    \$sec = '$HMAC_SECRET';
    \$email = strtolower(trim('$email'));
    \$path = '$path';
    \$exp = time() + 3600;
    \$q = ['email' => \$email];
    if (\$sec !== '') { \$q['exp'] = \$exp; \$q['sig'] = hash_hmac('sha256', \$email.'|'.\$exp, \$sec); }
    \$url = \$base.'/api/public/'.\$path.'?'.http_build_query(\$q);
    \$ctx = stream_context_create(['http' => ['header' => \"X-API-KEY: \$key\\r\\n\", 'ignore_errors' => true]]);
    \$body = @file_get_contents(\$url, false, \$ctx);
    preg_match('/\\d{3}/', \$http_response_header[0] ?? '', \$m);
    echo ((int)(\$m[0] ?? 0)).'|'.\$body;
  "
}

echo "=== RR Production API ($RR_BASE) ==="
echo "Email: $TEST_EMAIL | Coauthor: $COAUTHOR_EMAIL"

if [[ -z "$API_KEY" ]]; then
  bad "RESEARCH_API_KEY not set"
else
  c=$(http_code "$RR_BASE/api/public/cv-bundle-by-email?email=$TEST_EMAIL")
  if [[ "$c" == "401" || "$c" == "403" ]]; then ok "No API key → $c"; else bad "No API key expected 401/403 got $c"; fi

  IFS='|' read -r code body <<< "$(rr_get cv-bundle-by-email "$TEST_EMAIL")"
  if [[ "$code" == "200" ]] && echo "$body" | "${PHP_RUN[@]}" -r '$j=json_decode(stream_get_contents(STDIN),true); exit(empty($j["success"])?1:0);'; then
    secs=$(echo "$body" | "${PHP_RUN[@]}" -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["bundle"]["sections"]??[]);')
    ok "cv-bundle-by-email → 200 sections=$secs"
  else
    bad "cv-bundle-by-email → HTTP $code"
  fi

  IFS='|' read -r code body <<< "$(rr_get publications-sync-bundle-by-email "$TEST_EMAIL")"
  if [[ "$code" == "200" ]] && echo "$body" | "${PHP_RUN[@]}" -r '$j=json_decode(stream_get_contents(STDIN),true); exit(empty($j["success"])?1:0);'; then
    n=$(echo "$body" | "${PHP_RUN[@]}" -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["publications"]??[]);')
    ok "publications-sync-bundle → 200 pubs=$n"
  else
    bad "publications-sync-bundle → HTTP $code"
  fi

  IFS='|' read -r code body <<< "$(rr_get publications-sync-bundle-by-email "$COAUTHOR_EMAIL")"
  if [[ "$code" == "200" ]]; then
    n=$(echo "$body" | "${PHP_RUN[@]}" -r '$j=json_decode(stream_get_contents(STDIN),true); echo count($j["publications"]??[]);')
    ok "coauthor publications → 200 pubs=$n"
  elif [[ "$code" == "404" ]]; then
    skp "coauthor not in RR (404)"
  else
    bad "coauthor publications → HTTP $code"
  fi

  fp_code=$(http_code -H "X-API-KEY: $API_KEY" "$RR_BASE/api/public/faculty-personnel?faculty_code=FSC")
  if [[ "$fp_code" == "200" ]]; then ok "faculty-personnel → 200"; elif [[ "$fp_code" == "404" ]]; then skp "faculty-personnel → 404 (use pull-sci-faculty-cv-by-user)"; else bad "faculty-personnel → $fp_code"; fi
fi

echo ""
echo "=== NS Public ($NS_PUBLIC) ==="
c=$(http_code "$NS_PUBLIC/" 2>/dev/null || echo "000")
if [[ "$c" == "200" ]]; then ok "Homepage → 200"; else bad "Homepage → $c"; fi
enc=$("${PHP_RUN[@]}" -r "echo rawurlencode('$TEST_EMAIL');")
c=$(http_code "$NS_PUBLIC/personnel-cv/$enc" 2>/dev/null || echo "000")
if [[ "$c" == "200" ]]; then ok "Public CV → 200"; else bad "Public CV → $c"; fi

echo ""
echo "=== NS CLI (docker shared_php) ==="
if docker ps --format '{{.Names}}' 2>/dev/null | grep -q '^shared_php$'; then
  out1=$(docker exec -w /var/www/html/newScience shared_php php spark publications:sync-rr --email="$TEST_EMAIL" 2>&1 | tail -3)
  out2=$(docker exec -w /var/www/html/newScience shared_php php spark publications:sync-rr --email="$TEST_EMAIL" 2>&1 | tail -3)
  if echo "$out1$out2" | grep -qiE 'success|skipped|inserted|updated|unchanged|ซิงค์|ข้าม'; then
    ok "publications:sync-rr x2"
    echo "    run1: $(echo "$out1" | tr '\n' ' ')"
    echo "    run2: $(echo "$out2" | tr '\n' ' ')"
  else
    bad "publications:sync-rr"
    echo "$out1"
    echo "$out2"
  fi
  pull=$(docker exec -w /var/www/html/newScience shared_php php spark research:pull-sci-faculty-cv-by-user --email="$TEST_EMAIL" 2>&1 | tail -5)
  if echo "$pull" | grep -qiE 'ซิงค์|สำเร็จ|ข้าม|skipped|pull'; then ok "pull-sci-faculty-cv-by-user"; else bad "pull-sci-faculty-cv-by-user"; echo "$pull"; fi
else
  skp "shared_php not running — NS CLI tests skipped"
fi

echo ""
echo "=== SUMMARY: PASS=$pass FAIL=$fail SKIP=$skip ==="
[[ "$fail" -eq 0 ]]
