<?php

/**
 * ตรวจสอบชื่อ personnel กับ user (สำหรับแถวที่ personnel.user_uid ถูกตั้งแล้ว)
 * เปรียบเทียบ personnel.name / personnel.name_en กับชื่อจากตาราง user
 *
 * Usage: php scripts/check_personnel_user_names.php [--diff] [--csv]
 *   --diff  แสดงเฉพาะแถวที่ชื่อไม่ตรงกัน
 *   --csv   พิมพ์เป็น CSV
 */

$local = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$conn = @new mysqli($local['host'], $local['user'], $local['pass'], $local['db']);
if ($conn->connect_error) {
    fwrite(STDERR, "Connection failed: " . $conn->connect_error . "\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

$r = $conn->query("SHOW COLUMNS FROM personnel LIKE 'user_uid'");
if (!$r || $r->num_rows === 0) {
    echo "Column personnel.user_uid does not exist.\n";
    exit(1);
}

$cliArgs = $GLOBALS['argv'] ?? [];
$onlyDiff = in_array('--diff', $cliArgs, true);
$asCsv = in_array('--csv', $cliArgs, true);

function userFullNameTh(mysqli $conn, array $u): string
{
    $thName = trim(($u['th_name'] ?? '') . ' ' . ($u['thai_lastname'] ?? ''));
    if ($thName !== '') return $thName;
    $title = trim($u['title'] ?? '');
    $tf = trim($u['tf_name'] ?? '');
    $tl = trim($u['tl_name'] ?? '');
    return trim("{$title} {$tf} {$tl}");
}

function userFullNameEn(array $u): string
{
    $gf = trim($u['gf_name'] ?? '');
    $gl = trim($u['gl_name'] ?? '');
    return trim("{$gf} {$gl}");
}

$res = $conn->query("
    SELECT p.id, p.name AS p_name, p.name_en AS p_name_en, p.email, p.user_uid
    FROM personnel p
    INNER JOIN user u ON u.uid = p.user_uid
    WHERE p.user_uid IS NOT NULL
    ORDER BY p.id
");
if (!$res) {
    fwrite(STDERR, "Query failed: " . $conn->error . "\n");
    exit(1);
}

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}

$report = [];
foreach ($rows as $row) {
    $uid = (int) ($row['user_uid'] ?? 0);
    if ($uid <= 0) continue;

    $uRes = $conn->query("SELECT * FROM user WHERE uid = " . (int) $uid);
    $u = $uRes && $uRes->num_rows > 0 ? $uRes->fetch_assoc() : [];
    $uNameTh = userFullNameTh($conn, $u);
    $uNameEn = userFullNameEn($u);

    $pName = trim($row['p_name'] ?? '');
    $pNameEn = trim($row['p_name_en'] ?? '');
    $nameMatch = ($pName === $uNameTh);
    $nameEnMatch = ($pNameEn === $uNameEn || ($pNameEn === '' && $uNameEn === ''));

    if ($onlyDiff && $nameMatch && $nameEnMatch) continue;

    $report[] = [
        'id' => (int) $row['id'],
        'p_name' => $pName,
        'u_name_th' => $uNameTh,
        'name_match' => $nameMatch,
        'p_name_en' => $pNameEn,
        'u_name_en' => $uNameEn,
        'name_en_match' => $nameEnMatch,
        'email' => $row['email'] ?? '',
    ];
}

if ($asCsv) {
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id', 'personnel.name', 'user_name_th', 'name_match', 'personnel.name_en', 'user_name_en', 'name_en_match', 'email']);
    foreach ($report as $row) {
        fputcsv($out, [
            $row['id'],
            $row['p_name'],
            $row['u_name_th'],
            $row['name_match'] ? 'Y' : 'N',
            $row['p_name_en'],
            $row['u_name_en'],
            $row['name_en_match'] ? 'Y' : 'N',
            $row['email'],
        ]);
    }
    fclose($out);
    $conn->close();
    exit(0);
}

echo "=== ตรวจสอบชื่อ personnel กับ user (personnel.user_uid มีค่า) ===\n\n";
echo "จำนวน personnel ที่ลิงก์ user: " . count($rows) . "\n";
echo "จำนวนที่ชื่อไม่ตรงกัน (หรือแสดงทั้งหมด): " . count($report) . "\n\n";

foreach ($report as $row) {
    echo "personnel.id: {$row['id']} | email: {$row['email']}\n";
    echo "  ชื่อ (ไทย)  personnel: " . ($row['p_name'] ?: '(ว่าง)') . "\n";
    echo "               user:     " . ($row['u_name_th'] ?: '(ว่าง)') . " " . ($row['name_match'] ? "[ตรง]" : "[ไม่ตรง]") . "\n";
    echo "  ชื่อ (อังกฤษ) personnel: " . ($row['p_name_en'] ?: '(ว่าง)') . "\n";
    echo "                 user:     " . ($row['u_name_en'] ?: '(ว่าง)') . " " . ($row['name_en_match'] ? "[ตรง]" : "[ไม่ตรง]") . "\n";
    echo "\n";
}

echo "รัน migration เพื่ออัปเดต personnel จาก user: php scripts/migrate_personnel_names_from_user.php [--dry-run]\n";
$conn->close();
