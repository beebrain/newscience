<?php
/**
 * ลดฟิลด์ชื่อ personnel เป็น name + name_en เท่านั้น
 * Run: php scripts/run_remove_personnel_name_fields.php
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

// Check if already migrated
$r = $conn->query("SHOW COLUMNS FROM personnel LIKE 'name'");
if ($r && $r->num_rows > 0) {
    $r2 = $conn->query("SHOW COLUMNS FROM personnel LIKE 'first_name'");
    if (!$r2 || $r2->num_rows === 0) {
        echo "OK: personnel already uses name/name_en (old columns removed).\n";
        $conn->close();
        exit(0);
    }
}

// Step 1: Add new columns
$conn->query("ALTER TABLE personnel ADD COLUMN name VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล (ไทย)' AFTER id");
if ($conn->error) {
    fwrite(STDERR, "Error adding name: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}
$conn->query("ALTER TABLE personnel ADD COLUMN name_en VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล (อังกฤษ)' AFTER name");
if ($conn->error) {
    fwrite(STDERR, "Error adding name_en: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

// Step 2: Migrate data
$conn->query("UPDATE personnel SET name = TRIM(CONCAT(IFNULL(title,''), ' ', IFNULL(first_name,''), ' ', IFNULL(last_name,''))), name_en = NULLIF(TRIM(CONCAT(IFNULL(first_name_en,''), ' ', IFNULL(last_name_en,''))), '')");
if ($conn->error) {
    fwrite(STDERR, "Error migrating: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

$conn->query("UPDATE personnel SET name = '' WHERE name IS NULL");
$conn->query("ALTER TABLE personnel MODIFY COLUMN name VARCHAR(255) NOT NULL DEFAULT ''");

// Step 3: Drop old columns
foreach (['title', 'first_name', 'last_name', 'first_name_en', 'last_name_en'] as $col) {
    $r = $conn->query("SHOW COLUMNS FROM personnel LIKE '$col'");
    if ($r && $r->num_rows > 0) {
        $conn->query("ALTER TABLE personnel DROP COLUMN `$col`");
        if ($conn->error) {
            fwrite(STDERR, "Error dropping $col: " . $conn->error . "\n");
            $conn->close();
            exit(1);
        }
    }
}

echo "OK: personnel name fields replaced with name/name_en.\n";
$conn->close();
exit(0);
