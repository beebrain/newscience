<?php
/**
 * Categorize News Articles Script
 * วิเคราะห์เนื้อหาข่าวและจัดหมวดหมู่อัตโนมัติ
 * 
 * Usage:
 *   php scripts/categorize_news.php
 *   หรือเรียกผ่าน browser: /scripts/categorize_news.php (ต้อง login admin)
 */

// Bootstrap CodeIgniter
$rootPath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
define('FCPATH', $rootPath . 'public' . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';

use CodeIgniter\Boot;
Boot::bootWeb($paths);

use App\Models\NewsModel;

// Check if running via CLI or web
$isCli = is_cli();

if (!$isCli) {
    // Web access - require admin auth
    $session = session();
    if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        echo "Access denied. Please login as admin.";
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
}

$newsModel = new NewsModel();

echo $isCli ? "" : "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>จัดหมวดหมู่ข่าว</title></head><body><pre>";
echo "=== จัดหมวดหมู่ข่าวอัตโนมัติ ===\n\n";

// Get all news
$allNews = $newsModel->findAll();
$total = count($allNews);

if ($total === 0) {
    echo "ไม่พบข่าวในฐานข้อมูล\n";
    exit;
}

echo "พบข่าวทั้งหมด: {$total} รายการ\n\n";

$updated = 0;
$unchanged = 0;
$stats = [
    'general' => 0,
    'student_activity' => 0,
    'research_grant' => 0
];

foreach ($allNews as $news) {
    $currentCategory = $news['category'] ?? 'general';
    $suggestedCategory = $newsModel->suggestCategory($news);
    
    if ($currentCategory !== $suggestedCategory) {
        $newsModel->update($news['id'], ['category' => $suggestedCategory]);
        $updated++;
        
        echo "[อัปเดต] ID: {$news['id']} - " . mb_substr($news['title'], 0, 50) . "...\n";
        echo "  จาก: {$currentCategory} → เป็น: {$suggestedCategory}\n\n";
    } else {
        $unchanged++;
    }
    
    $stats[$suggestedCategory]++;
}

echo "\n=== สรุปผลการจัดหมวดหมู่ ===\n";
echo "ข่าวทั้งหมด: {$total} รายการ\n";
echo "อัปเดต: {$updated} รายการ\n";
echo "ไม่เปลี่ยนแปลง: {$unchanged} รายการ\n\n";
echo "การกระจายตามหมวดหมู่:\n";
echo "  - ทั่วไป (general): {$stats['general']} รายการ\n";
echo "  - กิจกรรมนักศึกษา (student_activity): {$stats['student_activity']} รายการ\n";
echo "  - วิจัย/ทุน (research_grant): {$stats['research_grant']} รายการ\n";
echo "\nเสร็จสิ้น!\n";

echo $isCli ? "" : "</pre></body></html>";
