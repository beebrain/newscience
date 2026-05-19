<?php

/**
 * ส่งไฟล์ CV AI โดยตรง (ไม่ผ่าน CodeIgniter router — ใช้เมื่อ IIS/route มีปัญหา)
 *
 * URL: …/public/cv-ai-serve.php?f={storedName}
 * ตัวอย่าง: cv-ai-serve.php?f=ab79191249151d2742b9c4bd7b9fd0d5.pdf
 */
declare(strict_types=1);

$name = isset($_GET['f']) ? basename((string) $_GET['f']) : '';
if ($name === '' || ! preg_match('/^[a-f0-9]{32}\.(pdf|doc|docx|jpg|jpeg|png|gif|txt)$/i', $name)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found';

    exit;
}

$root = dirname(__DIR__);
$dirs = [
    $root . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR,
    $root . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR,
    __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR,
];

$path = null;
foreach ($dirs as $dir) {
    $candidate = $dir . $name;
    $resolved  = realpath($candidate);
    if ($resolved !== false && is_file($resolved) && is_readable($resolved)) {
        $path = $resolved;

        break;
    }
}

if ($path === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found';

    exit;
}

$ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$mime = match ($ext) {
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg', 'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'txt'  => 'text/plain; charset=UTF-8',
    default => 'application/octet-stream',
};

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $name) . '"');
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
