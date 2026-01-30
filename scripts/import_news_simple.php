<?php
/**
 * Simple News Import Script
 * บันทึกข้อมูลข่าวจาก JSON ลงฐานข้อมูล (ใช้ database connection โดยตรง)
 * 
 * Usage:
 *   php scripts/import_news_simple.php
 */

// Database configuration
$dbConfig = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'newscience', // เปลี่ยนตาม database ของคุณ
    'charset'  => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];

// Connect to database
try {
    $mysqli = new mysqli($dbConfig['hostname'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
    
    if ($mysqli->connect_error) {
        die("❌ Connection failed: " . $mysqli->connect_error . "\n");
    }
    
    $mysqli->set_charset($dbConfig['charset']);
    echo "✓ Connected to database successfully\n\n";
    
} catch (Exception $e) {
    die("❌ Database connection error: " . $e->getMessage() . "\n");
}

// Load JSON data
$dataFile = __DIR__ . '/scraped_data/all_content.json';

if (!file_exists($dataFile)) {
    die("❌ Error: Data file not found: {$dataFile}\n   Please run scrape_all_content.py first.\n");
}

echo "📂 Loading data from: {$dataFile}\n";
$jsonData = file_get_contents($dataFile);
$data = json_decode($jsonData, true);

if (!$data || !isset($data['news'])) {
    die("❌ Error: Invalid data format or no news found\n");
}

$newsItems = $data['news'];
$total = count($newsItems);

echo "📰 Found {$total} news articles\n\n";

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/../public/uploads/news';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Statistics
$stats = [
    'total' => $total,
    'imported' => 0,
    'skipped' => 0,
    'errors' => 0,
    'images_copied' => 0
];

// Helper function to create slug
function createSlug($text) {
    // Remove special characters
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    // Convert to lowercase
    $text = mb_strtolower($text);
    // Replace spaces with hyphens
    $text = preg_replace('/[\s]+/', '-', $text);
    // Remove multiple hyphens
    $text = preg_replace('/-+/', '-', $text);
    // Trim hyphens
    return trim($text, '-');
}

// Helper function to copy image
function copyImage($sourcePath, $uploadDir, $prefix = '') {
    if (empty($sourcePath)) {
        return null;
    }
    
    // Handle relative paths
    $fullSourcePath = null;
    
    // Try different path combinations
    $possiblePaths = [
        __DIR__ . '/../' . $sourcePath,  // From project root
        __DIR__ . '/' . $sourcePath,     // From scripts directory
        $sourcePath,                      // Absolute path
        str_replace('scraped_data/', __DIR__ . '/scraped_data/', $sourcePath),
        str_replace('scripts/scraped_data/', __DIR__ . '/scraped_data/', $sourcePath),
    ];
    
    foreach ($possiblePaths as $path) {
        $realPath = realpath($path);
        if ($realPath && file_exists($realPath)) {
            $fullSourcePath = $realPath;
            break;
        }
    }
    
    if (!$fullSourcePath || !file_exists($fullSourcePath)) {
        return null;
    }
    
    // Get file extension
    $ext = pathinfo($fullSourcePath, PATHINFO_EXTENSION);
    if (empty($ext)) {
        $ext = 'jpg';
    }
    
    // Generate filename (limit prefix length to avoid Windows path length issues)
    $prefixSlug = $prefix ? createSlug($prefix) : '';
    if (mb_strlen($prefixSlug) > 50) {
        $prefixSlug = mb_substr($prefixSlug, 0, 50);
    }
    $filename = ($prefixSlug ? $prefixSlug . '-' : '') . uniqid() . '.' . $ext;
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    // Limit total filename length (Windows has 260 char path limit)
    if (strlen($filename) > 200) {
        $filename = substr($filename, 0, 200) . '.' . $ext;
    }
    
    $destPath = $uploadDir . '/' . $filename;
    
    if (copy($fullSourcePath, $destPath)) {
        return $filename;
    }
    
    return null;
}

// Import each news item
echo "Starting import...\n";
echo str_repeat("-", 60) . "\n";

foreach ($newsItems as $index => $news) {
    $current = $index + 1;
    $title = trim($news['title'] ?? '');
    
    if (empty($title)) {
        $stats['skipped']++;
        echo "[{$current}/{$total}] ⚠️  Skipped: Missing title\n";
        continue;
    }
    
    echo "[{$current}/{$total}] " . mb_substr($title, 0, 50) . "...\n";
    
    // Generate slug
    $slug = createSlug($news['slug'] ?? $title);
    if (empty($slug)) {
        $slug = 'news-' . time() . '-' . $current;
    }
    
    // Check if slug exists
    $checkStmt = $mysqli->prepare("SELECT id FROM news WHERE slug = ?");
    $checkStmt->bind_param("s", $slug);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Make slug unique
        $baseSlug = $slug;
        $counter = 1;
        do {
            $slug = $baseSlug . '-' . $counter;
            $checkStmt->bind_param("s", $slug);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $counter++;
        } while ($result->num_rows > 0);
    }
    $checkStmt->close();
    
    // Prepare data
    $content = $news['content'] ?? '';
    $excerpt = $news['excerpt'] ?? mb_substr($title, 0, 200);
    $status = $news['status'] ?? 'published';
    
    // Handle published date
    $publishedAt = date('Y-m-d H:i:s');
    if (!empty($news['published_at'])) {
        try {
            $date = strtotime($news['published_at']);
            if ($date !== false) {
                $publishedAt = date('Y-m-d H:i:s', $date);
            }
        } catch (Exception $e) {
            // Use current date
        }
    }
    
    // Handle featured image
    $featuredImage = null;
    if (!empty($news['featured_image'])) {
        $featuredImage = copyImage($news['featured_image'], $uploadDir, $title);
        if ($featuredImage) {
            $stats['images_copied']++;
        }
    } elseif (!empty($news['images']) && is_array($news['images']) && count($news['images']) > 0) {
        $featuredImage = copyImage($news['images'][0], $uploadDir, $title);
        if ($featuredImage) {
            $stats['images_copied']++;
        }
    }
    
    // Insert news
    $stmt = $mysqli->prepare("
        INSERT INTO news (title, slug, content, excerpt, status, featured_image, published_at, view_count, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
    ");
    
    $stmt->bind_param("sssssss", $title, $slug, $content, $excerpt, $status, $featuredImage, $publishedAt);
    
    if ($stmt->execute()) {
        $newsId = $mysqli->insert_id;
        $stats['imported']++;
        
        // Insert additional images
        if (!empty($news['images']) && is_array($news['images'])) {
            $imageOrder = 0;
            foreach ($news['images'] as $imagePath) {
                // Skip featured image
                if ($imagePath === $news['featured_image']) {
                    continue;
                }
                
                $copiedImage = copyImage($imagePath, $uploadDir, $title . '_' . $imageOrder);
                if ($copiedImage) {
                    $imgStmt = $mysqli->prepare("
                        INSERT INTO news_images (news_id, image_path, sort_order, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    $imgStmt->bind_param("isi", $newsId, $copiedImage, $imageOrder);
                    $imgStmt->execute();
                    $imgStmt->close();
                    
                    $stats['images_copied']++;
                    $imageOrder++;
                }
            }
        }
        
        echo "      ✓ Imported (ID: {$newsId})\n";
    } else {
        $stats['errors']++;
        echo "      ❌ Error: " . $mysqli->error . "\n";
    }
    
    $stmt->close();
}

// Close database connection
$mysqli->close();

// Print summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "Import Summary\n";
echo str_repeat("=", 60) . "\n";
echo "Total News Items:     {$stats['total']}\n";
echo "✓ Successfully Imported: {$stats['imported']}\n";
echo "⊘ Skipped:               {$stats['skipped']}\n";
echo "❌ Errors:                {$stats['errors']}\n";
echo "📷 Images Copied:         {$stats['images_copied']}\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ Import completed!\n";
