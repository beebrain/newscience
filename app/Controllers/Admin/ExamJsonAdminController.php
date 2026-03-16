<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Exam Admin Controller - JSON File Based (similar to original EdocSci)
 * 
 * Stores exam schedules in JSON files instead of database tables
 */
class ExamJsonAdminController extends BaseController
{
    private const UPLOAD_PATH = WRITEPATH . 'exampuploads/';
    private const ALLOWED_TYPES = ['midterm', 'final'];

    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();

        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_PATH)) {
            mkdir(self::UPLOAD_PATH, 0755, true);
        }
    }

    /**
     * Admin dashboard - list all JSON files
     */
    public function index()
    {
        $files = $this->scanScheduleFiles();

        $data = [
            'page_title' => 'จัดการตารางคุมสอบ',
            'files' => $files,
        ];

        return view('admin/exam_json/index', $data);
    }

    /**
     * Upload form
     */
    public function uploadForm()
    {
        $data = [
            'page_title' => 'นำเข้าตารางคุมสอบ',
        ];

        return view('admin/exam_json/upload_form', $data);
    }

    /**
     * Process Excel upload and save as JSON
     */
    public function upload()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'semester' => 'required',
            'exam_type' => 'required|in_list[midterm,final]',
            'excel_file' => 'uploaded[excel_file]|ext_in[excel_file,xlsx,xls]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->with('error', $validation->getErrors())->withInput();
        }

        $semester = $this->request->getPost('semester'); // e.g., "2/2568"
        $examType = $this->request->getPost('exam_type'); // midterm or final

        // Parse semester
        $parts = explode('/', $semester);
        if (count($parts) !== 2) {
            return redirect()->back()->with('error', 'รูปแบบภาคการศึกษาไม่ถูกต้อง (ตัวอย่าง: 2/2568)')->withInput();
        }

        $semesterNo = $parts[0];
        $year = $parts[1];

        // Process Excel file
        $file = $this->request->getFile('excel_file');

        try {
            $schedules = $this->parseExcelFile($file);

            // Build JSON structure (same as original EdocSci)
            $data = [
                'metadata' => [
                    'filename' => $file->getClientName(),
                    'semester' => $semester,
                    'year' => $year,
                    'semester_no' => $semesterNo,
                    'exam_type' => $examType,
                    'uploaded_at' => date('Y-m-d H:i:s'),
                    'uploaded_by' => session()->get('admin_id'),
                ],
                'schedules' => $schedules,
            ];

            // Save to JSON file (draft version with _ prefix)
            $filename = "_schedules_{$semesterNo}_{$year}_{$examType}.json";
            $filepath = self::UPLOAD_PATH . $filename;

            file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Log upload with examiner statistics
            $examinerNames = [];
            foreach ($schedules as $s) {
                if (!empty($s['examiner1'])) $examinerNames[] = $s['examiner1'];
                if (!empty($s['examiner2'])) $examinerNames[] = $s['examiner2'];
            }
            $uniqueExaminers = array_unique($examinerNames);

            log_message('info', '[ExamAdmin] File uploaded: {filename} by admin ID: {admin_id}. Semester: {semester}, Type: {exam_type}, Schedules: {count}, Unique Examiners: {examiners}', [
                'filename' => $filename,
                'admin_id' => session()->get('admin_id'),
                'semester' => $semester,
                'exam_type' => $examType,
                'count' => count($schedules),
                'examiners' => count($uniqueExaminers),
                'examiner_list' => $uniqueExaminers
            ]);

            return redirect()->to(base_url("admin/exam/preview/{$semesterNo}/{$year}/{$examType}"))
                ->with('success', 'นำเข้าข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            log_message('error', 'Exam upload error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Preview JSON data before publish
     */
    public function preview($semesterNo, $year, $examType)
    {
        $filename = "_schedules_{$semesterNo}_{$year}_{$examType}.json";
        $filepath = self::UPLOAD_PATH . $filename;

        if (!file_exists($filepath)) {
            return redirect()->to(base_url('admin/exam-json'))->with('error', 'ไม่พบไฟล์ร่างข้อมูล');
        }

        $jsonData = json_decode(file_get_contents($filepath), true);

        if (!$jsonData) {
            return redirect()->to(base_url('admin/exam-json'))->with('error', 'ไม่สามารถอ่านไฟล์ข้อมูลได้');
        }

        // Calculate stats
        $stats = $this->calculateStats($jsonData['schedules'] ?? []);

        $data = [
            'page_title' => 'ตรวจสอบตารางคุมสอบ',
            'metadata' => $jsonData['metadata'] ?? [],
            'schedules' => $jsonData['schedules'] ?? [],
            'stats' => $stats,
            'filename' => $filename,
            'is_published' => $this->isPublished($filename),
        ];

        return view('admin/exam_json/preview', $data);
    }

    /**
     * Publish JSON file (rename to remove _ prefix)
     */
    public function publish($semesterNo, $year, $examType)
    {
        $draftFile = "_schedules_{$semesterNo}_{$year}_{$examType}.json";
        $draftPath = self::UPLOAD_PATH . $draftFile;

        if (!file_exists($draftPath)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบไฟล์ร่างที่จะเผยแพร่']);
        }

        // Backup existing published file if exists
        $publishedFile = "schedules_{$semesterNo}_{$year}_{$examType}.json";
        $publishedPath = self::UPLOAD_PATH . $publishedFile;

        if (file_exists($publishedPath)) {
            $backupFile = "schedules_{$semesterNo}_{$year}_{$examType}_" . date('YmdHis') . '.json';
            rename($publishedPath, self::UPLOAD_PATH . $backupFile);
        }

        // Rename draft file to published file (remove _ prefix)
        rename($draftPath, $publishedPath);

        // Update metadata
        $jsonData = json_decode(file_get_contents($publishedPath), true);
        $jsonData['metadata']['published_at'] = date('Y-m-d H:i:s');
        $jsonData['metadata']['published_by'] = session()->get('admin_id');
        file_put_contents($publishedPath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Log publish action
        log_message('info', '[ExamAdmin] Schedule published: {semester}/{year} {exam_type} by admin ID: {admin_id}', [
            'semester' => $semesterNo,
            'year' => $year,
            'exam_type' => $examType,
            'admin_id' => session()->get('admin_id'),
            'file' => $publishedFile
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'เผยแพร่สำเร็จ']);
    }

    /**
     * Delete JSON file
     */
    public function delete($semesterNo, $year, $examType)
    {
        // Delete draft file (with _ prefix)
        $draftFile = "_schedules_{$semesterNo}_{$year}_{$examType}.json";
        $draftPath = self::UPLOAD_PATH . $draftFile;

        if (file_exists($draftPath)) {
            unlink($draftPath);
        }

        // Also delete published version (without _ prefix)
        $publishedFile = "schedules_{$semesterNo}_{$year}_{$examType}.json";
        $publishedPath = self::UPLOAD_PATH . $publishedFile;

        if (file_exists($publishedPath)) {
            unlink($publishedPath);
        }

        return redirect()->to(base_url('admin/exam-json'))->with('success', 'ลบไฟล์สำเร็จ');
    }

    /**
     * AJAX: Get exam schedules data (for user view)
     */
    public function loadData()
    {
        $semester = $this->request->getGet('semester'); // e.g., "2/2568"
        $examType = $this->request->getGet('exam_type'); // midterm or final

        if (!$semester || !$examType) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing parameters']);
        }

        $parts = explode('/', $semester);
        if (count($parts) !== 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid semester format']);
        }

        $semesterNo = $parts[0];
        $year = $parts[1];

        // Try published version first (without _ prefix)
        $publishedFile = "schedules_{$semesterNo}_{$year}_{$examType}.json";
        $filepath = self::UPLOAD_PATH . $publishedFile;

        // Fall back to draft version (with _ prefix)
        if (!file_exists($filepath)) {
            $draftFile = "_schedules_{$semesterNo}_{$year}_{$examType}.json";
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
        $stats = $this->calculateStats($schedules);

        return $this->response->setJSON([
            'success' => true,
            'schedules' => $schedules,
            'stats' => $stats,
            'metadata' => $jsonData['metadata'] ?? [],
        ]);
    }

    /**
     * Get available semesters/exam types
     */
    public function getAvailableSemesters()
    {
        $files = $this->scanScheduleFiles();
        $semesters = [];

        foreach ($files as $file) {
            // Parse filename pattern: _?schedules_#_#_type.json
            if (preg_match('/^_?schedules_(\d+)_(\d+)_(midterm|final)\.json$/', $file['name'], $matches)) {
                $label = $matches[1] . '/' . $matches[2];
                $type = $matches[3];

                if (!isset($semesters[$label])) {
                    $semesters[$label] = [];
                }

                $semesters[$label][] = $type;
            }
        }

        // Format response
        $result = [];
        foreach ($semesters as $label => $types) {
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

        return $this->response->setJSON(['success' => true, 'semesters' => $result]);
    }

    /**
     * Parse Excel file and extract schedule data
     */
    private function parseExcelFile($file): array
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getTempName());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $schedules = [];

        // Skip header row, process data rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $schedule = [
                'section' => $this->cleanValue($row[0] ?? ''),
                'course_code' => $this->cleanValue($row[1] ?? ''),
                'course_name' => $this->cleanValue($row[2] ?? ''),
                'student_group' => $this->cleanValue($row[3] ?? ''),
                'student_program' => $this->cleanValue($row[4] ?? ''),
                'instructor' => $this->cleanValue($row[5] ?? ''),
                'exam_date' => $this->cleanValue($row[6] ?? ''),
                'exam_time' => $this->cleanValue($row[7] ?? ''),
                'room' => $this->cleanValue($row[8] ?? ''),
                'examiner1' => $this->cleanValue($row[9] ?? ''),
                'examiner2' => $this->cleanValue($row[10] ?? ''),
            ];

            // Merge duplicate schedules (same examiners, course, date, time, room)
            $merged = false;
            foreach ($schedules as &$existing) {
                if ($this->shouldMerge($existing, $schedule)) {
                    $existing['section'] .= ', ' . $schedule['section'];
                    $existing['student_group'] .= ', ' . $schedule['student_group'];
                    $merged = true;
                    break;
                }
            }

            if (!$merged) {
                $schedules[] = $schedule;
            }
        }

        return $schedules;
    }

    /**
     * Check if two schedules should be merged
     */
    private function shouldMerge(array $a, array $b): bool
    {
        return $a['examiner1'] === $b['examiner1']
            && $a['examiner2'] === $b['examiner2']
            && $a['course_code'] === $b['course_code']
            && $a['course_name'] === $b['course_name']
            && $a['exam_date'] === $b['exam_date']
            && $a['exam_time'] === $b['exam_time']
            && $a['room'] === $b['room'];
    }

    /**
     * Clean cell value
     */
    private function cleanValue($value): string
    {
        if (is_null($value)) return '';
        return trim((string)$value);
    }

    /**
     * Scan for all schedule JSON files
     */
    private function scanScheduleFiles(): array
    {
        $files = [];

        if (!is_dir(self::UPLOAD_PATH)) {
            return $files;
        }

        $iterator = new \DirectoryIterator(self::UPLOAD_PATH);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'json') {
                $filename = $fileinfo->getFilename();

                // Parse filename to get metadata (handle both _prefix and normal formats)
                if (preg_match('/^_?schedules_(\d+)_(\d+)_(midterm|final)\.json$/', $filename, $matches)) {
                    $files[] = [
                        'name' => $filename,
                        'semester_no' => $matches[1],
                        'year' => $matches[2],
                        'exam_type' => $matches[3],
                        'semester_label' => $matches[1] . '/' . $matches[2],
                        'is_published' => strpos($filename, '_') !== 0, // Files without _ prefix are published
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

        return $files;
    }

    /**
     * Check if a file is published
     */
    private function isPublished(string $filename): bool
    {
        // Files without _ prefix are published
        return strpos($filename, '_') !== 0;
    }

    /**
     * Calculate statistics from schedules
     */
    private function calculateStats(array $schedules): array
    {
        $examinerCount = [];
        $courseCount = [];
        $allExaminers = [];
        $courseOwners = [];

        // Get all users for matching
        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        $userMap = [];

        foreach ($users as $user) {
            $nickname = $user['nickname'] ?? '';
            $thaiName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
            $userMap[$nickname] = $user;
            $userMap[$thaiName] = $user;
        }

        foreach ($schedules as $schedule) {
            // Count courses
            $courseCode = $schedule['course_code'] ?? '';
            $courseName = $schedule['course_name'] ?? '';
            $instructor = $schedule['instructor'] ?? '';

            if ($courseCode) {
                $courseCount[$courseCode] = true;

                // Try to find course owner from instructor field
                $instructorParts = explode(',', $instructor);
                $ownerInfo = ['matched' => false, 'name' => $instructor, 'user_info' => null];

                foreach ($instructorParts as $instructorName) {
                    $instructorName = trim($instructorName);
                    if ($instructorName && isset($userMap[$instructorName])) {
                        $user = $userMap[$instructorName];
                        $ownerInfo = [
                            'matched' => true,
                            'name' => $instructorName,
                            'user_info' => [
                                'uid' => $user['uid'],
                                'login_uid' => $user['login_uid'] ?? 'N/A',
                                'nickname' => $user['nickname'] ?? 'N/A',
                                'thai_name' => trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?: 'N/A',
                                'eng_name' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')) ?: 'N/A'
                            ]
                        ];
                        break;
                    }
                }

                $courseOwners[$courseCode] = [
                    'course_name' => $courseName,
                    'instructor' => $instructor,
                    'owner_info' => $ownerInfo
                ];
            }

            // Count examiners and check matching
            $ex1 = $schedule['examiner1'] ?? '';
            $ex2 = $schedule['examiner2'] ?? '';

            if ($ex1) {
                $examinerCount[$ex1] = ($examinerCount[$ex1] ?? 0) + 1;
                $allExaminers[] = $ex1;
            }
            if ($ex2) {
                $examinerCount[$ex2] = ($examinerCount[$ex2] ?? 0) + 1;
                $allExaminers[] = $ex2;
            }
        }

        // Analyze matching
        $matchedExaminers = [];
        $unmatchedExaminers = [];
        $uniqueExaminers = array_unique($allExaminers);

        foreach ($uniqueExaminers as $examiner) {
            if (isset($userMap[$examiner])) {
                $user = $userMap[$examiner];
                $matchedExaminers[] = [
                    'examiner_name' => $examiner,
                    'user_id' => $user['uid'],
                    'login_uid' => $user['login_uid'] ?? 'N/A',
                    'nickname' => $user['nickname'] ?? 'N/A',
                    'thai_name' => trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?: 'N/A'
                ];
            } else {
                $unmatchedExaminers[] = $examiner;
            }
        }

        return [
            'total_schedules' => count($schedules),
            'total_courses' => count($courseCount),
            'total_examiners' => count($examinerCount),
            'examiner_details' => $examinerCount,
            'course_owners' => $courseOwners,
            'matching_analysis' => [
                'total_unique_examiners' => count($uniqueExaminers),
                'matched_examiners' => count($matchedExaminers),
                'unmatched_examiners' => count($unmatchedExaminers),
                'match_rate' => count($uniqueExaminers) > 0 ? round((count($matchedExaminers) / count($uniqueExaminers)) * 100, 1) : 0,
                'matched_details' => $matchedExaminers,
                'unmatched_list' => $unmatchedExaminers
            ]
        ];
    }
}
