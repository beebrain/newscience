<?php

namespace App\Controllers;

use App\Models\ExamScheduleModel;
use App\Models\ExamPublishVersionModel;
use App\Models\ExamScheduleUserLinkModel;

class ExamController extends BaseController
{
    protected $scheduleModel;
    protected $versionModel;
    protected $linkModel;

    public function __construct()
    {
        $this->scheduleModel = new ExamScheduleModel();
        $this->versionModel  = new ExamPublishVersionModel();
        $this->linkModel     = new ExamScheduleUserLinkModel();
    }

    /**
     * Personal exam schedule view
     */
    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->to(base_url('login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        // Check if user has exam access
        $adminId = session()->get('admin_id');
        if ($adminId) {
            // Admin viewing - use session
            if (!\App\Libraries\AccessControl::hasAccess($adminId, 'exam') && 
                !\App\Libraries\AccessControl::hasAccess($adminId, 'exam_admin')) {
                return redirect()->to(base_url('dashboard'))->with('error', 'ไม่มีสิทธิ์เข้าถึง');
            }
        }

        $data = [
            'page_title' => 'ตารางคุมสอบ',
            'user'       => $user,
        ];

        return view('exam/index', $data);
    }

    /**
     * Get current user from session
     */
    private function getCurrentUser(): ?array
    {
        // Try admin session first
        $adminId = session()->get('admin_id');
        if ($adminId) {
            return [
                'uid' => $adminId,
                'name' => session()->get('admin_name'),
            ];
        }

        // Try regular user session
        $userUid = session()->get('user_uid');
        if ($userUid) {
            $userModel = new \App\Models\UserModel();
            return $userModel->find($userUid);
        }

        return null;
    }

    /**
     * AJAX: Get available semesters
     */
    public function getSemesters()
    {
        $versions = $this->versionModel
            ->select('semester_label, exam_type')
            ->where('is_active', 1)
            ->distinct()
            ->orderBy('semester_label', 'DESC')
            ->findAll();

        return $this->response->setJSON(['success' => true, 'semesters' => $versions]);
    }

    /**
     * AJAX: Get personal schedule for semester/type
     */
    public function getSchedule()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        }

        $semester = $this->request->getPost('semester');
        $examType = $this->request->getPost('exam_type');

        if (!$semester || !$examType) {
            return $this->response->setJSON(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
        }

        // Check if published version exists
        $version = $this->versionModel->getActive($semester, $examType);
        if (!$version) {
            return $this->response->setJSON(['success' => false, 'message' => 'ยังไม่มีข้อมูลตารางสอบ']);
        }

        // Get schedules for this user
        $schedules = $this->scheduleModel->getByUser($user['uid'], $semester, $examType);

        if (empty($schedules)) {
            return $this->response->setJSON([
                'success'   => true,
                'schedules' => [],
                'stats'     => ['total' => 0, 'as_examiner1' => 0, 'as_examiner2' => 0],
                'message'   => 'ไม่พบตารางคุมสอบสำหรับท่านในภาคเรียนนี้',
            ]);
        }

        // Calculate stats
        $stats = [
            'total'        => count($schedules),
            'as_examiner1' => count(array_filter($schedules, fn($s) => $s['link_role'] === 'examiner1')),
            'as_examiner2' => count(array_filter($schedules, fn($s) => $s['link_role'] === 'examiner2')),
        ];

        // Format for display
        $formatted = array_map(function ($schedule) {
            return [
                'id'            => $schedule['id'],
                'course_code'   => $schedule['course_code'],
                'course_name'   => $schedule['course_name'],
                'section'       => $schedule['section_text'],
                'student_group' => $schedule['student_group'],
                'exam_date'     => $schedule['exam_date'],
                'exam_time'     => $schedule['exam_time_text'],
                'room'          => $schedule['room'],
                'role'          => $schedule['link_role'],
            ];
        }, $schedules);

        return $this->response->setJSON([
            'success'   => true,
            'schedules' => $formatted,
            'stats'     => $stats,
        ]);
    }

    /**
     * AJAX: Get all examiners for a schedule (for modal)
     */
    public function getExaminerDetails(int $scheduleId)
    {
        $schedule = $this->scheduleModel->find($scheduleId);
        if (!$schedule) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }

        $links = $this->linkModel->getBySchedule($scheduleId);

        $examiners = array_map(function ($link) {
            return [
                'role'     => $link['link_role'],
                'nickname' => $link['nickname'] ?? '',
                'name_th'  => trim(($link['tf_name'] ?? '') . ' ' . ($link['tl_name'] ?? '')),
                'email'    => $link['email'] ?? '',
            ];
        }, $links);

        return $this->response->setJSON([
            'success'   => true,
            'schedule'  => [
                'course_code' => $schedule['course_code'],
                'course_name' => $schedule['course_name'],
                'exam_date'   => $schedule['exam_date'],
                'exam_time'   => $schedule['exam_time_text'],
                'room'        => $schedule['room'],
            ],
            'examiners' => $examiners,
        ]);
    }
}
