<?php

/**
 * Content Builder Integration Test
 * ทดสอบระบบ Content Builder ด้วยสิทธิ์ผู้ใช้แต่ละระดับ
 * 
 * Usage: php tests/test_content_builder.php
 */

$baseUrl = 'http://localhost/newScience/public';

// Track results
$results = [];
$testBlockId = null;
$duplicateBlockId = null;

// ─── Helper Functions ───────────────────────────────────────────────

function httpGet(string $url, ?string $cookie = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $error = curl_error($ch);
    curl_close($ch);

    // Extract redirect location
    $location = null;
    if (preg_match('/Location:\s*(.+)/i', $headers, $m)) {
        $location = trim($m[1]);
    }

    // Extract Set-Cookie
    $cookies = [];
    preg_match_all('/Set-Cookie:\s*([^;]+)/i', $headers, $m);
    foreach ($m[1] as $c) {
        $cookies[] = $c;
    }

    return [
        'code' => $httpCode,
        'body' => $body,
        'headers' => $headers,
        'location' => $location,
        'cookies' => $cookies,
        'error' => $error,
    ];
}

function httpPost(string $url, array $data, ?string $cookie = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 10,
    ]);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $error = curl_error($ch);
    curl_close($ch);

    $location = null;
    if (preg_match('/Location:\s*(.+)/i', $headers, $m)) {
        $location = trim($m[1]);
    }

    return [
        'code' => $httpCode,
        'body' => $body,
        'headers' => $headers,
        'location' => $location,
        'error' => $error,
    ];
}

/**
 * Login via dev route and return session cookie
 */
function devLogin(string $baseUrl, string $route): ?string
{
    $ch = curl_init("$baseUrl/$route");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_COOKIEJAR => '',
    ]);
    $response = curl_exec($ch);
    $headers = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
    curl_close($ch);

    // Extract ci_session cookie
    $cookie = null;
    if (preg_match('/Set-Cookie:\s*(ci_session=[^;]+)/i', $headers, $m)) {
        $cookie = $m[1];
    }
    return $cookie;
}

/**
 * Login as specific role by setting session directly via a test endpoint
 */
function loginAsRole(string $baseUrl, string $role): ?string
{
    // Use cookie jar file for this session
    $cookieFile = sys_get_temp_dir() . '/cb_test_' . $role . '.txt';
    @unlink($cookieFile);

    if ($role === 'super_admin') {
        $loginUrl = "$baseUrl/dev/test-content-builder";
    } else {
        $loginUrl = "$baseUrl/dev/login-as-admin";
    }

    $ch = curl_init($loginUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 300 && $httpCode < 400) {
        return $cookieFile;
    }
    return null;
}

function getWithCookieFile(string $url, string $cookieFile): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $error = curl_error($ch);
    curl_close($ch);

    $location = null;
    if (preg_match('/Location:\s*(.+)/i', $headers, $m)) {
        $location = trim($m[1]);
    }

    return [
        'code' => $httpCode,
        'body' => $body,
        'headers' => $headers,
        'location' => $location,
        'error' => $error,
    ];
}

function postWithCookieFile(string $url, array $data, string $cookieFile): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $error = curl_error($ch);
    curl_close($ch);

    $location = null;
    if (preg_match('/Location:\s*(.+)/i', $headers, $m)) {
        $location = trim($m[1]);
    }

    return [
        'code' => $httpCode,
        'body' => $body,
        'headers' => $headers,
        'location' => $location,
        'error' => $error,
    ];
}

function recordResult(string $test, bool $pass, string $detail = ''): void
{
    global $results;
    $icon = $pass ? '✅' : '❌';
    $results[] = ['test' => $test, 'pass' => $pass, 'detail' => $detail];
    echo "  $icon $test" . ($detail ? " — $detail" : "") . "\n";
}

// ─── Test Execution ─────────────────────────────────────────────────

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Content Builder Integration Test\n";
echo "  Base URL: $baseUrl\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─── 1. Test super_admin ────────────────────────────────────────────
echo "── 1. super_admin Tests ──────────────────────────────────────\n";

$cookieFile = loginAsRole($baseUrl, 'super_admin');
if (!$cookieFile) {
    echo "  ❌ Failed to login as super_admin via /dev/test-content-builder\n";
    echo "     Make sure ENVIRONMENT=development and user table has data.\n";
    exit(1);
}
echo "  ✓ Logged in as super_admin\n";

// 1a. GET /program-admin
$r = getWithCookieFile("$baseUrl/program-admin", $cookieFile);
$pass = ($r['code'] === 200 && strpos($r['body'], 'จัดการหลักสูตร') !== false);
recordResult('/program-admin (super_admin)', $pass, "HTTP {$r['code']}");

// 1b. GET /program-admin/content-builder/1
$r = getWithCookieFile("$baseUrl/program-admin/content-builder/1", $cookieFile);
$pass = ($r['code'] === 200 && (strpos($r['body'], 'Content Builder') !== false || strpos($r['body'], 'content-builder') !== false));
recordResult('/program-admin/content-builder/1 (super_admin)', $pass, "HTTP {$r['code']}");

// 1c. POST create block
$r = postWithCookieFile("$baseUrl/program-admin/content-builder/1/blocks", [
    'title' => 'Test Block from Script',
    'block_type' => 'html',
], $cookieFile);
$pass = ($r['code'] >= 300 && $r['code'] < 400);
// Extract block ID from redirect URL
if ($r['location'] && preg_match('/block\/(\d+)\/edit/', $r['location'], $m)) {
    $testBlockId = (int) $m[1];
    recordResult('Create block (super_admin)', true, "Block ID: $testBlockId");
} else {
    recordResult('Create block (super_admin)', $pass, "HTTP {$r['code']}, Location: " . ($r['location'] ?? 'none'));
}

// 1d. GET edit block
if ($testBlockId) {
    $r = getWithCookieFile("$baseUrl/program-admin/content-builder/block/$testBlockId/edit", $cookieFile);
    $pass = ($r['code'] === 200 && strpos($r['body'], 'Test Block from Script') !== false);
    recordResult("Edit block page (ID: $testBlockId)", $pass, "HTTP {$r['code']}");
}

// 1e. POST update block
if ($testBlockId) {
    $r = postWithCookieFile("$baseUrl/program-admin/content-builder/block/$testBlockId/update", [
        'title' => 'Updated Test Block',
        'content' => '<h1>Hello from Test</h1><p>This is a test block.</p>',
        'custom_css' => '.test-block { color: blue; }',
        'custom_js' => 'console.log("test block loaded");',
    ], $cookieFile);
    $pass = ($r['code'] >= 300 && $r['code'] < 400);
    recordResult("Update block (ID: $testBlockId)", $pass, "HTTP {$r['code']}");
}

// 1f. POST toggle active
if ($testBlockId) {
    $r = postWithCookieFile("$baseUrl/program-admin/content-builder/block/$testBlockId/toggle", [], $cookieFile);
    $pass = ($r['code'] >= 300 && $r['code'] < 400);
    recordResult("Toggle active (ID: $testBlockId)", $pass, "HTTP {$r['code']}");
}

// 1g. POST publish
if ($testBlockId) {
    $r = postWithCookieFile("$baseUrl/program-admin/content-builder/block/$testBlockId/publish", [], $cookieFile);
    $pass = ($r['code'] >= 300 && $r['code'] < 400);
    recordResult("Publish block (ID: $testBlockId)", $pass, "HTTP {$r['code']}");
}

// 1h. POST duplicate
if ($testBlockId) {
    $r = postWithCookieFile("$baseUrl/program-admin/content-builder/block/$testBlockId/duplicate", [], $cookieFile);
    $pass = ($r['code'] >= 300 && $r['code'] < 400);
    recordResult("Duplicate block (ID: $testBlockId)", $pass, "HTTP {$r['code']}");

    // Find the duplicate block by checking the content builder page
    $r2 = getWithCookieFile("$baseUrl/program-admin/content-builder/1/blocks", $cookieFile);
    if ($r2['code'] === 200) {
        $blocks = json_decode($r2['body'], true);
        if (is_array($blocks)) {
            foreach ($blocks as $b) {
                if (strpos($b['title'] ?? '', 'Updated Test Block') !== false && $b['id'] != $testBlockId) {
                    $duplicateBlockId = (int) $b['id'];
                    break;
                }
            }
        }
    }
}

// 1i. GET live-preview
$r = getWithCookieFile("$baseUrl/program-admin/live-preview/1", $cookieFile);
$pass = ($r['code'] === 200);
recordResult('/program-admin/live-preview/1 (super_admin)', $pass, "HTTP {$r['code']}");

// 1j. GET blocks JSON
$r = getWithCookieFile("$baseUrl/program-admin/content-builder/1/blocks", $cookieFile);
$pass = ($r['code'] === 200);
$blocks = json_decode($r['body'], true);
$blockCount = is_array($blocks) ? count($blocks) : 0;
recordResult('GET blocks JSON (super_admin)', $pass, "HTTP {$r['code']}, blocks: $blockCount");

echo "\n";

// ─── 2. Test admin ──────────────────────────────────────────────────
echo "── 2. admin Tests ────────────────────────────────────────────\n";

$adminCookie = loginAsRole($baseUrl, 'admin');
if ($adminCookie) {
    echo "  ✓ Logged in as admin\n";

    $r = getWithCookieFile("$baseUrl/program-admin", $adminCookie);
    // admin may see programs or get redirected depending on role stored
    $isOk = ($r['code'] === 200);
    $isRedirectToDashboard = ($r['code'] >= 300 && $r['location'] && strpos($r['location'], 'dashboard') !== false);
    $isRedirectToLogin = ($r['code'] >= 300 && $r['location'] && strpos($r['location'], 'login') !== false);

    if ($isOk) {
        recordResult('/program-admin (admin)', true, "HTTP 200 - Access granted");
    } elseif ($isRedirectToDashboard) {
        recordResult('/program-admin (admin)', false, "Redirected to dashboard - may need chair role");
    } elseif ($isRedirectToLogin) {
        recordResult('/program-admin (admin)', false, "Redirected to login - session issue");
    } else {
        recordResult('/program-admin (admin)', false, "HTTP {$r['code']}");
    }

    $r = getWithCookieFile("$baseUrl/program-admin/content-builder/1", $adminCookie);
    $pass = ($r['code'] === 200);
    recordResult('/program-admin/content-builder/1 (admin)', $pass, "HTTP {$r['code']}");
} else {
    recordResult('Login as admin', false, 'Could not login');
}

echo "\n";

// ─── 3. Test public website ─────────────────────────────────────────
echo "── 3. Public Website Tests ───────────────────────────────────\n";

$r = getWithCookieFile("$baseUrl/program-site/1", $cookieFile);
$pass = ($r['code'] === 200);
$hasContent = (strpos($r['body'], 'Hello from Test') !== false || strpos($r['body'], 'Updated Test Block') !== false);
recordResult('GET /program-site/1', $pass, "HTTP {$r['code']}");
if ($pass) {
    recordResult('Public site has test block content', $hasContent, $hasContent ? 'Content found' : 'Content not found (may need publish+active)');
}

echo "\n";

// ─── 4. Test unauthenticated access ────────────────────────────────
echo "── 4. Unauthenticated Access Tests ──────────────────────────\n";

// Use a fresh cookie file with no session
$noCookieFile = sys_get_temp_dir() . '/cb_test_noauth.txt';
@unlink($noCookieFile);
file_put_contents($noCookieFile, '');

$r = getWithCookieFile("$baseUrl/program-admin", $noCookieFile);
$pass = ($r['code'] >= 300 && $r['location'] && strpos($r['location'], 'login') !== false);
recordResult('/program-admin (no auth)', $pass, "HTTP {$r['code']}, Location: " . ($r['location'] ?? 'none'));

$r = getWithCookieFile("$baseUrl/program-admin/content-builder/1", $noCookieFile);
$pass = ($r['code'] >= 300 && $r['location'] && strpos($r['location'], 'login') !== false);
recordResult('/program-admin/content-builder/1 (no auth)', $pass, "HTTP {$r['code']}, Location: " . ($r['location'] ?? 'none'));

echo "\n";

// ─── 5. Cleanup ─────────────────────────────────────────────────────
echo "── 5. Cleanup ────────────────────────────────────────────────\n";

// Delete duplicate block
if ($duplicateBlockId) {
    $r = postWithCookieFile("$baseUrl/program-admin/content-builder/block/$duplicateBlockId/delete", [], $cookieFile);
    $pass = ($r['code'] >= 300 && $r['code'] < 400);
    recordResult("Delete duplicate block (ID: $duplicateBlockId)", $pass, "HTTP {$r['code']}");
}

// Delete test block
if ($testBlockId) {
    $r = postWithCookieFile("$baseUrl/program-admin/content-builder/block/$testBlockId/delete", [], $cookieFile);
    $pass = ($r['code'] >= 300 && $r['code'] < 400);
    recordResult("Delete test block (ID: $testBlockId)", $pass, "HTTP {$r['code']}");
}

// Cleanup temp files
foreach (['super_admin', 'admin', 'noauth'] as $f) {
    @unlink(sys_get_temp_dir() . "/cb_test_$f.txt");
}

echo "\n";

// ─── Summary ────────────────────────────────────────────────────────
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Test Summary\n";
echo "═══════════════════════════════════════════════════════════════\n";

$total = count($results);
$passed = count(array_filter($results, fn($r) => $r['pass']));
$failed = $total - $passed;

echo "  Total:  $total\n";
echo "  Passed: $passed ✅\n";
echo "  Failed: $failed ❌\n";
echo "\n";

if ($failed > 0) {
    echo "  Failed tests:\n";
    foreach ($results as $r) {
        if (!$r['pass']) {
            echo "    ❌ {$r['test']} — {$r['detail']}\n";
        }
    }
    echo "\n";
}

// Result table
echo "  Results:\n";
foreach ($results as $r) {
    $status = $r['pass'] ? 'PASS' : 'FAIL';
    $icon = $r['pass'] ? '✅' : '❌';
    echo "    $icon [$status] {$r['test']}\n";
}

function mb_str_pad(string $str, int $pad_length, string $pad_string = ' ', int $pad_type = STR_PAD_RIGHT): string
{
    $str_len = mb_strlen($str);
    if ($str_len >= $pad_length) {
        return mb_substr($str, 0, $pad_length);
    }
    $diff = $pad_length - $str_len;
    if ($pad_type === STR_PAD_RIGHT) {
        return $str . str_repeat($pad_string, $diff);
    }
    return str_repeat($pad_string, $diff) . $str;
}

exit($failed > 0 ? 1 : 0);
