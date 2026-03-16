<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Exam Controller - JSON File Based (User View)
 * 
 * Reads exam schedules from JSON files
 */
class ExamJsonController extends BaseController
{
    private const UPLOAD_PATH = WRITEPATH . 'exampuploads/';

    /**
     * Display user's exam schedules
     */
    public function index()
    {
        // Get current user info for debugging
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('admin_id');

        if ($userId) {
            $userModel = new UserModel();
            $user = $userModel->find($userId);

            $data = [
                'page_title' => 'ตารางคุมสอบ',
                'debug_user' => [
                    'user_id' => $userId,
                    'nickname' => $user['nickname'] ?? '(ไม่มี)',
                    'thai_name' => trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?: '(ไม่มี)',
                    'eng_name' => trim(($user['gf_name'] ?? '') . ' . ($user['gl_name'] ?? '')) ?: '(ไม่มี)',
                ]
            ];
        } else {
            $data = [
                'page_title' => 'ตารางคุมสอบ',
                'debug_user' => ['error' => 'ไม่พบ User ID']
            ];
        }

        return view('exam_json/index', $data);
    }

    /**
     * AJAX: Get available semesters
     */
    public function getSemesters()
    {
        $files = [];
        
        if (is_dir(self::UPLOAD_PATH)) {
            $iterator = new \DirectoryIterator(self::UPLOAD_PATH);
            
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getExtension() === 'json') {
                    $filename = $fileinfo->getFilename();
                    
                    // Parse filename to get metadata
                    if (preg_match('/schedules_(\d+)_(\d+)_(midterm|final)(?:_published)?\.json/', $filename, $matches)) {
                        $files[] = [
                            'name' => $filename,
                            'semester_no' => $matches[1],
                            'year' => $matches[2],
                            'exam_type' => $matches[3],
                            'semester_label' => $matches[1] . '/' . $matches[2],
                            'is_published' => strpos($filename, '_published') !== false,
                            'size' => $fileinfo->getSize(),
                            'modified' => $fileinfo->getMTime(),
                        ];
                    }
                }
            }
            
            // Sort by modified time descending
            usort($files, function ($a, $b) {
                return $b['modified'] - $a['modified'];
            });
            
            // Group by semester
            $result = [];
            $semesterMap = [];
            
            foreach ($files as $file) {
                $label = $file['semester_label'];
                $type = $file['exam_type'];
                
                if (!isset($semesterMap[$label])) {
                    $semesterMap[$label] = [];
                }
                
                $semesterMap[$label][] = $type;
            }
            
            foreach ($semesterMap as $label => $types) {
                $result[] = [
                    'label' => $label,
                    'exam_types' => array_unique($types),
                ];
            }
            
            // Sort by year descending, then semester descending
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
        }

        return $this->response->setJSON(['success' => true, 'semesters' => $result]);
    }

    /**
     * AJAX: Get schedules for a semester/exam type
     */
    public function getSchedules()
    {
        $semester = $this->request->getGet('semester');
        $examType = $this->request->getGet('exam_type');

        if (!$semester || !$examType) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing parameters']);
        }

        $parts = explode('/', $semester);
        if (count($parts) !== 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid semester format']);
        }

        $semesterNo = $parts[0];
        $year = $parts[1];

        // Try published version first
        $publishedFile = "schedules_{$semesterNo}_{$year}_{$examType}_published.json";
        $filepath = self::UPLOAD_PATH . $publishedFile;

        // Fall back to draft
        if (!file_exists($filepath)) {
            $draftFile = "schedules_{$semesterNo}_{$year}_{$examType}.json";
            $filepath = self::UPLOAD_PATH . $draftFile;
        }

        if (!file_exists($filepath)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลตารางสอบ']);
        }

        $jsonData = json_decode(file_get_contents($filepath), true);

        if (!$jsonData) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถอ่านข้อมูลได้']);
        }

        $schedules = $jsonData['schedules'] ?? [];

        // Get current user info from database (not just session)
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('admin_id');

        // Query user table directly for fresh data
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $userNickname = $user['nickname'] ?? '';
        $userThaiName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
        $userEngName = trim(($user['gf_name'] ?? '') . ' . ($user['gl_name'] ?? ''));

        // Calculate stats
        $stats = [
            'total' => count($schedules),
            'my_schedules' => 0,
        ];

        // Filter for user's schedules - check against BOTH nickname AND Thai name
        $mySchedules = [];
        $matchedBy = [];

        foreach ($schedules as $schedule) {
            $examiner1 = $schedule['examiner1'] ?? '';
            $examiner2 = $schedule['examiner2'] ?? '';
            $isMatch = false;
            $matchSource = '';

            // Check against nickname
            if ($userNickname && (
                strcasecmp($examiner1, $userNickname) === 0 ||
                strcasecmp($examiner2, $userNickname) === 0
            )) {
                $isMatch = true;
                $matchSource = 'nickname: ' . $userNickname;
            }
            // Check against Thai name (if no nickname match or additional check)
            elseif ($userThaiName && (
                strcasecmp($examiner1, $userThaiName) === 0 ||
                strcasecmp($examiner2, $userThaiName) === 0
            )) {
                $isMatch = true;
                $matchSource = 'thai_name: ' . $userThaiName;
            }

            if ($isMatch) {
                $mySchedules[] = $schedule;
                $matchedBy[] = [
                    'course' => $schedule['course_code'] ?? '',
                    'examiner1' => $examiner1,
                    'examiner2' => $examiner2,
                    'matched_by' => $matchSource,
                    'user_id' => $userId,
                    'login_uid' => $user['login_uid'] ?? 'N/A',
                    'nickname' => $userNickname,
                    'thai_name' => $userThaiName,
                    'examiner_name' => ($examiner1 === $userNickname || $examiner1 === $userThaiName) ? $examiner1 : $examiner2
                ];
                $stats['my_schedules']++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'schedules' => $schedules,
            'my_schedules' => $mySchedules,
            'stats' => $stats,
            'debug' => [
                'user_id' => $userId,
                'nickname_from_table' => $userNickname,
                'thai_name_from_table' => $userThaiName,
                'eng_name_from_table' => $userEngName,
                'matched_schedules' => count($mySchedules),
                'match_details' => $matchedBy
            ]
        ]);
    }
}
