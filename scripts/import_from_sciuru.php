<?php
/**
 * Import News from sci.uru.ac.th to Database
 * Uses external image links instead of downloading images
 * Simple standalone script using mysqli
 * 
 * Usage: php import_from_sciuru.php
 */

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'newscience';

// Connect to database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

echo "Database connected successfully!\n\n";

// Function to fetch web page
function fetchPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode == 200 ? $response : null;
}

// Function to parse Thai date
function parseThaiDate($dateStr) {
    $thaiMonths = [
        'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3, 'เมษายน' => 4,
        'พฤษภาคม' => 5, 'มิถุนายน' => 6, 'กรกฎาคม' => 7, 'สิงหาคม' => 8,
        'กันยายน' => 9, 'ตุลาคม' => 10, 'พฤศจิกายน' => 11, 'ธันวาคม' => 12
    ];
    
    if (preg_match('/(\d+)\s+(\S+)\s+(\d{4})/', $dateStr, $matches)) {
        $day = (int)$matches[1];
        $month = $thaiMonths[$matches[2]] ?? 1;
        $year = (int)$matches[3];
        
        if ($year > 2500) {
            $year -= 543;
        }
        
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
    
    return null;
}

// Function to generate slug
function generateSlug($title, $mysqli) {
    // Simple slugify - replace non-alphanumeric with dash
    $slug = preg_replace('/[^a-zA-Z0-9\x{0E00}-\x{0E7F}]+/u', '-', $title);
    $slug = trim($slug, '-');
    $slug = mb_strtolower($slug);
    
    if (empty($slug) || mb_strlen($slug) < 3) {
        $slug = 'news-' . time() . '-' . rand(1000, 9999);
    }
    
    // Limit length
    if (mb_strlen($slug) > 200) {
        $slug = mb_substr($slug, 0, 200);
    }
    
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM news WHERE slug = ?");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            break;
        }
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

echo "=======================================================\n";
echo "Import News from sci.uru.ac.th\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "=======================================================\n\n";

$baseUrl = 'https://sci.uru.ac.th';
$newsData = [];

// Step 1: Get all news from listing pages
echo "=== Fetching News Listing ===\n";

$page = 1;
$maxPages = 15;

while ($page <= $maxPages) {
    $url = ($page === 1) ? "$baseUrl/news" : "$baseUrl/news?page=$page";
    echo "Page $page... ";
    
    $html = fetchPage($url);
    if (!$html) {
        echo "Failed\n";
        break;
    }
    
    // Parse HTML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $newsLinks = $xpath->query("//a[contains(@href, '/news/')]");
    
    $foundNew = false;
    $pageNews = 0;
    
    foreach ($newsLinks as $link) {
        $href = $link->getAttribute('href');
        if (preg_match('/\/news\/(\d+)$/', $href, $matches)) {
            $newsId = (int)$matches[1];
            
            if (!isset($newsData[$newsId])) {
                $title = trim($link->textContent);
                
                // Skip navigation links
                $skipTitles = ['แสดงทั้งหมด', 'อ่านต่อ', 'ดูเพิ่มเติม', 'แสดงป้ายประชาสัมพันธ์', ''];
                
                if (mb_strlen($title) > 5 && !in_array($title, $skipTitles)) {
                    $newsData[$newsId] = [
                        'id' => $newsId,
                        'title' => $title,
                        'date' => null,
                        'image_url' => "$baseUrl/image/getimage/$newsId"
                    ];
                    $foundNew = true;
                    $pageNews++;
                }
            }
        }
    }
    
    echo "$pageNews new\n";
    
    if (!$foundNew && $page > 1) {
        break;
    }
    
    $page++;
    usleep(200000);
}

echo "\nTotal: " . count($newsData) . " news\n\n";

// Step 2: Get dates from individual pages
echo "=== Fetching Dates ===\n";

$count = 0;
$total = count($newsData);

foreach ($newsData as $newsId => &$news) {
    $count++;
    echo "[$count/$total] ID $newsId: ";
    
    $html = fetchPage("$baseUrl/news/$newsId");
    if ($html && preg_match('/(\d+\s+(มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตุลาคม|พฤศจิกายน|ธันวาคม)\s+25\d{2})/', $html, $matches)) {
        $news['date'] = parseThaiDate($matches[1]);
        echo $news['date'] . "\n";
    } else {
        echo "No date\n";
    }
    
    usleep(100000);
}

// Step 3: Import to database
echo "\n=== Importing to Database ===\n";

$imported = 0;
$skipped = 0;
$errors = 0;

// Sort by ID (oldest first)
ksort($newsData);

// Prepare insert statement
$insertStmt = $mysqli->prepare("
    INSERT INTO news (title, slug, content, excerpt, featured_image, status, author_id, view_count, published_at, created_at, updated_at) 
    VALUES (?, ?, ?, ?, ?, 'published', 1, 0, ?, NOW(), NOW())
");

foreach ($newsData as $news) {
    // Check if already exists by image URL
    $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM news WHERE featured_image LIKE ?");
    $likePattern = "%getimage/{$news['id']}%";
    $checkStmt->bind_param('s', $likePattern);
    $checkStmt->execute();
    $checkStmt->bind_result($exists);
    $checkStmt->fetch();
    $checkStmt->close();
    
    if ($exists > 0) {
        echo "- Skip {$news['id']}: exists\n";
        $skipped++;
        continue;
    }
    
    $slug = generateSlug($news['title'], $mysqli);
    $content = '<p>' . htmlspecialchars($news['title']) . '</p>';
    $excerpt = mb_substr($news['title'], 0, 200);
    $publishedAt = $news['date'] ?? date('Y-m-d');
    
    $insertStmt->bind_param('ssssss', 
        $news['title'],
        $slug,
        $content,
        $excerpt,
        $news['image_url'],
        $publishedAt
    );
    
    if ($insertStmt->execute()) {
        $imported++;
        echo "+ " . mb_substr($news['title'], 0, 40) . "...\n";
    } else {
        $errors++;
        echo "! Error ID {$news['id']}: " . $mysqli->error . "\n";
    }
}

$insertStmt->close();

// Get final count
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM news");
$row = $result->fetch_assoc();
$totalInDb = $row['cnt'];

$mysqli->close();

echo "\n=======================================================\n";
echo "COMPLETE\n";
echo "=======================================================\n";
echo "Imported: $imported\n";
echo "Skipped: $skipped\n";
echo "Errors: $errors\n";
echo "Total in DB: $totalInDb\n";
echo "Done at: " . date('Y-m-d H:i:s') . "\n";
