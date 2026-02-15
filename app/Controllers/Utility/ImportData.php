<?php

namespace App\Controllers\Utility;

use App\Controllers\BaseController;
use CodeIgniter\CLI\CLI;

/**
 * Import scraped data from sci.uru.ac.th into the database
 * Run via CLI: php spark import:data
 * Or access via browser: /utility/import (admin only)
 */
class ImportData extends BaseController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        // Ensure UTF-8 encoding for Thai content
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET CHARACTER SET utf8mb4");
    }
    
    /**
     * Main import function - accessible via web
     */
    public function index()
    {
        // Check if running in CLI or web
        $isCli = is_cli();
        
        if (!$isCli) {
            // Web access – ปกติใช้ filter adminauth; ตรวจสอบ session เดียวกับ Admin
            $session = session();
            if (!$session->get('admin_logged_in') || ($session->get('admin_role') !== 'admin' && $session->get('admin_role') !== 'editor')) {
                return redirect()->to(base_url('admin/login'))->with('error', 'Please login to access.');
            }
        }
        
        $results = $this->runImport();
        
        if ($isCli) {
            return;
        }
        
        return view('admin/import_results', ['results' => $results]);
    }
    
    /**
     * Run the import process
     */
    public function runImport(): array
    {
        $results = [
            'success' => true,
            'messages' => [],
            'counts' => [
                'site_settings' => 0,
                'programs' => 0,
                'news' => 0,
                'quick_links' => 0,
            ]
        ];
        
        // Try organized_data.json first, then fallback to import_data.json
        $jsonPath = ROOTPATH . 'scripts/scraped_data/organized_data.json';
        if (!file_exists($jsonPath)) {
            $jsonPath = ROOTPATH . 'scripts/scraped_data/import_data.json';
        }
        
        if (!file_exists($jsonPath)) {
            $results['success'] = false;
            $results['messages'][] = "Import file not found";
            return $results;
        }
        
        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);
        
        if (!$data) {
            $results['success'] = false;
            $results['messages'][] = "Failed to parse JSON data";
            return $results;
        }
        
        $results['messages'][] = "Loaded data from {$jsonPath}";
        
        // Import site settings
        if (!empty($data['site_settings'])) {
            $count = $this->importSiteSettings($data['site_settings']);
            $results['counts']['site_settings'] = $count;
            $results['messages'][] = "Imported {$count} site settings";
        }
        
        // Import programs (new format with bachelor/master/doctorate)
        if (!empty($data['programs'])) {
            $count = $this->importProgramsOrganized($data['programs']);
            $results['counts']['programs'] = $count;
            $results['messages'][] = "Imported {$count} programs";
        }
        
        // Import news from separate file if available
        $newsPath = ROOTPATH . 'scripts/scraped_data/import_data.json';
        if (file_exists($newsPath)) {
            $newsData = json_decode(file_get_contents($newsPath), true);
            if (!empty($newsData['news'])) {
                $count = $this->importNews($newsData['news']);
                $results['counts']['news'] = $count;
                $results['messages'][] = "Imported {$count} news articles";
            }
        }
        
        // Import page content
        if (!empty($data['page_content'])) {
            $count = $this->importPageContent($data['page_content']);
            $results['messages'][] = "Imported page content";
        }
        
        // Import quick links
        if (!empty($data['quick_links'])) {
            $count = $this->importQuickLinks($data['quick_links']);
            $results['counts']['quick_links'] = $count;
            $results['messages'][] = "Imported {$count} quick links";
        }
        
        $results['messages'][] = "Import completed successfully!";
        
        return $results;
    }
    
    /**
     * Import site settings (new format)
     */
    protected function importSiteSettings(array $settings): int
    {
        $count = 0;
        
        // Direct mapping for the new format
        $directFields = [
            'site_name_th', 'site_name_en',
            'university_name_th', 'university_name_en',
            'phone', 'fax', 'email',
            'address_th', 'address_en',
            'facebook', 'website', 'logo',
            'philosophy_th', 'philosophy_en',
            'vision_th', 'vision_en',
            'mission_th', 'mission_en',
            'identity_th', 'identity_en',
            'established_year', 'years_of_excellence'
        ];
        
        foreach ($directFields as $key) {
            if (isset($settings[$key]) && !empty($settings[$key])) {
                $value = $settings[$key];
                $type = strlen($value) > 255 ? 'textarea' : 'text';
                $category = $this->getCategoryForSetting($key);
                
                // Use REPLACE to handle both insert and update
                $this->db->table('site_settings')->replace([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'category' => $category,
                ]);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get category for a setting key
     */
    protected function getCategoryForSetting(string $key): string
    {
        $categories = [
            'site_name' => 'general',
            'university' => 'general',
            'phone' => 'contact',
            'fax' => 'contact',
            'email' => 'contact',
            'address' => 'contact',
            'facebook' => 'social',
            'website' => 'social',
            'logo' => 'branding',
            'philosophy' => 'about',
            'vision' => 'about',
            'mission' => 'about',
            'identity' => 'about',
            'established' => 'about',
            'years' => 'about',
        ];
        
        foreach ($categories as $prefix => $category) {
            if (strpos($key, $prefix) === 0) {
                return $category;
            }
        }
        
        return 'general';
    }
    
    /**
     * Import programs from organized format (bachelor/master/doctorate)
     * Uses organization_unit_id: 4=bachelor, 5=graduate (master/doctorate)
     */
    protected function importProgramsOrganized(array $programs): int
    {
        $count = 0;
        
        $levels = ['bachelor', 'master', 'doctorate'];
        
        foreach ($levels as $level) {
            if (empty($programs[$level])) {
                continue;
            }
            
            $orgUnitId = ($level === 'bachelor') ? 4 : 5;
            foreach ($programs[$level] as $index => $prog) {
                $data = [
                    'name_th' => $prog['name_th'] ?? '',
                    'name_en' => $prog['name_en'] ?? '',
                    'degree_th' => $prog['degree_th'] ?? 'วิทยาศาสตรบัณฑิต',
                    'degree_en' => $prog['degree_en'] ?? 'Bachelor of Science',
                    'level' => $level,
                    'organization_unit_id' => $this->db->fieldExists('organization_unit_id', 'programs') ? $orgUnitId : null,
                    'description' => $prog['description_th'] ?? $prog['description'] ?? '',
                    'duration' => $prog['duration'] ?? ($level === 'bachelor' ? 4 : ($level === 'master' ? 2 : 3)),
                    'credits' => $prog['credits'] ?? 0,
                    'sort_order' => $count + 1,
                    'status' => 'active',
                ];
                if (! $this->db->fieldExists('organization_unit_id', 'programs')) {
                    unset($data['organization_unit_id']);
                }

                $existing = $this->db->table('programs')
                    ->where('name_th', $data['name_th'])
                    ->where('level', $data['level'])
                    ->get()
                    ->getRow();

                if ($existing) {
                    $this->db->table('programs')
                        ->where('id', $existing->id)
                        ->update($data);
                } else {
                    $this->db->table('programs')->insert($data);
                }
                
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Import page content as site settings
     */
    protected function importPageContent(array $pageContent): int
    {
        $count = 0;
        
        // Home page content
        if (!empty($pageContent['home'])) {
            $home = $pageContent['home'];
            
            if (!empty($home['hero_title_th'])) {
                $this->db->table('site_settings')->replace([
                    'setting_key' => 'hero_title_th',
                    'setting_value' => $home['hero_title_th'],
                    'setting_type' => 'text',
                    'category' => 'home',
                ]);
                $count++;
            }
            
            if (!empty($home['hero_subtitle_th'])) {
                $this->db->table('site_settings')->replace([
                    'setting_key' => 'hero_subtitle_th',
                    'setting_value' => $home['hero_subtitle_th'],
                    'setting_type' => 'text',
                    'category' => 'home',
                ]);
                $count++;
            }
            
            if (!empty($home['hero_description_th'])) {
                $this->db->table('site_settings')->replace([
                    'setting_key' => 'hero_description_th',
                    'setting_value' => $home['hero_description_th'],
                    'setting_type' => 'textarea',
                    'category' => 'home',
                ]);
                $count++;
            }
            
            // Store stats
            if (!empty($home['stats'])) {
                foreach ($home['stats'] as $key => $value) {
                    $this->db->table('site_settings')->replace([
                        'setting_key' => 'stat_' . $key,
                        'setting_value' => $value,
                        'setting_type' => 'text',
                        'category' => 'stats',
                    ]);
                    $count++;
                }
            }
        }
        
        // About page content
        if (!empty($pageContent['about'])) {
            $about = $pageContent['about'];
            
            if (!empty($about['history_th'])) {
                $this->db->table('site_settings')->replace([
                    'setting_key' => 'history_th',
                    'setting_value' => $about['history_th'],
                    'setting_type' => 'textarea',
                    'category' => 'about',
                ]);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Import quick links
     */
    protected function importQuickLinks(array $links): int
    {
        $count = 0;
        
        foreach ($links as $index => $link) {
            $data = [
                'setting_key' => 'quick_link_' . ($index + 1),
                'setting_value' => json_encode([
                    'name_th' => $link['name_th'] ?? '',
                    'name_en' => $link['name_en'] ?? '',
                    'url' => $link['url'] ?? '',
                    'category' => $link['category'] ?? 'general',
                ], JSON_UNESCAPED_UNICODE),
                'setting_type' => 'json',
                'category' => 'quick_links',
            ];
            
            $this->db->table('site_settings')->replace($data);
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Import news articles
     */
    protected function importNews(array $newsItems): int
    {
        $count = 0;
        helper('url');
        
        foreach ($newsItems as $news) {
            if (empty($news['title'])) {
                continue;
            }
            
            // Generate slug
            $slug = url_title($news['title'], '-', true);
            if (empty($slug)) {
                $slug = 'news-' . ($news['id'] ?? time());
            }
            
            // Make slug unique
            $baseSlug = $slug;
            $counter = 1;
            while ($this->db->table('news')->where('slug', $slug)->countAllResults() > 0) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            // Get featured image
            $featuredImage = null;
            if (!empty($news['images'])) {
                foreach ($news['images'] as $img) {
                    if (strpos($img, 'getimage') !== false) {
                        $featuredImage = $img;
                        break;
                    }
                }
                if (!$featuredImage) {
                    $featuredImage = $news['images'][0] ?? null;
                }
            }
            
            // Parse date
            $publishedAt = null;
            if (!empty($news['date'])) {
                $publishedAt = $news['date'] . ' 12:00:00';
            }
            
            $data = [
                'title' => $news['title'],
                'slug' => $slug,
                'content' => $news['content'] ?? '',
                'excerpt' => mb_substr($news['title'], 0, 200),
                'featured_image' => $featuredImage,
                'status' => 'published',
                'published_at' => $publishedAt ?? date('Y-m-d H:i:s'),
                'view_count' => 0,
            ];
            
            // Check if already exists
            $existing = $this->db->table('news')
                ->where('title', $data['title'])
                ->get()
                ->getRow();
            
            if (!$existing) {
                $this->db->table('news')->insert($data);
                $count++;
            }
        }
        
        return $count;
    }
}
