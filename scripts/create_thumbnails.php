<?php
/**
 * Create Thumbnails Script (Standalone)
 *
 * - สร้าง thumbnail ใหม่ โดยไม่คงรายละเอียดเต็มของรูปต้นฉบับ
 * - ถ้ารูปใหญ่กว่า 1 MB จะลดขนาด/คุณภาพให้เหลือน้อยกว่า 1 MB
 * - สคริปต์แยก ไม่พึ่งพา CodeIgniter หรือโปรเจกต์
 *
 * Usage:
 *   php create_thumbnails.php [source_dir] [output_dir] [max_width] [max_height] [max_bytes]
 *
 * Example:
 *   php create_thumbnails.php
 *   php create_thumbnails.php ../public/newsimages ./thumbnails 800 800 1048576
 */

// --- Config (แก้ได้ตามต้องการ) ---
$BASE_DIR = dirname(__DIR__);
$DEFAULT_SOURCE = $BASE_DIR . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'newsimages';
$DEFAULT_OUTPUT = $BASE_DIR . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'thumbnails';
$MAX_WIDTH   = 800;
$MAX_HEIGHT  = 800;
$MAX_BYTES   = 1024 * 1024;  // 1 MB
$JPEG_QUALITY = 75;          // คุณภาพ JPEG (ต่ำลง = ไฟล์เล็กลง ไม่คงรายละเอียดเต็ม)
$PNG_QUALITY  = 6;           // การบีบอัด PNG (0-9, สูง = เล็กลง)

// --- Parse CLI ---
$sourceDir = $argv[1] ?? $DEFAULT_SOURCE;
$outputDir = $argv[2] ?? $DEFAULT_OUTPUT;
$maxW      = isset($argv[3]) ? (int) $argv[3] : $MAX_WIDTH;
$maxH      = isset($argv[4]) ? (int) $argv[4] : $MAX_HEIGHT;
$maxBytes  = isset($argv[5]) ? (int) $argv[5] : $MAX_BYTES;

$sourceDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sourceDir);
$outputDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $outputDir);

if (!is_dir($sourceDir)) {
    fwrite(STDERR, "Error: Source directory not found: {$sourceDir}\n");
    exit(1);
}

if (!function_exists('imagecreatetruecolor')) {
    fwrite(STDERR, "Error: PHP GD extension is required.\n");
    exit(1);
}

echo "=== Create Thumbnails ===\n";
echo "Source: {$sourceDir}\n";
echo "Output: {$outputDir}\n";
echo "Max size: {$maxW}x{$maxH} px, max file size: " . round($maxBytes / 1024) . " KB\n\n";

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "Created output directory.\n";
}

$extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$count = 0;
$skipped = 0;
$errors = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $extensions, true)) continue;

    $srcPath = $file->getPathname();
    $relPath = substr($srcPath, strlen($sourceDir) + 1);
    $outPath = $outputDir . DIRECTORY_SEPARATOR . $relPath;
    $outDir  = dirname($outPath);

    if (!is_dir($outDir)) {
        mkdir($outDir, 0755, true);
    }

    $sizeBytes = filesize($srcPath);
    $createThumb = true;

    if ($createThumb) {
        $result = createThumbnail($srcPath, $outPath, $maxW, $maxH, $maxBytes, $JPEG_QUALITY, $PNG_QUALITY);
        if ($result === true) {
            $newSize = file_exists($outPath) ? filesize($outPath) : 0;
            echo "OK: " . $relPath . " (" . round($sizeBytes / 1024) . " KB -> " . round($newSize / 1024) . " KB)\n";
            $count++;
        } elseif ($result === 'skip') {
            $skipped++;
        } else {
            $errors[] = $relPath . ': ' . $result;
        }
    }
}

echo "\n--- Done ---\n";
echo "Processed: {$count}, Skipped: {$skipped}\n";
if (!empty($errors)) {
    echo "Errors:\n";
    foreach ($errors as $e) echo "  - {$e}\n";
}

/**
 * Create thumbnail. Reduce dimensions and quality so image does not retain full detail.
 * If result > maxBytes, reduce quality or dimensions until under maxBytes.
 */
function createThumbnail($srcPath, $outPath, $maxW, $maxH, $maxBytes, $jpegQuality, $pngQuality) {
    $img = loadImage($srcPath);
    if (!$img) return 'Could not load image.';

    $w = imagesx($img);
    $h = imagesy($img);
    if ($w <= 0 || $h <= 0) {
        imagedestroy($img);
        return 'Invalid dimensions.';
    }

    $ext = strtolower(pathinfo($outPath, PATHINFO_EXTENSION));
    if ($ext === 'jpg') $ext = 'jpeg';

    $scale = min($maxW / $w, $maxH / $h, 1.0);
    $newW = (int) round($w * $scale);
    $newH = (int) round($h * $scale);
    if ($newW < 1) $newW = 1;
    if ($newH < 1) $newH = 1;

    $thumb = imagecreatetruecolor($newW, $newH);
    if (!$thumb) {
        imagedestroy($img);
        return 'Could not create thumbnail.';
    }

    if (!imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $w, $h)) {
        imagedestroy($img);
        imagedestroy($thumb);
        return 'Resample failed.';
    }
    imagedestroy($img);

    $q = $jpegQuality;
    $pngQ = $pngQuality;
    $maxIter = 15;
    $iter = 0;

    while ($iter < $maxIter) {
        $iter++;
        $ok = false;
        if ($ext === 'jpeg' || $ext === 'jpg') {
            $ok = imagejpeg($thumb, $outPath, $q);
        } elseif ($ext === 'png') {
            $ok = imagepng($thumb, $outPath, $pngQ);
        } elseif ($ext === 'gif') {
            $ok = imagegif($thumb, $outPath);
        } elseif ($ext === 'webp' && function_exists('imagewebp')) {
            $ok = imagewebp($thumb, $outPath, $q);
        } else {
            $ok = imagejpeg($thumb, $outPath, $q);
        }

        if (!$ok) {
            imagedestroy($thumb);
            return 'Could not write file.';
        }

        $outSize = @filesize($outPath);
        if ($outSize !== false && $outSize <= $maxBytes) {
            imagedestroy($thumb);
            return true;
        }

        if ($ext === 'jpeg' || $ext === 'jpg' || ($ext === 'webp' && function_exists('imagewebp'))) {
            $q -= 8;
            if ($q < 25) {
                $q = 25;
                if ($newW > 80 && $newH > 80) {
                    $nextW = (int) ($newW * 0.8);
                    $nextH = (int) ($newH * 0.8);
                    $tmp = imagecreatetruecolor($nextW, $nextH);
                    if ($tmp) {
                        imagecopyresampled($tmp, $thumb, 0, 0, 0, 0, $nextW, $nextH, $newW, $newH);
                        imagedestroy($thumb);
                        $thumb = $tmp;
                        $newW = $nextW;
                        $newH = $nextH;
                        $q = $jpegQuality;
                    }
                }
            }
        } elseif ($ext === 'png') {
            $pngQ = min(9, $pngQ + 1);
            if ($pngQ >= 9 && $newW > 80 && $newH > 80) {
                $nextW = (int) ($newW * 0.8);
                $nextH = (int) ($newH * 0.8);
                $tmp = imagecreatetruecolor($nextW, $nextH);
                if ($tmp) {
                    imagecopyresampled($tmp, $thumb, 0, 0, 0, 0, $nextW, $nextH, $newW, $newH);
                    imagedestroy($thumb);
                    $thumb = $tmp;
                    $newW = $nextW;
                    $newH = $nextH;
                    $pngQ = $pngQuality;
                }
            }
        } else {
            imagedestroy($thumb);
            return true;
        }
    }

    imagedestroy($thumb);
    return true;
}

function loadImage($path) {
    $info = @getimagesize($path);
    if (!$info) return null;
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            return @imagecreatefromjpeg($path);
        case IMAGETYPE_PNG:
            return @imagecreatefrompng($path);
        case IMAGETYPE_GIF:
            return @imagecreatefromgif($path);
        case IMAGETYPE_WEBP:
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
        default:
            return null;
    }
}
