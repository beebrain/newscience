<?php
/**
 * Clone table `user` จากฐานข้อมูล researchrecord มาใช้ในโปรเจกต์ (newscience)
 * เพื่อใช้ uid อ้างอิงข้ามระบบ
 *
 * การทำงาน:
 * 1. อ่านโครงสร้างตาราง user จาก researchrecord
 * 2. สร้างตาราง user ใน newscience (หรือ DB เป้าหมาย) ให้ตรงกับต้นทาง
 * 3. (ตัวเลือก) คัดลอกข้อมูลจาก researchrecord.user → newscience.user
 *
 * รัน: php scripts/clone_user_table_from_researchrecord.php [--copy-data] [--no-backup]
 *
 * --copy-data   คัดลอกข้อมูลจาก researchrecord.user (default: ไม่คัดลอก)
 * --no-backup   ไม่สำรองตาราง user เดิมก่อนทับ (default: เปลี่ยนชื่อเป็น user_backup_YYYYMMDD)
 */

$source = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'researchrecord',
];

$target = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$copyData = in_array('--copy-data', $argv ?? []);
$noBackup = in_array('--no-backup', $argv ?? []);

$srcConn = @new mysqli($source['host'], $source['user'], $source['pass'], $source['db']);
if ($srcConn->connect_error) {
    fwrite(STDERR, "Source DB (researchrecord) connection failed: " . $srcConn->connect_error . "\n");
    exit(1);
}
$srcConn->set_charset('utf8mb4');

$tgtConn = @new mysqli($target['host'], $target['user'], $target['pass'], $target['db']);
if ($tgtConn->connect_error) {
    fwrite(STDERR, "Target DB (newscience) connection failed: " . $tgtConn->connect_error . "\n");
    $srcConn->close();
    exit(1);
}
$tgtConn->set_charset('utf8mb4');

// 1. Get CREATE TABLE from source
$r = $srcConn->query("SHOW CREATE TABLE `user`");
if (!$r || $r->num_rows === 0) {
    fwrite(STDERR, "Table researchrecord.user not found.\n");
    $srcConn->close();
    $tgtConn->close();
    exit(1);
}

$row = $r->fetch_array(MYSQLI_NUM);
$createSql = $row[1];

// ลบ database prefix ออก (เช่น `researchrecord`.`user` -> `user`)
$createSql = preg_replace('/`researchrecord`\.`user`/', '`user`', $createSql);
$createSql = preg_replace('/CREATE TABLE `user`/', 'CREATE TABLE `user`', $createSql);

// ลบ FOREIGN KEY ออก (ตารางที่ถูกอ้างอิงอาจไม่มีใน newscience)
$createSql = preg_replace('/,\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN\s+KEY\s+\([^)]+\)\s+REFERENCES\s+[^)]+\)[^,)]*/i', '', $createSql);
$createSql = preg_replace('/,\s*FOREIGN\s+KEY\s+\([^)]+\)\s+REFERENCES\s+[^)]+\)[^,)]*/i', '', $createSql);
$createSql = preg_replace('/,\s*\)\s*ENGINE/', ') ENGINE', $createSql);

echo "Step 1: Got CREATE TABLE from researchrecord.user\n";

// 2. Target: backup existing user table if exists
$r2 = $tgtConn->query("SHOW TABLES LIKE 'user'");
$targetHasUser = $r2 && $r2->num_rows > 0;

if ($targetHasUser && !$noBackup) {
    $backupName = 'user_backup_' . date('Ymd_His');
    if (!$tgtConn->query("RENAME TABLE `user` TO `{$backupName}`")) {
        fwrite(STDERR, "Failed to backup existing user table: " . $tgtConn->error . "\n");
        $srcConn->close();
        $tgtConn->close();
        exit(1);
    }
    echo "Step 2: Renamed existing user to {$backupName}\n";
} elseif ($targetHasUser && $noBackup) {
    if (!$tgtConn->query("DROP TABLE `user`")) {
        fwrite(STDERR, "Failed to DROP TABLE user: " . $tgtConn->error . "\n");
        $srcConn->close();
        $tgtConn->close();
        exit(1);
    }
    echo "Step 2: Dropped existing user table (--no-backup)\n";
} else {
    echo "Step 2: No existing user table in target\n";
}

// 3. Create table in target
if (!$tgtConn->query($createSql)) {
    fwrite(STDERR, "Failed to CREATE TABLE user in target: " . $tgtConn->error . "\n");
    $srcConn->close();
    $tgtConn->close();
    exit(1);
}
echo "Step 3: Created table user in {$target['db']}\n";

// 3b. ถ้าเพิ่ง RENAME ตาราง user → backup แล้ว MySQL จะทำให้ FK ที่อ้างอิง user ไปชี้ที่ backup — ต้องแก้ให้ชี้กลับที่ user
if ($targetHasUser && !$noBackup) {
    foreach (
        [
            ['personnel', 'fk_personnel_user', 'user_uid', 'user', 'uid', 'ON DELETE RESTRICT ON UPDATE CASCADE'],
            ['news', 'fk_news_author', 'author_id', 'user', 'uid', 'ON DELETE SET NULL ON UPDATE CASCADE'],
        ] as $fk
    ) {
        [$tbl, $cname, $col, $refTbl, $refCol, $onClause] = $fk;
        $r = $tgtConn->query("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '" . $tgtConn->real_escape_string($target['db']) . "' AND TABLE_NAME = '" . $tgtConn->real_escape_string($tbl) . "' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = '" . $tgtConn->real_escape_string($cname) . "'");
        if ($r && $r->num_rows > 0) {
            $tgtConn->query("ALTER TABLE `{$tbl}` DROP FOREIGN KEY `{$cname}`");
            $tgtConn->query("ALTER TABLE `{$tbl}` ADD CONSTRAINT `{$cname}` FOREIGN KEY (`{$col}`) REFERENCES `{$refTbl}`(`{$refCol}`) {$onClause}");
            echo "Step 3b: Fixed {$tbl}.{$cname} → user\n";
        }
    }
}

// 4. Copy data if requested (same server: INSERT ... SELECT from source DB)
if ($copyData) {
    if (strcasecmp($source['host'], $target['host']) !== 0 || $source['user'] !== $target['user']) {
        fwrite(STDERR, "Copy data requires same MySQL server and user. Use mysqldump/pipeline instead.\n");
    } else {
        $tgtConn->query("SET FOREIGN_KEY_CHECKS = 0");
        $insertSql = "INSERT INTO `user` SELECT * FROM `{$source['db']}`.`user`";
        if (!$tgtConn->query($insertSql)) {
            fwrite(STDERR, "Failed to copy data: " . $tgtConn->error . "\n");
            $tgtConn->query("SET FOREIGN_KEY_CHECKS = 1");
            $srcConn->close();
            $tgtConn->close();
            exit(1);
        }
        $tgtConn->query("SET FOREIGN_KEY_CHECKS = 1");
        $count = $tgtConn->query("SELECT COUNT(*) FROM `user`")->fetch_array(MYSQLI_NUM)[0] ?? 0;
        echo "Step 4: Copied data from researchrecord.user (" . (int) $count . " rows)\n";
    }
} else {
    echo "Step 4: Skipped copy (use --copy-data to copy rows)\n";
}

$srcConn->close();
$tgtConn->close();
echo "OK: Clone user table from researchrecord done.\n";
exit(0);
