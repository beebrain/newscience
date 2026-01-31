<?php
/**
 * Sync news from local DB to remote server
 * Run: php scripts/sync_news_to_server.php
 */

$local = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$remote = [
    'host' => '49.231.30.18',
    'user' => 'root',
    'pass' => 'admin@SCI@2026',
    'db'   => 'newscience',
];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$localConn = new mysqli($local['host'], $local['user'], $local['pass'], $local['db']);
$localConn->set_charset('utf8mb4');

$remoteConn = new mysqli($remote['host'], $remote['user'], $remote['pass'], $remote['db']);
$remoteConn->set_charset('utf8mb4');

echo "Connected to local and remote.\n";

// Truncate remote news tables
$remoteConn->query("SET FOREIGN_KEY_CHECKS = 0");
$remoteConn->query("TRUNCATE TABLE news_images");
$remoteConn->query("TRUNCATE TABLE news");
$remoteConn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Truncated remote news tables.\n";

// Fetch all news from local
$result = $localConn->query("SELECT * FROM news ORDER BY id");
$news = $result->fetch_all(MYSQLI_ASSOC);
$total = count($news);
echo "Fetched $total news from local.\n";

$stmt = $remoteConn->prepare("INSERT INTO news (id, title, slug, content, excerpt, status, category, featured_image, author_id, view_count, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$synced = 0;
foreach ($news as $row) {
    $content = $row['content'] ?? '';
    $excerpt = $row['excerpt'] ?? '';
    $featured_image = $row['featured_image'] ?? null;
    $author_id = $row['author_id'] ?? null;
    $view_count = (int)($row['view_count'] ?? 0);
    $published_at = ($row['published_at'] ?? null) && $row['published_at'] !== '0000-00-00 00:00:00' ? $row['published_at'] : ($row['created_at'] ?? date('Y-m-d H:i:s'));
    $created_at = ($row['created_at'] ?? null) && $row['created_at'] !== '0000-00-00 00:00:00' ? $row['created_at'] : date('Y-m-d H:i:s');
    $updated_at = ($row['updated_at'] ?? null) && $row['updated_at'] !== '0000-00-00 00:00:00' ? $row['updated_at'] : date('Y-m-d H:i:s');
    $stmt->bind_param(
        'isssssssiisss',
        $row['id'],
        $row['title'],
        $row['slug'],
        $content,
        $excerpt,
        $row['status'],
        $row['category'],
        $featured_image,
        $author_id,
        $view_count,
        $published_at,
        $created_at,
        $updated_at
    );
    $stmt->execute();
    $synced++;
    if ($synced % 50 === 0) {
        echo "Synced $synced / $total\n";
    }
}
$stmt->close();
echo "News synced: $synced\n";

// Sync news_images if table exists and has data
$imgResult = $localConn->query("SELECT * FROM news_images ORDER BY id");
if ($imgResult && $imgResult->num_rows > 0) {
    $images = $imgResult->fetch_all(MYSQLI_ASSOC);
    $imgStmt = $remoteConn->prepare("INSERT INTO news_images (id, news_id, image_path, caption, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $syncedImg = 0;
    foreach ($images as $img) {
        $imgStmt->bind_param('iissis', $img['id'], $img['news_id'], $img['image_path'], $img['caption'] ?? '', $img['sort_order'] ?? 0, $img['created_at'] ?? null);
        $imgStmt->execute();
        $syncedImg++;
    }
    $imgStmt->close();
    echo "News images synced: $syncedImg\n";
}

$localConn->close();
$remoteConn->close();
echo "Done.\n";
