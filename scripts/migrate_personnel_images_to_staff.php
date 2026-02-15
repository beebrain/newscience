<?php
/**
 * Migration ครั้งเดียว: ย้ายรูปบุคลากรจาก uploads/personnel → uploads/staff และอัปเดต personnel.image
 *
 * โหมดปกติ (รันบน server):
 *   php scripts/migrate_personnel_images_to_staff.php
 *   คัดลอกไฟล์ + อัปเดต DB ทันที
 *
 * โหมด FTP (เตรียมที่ local แล้วอัปโหลดโฟลเดอร์ + รัน SQL บน server เอง):
 *   php scripts/migrate_personnel_images_to_staff.php --ftp
 *   - คัดลอกไฟล์ไปที่ writable/uploads/staff ในโปรเจกต์ local
 *   - สร้างไฟล์ database/update_personnel_image_to_staff.sql
 *   จากนั้นอัปโหลดโฟลเดอร์ writable/uploads/staff ขึ้น server ผ่าน FTP แล้วรัน SQL บน server
 *
 * กำหนดค่า DB ด้านล่าง (หรือตัวแปรสภาพแวดล้อม) — โหมด --ftp ใช้เฉพาะอ่าน personnel เพื่อสร้าง SQL
 */

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'newscience';

$ftpMode = (isset($argv[1]) && ($argv[1] === '--ftp' || $argv[1] === '-ftp'));

$projectRoot = dirname(__DIR__);
$writableStaff = $projectRoot . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'staff';
$sources = [
    $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'personnel',
    $projectRoot . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'personnel',
];

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset('utf8mb4');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error . "\n");
}

echo $ftpMode
    ? "=== เตรียมโฟลเดอร์ staff + สร้าง SQL สำหรับอัปโหลด FTP ===\n\n"
    : "=== Migrate personnel images to uploads/staff ===\n\n";

// 1) สร้างโฟลเดอร์ปลายทาง
if (!is_dir($writableStaff)) {
    $parent = dirname($writableStaff);
    if (!is_dir($parent)) {
        @mkdir($parent, 0755, true);
    }
    if (!@mkdir($writableStaff, 0755, true)) {
        die("Cannot create directory: $writableStaff\n");
    }
    echo "Created: writable/uploads/staff\n";
}

// 2) ดึงแถวที่ image ยังเป็นรูปแบบเก่า
$res = $mysqli->query("SELECT id, image FROM personnel WHERE image IS NOT NULL AND TRIM(image) != ''");
if (!$res) {
    die("Query failed: " . $mysqli->error . "\n");
}

$toUpdate = [];
while ($row = $res->fetch_assoc()) {
    $current = trim(str_replace('\\', '/', $row['image']));
    $basename = basename($current);
    if ($basename === '') {
        continue;
    }
    $isLegacy = (strpos($current, 'uploads/personnel/') === 0) || (strpos($current, 'personnel/') === 0);
    if ($isLegacy) {
        $toUpdate[] = [
            'id' => (int) $row['id'],
            'image' => $current,
            'basename' => $basename,
            'new_value' => 'staff/' . $basename,
        ];
    }
}
$res->free();

echo "Rows to update (legacy image path): " . count($toUpdate) . "\n";

$copied = 0;
$updated = 0;
$errors = [];
$sqlLines = [];

foreach ($toUpdate as $item) {
    $id = $item['id'];
    $basename = $item['basename'];
    $newValue = $item['new_value'];
    $newValueEsc = $mysqli->real_escape_string($newValue);

    $sourcePath = null;
    foreach ($sources as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $basename;
        if (is_file($path)) {
            $sourcePath = $path;
            break;
        }
        $thumbDir = $dir . DIRECTORY_SEPARATOR . 'thumbs';
        if (is_file($thumbDir . DIRECTORY_SEPARATOR . $basename)) {
            $thumbDest = $writableStaff . DIRECTORY_SEPARATOR . 'thumbs';
            if (!is_dir($thumbDest)) {
                @mkdir($thumbDest, 0755, true);
            }
            $thumbDestPath = $thumbDest . DIRECTORY_SEPARATOR . $basename;
            if (!is_file($thumbDestPath) && @copy($thumbDir . DIRECTORY_SEPARATOR . $basename, $thumbDestPath)) {
                $copied++;
            }
        }
    }
    if ($sourcePath === null) {
        $errors[] = "id=$id: file not found: $basename";
    } else {
        $destPath = $writableStaff . DIRECTORY_SEPARATOR . $basename;
        if (!is_file($destPath) && @copy($sourcePath, $destPath)) {
            $copied++;
        }
    }

    $sqlLines[] = "UPDATE personnel SET image = '$newValueEsc' WHERE id = $id;";

    if (!$ftpMode) {
        $stmt = $mysqli->prepare("UPDATE personnel SET image = ? WHERE id = ?");
        $stmt->bind_param('si', $newValue, $id);
        if ($stmt->execute()) {
            $updated++;
        } else {
            $errors[] = "id=$id: DB update failed";
        }
        $stmt->close();
    }
}

if ($ftpMode && !empty($sqlLines)) {
    $sqlDir = $projectRoot . DIRECTORY_SEPARATOR . 'database';
    if (!is_dir($sqlDir)) {
        @mkdir($sqlDir, 0755, true);
    }
    $sqlFile = $sqlDir . DIRECTORY_SEPARATOR . 'update_personnel_image_to_staff.sql';
    $content = "-- อัปเดต personnel.image เป็น staff/... รันบน production หลังอัปโหลดโฟลเดอร์ writable/uploads/staff ผ่าน FTP\n"
        . "-- Generated by: php scripts/migrate_personnel_images_to_staff.php --ftp\n\n"
        . implode("\n", $sqlLines) . "\n";
    if (file_put_contents($sqlFile, $content) !== false) {
        echo "Created: database/update_personnel_image_to_staff.sql (" . count($sqlLines) . " statements)\n";
    } else {
        echo "Warning: Could not write $sqlFile\n";
    }
}

echo "\n=== สรุป ===\n";
if ($ftpMode) {
    echo "ไฟล์ที่คัดลอก (รวม thumb): $copied\n";
    echo "ขั้นตอนถัดไป:\n";
    echo "  1) อัปโหลดโฟลเดอร์ writable/uploads/staff ขึ้น server ผ่าน FTP ไปที่ writable/uploads/\n";
    echo "  2) บน server รัน SQL: database/update_personnel_image_to_staff.sql (phpMyAdmin หรือ mysql client)\n";
} else {
    echo "อัปเดต path ใน DB: $updated แถว\n";
    echo "ไฟล์ที่คัดลอก (รวม thumb): $copied\n";
}
if (!empty($errors)) {
    echo "ข้อผิดพลาด:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
}
echo "Done.\n";
