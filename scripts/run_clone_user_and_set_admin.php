<?php

/**
 * 1) Clone researchrecord.user → newscience.user (โครงสร้าง + ข้อมูล)
 * 2) เพิ่มคอลัมน์ที่แอปใช้ (title, tf_name, tl_name, th_name, thai_lastname, ฯลฯ)
 * 3) ตั้งผู้ใช้ admin: login_uid=admin, password=admin123, role=super_admin
 *
 * Run: php scripts/run_clone_user_and_set_admin.php [--no-backup]
 *   --no-backup  ไม่สำรองตาราง user เดิมก่อนทับ
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

$noBackup = in_array('--no-backup', $GLOBALS['argv'] ?? [], true);

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

echo "=== Clone researchrecord.user → newscience.user + Set Admin ===\n\n";

// ----- 1. Get CREATE TABLE from source -----
$r = $srcConn->query("SHOW CREATE TABLE `user`");
if (!$r || $r->num_rows === 0) {
    fwrite(STDERR, "Table researchrecord.user not found.\n");
    $srcConn->close();
    $tgtConn->close();
    exit(1);
}
$row = $r->fetch_array(MYSQLI_NUM);
$createSql = $row[1];
$createSql = preg_replace('/`researchrecord`\.`user`/', '`user`', $createSql);
// ลบ CONSTRAINT ... FOREIGN KEY ... REFERENCES ... ON DELETE SET NULL (ตาราง curriculum อาจไม่มีใน newscience)
$createSql = preg_replace('/,\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN\s+KEY\s+\([^)]+\)\s+REFERENCES\s+`[^`]+`\s+\([^)]+\)\s*ON\s+(?:DELETE|UPDATE)\s+(?:SET\s+NULL|CASCADE|RESTRICT|NO\s+ACTION|\w+)/i', '', $createSql);
// ซ่อม comma ค้างก่อน ) ENGINE
$createSql = preg_replace('/,\s*\)\s*ENGINE/', ') ENGINE', $createSql);
echo "Step 1: Got CREATE TABLE from researchrecord.user\n";

// ----- 2. Backup / drop existing user in target -----
$r2 = $tgtConn->query("SHOW TABLES LIKE 'user'");
$targetHasUser = $r2 && $r2->num_rows > 0;

if ($targetHasUser && !$noBackup) {
    $backupName = 'user_backup_' . date('Ymd_His');
    if (!$tgtConn->query("RENAME TABLE `user` TO `{$backupName}`")) {
        fwrite(STDERR, "Failed to backup existing user: " . $tgtConn->error . "\n");
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
    echo "Step 2: Dropped existing user table\n";
} else {
    echo "Step 2: No existing user table in target\n";
}

// ----- 3. Create table in target -----
if (!$tgtConn->query($createSql)) {
    fwrite(STDERR, "Failed to CREATE TABLE user: " . $tgtConn->error . "\n");
    $srcConn->close();
    $tgtConn->close();
    exit(1);
}
echo "Step 3: Created table user in newscience\n";

// ----- 3b. หลัง RENAME TABLE user → user_backup_* แล้ว MySQL จะทำให้ FK ที่อ้างอิง user ไปชี้ที่ backup — แก้ให้ชี้กลับที่ user -----
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

// ----- 4. Copy data -----
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

$srcConn->close();

// ----- 5. Ensure role ENUM includes super_admin -----
$rRole = $tgtConn->query("SHOW COLUMNS FROM `user` WHERE Field = 'role'");
if ($rRole && $rRole->num_rows > 0) {
    $col = $rRole->fetch_assoc();
    $type = $col['Type'] ?? '';
    if (stripos($type, 'super_admin') === false) {
        $tgtConn->query("ALTER TABLE `user` MODIFY COLUMN `role` ENUM('user','admin','editor','super_admin','faculty_admin') DEFAULT 'user'");
        echo "Step 5: Updated user.role ENUM to include super_admin\n";
    } else {
        echo "Step 5: user.role already includes super_admin\n";
    }
} else {
    echo "Step 5: No role column (skip)\n";
}

// ----- 6. Add missing columns (login_uid, title, gf_name, gl_name, tf_name, tl_name, profile_image, status, th_name, thai_lastname) -----
$colsToAdd = [
    ['name' => 'login_uid',     'after' => 'uid',   'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'title',         'after' => 'password', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'gf_name',       'after' => 'title', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'gl_name',       'after' => 'gf_name', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'tf_name',       'after' => 'gl_name', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'tl_name',       'after' => 'tf_name', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'profile_image', 'after' => 'role',  'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'status',        'after' => 'profile_image', 'def' => "ENUM('active','inactive') DEFAULT 'active'"],
    ['name' => 'th_name',       'after' => 'tl_name', 'def' => 'VARCHAR(255) DEFAULT NULL COMMENT \'ชื่อ (ไทย)\''],
    ['name' => 'thai_lastname', 'after' => 'th_name', 'def' => 'VARCHAR(255) DEFAULT NULL COMMENT \'นามสกุล (ไทย)\''],
];
$after = 'password';
foreach ($colsToAdd as $d) {
    $col = $d['name'];
    $r = $tgtConn->query("SHOW COLUMNS FROM `user` LIKE '" . $tgtConn->real_escape_string($col) . "'");
    if ($r && $r->num_rows > 0) {
        $after = $col;
        continue;
    }
    $insertAfter = $d['after'];
    $r2 = $tgtConn->query("SHOW COLUMNS FROM `user` LIKE '" . $tgtConn->real_escape_string($insertAfter) . "'");
    if (!$r2 || $r2->num_rows === 0) {
        $insertAfter = $after;
    }
    $sql = "ALTER TABLE `user` ADD COLUMN `$col` " . $d['def'] . " AFTER `$insertAfter`";
    if (!$tgtConn->query($sql)) {
        fwrite(STDERR, "Warning: could not add user.$col: " . $tgtConn->error . "\n");
    } else {
        $after = $col;
    }
}
echo "Step 6: Ensured extra columns on user table\n";

// ----- 7. Set admin user: login_uid=admin, password=admin123, role=super_admin -----
$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
$loginUid = 'admin';
$email = 'admin@localhost';

// 7a) Update existing user with login_uid=admin
$stmt = $tgtConn->prepare("UPDATE `user` SET `password` = ?, `role` = 'super_admin', `status` = 'active' WHERE `login_uid` = ? LIMIT 1");
$stmt->bind_param('ss', $passwordHash, $loginUid);
$stmt->execute();
$updated = $stmt->affected_rows;
$stmt->close();

if ($updated > 0) {
    echo "Step 7: Admin user updated. Login: admin / admin123 (super_admin)\n";
    $tgtConn->close();
    echo "\nOK: Clone + Admin done. Use admin / admin123 to access admin.\n";
    exit(0);
}

// 7b) Update first user to be admin (e.g. after clone, no login_uid set yet)
$first = $tgtConn->query("SELECT uid FROM `user` ORDER BY uid ASC LIMIT 1");
if ($first && $first->num_rows > 0) {
    $row = $first->fetch_assoc();
    $uid = (int) $row['uid'];
    $stmt = $tgtConn->prepare("UPDATE `user` SET `login_uid` = ?, `password` = ?, `role` = 'super_admin', `status` = 'active' WHERE uid = ?");
    $stmt->bind_param('ssi', $loginUid, $passwordHash, $uid);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Step 7: First user (uid=$uid) set as super_admin. Login: admin / admin123\n";
        $stmt->close();
        $tgtConn->close();
        echo "\nOK: Clone + Admin done. Use admin / admin123 to access admin.\n";
        exit(0);
    }
    $stmt->close();
}

// 7c) Insert new admin user (empty table or no one updated)
$stmt = $tgtConn->prepare("INSERT INTO `user` (`login_uid`, `email`, `password`, `gf_name`, `gl_name`, `tf_name`, `tl_name`, `role`, `status`) VALUES (?, ?, ?, 'Admin', 'User', 'ผู้ดูแล', 'ระบบ', 'super_admin', 'active')");
$stmt->bind_param('sss', $loginUid, $email, $passwordHash);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo "Step 7: New admin user created. Login: admin / admin123 (super_admin)\n";
} else {
    fwrite(STDERR, "Could not create admin user: " . $tgtConn->error . "\n");
    $stmt->close();
    $tgtConn->close();
    exit(1);
}
$stmt->close();

$tgtConn->close();
echo "\nOK: Clone + Admin done. Use admin / admin123 to access admin.\n";
exit(0);
