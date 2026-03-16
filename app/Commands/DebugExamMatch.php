<?php

namespace App\Commands;

use App\Models\UserModel;
use App\Controllers\ExamJsonController;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI Command: Debug Exam Matching
 * 
 * Tests exam matching for all users and shows detailed results
 */
class DebugExamMatch extends BaseCommand
{
    protected $group = 'Exam';
    protected $name = 'exam:debug-match';
    protected $description = 'Debug exam matching for all users - check why some users still don\'t match';
    protected $usage = 'exam:debug-match [--user=ID] [--show-all]';
    protected $arguments = [
        '--user' => 'Test specific user ID only',
        '--show-all' => 'Show all users (even those with no matches)',
        '--limit' => 'Limit number of users to test (default: 20)',
    ];

    protected $userModel;
    protected $examController;

    public function run(array $params)
    {
        $this->userModel = new UserModel();
        $this->examController = new ExamJsonController();

        $specificUserId = CLI::getOption('user');
        $showAll = CLI::getOption('show-all') !== null;
        $limit = CLI::getOption('limit') ?? 20;

        CLI::write('=== Debug Exam Matching ===', 'cyan');
        CLI::write('');

        if ($specificUserId) {
            CLI::write("Testing specific user ID: {$specificUserId}", 'yellow');
            $this->testSingleUser($specificUserId);
            return;
        } else {
            CLI::write("Testing up to {$limit} users...", 'yellow');
            $this->testMultipleUsers($limit, $showAll);
        }

        CLI::write('');
        CLI::write('=== END ===', 'cyan');
    }

    private function testSingleUser($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            CLI::write("❌ User ID {$userId} not found", 'red');
            return;
        }

        CLI::write("👤 User: {$user['login_uid']} (ID: {$userId})", 'white');
        CLI::write("   Nickname: " . ($user['nickname'] ?? '(ไม่มี)'), 'white');
        CLI::write("   Thai Name: " . (trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?: '(ไม่มี)'), 'white');
        CLI::write("   Eng Name: " . (trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')) ?: '(ไม่มี)'), 'white');
        CLI::write('');

        // Test matching logic
        $this->testUserMatching($user);
    }

    private function testMultipleUsers($limit, $showAll)
    {
        // Get users with exam data
        $users = $this->getUsersWithExamData($limit);
        $totalUsers = count($users);

        CLI::write("Found {$totalUsers} users to test", 'white');
        CLI::write('');

        $stats = [
            'total' => 0,
            'matched' => 0,
            'no_nickname' => 0,
            'no_thai_name' => 0,
            'issues' => []
        ];

        foreach ($users as $user) {
            $userId = $user['uid'];
            $nickname = $user['nickname'] ?? '';
            $thaiName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
            $engName = trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? ''));

            $stats['total']++;

            if (empty($nickname)) $stats['no_nickname']++;
            if (empty($thaiName)) $stats['no_thai_name']++;

            // Test matching
            $matchResult = $this->testUserMatching($user, false);

            if ($matchResult['matched_count'] > 0) {
                $stats['matched']++;
            } elseif ($showAll) {
                $stats['issues'][] = [
                    'uid' => $userId,
                    'login_uid' => $user['login_uid'] ?? 'N/A',
                    'nickname' => $nickname ?: '(ไม่มี)',
                    'thai_name' => $thaiName ?: '(ไม่มี)',
                    'matched_count' => 0
                ];
            }
        }

        // Summary
        CLI::write('=== SUMMARY ===', 'cyan');
        CLI::write("Total users tested: {$stats['total']}", 'white');
        CLI::write("Users with matches: {$stats['matched']}", 'green');
        CLI::write("Users without nickname: {$stats['no_nickname']}", 'yellow');
        CLI::write("Users without Thai name: {$stats['no_thai_name']}", 'yellow');
        CLI::write("Users with no matches: " . ($stats['total'] - $stats['matched']), 'red');

        if (!$showAll && ($stats['total'] - $stats['matched']) > 0) {
            CLI::write('');
            CLI::write('💡 Use --show-all to see users with no matches', 'yellow');
        }

        if (!empty($stats['issues']) && $showAll) {
            CLI::write('');
            CLI::write('Users with no matches:', 'red');
            foreach ($stats['issues'] as $issue) {
                CLI::write("  [{$issue['uid']}] {$issue['login_uid']}", 'white');
                CLI::write("    Nickname: {$issue['nickname']}", 'white');
                CLI::write("    Thai Name: {$issue['thai_name']}", 'white');
                CLI::write('');
            }
        }
    }

    private function testUserMatching($user, $showDetails = true)
    {
        $userId = $user['uid'];
        $nickname = $user['nickname'] ?? '';
        $thaiName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
        $engName = trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? ''));

        if ($showDetails) {
            CLI::write("🔍 Testing matching logic...");
        }

        // Get available semesters
        $semesters = $this->getAvailableSemesters();

        if (empty($semesters)) {
            if ($showDetails) CLI::write("   ❌ No exam data available", 'red');
            return ['matched_count' => 0];
        }

        $totalMatches = 0;
        $matchDetails = [];

        // Test each semester
        foreach ($semesters as $semester) {
            $semesterLabel = $semester['label'];

            foreach ($semester['exam_types'] as $examType) {
                $matches = $this->testSemesterMatching($userId, $semesterLabel, $examType, $nickname, $thaiName);

                if (!empty($matches)) {
                    $totalMatches += count($matches);
                    $matchDetails = array_merge($matchDetails, $matches);

                    if ($showDetails) {
                        CLI::write("   ✅ {$semesterLabel} {$examType}: " . count($matches) . " matches", 'green');
                    }
                } elseif ($showDetails) {
                    CLI::write("   ❌ {$semesterLabel} {$examType}: 0 matches", 'red');
                }
            }
        }

        if ($showDetails) {
            CLI::write("   📊 Total matches: {$totalMatches}", $totalMatches > 0 ? 'green' : 'red');

            if (!empty($matchDetails) && $totalMatches > 0) {
                CLI::write('');
                CLI::write("   Sample matches:");
                $limit = min(3, count($matchDetails));
                for ($i = 0; $i < $limit; $i++) {
                    $match = $matchDetails[$i];
                    CLI::write("     {$match['course']} - {$match['matched_by']}", 'white');
                    CLI::write("       → \"{$match['examiner']}\"", 'white');
                }
            }

            if ($totalMatches === 0) {
                CLI::write('');
                CLI::write("   💡 Possible issues:", 'yellow');
                if (empty($nickname) && empty($thaiName)) {
                    CLI::write("     - No nickname AND no Thai name", 'yellow');
                } elseif (empty($nickname)) {
                    CLI::write("     - No nickname (Thai name exists: \"{$thaiName}\")", 'yellow');
                } elseif (empty($thaiName)) {
                    CLI::write("     - No Thai name (nickname exists: \"{$nickname}\")", 'yellow');
                } else {
                    CLI::write("     - Names exist but don't match exam data", 'yellow');
                    CLI::write("     - Check if names in Excel match exactly", 'yellow');
                }
            }
        }

        return ['matched_count' => $totalMatches, 'details' => $matchDetails];
    }

    private function testSemesterMatching($userId, $semester, $examType, $nickname, $thaiName)
    {
        // Simulate the matching logic from ExamJsonController
        $semesterNo = explode('/', $semester)[0];
        $year = explode('/', $semester)[1];

        $publishedFile = "schedules_{$semesterNo}_{$year}_{$examType}_published.json";
        $filepath = WRITEPATH . 'exampuploads/' . $publishedFile;

        if (!file_exists($filepath)) {
            $draftFile = "schedules_{$semesterNo}_{$year}_{$examType}.json";
            $filepath = WRITEPATH . 'exampuploads/' . $draftFile;
        }

        if (!file_exists($filepath)) {
            return [];
        }

        $jsonData = json_decode(file_get_contents($filepath), true);
        if (!$jsonData) {
            return [];
        }

        $schedules = $jsonData['schedules'] ?? [];
        $matches = [];

        foreach ($schedules as $schedule) {
            $examiner1 = $schedule['examiner1'] ?? '';
            $examiner2 = $schedule['examiner2'] ?? '';

            // Check against nickname
            if ($nickname && (
                strcasecmp($examiner1, $nickname) === 0 ||
                strcasecmp($examiner2, $nickname) === 0
            )) {
                $matches[] = [
                    'course' => $schedule['course_code'] ?? 'N/A',
                    'examiner' => $examiner1 === $nickname ? $examiner1 : $examiner2,
                    'matched_by' => 'nickname: ' . $nickname
                ];
            }
            // Check against Thai name
            elseif ($thaiName && (
                strcasecmp($examiner1, $thaiName) === 0 ||
                strcasecmp($examiner2, $thaiName) === 0
            )) {
                $matches[] = [
                    'course' => $schedule['course_code'] ?? 'N/A',
                    'examiner' => $examiner1 === $thaiName ? $examiner1 : $examiner2,
                    'matched_by' => 'thai_name: ' . $thaiName
                ];
            }
        }

        return $matches;
    }

    private function getAvailableSemesters()
    {
        $uploadPath = WRITEPATH . 'exampuploads/';
        $semesters = [];

        if (!is_dir($uploadPath)) {
            return [];
        }

        $iterator = new \DirectoryIterator($uploadPath);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'json') {
                $filename = $fileinfo->getFilename();

                if (preg_match('/schedules_(\d+)_(\d+)_(midterm|final)(?:_published)?\.json/', $filename, $matches)) {
                    $label = $matches[1] . '/' . $matches[2];
                    $type = $matches[3];

                    if (!isset($semesters[$label])) {
                        $semesters[$label] = [];
                    }

                    $semesters[$label][] = $type;
                }
            }
        }

        // Format and sort
        $result = [];
        foreach ($semesters as $label => $types) {
            $result[] = [
                'label' => $label,
                'exam_types' => array_unique($types),
            ];
        }

        usort($result, function ($a, $b) {
            $aParts = explode('/', $a['label']);
            $bParts = explode('/', $b['label']);

            $aYear = (int)$aParts[1];
            $bYear = (int)$bParts[1];
            $aSem = (int)$aParts[0];
            $bSem = (int)$bParts[0];

            if ($aYear !== $bYear) return $bYear - $aYear;
            return $bSem - $aSem;
        });

        return $result;
    }

    private function getUsersWithExamData($limit)
    {
        // Get users who might have exam schedules
        // For now, just get recent users
        return $this->userModel->orderBy('uid', 'DESC')->limit($limit)->findAll();
    }
}
