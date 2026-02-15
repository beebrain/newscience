<?php

/**
 * Migration: อัปเดต personnel.name และ personnel.name_en จากตาราง user
 * สำหรับแถวที่ personnel.user_uid ถูกตั้งแล้ว — ให้ชื่อใน personnel ตรงกับ user
 *
 * Usage: php scripts/migrate_personnel_names_from_user.php [--dry-run]
 *   --dry-run  แสดงว่าจะอัปเดตอะไร ไม่เขียน DB
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

$dryRun = in_array('--dry-run', $GLOBALS['argv'] ?? [], true);

function userFullNameTh(array $u): string
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
    SELECT p.id, p.name AS p_name, p.name_en AS p_name_en, p.user_uid
    FROM personnel p
    INNER JOIN user u ON u.uid = p.user_uid
    WHERE p.user_uid IS NOT NULL
    ORDER BY p.id
");
if (!$res) {
    fwrite(STDERR, "Query failed: " . $conn->error . "\n");
    exit(1);
}

$stmt = $conn->prepare("SELECT * FROM user WHERE uid = ?");
$updateStmt = $conn->prepare("UPDATE personnel SET name = ?, name_en = ? WHERE id = ?");

$updated = 0;
$skipped = 0;

while ($row = $res->fetch_assoc()) {
    $uid = (int) ($row['user_uid'] ?? 0);
    if ($uid <= 0) {
        $skipped++;
        continue;
    }

    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $uRes = $stmt->get_result();
    $user = $uRes && $uRes->num_rows > 0 ? $uRes->fetch_assoc() : null;
    if (!$user) {
        $skipped++;
        continue;
    }

    $nameTh = userFullNameTh($user);
    $nameEn = userFullNameEn($user);

    $pName = trim($row['p_name'] ?? '');
    $pNameEn = trim($row['p_name_en'] ?? '');

    $needUpdate = false;
    if ($nameTh !== '' && $pName !== $nameTh) $needUpdate = true;
    if ($nameEn !== '' && $pNameEn !== $nameEn) $needUpdate = true;
    if (!$needUpdate) {
        $skipped++;
        continue;
    }

    $newName = $nameTh !== '' ? $nameTh : $pName;
    $newNameEn = $nameEn !== '' ? $nameEn : $pNameEn;

    if ($dryRun) {
        echo "Would update personnel.id={$row['id']}: name '{$pName}' -> '{$newName}', name_en '{$pNameEn}' -> '{$newNameEn}'\n";
        $updated++;
        continue;
    }

    $pid = (int) $row['id'];
    $updateStmt->bind_param('ssi', $newName, $newNameEn, $pid);
    $updateStmt->execute();
    $updated++;
    echo "Updated personnel.id={$row['id']}: name -> {$newName}, name_en -> {$newNameEn}\n";
}

echo "\n" . ($dryRun ? "[DRY RUN] " : "") . "Done: {$updated} updated, {$skipped} skipped (already same or no user).\n";
if ($dryRun && $updated > 0) {
    echo "Run without --dry-run to apply: php scripts/migrate_personnel_names_from_user.php\n";
}

$stmt->close();
if ($updateStmt) $updateStmt->close();
$conn->close();
