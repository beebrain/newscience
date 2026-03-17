<?php

namespace App\Controllers\Evaluate;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\Evaluate\TeachingEvaluationModel;
use App\Models\Evaluate\EvaluationScoreModel;
use App\Models\Edoc\SendmailModel;

class LectureEvaluateController extends BaseController
{
    protected $userModel;
    protected $teachingModel;
    protected $scoreModel;
    protected $sendmail;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->teachingModel = new TeachingEvaluationModel();
        $this->scoreModel    = new EvaluationScoreModel();
        $this->sendmail      = new SendmailModel();
        helper(['form', 'url']);

        if (! session()->get('admin_logged_in')) {
            redirect()->to(base_url('admin/login'))->send();
            exit;
        }
    }

    /**
     * Check if user has admin role for managing evaluate
     */
    private function canManageEvaluate(int $uid): bool
    {
        $user = $this->userModel->find($uid);
        return in_array($user['role'] ?? '', ['super_admin', 'faculty_admin'], true);
    }

    public function index()
    {
        return $this->submitEvaluate();
    }

    public function submitEvaluate()
    {
        $uid = (int) session()->get('admin_id');
        $user = $this->userModel->find($uid);
        $data['infoUser'] = is_array($user) ? $user : [];
        $data['can_manage_evaluate'] = $this->canManageEvaluate($uid);

        $data['curriculumOptions'] = [];
        $curriculumClass = class_exists('App\Models\CurriculumModel') ? 'App\Models\CurriculumModel' : null;
        if ($curriculumClass !== null) {
            $curriculumModel = new $curriculumClass();
            $data['curriculumOptions'] = method_exists($curriculumModel, 'getDropdownOptions') ? $curriculumModel->getDropdownOptions() : [];
        }
        $user = $this->userModel->find($uid);
        $data['infoUser'] = is_array($user) ? $user : [];
        $data['can_manage_evaluate'] = $this->canManageEvaluate($uid);
        $userEmail = $user['email'] ?? '';

        // Use email to fetch submissions (imported data may have mismatched uid)
        $data['userSubmissions']    = $userEmail ? $this->teachingModel->getByEmail($userEmail) : [];
        $submittedPositions         = $this->teachingModel->getSubmittedPositionsByEmail($userEmail);
        $data['submittedPositions'] = $submittedPositions;

        // Use cooldown-aware position check with email
        $data['availablePositions'] = $this->teachingModel->getAvailablePositionsWithCooldownByEmail($userEmail);
        $data['canSubmitNewRequest'] = $data['availablePositions'] !== [];

        // Provide cooldown info for UI display using email
        $allPositions = ['ผู้ช่วยศาสตราจารย์', 'รองศาสตราจารย์', 'ศาสตราจารย์'];
        $data['cooldownInfo'] = [];
        foreach ($allPositions as $pos) {
            if ($this->teachingModel->isPositionInCooldownByEmail($userEmail, $pos)) {
                $data['cooldownInfo'][$pos] = $this->teachingModel->getCooldownEndDateByEmail($userEmail, $pos);
            }
        }

        $result = $userEmail ? $this->teachingModel->getSubmissionsWithStatusByEmail($userEmail) : null;
        if ($result && $result->getNumRows() > 0) {
            $rows = $result->getResultArray();
            $data['teachingsubmit'] = $rows[0];
            $tid = (int) ($data['teachingsubmit']['tid'] ?? $data['teachingsubmit']['id']);
            $data['evaluate'] = $this->scoreModel->getByTeachingId($tid);
        } else {
            $data['teachingsubmit'] = null;
            $data['evaluate'] = [];
        }

        $data['page_title'] = 'ระบบการประเมินการสอน';
        return view('evaluate/submit_evaluate', $data);
    }

    private function getAvailablePositions(string $email): array
    {
        return $this->teachingModel->getAvailablePositionsWithCooldownByEmail($email);
    }

    public function save()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request'])->setStatusCode(400);
        }

        $uid = (int) session()->get('admin_id');
        $requestedPosition = $this->request->getPost('position');
        $user = $this->userModel->find($uid);
        $userEmail = $user['email'] ?? '';
        $availablePositions = $this->getAvailablePositions($userEmail);

        if ($availablePositions === []) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่มีตำแหน่งที่สามารถส่งคำร้องได้ในขณะนี้ (คำร้องทุกตำแหน่งยังอยู่ระหว่างดำเนินการ)',
            ])->setStatusCode(409);
        }
        if (! in_array($requestedPosition, $availablePositions, true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ตำแหน่ง "' . $requestedPosition . '" ยังมีคำร้องที่อยู่ระหว่างดำเนินการ สามารถส่งใหม่ได้เมื่อคำร้องเดิมสิ้นสุดแล้ว',
                'availablePositions' => $availablePositions,
            ])->setStatusCode(409);
        }

        $file = $this->request->getFile('filedoc');
        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'กรุณาแนบไฟล์เอกสารประกอบ',
            ])->setStatusCode(422);
        }

        $fileName = $this->generateUniqueFileName($file);
        $uploadPath = WRITEPATH . 'uploads/documents/';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        if (! $file->move($uploadPath, $fileName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถอัปโหลดไฟล์ได้',
            ])->setStatusCode(500);
        }

        $user = $this->userModel->find($uid);
        $userEmail = $user['email'] ?? '';

        // Validate email must be @live.uru.ac.th
        if (!str_ends_with(strtolower($userEmail), '@live.uru.ac.th')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Email must be @live.uru.ac.th domain only. Please contact administrator.',
            ])->setStatusCode(403);
        }

        $teachingData = [
            'first_name'        => $this->request->getPost('first_name'),
            'last_name'         => $this->request->getPost('last_name'),
            'title_thai'        => $this->request->getPost('title_thai'),
            'curriculum'        => $this->request->getPost('curriculum_name') ?: $this->request->getPost('curriculum'),
            'position'          => $requestedPosition,
            'position_major'    => $this->request->getPost('position_major'),
            'position_major_id' => $this->request->getPost('position_major_id'),
            'start_date'        => $this->request->getPost('start_date'),
            'subject_id'        => $this->request->getPost('subject_id'),
            'subject_name'      => $this->request->getPost('subject_name'),
            'subject_credit'    => $this->request->getPost('subject_credit'),
            'subject_teacher'   => $this->request->getPost('subject_teacher') ?: '-',
            'subject_detail'    => $this->request->getPost('subject_detail'),
            'file_doc'          => $fileName,
            'link_video'        => $this->request->getPost('link_video') ?: '',
            'uid'               => $uid,
            'email'             => $this->userModel->find($uid)['email'] ?? '',
            'status'            => TeachingEvaluationModel::STATUS_PENDING,
            'submit_date'       => date('Y-m-d'),
        ];

        $insertId = $this->teachingModel->insert($teachingData);
        if (! $insertId) {
            @unlink($uploadPath . $fileName);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถบันทึกข้อมูลได้',
            ])->setStatusCode(500);
        }

        $user = $this->userModel->find($uid);
        if (! empty($user['email'])) {
            try {
                $this->sendEmailNotification($teachingData, $user);
            } catch (\Throwable $e) {
                log_message('warning', 'Evaluation email failed: ' . $e->getMessage());
            }
        }

        $remaining = $this->getAvailablePositions($userEmail);
        return $this->response->setJSON([
            'success' => true,
            'message' => 'ส่งคำร้องขอประเมินการสอนสำหรับตำแหน่ง "' . $requestedPosition . '" เรียบร้อยแล้ว',
            'data' => [
                'filename' => $fileName,
                'submit_date' => date('Y-m-d'),
                'position' => $requestedPosition,
                'remainingPositions' => $remaining,
            ],
        ]);
    }

    private function generateUniqueFileName($file): string
    {
        $baseName = pathinfo($file->getClientName(), PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        return $baseName . '_' . time() . '_' . uniqid('', true) . '.' . $file->getClientExtension();
    }

    private function sendEmailNotification(array $teachingData, array $user): void
    {
        $email = \Config\Services::email();
        $subject = 'คำร้องประเมินการสอนของ ' . ($teachingData['first_name'] ?? '') . ' ' . ($teachingData['last_name'] ?? '');
        $message = 'เรียน ' . ($teachingData['first_name'] ?? '') . ' ' . ($teachingData['last_name'] ?? '') . "\n\n";
        $message .= "แจ้งระบบการประเมินตำแหน่งวิชาการ มหาวิทยาลัยราชภัฏอุตรดิตถ์\n";
        $message .= "ทางระบบได้รับความประสงค์ในการประเมินการสอนเรียบร้อยแล้ว ทั้งนี้ ทางผู้เกี่ยวข้องจะดำเนินการในลำดับต่อไป จึงแจ้งมาเพื่อทราบ\n\n";
        $message .= "รายละเอียด:\n";
        $message .= "- รายวิชา: " . ($teachingData['subject_name'] ?? '') . "\n";
        $message .= "- รหัสวิชา: " . ($teachingData['subject_id'] ?? '') . "\n";
        $message .= "- วันที่ส่ง: " . date('d/m/Y H:i:s') . "\n";

        $email->setTo($user['email']);
        $email->setSubject($subject);
        $email->setMessage($message);
        $email->send();
    }

    public function checkAvailablePositions()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }
        $uid = (int) session()->get('admin_id');
        $user = $this->userModel->find($uid);
        $userEmail = $user['email'] ?? '';
        $available = $this->getAvailablePositions($userEmail);
        return $this->response->setJSON([
            'available' => $available,
            'canSubmit' => $available !== [],
            'message'  => $available === [] ? 'คุณได้ส่งคำร้องขอประเมินครบทุกตำแหน่งแล้ว' : 'มีตำแหน่งที่สามารถส่งคำร้องได้ ' . count($available) . ' ตำแหน่ง',
        ]);
    }
}
