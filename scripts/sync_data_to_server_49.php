<?php

/**
 * อัปโหลดข้อมูลจาก local ขึ้น server 49 (49.231.30.18)
 * Sync หลายตาราง: site_settings, departments, programs, user, personnel, personnel_programs, news, news_images
 *
 * วิธีรัน: php scripts/sync_data_to_server_49.php
 *
 * หมายเหตุ:
 * - ต้องเปิด MySQL บน server 49 รับ connection จาก IP ของคุณ (หรือรันจากเครื่องที่อนุญาตแล้ว)
 * - รหัส remote ใช้จาก sync_news_to_server.php / server.env
 */

$local = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$remote = [
    'host' => '49.231.30.18',
    'user' => 'root',
    'pass' => 'admin@SCI@2026',
    'db'   => 'newscience',
];

// ลำดับตาราง (เคารพ foreign key): ต้นทางก่อน แปลงก่อน
$tablesToSync = [
    'site_settings',
    'departments',
    'programs',
    'user',
    'personnel',
    'personnel_programs',
    'news',
    'news_images',
];

// ตารางเสริม (ถ้ามีใน local จะ sync)
$optionalTables = [
    'hero_slides',
    'activities',
    'activity_images',
    'links',
];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$localConn = new mysqli($local['host'], $local['user'], $local['pass'], $local['db']);
$localConn->set_charset('utf8mb4');

$remoteConn = new mysqli($remote['host'], $remote['user'], $remote['pass'], $remote['db']);
$remoteConn->set_charset('utf8mb4');

echo "=== อัปโหลดข้อมูลขึ้น Server 49 ({$remote['host']}) ===\n";

$remoteConn->query("SET FOREIGN_KEY_CHECKS = 0");

foreach ($tablesToSync as $table) {
    syncTable($localConn, $remoteConn, $table);
}

foreach ($optionalTables as $table) {
    $r = $localConn->query("SHOW TABLES LIKE " . $localConn->escape_string($table));
    if ($r && $r->num_rows > 0) {
        syncTable($localConn, $remoteConn, $table);
    }
}

$remoteConn->query("SET FOREIGN_KEY_CHECKS = 1");

$localConn->close();
$remoteConn->close();

echo "Done.\n";

/**
 * ดึงข้อมูลจาก local แล้วเขียนทับที่ remote (truncate แล้ว insert)
 */
function syncTable(mysqli $localConn, mysqli $remoteConn, string $table): void
{
    $table = preg_replace('/[^a-z0-9_]/', '', $table);
    if ($table === '') {
        return;
    }

    // ตรวจสอบว่าตารางมีทั้ง local และ remote
    $localExists = $localConn->query("SHOW TABLES LIKE " . $localConn->escape_string($table));
    if (!$localExists || $localExists->num_rows === 0) {
        echo "  [SKIP] $table (ไม่มีใน local)\n";
        return;
    }

    $remoteExists = $remoteConn->query("SHOW TABLES LIKE " . $remoteConn->escape_string($table));
    if (!$remoteExists || $remoteExists->num_rows === 0) {
        echo "  [SKIP] $table (ไม่มีใน server 49)\n";
        return;
    }

    $result = $localConn->query("SELECT * FROM `" . $localConn->escape_string($table) . "`");
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $total = count($rows);

    if ($total === 0) {
        echo "  [OK] $table (0 แถว)\n";
        return;
    }

    $cols = array_keys($rows[0]);
    $colList = '`' . implode('`,`', array_map([$remoteConn, 'escape_string'], $cols)) . '`';
    $placeholders = implode(',', array_fill(0, count($cols), '?'));

    $remoteConn->query("TRUNCATE TABLE `" . $remoteConn->escape_string($table) . "`");

    $types = '';
    foreach ($cols as $c) {
        $v = $rows[0][$c];
        if ($v === null) {
            $types .= 's';
        } elseif (is_int($v)) {
            $types .= 'i';
        } elseif (is_float($v)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }

    $sql = "INSERT INTO `" . $remoteConn->escape_string($table) . "` ($colList) VALUES ($placeholders)";
    $stmt = $remoteConn->prepare($sql);
    if (!$stmt) {
        echo "  [FAIL] $table prepare error\n";
        return;
    }

    $bound = 0;
    foreach ($rows as $row) {
        $vals = [];
        foreach ($cols as $col) {
            $vals[] = $row[$col];
        }
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        $bound++;
        if ($bound % 100 === 0) {
            echo "    $table: $bound / $total\n";
        }
    }
    $stmt->close();
    echo "  [OK] $table ($bound แถว)\n";
}
