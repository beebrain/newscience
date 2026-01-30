<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\NewsModel;

class CategorizeNews extends BaseCommand
{
    protected $group       = 'News';
    protected $name        = 'news:categorize';
    protected $description = 'Analyze news content and automatically categorize news articles';

    protected $newsModel;

    // Keywords for each category
    protected $categoryKeywords = [
        'student_activity' => [
            'นักศึกษา', 'กิจกรรมนักศึกษา', 'ค่าย', 'ทัศนศึกษา', 'แข่งขัน', 'ประกวด',
            'นิทรรศการ', 'งานแสดง', 'การแข่งขัน', 'รางวัล', 'เกียรติบัตร', 'ประกาศนียบัตร',
            'รับสมัคร', 'สอบ', 'สัมมนา', 'อบรม', 'workshop', 'student', 'activity',
            'กิจกรรม', 'โครงการ', 'ค่าย', 'ทัศนศึกษา', 'ทัศนศึกษา', 'ทัศนศึกษา'
        ],
        'research_grant' => [
            'วิจัย', 'งานวิจัย', 'โครงการวิจัย', 'ทุนวิจัย', 'วิจัย', 'research',
            'grant', 'funding', 'ทุน', 'scholarship', 'fellowship', 'ทุนสนับสนุน',
            'ผลงานวิจัย', 'ตีพิมพ์', 'publication', 'journal', 'วารสาร', 'conference',
            'การประชุมวิชาการ', 'symposium', 'seminar', 'การนำเสนอผลงาน'
        ],
        'general' => [
            'ประกาศ', 'แจ้งเตือน', 'ข่าว', 'ประชาสัมพันธ์', 'announcement', 'notice',
            'general', 'ทั่วไป', 'ข้อมูล', 'information'
        ]
    ];

    public function __construct()
    {
        $this->newsModel = new NewsModel();
    }

    public function run(array $params)
    {
        CLI::write('Starting news categorization...', 'yellow');
        CLI::newLine();

        // Get all news articles
        $allNews = $this->newsModel->findAll();
        $total = count($allNews);
        
        if ($total === 0) {
            CLI::write('No news articles found in database.', 'red');
            return;
        }

        CLI::write("Found {$total} news articles to categorize.", 'green');
        CLI::newLine();

        $updated = 0;
        $unchanged = 0;
        $stats = [
            'general' => 0,
            'student_activity' => 0,
            'research_grant' => 0
        ];

        foreach ($allNews as $news) {
            $currentCategory = $news['category'] ?? 'general';
            $suggestedCategory = $this->categorizeNews($news);
            
            if ($currentCategory !== $suggestedCategory) {
                // Update category
                $this->newsModel->update($news['id'], ['category' => $suggestedCategory]);
                $updated++;
                
                CLI::write("  [UPDATED] ID: {$news['id']} - '{$news['title']}'", 'cyan');
                CLI::write("    From: {$currentCategory} → To: {$suggestedCategory}", 'yellow');
            } else {
                $unchanged++;
            }
            
            $stats[$suggestedCategory]++;
        }

        CLI::newLine();
        CLI::write('=== Categorization Summary ===', 'green');
        CLI::write("Total articles: {$total}", 'white');
        CLI::write("Updated: {$updated}", 'cyan');
        CLI::write("Unchanged: {$unchanged}", 'white');
        CLI::newLine();
        CLI::write('Category distribution:', 'yellow');
        CLI::write("  - General: {$stats['general']}", 'white');
        CLI::write("  - Student Activity: {$stats['student_activity']}", 'white');
        CLI::write("  - Research/Grant: {$stats['research_grant']}", 'white');
        CLI::newLine();
        CLI::write('Categorization completed!', 'green');
    }

    /**
     * Categorize a news article based on its content
     */
    protected function categorizeNews(array $news): string
    {
        $title = mb_strtolower($news['title'] ?? '');
        $content = mb_strtolower($news['content'] ?? '');
        $excerpt = mb_strtolower($news['excerpt'] ?? '');
        
        $fullText = $title . ' ' . $excerpt . ' ' . $content;
        
        $scores = [
            'student_activity' => 0,
            'research_grant' => 0,
            'general' => 0
        ];

        // Score each category based on keyword matches
        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                $keywordLower = mb_strtolower($keyword);
                // Count occurrences in title (weight: 3), excerpt (weight: 2), content (weight: 1)
                $titleCount = substr_count($title, $keywordLower) * 3;
                $excerptCount = substr_count($excerpt, $keywordLower) * 2;
                $contentCount = substr_count($content, $keywordLower) * 1;
                
                $scores[$category] += $titleCount + $excerptCount + $contentCount;
            }
        }

        // Find category with highest score
        $maxScore = max($scores);
        
        // If no strong match, default to general
        if ($maxScore === 0) {
            return 'general';
        }

        // Return category with highest score
        return array_search($maxScore, $scores);
    }
}
