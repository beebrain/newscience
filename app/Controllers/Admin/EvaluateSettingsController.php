<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Evaluate\EvaluateSettingsModel;

/**
 * จัดการตั้งค่าระบบการประเมินการสอน
 */
class EvaluateSettingsController extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new EvaluateSettingsModel();

        if (! session()->get('admin_logged_in')) {
            redirect()->to(base_url('admin/login'))->send();
            exit;
        }
    }

    /**
     * แสดงหน้าตั้งค่า
     */
    public function index()
    {
        $settings = $this->settingsModel->getSettings();

        $data = [
            'page_title' => 'ตั้งค่าระบบการประเมินการสอน',
            'settings'   => $settings,
        ];

        return view('evaluate/admin_evaluate_settings', $data);
    }

    /**
     * บันทึกการตั้งค่า
     */
    public function save()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request'])->setStatusCode(400);
        }

        $rules = [
            'is_active'                  => 'required|in_list[0,1]',
            'start_date'                 => 'permit_empty|valid_date',
            'end_date'                   => 'permit_empty|valid_date',
            'notification_emails'        => 'permit_empty',
            'referee_email_subject'      => 'permit_empty|max_length[255]',
            'referee_email_template'     => 'permit_empty',
            'applicant_email_subject'    => 'permit_empty|max_length[255]',
            'applicant_email_template'   => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $data = [
            'is_active'                  => $this->request->getPost('is_active') ? 1 : 0,
            'start_date'                 => $this->request->getPost('start_date') ?: null,
            'end_date'                   => $this->request->getPost('end_date') ?: null,
            'notification_emails'        => $this->request->getPost('notification_emails') ?: null,
            'referee_email_subject'      => $this->request->getPost('referee_email_subject') ?: null,
            'referee_email_template'     => $this->request->getPost('referee_email_template') ?: null,
            'applicant_email_subject'    => $this->request->getPost('applicant_email_subject') ?: null,
            'applicant_email_template'   => $this->request->getPost('applicant_email_template') ?: null,
        ];

        // ตรวจสอบว่ามีข้อมูลหรือยัง
        $existing = $this->settingsModel->first();
        if ($existing) {
            $result = $this->settingsModel->update($existing['id'], $data);
        } else {
            $result = $this->settingsModel->insert($data);
        }

        if (! $result) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถบันทึกการตั้งค่าได้',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'บันทึกการตั้งค่าเรียบร้อย',
        ]);
    }
}
