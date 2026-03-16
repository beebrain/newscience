<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExamImportBatchModel;
use App\Models\ExamScheduleModel;
use App\Models\ExamScheduleUserLinkModel;
use App\Models\ExamPublishVersionModel;
use App\Models\UserModel;
use App\Libraries\AccessControl;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ExamAdminController extends BaseController
{
    protected $batchModel;
    protected $scheduleModel;
    protected $linkModel;
    protected $versionModel;
    protected $userModel;

    public function __construct()
    {
        $this->batchModel    = new ExamImportBatchModel();
        $this->scheduleModel = new ExamScheduleModel();
        $this->linkModel     = new ExamScheduleUserLinkModel();
        $this->versionModel  = new ExamPublishVersionModel();
        $this->userModel     = new UserModel();
    }

    /**
     * Admin index page
     */
    public function index()
    {
        $adminId = session()->get('admin_id');
        if (!AccessControl::hasAccess($adminId, 'exam_admin')) {
            return redirect()->to(base_url('dashboard'))->with('error', 'คุณไม่มีสิทธิ์จัดการระบบสอบ');
        }

        $data = [
            'page_title' => 'จัดการตารางคุมสอบ',
            'batches'    => $this->batchModel->getBatches(['status' => 'published']),
        ];

        return view('admin/exam/index', $data);
    }

    /**
     * Upload form for Excel import
     */
    public function uploadForm()
    {
        $adminId = session()->get('admin_id');
        if (!AccessControl::hasAccess($adminId, 'exam_admin')) {
            return redirect()->to(base_url('dashboard'))->with('error', 'คุณไม่มีสิทธิ์จัดการระบบสอบ');
        }

        return view('admin/exam/upload_form', [
            'page_title' => 'นำเข้าข้อมูลตารางสอบ',
        ]);
    }

    /**
     * Process Excel upload
     */
    public function upload()
    {
        $adminId = session()->get('admin_id');
        if (!AccessControl::hasAccess($adminId, 'exam_admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $validationRule = [
            'excelFile' => [
                'label' => 'Excel file',
                'rules' => 'uploaded[excelFile]'
                    . '|mime_in[excelFile,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]'
                    . '|max_size[excelFile,5120]',
            ],
            'semester' => [
                'label' => 'Semester',
                'rules' => 'required|regex_match[/^[1-3]\/\d{4}$/]',
            ],
            'exam_type' => [
                'label' => 'Exam Type',
                'rules' => 'required|in_list[midterm,final]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $file      = $this->request->getFile('excelFile');
        $semester  = $this->request->getPost('semester');
        $examType  = $this->request->getPost('exam_type');
        $parts     = explode('/', $semester);
        $semesterNo = (int) $parts[0];
        $year      = (int) $parts[1];

        // Parse Excel
        $schedules = $this->parseExcel($file->getTempName());

        if (empty($schedules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบข้อมูลในไฟล์ Excel',
            ]);
        }

        // Create batch
        $batchId = $this->batchModel->insert([
            'semester_label' => $semester,
            'academic_year'  => $year,
            'semester_no'   => $semesterNo,
            'exam_type'     => $examType,
            'source_filename' => $file->getName(),
            'source_hash'   => md5_file($file->getTempName()),
            'status'        => 'draft',
            'imported_by'   => $adminId,
        ]);

        if (!$batchId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถสร้างรายการนำเข้าได้',
            ]);
        }

        // Save schedules and auto-match
        $imported = 0;
        $matched  = 0;

        foreach ($schedules as $schedule) {
            $scheduleData = [
                'batch_id'          => $batchId,
                'section_text'      => $schedule['section'] ?? '',
                'course_code'       => $schedule['course_code'] ?? '',
                'course_name'       => $schedule['course_name'] ?? '',
                'student_group'     => $schedule['student_group'] ?? '',
                'student_program'   => $schedule['student_program'] ?? '',
                'instructor_text'   => $schedule['instructor'] ?? '',
                'exam_date'         => $this->parseDate($schedule['exam_date'] ?? ''),
                'exam_time_text'    => $schedule['exam_time'] ?? '',
                'room'              => $schedule['room'] ?? '',
                'examiner1_text'    => $schedule['examiner1'] ?? '',
                'examiner2_text'    => $schedule['examiner2'] ?? '',
                'semester_label'    => $semester,
                'academic_year'     => $year,
                'semester_no'       => $semesterNo,
                'exam_type'         => $examType,
            ];

            $scheduleId = $this->scheduleModel->insert($scheduleData);

            if ($scheduleId) {
                $imported++;

                // Auto-match by nickname
                if (!empty($schedule['examiner1'])) {
                    $userId = $this->linkModel->autoMatchByNickname($scheduleId, $schedule['examiner1'], 'examiner1', $this->userModel);
                    if ($userId) $matched++;
                }

                if (!empty($schedule['examiner2'])) {
                    $userId = $this->linkModel->autoMatchByNickname($scheduleId, $schedule['examiner2'], 'examiner2', $this->userModel);
                    if ($userId) $matched++;
                }
            }
        }

        return $this->response->setJSON([
            'success'      => true,
            'batch_id'     => $batchId,
            'imported'     => $imported,
            'matched'      => $matched,
            'message'      => "นำเข้าสำเร็จ: {$imported} รายการ, จับคู่อัตโนมัติ: {$matched} รายการ",
            'redirect_url' => base_url("admin/exam/preview/{$batchId}"),
        ]);
    }

    /**
     * Preview imported batch
     */
    public function preview(int $batchId)
    {
        $adminId = session()->get('admin_id');
        if (!AccessControl::hasAccess($adminId, 'exam_admin')) {
            return redirect()->to(base_url('dashboard'))->with('error', 'ไม่มีสิทธิ์');
        }

        $batch = $this->batchModel->find($batchId);
        if (!$batch) {
            return redirect()->to(base_url('admin/exam'))->with('error', 'ไม่พบข้อมูล');
        }

        $schedules = $this->scheduleModel->getByBatch($batchId);
        $matchStats = $this->linkModel->getMatchStats($batchId);

        $data = [
            'page_title' => 'ตรวจสอบข้อมูลนำเข้า',
            'batch'      => $batch,
            'schedules'  => $schedules,
            'matchStats' => $matchStats,
        ];

        return view('admin/exam/preview', $data);
    }

    /**
     * Publish batch
     */
    public function publish(int $batchId)
    {
        $adminId = session()->get('admin_id');
        if (!AccessControl::hasAccess($adminId, 'exam_admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $batch = $this->batchModel->find($batchId);
        if (!$batch) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }

        // Update batch status
        $this->batchModel->publish($batchId, $adminId);

        // Publish schedules
        $this->scheduleModel->publishBatch($batchId);

        // Set active version
        $this->versionModel->setActive(
            $batchId,
            $batch['semester_label'],
            $batch['exam_type'],
            $adminId
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'เผยแพร่ตารางสอบสำเร็จ',
        ]);
    }

    /**
     * Get available semesters (AJAX)
     */
    public function getSemesters()
    {
        $versions = $this->versionModel->where('is_active', 1)
            ->select('semester_label, exam_type')
            ->distinct()
            ->findAll();

        return $this->response->setJSON(['success' => true, 'semesters' => $versions]);
    }

    /**
     * Manual match examiner to user
     */
    public function manualMatch()
    {
        $adminId = session()->get('admin_id');
        if (!AccessControl::hasAccess($adminId, 'exam_admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $scheduleId = $this->request->getPost('schedule_id');
        $userUid    = $this->request->getPost('user_uid');
        $role       = $this->request->getPost('role');

        if (!$scheduleId || !$userUid || !in_array($role, ['examiner1', 'examiner2'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
        }

        // Get schedule to find original text
        $schedule = $this->scheduleModel->find($scheduleId);
        if (!$schedule) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบตารางสอบ']);
        }

        $matchedValue = ($role === 'examiner1') ? $schedule['examiner1_text'] : $schedule['examiner2_text'];

        // Clear existing links for this schedule+role
        $this->linkModel->where('schedule_id', $scheduleId)
            ->where('link_role', $role)
            ->delete();

        // Create new link
        $this->linkModel->createLink($scheduleId, $userUid, $role, $matchedValue, 'manual', 1.0);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'จับคู่สำเร็จ',
        ]);
    }

    /**
     * Search users by nickname (AJAX)
     */
    public function searchUsers()
    {
        $query = $this->request->getGet('q');
        if (strlen($query) < 2) {
            return $this->response->setJSON(['success' => true, 'users' => []]);
        }

        // Search by nickname, name, or email
        $users = $this->userModel
            ->groupStart()
            ->like('nickname', $query)
            ->orLike('tf_name', $query)
            ->orLike('tl_name', $query)
            ->orLike('gf_name', $query)
            ->orLike('gl_name', $query)
            ->orLike('email', $query)
            ->groupEnd()
            ->where('status', 'active')
            ->limit(20)
            ->findAll();

        $formatted = array_map(function ($user) {
            return [
                'uid'      => $user['uid'],
                'nickname' => $user['nickname'] ?? '',
                'name_th'  => trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')),
                'name_en'  => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')),
                'email'    => $user['email'] ?? '',
            ];
        }, $users);

        return $this->response->setJSON(['success' => true, 'users' => $formatted]);
    }

    /**
     * Parse Excel file
     */
    private function parseExcel(string $filePath): array
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip header
        array_shift($rows);

        $rawData = [];
        $prevExaminer1 = '';
        $prevExaminer2 = '';
        $prevCourseCode = '';

        // First pass: extract raw data and handle empty examiners
        foreach ($rows as $row) {
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $schedule = [
                'section'         => trim($row[0] ?? ''),
                'course_code'     => trim($row[1] ?? ''),
                'course_name'     => trim($row[2] ?? ''),
                'student_group'   => trim($row[3] ?? ''),
                'student_program' => trim($row[4] ?? ''),
                'instructor'      => trim($row[5] ?? ''),
                'exam_date'       => trim($row[6] ?? ''),
                'exam_time'       => trim($row[7] ?? ''),
                'room'            => trim($row[8] ?? ''),
                'examiner1'       => trim($row[9] ?? ''),
                'examiner2'       => trim($row[10] ?? ''),
            ];

            // Inherit examiners from previous row if same course
            if (empty($schedule['examiner1']) && empty($schedule['examiner2']) && $schedule['course_code'] == $prevCourseCode) {
                $schedule['examiner1'] = $prevExaminer1;
                $schedule['examiner2'] = $prevExaminer2;
            }

            $prevExaminer1 = $schedule['examiner1'];
            $prevExaminer2 = $schedule['examiner2'];
            $prevCourseCode = $schedule['course_code'];

            $rawData[] = $schedule;
        }

        // Second pass: merge sections with same examiners/course/date/time/room
        $merged = [];
        $groups = [];

        foreach ($rawData as $current) {
            $key = implode('|', [
                $current['course_code'],
                $current['examiner1'],
                $current['examiner2'],
                $current['exam_date'],
                $current['exam_time'],
                $current['room'],
            ]);

            if (isset($groups[$key])) {
                $index = $groups[$key];
                $sections = array_map('trim', explode(',', $merged[$index]['section']));
                if (!in_array($current['section'], $sections)) {
                    $sections[] = $current['section'];
                    sort($sections, SORT_NUMERIC);
                    $merged[$index]['section'] = implode(',', $sections);
                }
            } else {
                $newIndex = count($merged);
                $merged[] = $current;
                $groups[$key] = $newIndex;
            }
        }

        return $merged;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) return null;

        // Try common formats
        $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $dateStr);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }
}
