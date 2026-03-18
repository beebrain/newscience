<?php

namespace App\Controllers\Evaluate;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\Evaluate\TeachingEvaluationModel;
use App\Models\Evaluate\EvaluationScoreModel;
use App\Models\Evaluate\EvaluationRefereeModel;
use App\Models\Evaluate\EvaluateSettingsModel;
use App\Models\Edoc\SendmailModel;

class AdminEvaluateController extends BaseController
{
    protected $userModel;
    protected $teachingModel;
    protected $scoreModel;
    protected $refereeModel;
    protected $sendmail;
    protected $settingsModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->teachingModel = new TeachingEvaluationModel();
        $this->scoreModel    = new EvaluationScoreModel();
        $this->refereeModel  = new EvaluationRefereeModel();
        $this->sendmail      = new SendmailModel();
        $this->settingsModel = new EvaluateSettingsModel();

        if (! session()->get('admin_logged_in')) {
            session()->remove('access_token');
            session()->remove('admin_id');
            session()->remove('admin_logged_in');
            redirect()->to(base_url('admin/login'))->send();
            exit;
        }
    }

    /**
     * Check if user has admin role
     */
    private function hasManageRights(int $uid): bool
    {
        $user = $this->userModel->find($uid);
        $role = $user['role'] ?? '';
        return in_array($role, ['super_admin', 'faculty_admin'], true);
    }

    public function index()
    {
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return redirect()->to(base_url('dashboard'))->with('error', 'ไม่มีสิทธิ์จัดการระบบประเมิน');
        }
        return $this->showAllEvaluate();
    }

    public function showAllEvaluate()
    {
        $adminId = session()->get('admin_id');
        $user    = $this->userModel->find($adminId);
        $data['infoUser'] = is_array($user) ? $user : [];

        $data['teachinglist'] = $this->teachingModel->getAllForAdmin();
        $data['allTeacher']   = $this->refereeModel->getActiveReferees();

        $data['detailref'] = [];
        foreach ($data['teachinglist'] as $row) {
            $data['detailref'][] = $this->scoreModel->getByTeachingId((int) $row['id']);
        }
        $data['page_title'] = 'จัดการระบบประเมินผลการสอน';

        return view('evaluate/admin_evaluate', $data);
    }

    public function getResult()
    {
        $id = (int) $this->request->getPost('id');
        if (! $id) {
            return $this->response->setJSON(['referees' => []]);
        }
        $referees = $this->scoreModel->getActiveByTeachingId($id);
        return $this->response->setJSON(['referees' => $referees]);
    }

    public function getEvaluateInfo()
    {
        $id = (int) $this->request->getPost('id');
        if (! $id) {
            return $this->response->setJSON([]);
        }
        $teaching = $this->teachingModel->getById($id);
        if (! $teaching) {
            return $this->response->setJSON([]);
        }
        $teaching['referees'] = $this->scoreModel->getByTeachingId($id);
        return $this->response->setJSON($teaching);
    }

    public function printRefAndSave()
    {
        $idEvaluate = (int) $this->request->getPost('idEvaluate');
        if (! $idEvaluate) {
            return $this->response->setJSON(['error' => 'missing id']);
        }

        $this->scoreModel->updateByCondition(
            ['teaching_id' => $idEvaluate],
            ['status' => (string) EvaluationScoreModel::STATUS_DELETED]
        );

        $currentDate = date('Y-m-d');
        $batch = [];
        for ($i = 1; $i <= 3; $i++) {
            $email = $this->request->getPost("ref{$i}");
            $name  = $this->request->getPost("nameref{$i}");
            if ($email !== null && $email !== '') {
                $batch[] = [
                    'teaching_id' => $idEvaluate,
                    'email'       => $email,
                    'name'        => $name ?? '',
                    'status'      => 0,
                    'ref_num'     => $i,
                    'send_date'   => $currentDate,
                ];
            }
        }
        if ($batch !== []) {
            $this->scoreModel->insertBatch($batch);
        }

        return $this->response->setJSON([
            'idEvaluate' => $idEvaluate,
            'ref1'       => $this->request->getPost('ref1'),
            'ref2'       => $this->request->getPost('ref2'),
            'ref3'       => $this->request->getPost('ref3'),
        ]);
    }

    public function sendmailEvaluate()
    {
        $id    = (int) $this->request->getPost('id');
        $mail  = $this->request->getPost('mail');
        $name  = $this->request->getPost('name');
        $refnum = (int) $this->request->getPost('refnum');

        $data = ['id' => $id, 'mail' => $mail, 'name' => $name, 'refnum' => $refnum];

        if ($mail !== '' && $mail !== null && $name !== '' && $name !== null && $id) {
            $currentDate = date('Y-m-d');
            $evaluateData = [
                'teaching_id' => $id,
                'email'       => $mail,
                'name'        => $name,
                'status'      => 0,
                'ref_num'     => $refnum,
                'send_date'   => $currentDate,
            ];

            if (! $this->scoreModel->checkDuplicate($evaluateData)) {
                $this->scoreModel->updateByCondition(
                    [
                        'teaching_id' => $id,
                        'ref_num'     => $refnum,
                        'status'      => 0,
                    ],
                    ['status' => (string) EvaluationScoreModel::STATUS_DELETED]
                );
                $this->scoreModel->insertRecord($evaluateData);
                $data['info'] = '<p>insert new data complete</p>';
            }

            $teaching = $this->teachingModel->getById($id);
            if ($teaching) {
                $payload = ['id' => $id, 'email' => $mail];
                $linkAccess = base_url('evaluate/evaluate/' . base64_encode(json_encode($payload)));

                // Use settings for referee email template
                $settings = $this->settingsModel->getSettings();

                $templateData = [
                    'referee_name'   => $name,
                    'applicant_name' => ($teaching['first_name'] ?? '') . ' ' . ($teaching['last_name'] ?? ''),
                    'position'       => $teaching['position'] ?? '',
                    'position_major' => $teaching['position_major'] ?? '',
                    'subject_name'   => $teaching['subject_name'] ?? '',
                    'subject_id'     => $teaching['subject_id'] ?? '',
                    'link_access'    => $linkAccess,
                ];

                // Parse subject from settings
                $subject = $this->settingsModel->parseTemplate(
                    $settings['referee_email_subject'] ?? 'เรียนเชิญพิจารณาและประเมินการสอน',
                    $templateData
                );

                // Parse template from settings with fallback
                $template = $settings['referee_email_template'] ?? '';
                if (empty($template)) {
                    // Fallback default template
                    $template = "เรียน {referee_name}\n\n"
                        . "ตามประกาศคณะกรรมการพิจารณาตําแหน่งวิชาการ มหาวิทยาลัยราชภัฏอุตรดิตถ์ เรื่อง ขั้นตอนและวิธีการเกี่ยวข้องกับผลการสอน "
                        . "พศ. 2565 ข้อ 4(4.2) ให้คณบดี/รองคณบดี เสนอชื่อผู้ทรงคุณวุฒิประเมินผลการสอนของบุคลากรในคณะฯ นั้น ทางคณะฯ เห็นว่าท่านเป็นผู้มีความรู้ "
                        . "และความเชี่ยวชาญในสาขา{position_major} "
                        . "จึงขอเชิญท่านประเมินผลการสอนในรายวิชา {subject_name} "
                        . "ทั้งนี้ขอให้ท่านประเมินให้แล้วเสร็จภายใน 30 วัน ตาม Link ที่ระบุให้ หลังจากที่ท่านได้รับการแต่งตั้ง \n"
                        . "{link_access}\n\n"
                        . "ขอแสดงความนับถือ\n"
                        . "ผศ.ดร.เสรี แสงอุทัย\n"
                        . "คณบดีคณะวิทยาศาสตร์และเทคโนโลยี";
                }

                $detail = $this->settingsModel->parseTemplate($template, $templateData);

                $bcc = env('mail.refereeBcc');
                $this->sendmail->sendMail($mail, $detail, $subject, $bcc);
            }
        }

        return $this->response->setJSON($data);
    }

    public function saveDate()
    {
        $id       = (int) $this->request->getPost('id');
        $stopdate = $this->request->getPost('stopdate');
        if ($id && $stopdate !== null && $stopdate !== '') {
            $this->teachingModel->updateRecord(['stop_date' => $stopdate], $id);
        }
        return $this->response->setJSON(['id' => $id, 'stopdate' => $stopdate]);
    }

    /**
     * Search teaching evaluations by email
     */
    public function searchByEmail()
    {
        $email = $this->request->getGet('email');
        if (! $email) {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาระบุอีเมล']);
        }

        $evaluations = $this->teachingModel->searchByEmail($email);
        $result = [];

        foreach ($evaluations as $eval) {
            $eval['referees'] = $this->scoreModel->getByTeachingId($eval['id']);
            $result[] = $eval;
        }

        return $this->response->setJSON([
            'success' => true,
            'count' => count($result),
            'data' => $result
        ]);
    }

    /**
     * Delete teaching evaluation record
     */
    public function delete()
    {
        $id = (int) $this->request->getPost('id');

        if (! $id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบรหัสรายการที่ต้องการลบ'
            ])->setStatusCode(400);
        }

        // Check if user has manage rights
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่มีสิทธิ์ลบรายการ'
            ])->setStatusCode(403);
        }

        // Get record info before deletion for logging
        $record = $this->teachingModel->getById($id);
        if (! $record) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบรายการที่ต้องการลบ'
            ])->setStatusCode(404);
        }

        // Delete related evaluation scores first
        $this->scoreModel->updateByCondition(
            ['teaching_id' => $id],
            ['status' => (string) EvaluationScoreModel::STATUS_DELETED]
        );

        // Delete the teaching evaluation record
        $deleted = $this->teachingModel->delete($id);

        if ($deleted) {
            log_message('info', 'Teaching evaluation deleted: ID=' . $id . ' by admin=' . $adminId);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'ลบรายการเรียบร้อยแล้ว'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'ไม่สามารถลบรายการได้'
        ])->setStatusCode(500);
    }
}
