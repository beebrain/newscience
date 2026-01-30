<?php
/**
 * Import News Data to Database
 * บันทึกข้อมูลข่าวจาก JSON ลงฐานข้อมูล
 * 
 * Usage:
 *   php scripts/import_news_to_db.php
 *   หรือเรียกผ่าน browser: /scripts/import_news_to_db.php (ต้อง login admin)
 */

// Bootstrap CodeIgniter 4.5+
// Path to the front controller directory
$rootPath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

// Define FCPATH
define('FCPATH', $rootPath . 'public' . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

// Load our paths config file
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();

// Load the framework bootstrap file
require $paths->systemDirectory . '/Boot.php';

// Bootstrap the application
\CodeIgniter\Boot::bootWeb($paths);

use App\Models\NewsModel;
use App\Models\NewsImageModel;
use CodeIgniter\I18n\Time;

class ImportNews
{
    protected $newsModel;
    protected $newsImageModel;
    protected $dataFile;
    protected $stats = [
        'total' => 0,
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0,
        'images_copied' => 0
    ];

    public function __construct()
    {
        $this->newsModel = new NewsModel();
        $this->newsImageModel = new NewsImageModel();
        
        // Path to scraped data
        $this->dataFile = __DIR__ . '/scraped_data/all_content.json';
        
        // Ensure uploads directory exists
        $uploadDir = FCPATH . 'uploads/news';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }

    /**
     * Main import function
     */
    public function run()
    {
        echo "=" . str_repeat("=", 60) . "\n";
        echo "Import News Data to Database\n";
        echo "=" . str_repeat("=", 60) . "\n\n";

        // Check if file exists
        if (!file_exists($this->dataFile)) {
            echo "❌ Error: Data file not found: {$this->dataFile}\n";
            echo "   Please run scrape_all_content.py first to generate data.\n";
            return false;
        }

        // Load JSON data
        echo "📂 Loading data from: {$this->dataFile}\n";
        $jsonData = file_get_contents($this->dataFile);
        $data = json_decode($jsonData, true);

        if (!$data || !isset($data['news'])) {
            echo "❌ Error: Invalid data format or no news found\n";
            return false;
        }

        $newsItems = $data['news'];
        $this->stats['total'] = count($newsItems);

        echo "📰 Found {$this->stats['total']} news articles\n\n";

        // Import each news item
        $progress = 0;
        foreach ($newsItems as $index => $news) {
            $progress++;
            $this->importNewsItem($news, $progress, $this->stats['total']);
        }

        // Print summary
        $this->printSummary();

        return true;
    }

    /**
     * Import a single news item
     */
    protected function importNewsItem($news, $current, $total)
    {
        try {
            // Validate required fields
            if (empty($news['title'])) {
                $this->stats['skipped']++;
                echo "  [{$current}/{$total}] ⚠️  Skipped: Missing title\n";
                return false;
            }

            $title = trim($news['title']);
            echo "  [{$current}/{$total}] Processing: " . mb_substr($title, 0, 50) . "...\n";

            // Generate unique slug
            $slug = $this->generateUniqueSlug($news['slug'] ?? $title);

            // Check if already exists
            $existing = $this->newsModel->where('slug', $slug)->first();
            if ($existing) {
                $this->stats['skipped']++;
                echo "      ⊘ Already exists (slug: {$slug})\n";
                return false;
            }

            // Prepare news data
            $newsData = [
                'title' => $title,
                'slug' => $slug,
                'content' => $news['content'] ?? '',
                'excerpt' => $news['excerpt'] ?? mb_substr($title, 0, 200),
                'status' => $news['status'] ?? 'published',
                'view_count' => 0,
            ];

            // Handle published date
            if (!empty($news['published_at'])) {
                try {
                    $publishedDate = date('Y-m-d H:i:s', strtotime($news['published_at']));
                    $newsData['published_at'] = $publishedDate;
                } catch (Exception $e) {
                    $newsData['published_at'] = date('Y-m-d H:i:s');
                }
            } else {
                $newsData['published_at'] = date('Y-m-d H:i:s');
            }

            // Handle featured image
            $featuredImage = null;
            if (!empty($news['featured_image'])) {
                $featuredImage = $this->copyImage($news['featured_image'], $title);
                if ($featuredImage) {
                    $newsData['featured_image'] = $featuredImage;
                }
            } elseif (!empty($news['images']) && is_array($news['images']) && count($news['images']) > 0) {
                // Use first image as featured
                $featuredImage = $this->copyImage($news['images'][0], $title);
                if ($featuredImage) {
                    $newsData['featured_image'] = $featuredImage;
                }
            }

            // Insert news
            $newsId = $this->newsModel->insert($newsData);

            if (!$newsId) {
                $this->stats['errors']++;
                $errors = $this->newsModel->errors();
                echo "      ❌ Failed to insert: " . implode(', ', $errors) . "\n";
                return false;
            }

            // Import additional images
            if (!empty($news['images']) && is_array($news['images'])) {
                $imageOrder = 0;
                foreach ($news['images'] as $imagePath) {
                    // Skip if it's the featured image
                    if ($imagePath === $news['featured_image']) {
                        continue;
                    }

                    $copiedImage = $this->copyImage($imagePath, $title . '_' . $imageOrder);
                    if ($copiedImage) {
                        $this->newsImageModel->addImage($newsId, $copiedImage, null, $imageOrder);
                        $imageOrder++;
                    }
                }
            }

            $this->stats['imported']++;
            echo "      ✓ Imported successfully (ID: {$newsId})\n";

            return true;

        } catch (Exception $e) {
            $this->stats['errors']++;
            echo "      ❌ Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Generate unique slug
     */
    protected function generateUniqueSlug($slug)
    {
        if (empty($slug)) {
            $slug = 'news-' . time();
        }

        // Clean slug
        $slug = url_title($slug, '-', true);
        if (empty($slug)) {
            $slug = 'news-' . time();
        }

        // Make unique
        $baseSlug = $slug;
        $counter = 1;
        
        while ($this->newsModel->where('slug', $slug)->first()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Copy image from scraped data to uploads directory
     */
    protected function copyImage($imagePath, $prefix = '')
    {
        if (empty($imagePath)) {
            return null;
        }

        try {
            // Handle relative paths
            if (strpos($imagePath, 'scraped_data/') === 0 || strpos($imagePath, 'scripts/scraped_data/') === 0) {
                // Relative path from project root
                $sourcePath = __DIR__ . '/../' . $imagePath;
            } elseif (!file_exists($imagePath)) {
                // Try relative to script directory
                $sourcePath = __DIR__ . '/' . $imagePath;
            } else {
                $sourcePath = $imagePath;
            }

            // Normalize path
            $sourcePath = realpath($sourcePath);
            
            if (!$sourcePath || !file_exists($sourcePath)) {
                return null;
            }

            // Get file extension
            $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $ext = 'jpg'; // Default extension
            }

            // Generate new filename
            $filename = ($prefix ? url_title($prefix, '-', true) . '-' : '') . uniqid() . '.' . $ext;
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

            // Destination path
            $destPath = FCPATH . 'uploads/news/' . $filename;

            // Copy file
            if (copy($sourcePath, $destPath)) {
                $this->stats['images_copied']++;
                return $filename;
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Print import summary
     */
    protected function printSummary()
    {
        echo "\n" . str_repeat("=", 62) . "\n";
        echo "Import Summary\n";
        echo str_repeat("=", 62) . "\n";
        echo "Total News Items:     {$this->stats['total']}\n";
        echo "✓ Successfully Imported: {$this->stats['imported']}\n";
        echo "⊘ Skipped (duplicates):  {$this->stats['skipped']}\n";
        echo "❌ Errors:                {$this->stats['errors']}\n";
        echo "📷 Images Copied:         {$this->stats['images_copied']}\n";
        echo str_repeat("=", 62) . "\n\n";
    }
}

// Run import
try {
    $importer = new ImportNews();
    $importer->run();
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
