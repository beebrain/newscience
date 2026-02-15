<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\NewsModel;
use App\Models\NewsTagModel;

class CategorizeNews extends BaseCommand
{
    protected $group       = 'News';
    protected $name        = 'news:categorize';
    protected $description = 'Analyze news content and set tag (news_tags) for each article';

    protected $newsModel;
    protected $newsTagModel;

    protected $categoryKeywords = [
        'student_activity' => [
            'นักศึกษา', 'กิจกรรมนักศึกษา', 'ค่าย', 'ทัศนศึกษา', 'แข่งขัน', 'ประกวด',
            'นิทรรศการ', 'งานแสดง', 'การแข่งขัน', 'รางวัล', 'เกียรติบัตร', 'ประกาศนียบัตร',
            'รับสมัคร', 'สอบ', 'สัมมนา', 'อบรม', 'workshop', 'student', 'activity',
            'กิจกรรม', 'โครงการ', 'ค่าย', 'ทัศนศึกษา'
        ],
        'research_grant' => [
            'วิจัย', 'งานวิจัย', 'โครงการวิจัย', 'ทุนวิจัย', 'research',
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
        $this->newsTagModel = new NewsTagModel();
    }

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            CLI::write('Tables news_tags and news_news_tags are required. Run add_news_tags.sql first.', 'red');
            return;
        }

        CLI::write('Starting news categorization (by tag)...', 'yellow');
        CLI::newLine();

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
        $stats = ['general' => 0, 'student_activity' => 0, 'research_grant' => 0];

        foreach ($allNews as $news) {
            $suggestedSlug = $this->categorizeNews($news);
            $tagRow = $this->newsTagModel->findBySlug($suggestedSlug);
            if (!$tagRow) {
                $tagRow = $this->newsTagModel->findBySlug('general');
            }
            $suggestedTagId = $tagRow ? (int) $tagRow['id'] : null;

            $currentTagIds = $this->newsTagModel->getTagIdsByNewsId((int) $news['id']);
            $currentHasSuggested = $suggestedTagId && in_array($suggestedTagId, $currentTagIds, true);

            if ($suggestedTagId && !$currentHasSuggested) {
                $this->newsTagModel->setTagsForNews((int) $news['id'], [$suggestedTagId]);
                $updated++;
                $currentSlug = !empty($currentTagIds) ? ($this->newsTagModel->find($currentTagIds[0])['slug'] ?? 'general') : 'none';
                CLI::write("  [UPDATED] ID: {$news['id']} - '{$news['title']}'", 'cyan');
                CLI::write("    Tag: {$currentSlug} → {$suggestedSlug}", 'yellow');
            } else {
                $unchanged++;
            }
            $stats[$suggestedSlug] = ($stats[$suggestedSlug] ?? 0) + 1;
        }

        CLI::newLine();
        CLI::write('=== Categorization Summary (by tag) ===', 'green');
        CLI::write("Total articles: {$total}", 'white');
        CLI::write("Updated: {$updated}", 'cyan');
        CLI::write("Unchanged: {$unchanged}", 'white');
        CLI::newLine();
        CLI::write('Tag distribution:', 'yellow');
        CLI::write("  - general: " . ($stats['general'] ?? 0), 'white');
        CLI::write("  - student_activity: " . ($stats['student_activity'] ?? 0), 'white');
        CLI::write("  - research_grant: " . ($stats['research_grant'] ?? 0), 'white');
        CLI::newLine();
        CLI::write('Categorization completed!', 'green');
    }

    protected function categorizeNews(array $news): string
    {
        $title = mb_strtolower($news['title'] ?? '');
        $content = mb_strtolower($news['content'] ?? '');
        $excerpt = mb_strtolower($news['excerpt'] ?? '');

        $scores = ['student_activity' => 0, 'research_grant' => 0, 'general' => 0];
        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                $keywordLower = mb_strtolower($keyword);
                $titleCount = substr_count($title, $keywordLower) * 3;
                $excerptCount = substr_count($excerpt, $keywordLower) * 2;
                $contentCount = substr_count($content, $keywordLower) * 1;
                $scores[$category] += $titleCount + $excerptCount + $contentCount;
            }
        }
        $maxScore = max($scores);
        if ($maxScore === 0) {
            return 'general';
        }
        return array_search($maxScore, $scores);
    }
}
