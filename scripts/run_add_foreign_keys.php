<?php

/**
 * เพิ่ม Foreign Keys ตามที่ review ไว้
 * นโยบาย: ไม่ลบ User (ถอดถอนสิทธิ์เท่านั้น)
 * Run: php scripts/run_add_foreign_keys.php
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

$db = $conn->real_escape_string($local['db']);

function fkExists($conn, $db, $table, $name)
{
    $table = $conn->real_escape_string($table);
    $name  = $conn->real_escape_string($name);
    $r = $conn->query("
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = '$db' AND TABLE_NAME = '$table'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = '$name'
    ");
    return $r && $r->num_rows > 0;
}

$done = 0;
$skipped = 0;

// 1) personnel_programs.personnel_id → personnel.id
if (!fkExists($conn, $db, 'personnel_programs', 'fk_pp_personnel')) {
    $ok = $conn->query("
        ALTER TABLE personnel_programs
        ADD CONSTRAINT fk_pp_personnel
        FOREIGN KEY (personnel_id) REFERENCES personnel(id) ON DELETE CASCADE ON UPDATE CASCADE
    ");
    if (!$ok) {
        fwrite(STDERR, "Error fk_pp_personnel: " . $conn->error . "\n");
        $conn->close();
        exit(1);
    }
    $done++;
} else {
    $skipped++;
}

// 2) personnel_programs.program_id → programs.id
if (!fkExists($conn, $db, 'personnel_programs', 'fk_pp_program')) {
    $ok = $conn->query("
        ALTER TABLE personnel_programs
        ADD CONSTRAINT fk_pp_program
        FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE ON UPDATE CASCADE
    ");
    if (!$ok) {
        fwrite(STDERR, "Error fk_pp_program: " . $conn->error . "\n");
        $conn->close();
        exit(1);
    }
    $done++;
} else {
    $skipped++;
}

// 3) personnel.user_uid → user.uid (RESTRICT = ไม่ลบ user ถ้ามี personnel ผูกอยู่)
if (!fkExists($conn, $db, 'personnel', 'fk_personnel_user')) {
    $ok = $conn->query("
        ALTER TABLE personnel
        ADD CONSTRAINT fk_personnel_user
        FOREIGN KEY (user_uid) REFERENCES user(uid) ON DELETE RESTRICT ON UPDATE CASCADE
    ");
    if (!$ok) {
        fwrite(STDERR, "Error fk_personnel_user: " . $conn->error . "\n");
        $conn->close();
        exit(1);
    }
    $done++;
} else {
    $skipped++;
}

// 4) news.author_id → user.uid (SET NULL = ข่าวยังอยู่ ถ้าลบ user)
if (!fkExists($conn, $db, 'news', 'fk_news_author')) {
    $ok = $conn->query("
        ALTER TABLE news
        ADD CONSTRAINT fk_news_author
        FOREIGN KEY (author_id) REFERENCES user(uid) ON DELETE SET NULL ON UPDATE CASCADE
    ");
    if (!$ok) {
        fwrite(STDERR, "Error fk_news_author: " . $conn->error . "\n");
        $conn->close();
        exit(1);
    }
    $done++;
} else {
    $skipped++;
}

echo "OK: Foreign keys added ($done new, $skipped already existed).\n";
echo "  - personnel_programs → personnel, programs (ON DELETE CASCADE)\n";
echo "  - personnel.user_uid → user.uid (ON DELETE RESTRICT)\n";
echo "  - news.author_id → user.uid (ON DELETE SET NULL)\n";
$conn->close();
exit(0);
