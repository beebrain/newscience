<?php

/**
 * แก้ FK ที่ชี้ไปตาราง user backup (user_backup_*) ให้ชี้กลับไปที่ตาราง user
 * สาเหตุ: สคริปต์ clone user ใช้ RENAME TABLE user → user_backup_... ทำให้ MySQL
 *         อัปเดต FK ที่อ้างอิง user ให้ชี้ไปชื่อตารางใหม่ (backup)
 * Run: php scripts/run_fix_personnel_user_fk.php
 *
 * ใช้ค่าเชื่อมต่อจาก DB_HOST, DB_USER, DB_PASS, DB_NAME (ถ้ามี) ไม่ก็ใช้ค่าด้านล่าง
 */

$local = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'db'   => getenv('DB_NAME') ?: 'newscience',
];

$conn = @new mysqli($local['host'], $local['user'], $local['pass'], $local['db']);
if ($conn->connect_error) {
    fwrite(STDERR, "Connection failed: " . $conn->connect_error . "\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

$db = $conn->real_escape_string($local['db']);

function fkReferencesTable($conn, $db, $table, $constraintName, $referencedTable)
{
    $table = $conn->real_escape_string($table);
    $name  = $conn->real_escape_string($constraintName);
    $ref   = $conn->real_escape_string($referencedTable);
    $r = $conn->query("
        SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = '$db' AND TABLE_NAME = '$table'
        AND CONSTRAINT_NAME = '$name' AND REFERENCED_TABLE_NAME = '$ref'
    ");
    return $r && $r->num_rows > 0;
}

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

$fixed = 0;

// 1) personnel.user_uid → ต้องชี้ไป user(uid)
if (fkExists($conn, $db, 'personnel', 'fk_personnel_user')) {
    if (!fkReferencesTable($conn, $db, 'personnel', 'fk_personnel_user', 'user')) {
        if ($conn->query("ALTER TABLE `personnel` DROP FOREIGN KEY `fk_personnel_user`")) {
            if ($conn->query("
                ALTER TABLE `personnel`
                ADD CONSTRAINT `fk_personnel_user`
                FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) ON DELETE RESTRICT ON UPDATE CASCADE
            ")) {
                echo "Fixed: personnel.fk_personnel_user now references user(uid)\n";
                $fixed++;
            } else {
                fwrite(STDERR, "Error re-adding fk_personnel_user: " . $conn->error . "\n");
                $conn->close();
                exit(1);
            }
        } else {
            fwrite(STDERR, "Error dropping fk_personnel_user: " . $conn->error . "\n");
            $conn->close();
            exit(1);
        }
    } else {
        echo "personnel.fk_personnel_user already references user (OK)\n";
    }
} else {
    // ไม่มี FK อาจยังไม่ได้ add — ให้ add
    if ($conn->query("
        ALTER TABLE `personnel`
        ADD CONSTRAINT `fk_personnel_user`
        FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) ON DELETE RESTRICT ON UPDATE CASCADE
    ")) {
        echo "Added: personnel.fk_personnel_user → user(uid)\n";
        $fixed++;
    } else {
        fwrite(STDERR, "Error adding fk_personnel_user: " . $conn->error . "\n");
    }
}

// 2) news.author_id — ชี้ไป user(uid) (ถ้าถูกลบหรือชี้ backup ให้ add กลับ)
$rNews = $conn->query("SHOW TABLES LIKE 'news'");
$newsTableExists = $rNews && $rNews->num_rows > 0;
$rCol = $newsTableExists ? $conn->query("SHOW COLUMNS FROM `news` LIKE 'author_id'") : null;
$newsHasAuthorId = $rCol && $rCol->num_rows > 0;

if ($newsTableExists && $newsHasAuthorId) {
    $savedMode = null;
    $rMode = $conn->query("SELECT @@SESSION.sql_mode AS m");
    if ($rMode && $rMode->num_rows > 0) {
        $savedMode = $rMode->fetch_assoc()['m'] ?? null;
    }
    if ($savedMode !== null) {
        $conn->query("SET SESSION sql_mode = ''");
    }

    // ทำความสะอาด orphan: author_id ที่ไม่มีใน user
    $conn->query("UPDATE `news` SET `author_id` = NULL WHERE `author_id` IS NOT NULL AND `author_id` NOT IN (SELECT `uid` FROM `user`)");
    $orphaned = $conn->affected_rows;
    if ($orphaned > 0) {
        echo "Set author_id=NULL for {$orphaned} news row(s) whose author is not in user table.\n";
    }
    // แก้ค่า datetime ไม่ถูกต้อง (0000-00-00) เพื่อให้ ALTER TABLE ผ่านใน strict mode (ตอนนี้ SESSION sql_mode = '')
    $conn->query("UPDATE `news` SET `published_at` = NULL WHERE `published_at` IN ('0000-00-00', '0000-00-00 00:00:00')");
    $fixedDates = $conn->affected_rows;
    if ($fixedDates > 0) {
        echo "Set published_at=NULL for {$fixedDates} invalid date row(s) in news.\n";
    }

    if (fkExists($conn, $db, 'news', 'fk_news_author')) {
        if (!fkReferencesTable($conn, $db, 'news', 'fk_news_author', 'user')) {
            if ($conn->query("ALTER TABLE `news` DROP FOREIGN KEY `fk_news_author`")) {
                if ($conn->query("
                    ALTER TABLE `news`
                    ADD CONSTRAINT `fk_news_author`
                    FOREIGN KEY (`author_id`) REFERENCES `user`(`uid`) ON DELETE SET NULL ON UPDATE CASCADE
                ")) {
                    echo "Fixed: news.fk_news_author now references user(uid)\n";
                    $fixed++;
                } else {
                    fwrite(STDERR, "Error re-adding fk_news_author: " . $conn->error . "\n");
                }
            } else {
                fwrite(STDERR, "Error dropping fk_news_author: " . $conn->error . "\n");
            }
        } else {
            echo "news.fk_news_author already references user (OK)\n";
        }
    } else {
        if ($conn->query("
            ALTER TABLE `news`
            ADD CONSTRAINT `fk_news_author`
            FOREIGN KEY (`author_id`) REFERENCES `user`(`uid`) ON DELETE SET NULL ON UPDATE CASCADE
        ")) {
            echo "Added: news.fk_news_author → user(uid)\n";
            $fixed++;
        } else {
            fwrite(STDERR, "Error adding fk_news_author: " . $conn->error . "\n");
        }
    }

    if ($savedMode !== null) {
        $conn->query("SET SESSION sql_mode = '" . $conn->real_escape_string($savedMode) . "'");
    }
}

$conn->close();
echo "\nDone. Fixed {$fixed} foreign key(s).\n";
exit(0);
