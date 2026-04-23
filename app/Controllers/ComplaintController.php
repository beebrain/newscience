<?php

namespace App\Controllers;

use App\Models\ComplaintModel;
use App\Models\SiteSettingModel;
use App\Services\ComplaintNotificationService;
use CodeIgniter\HTTP\Files\UploadedFile;

class ComplaintController extends BaseController
{
    private const RATE_LIMIT_WINDOW = 600;
    private const RATE_LIMIT_MAX_ATTEMPTS = 3;

    private ComplaintModel $complaintModel;
    private SiteSettingModel $siteSettingModel;
    private ComplaintNotificationService $notificationService;

    public function __construct()
    {
        $this->complaintModel = new ComplaintModel();
        $this->siteSettingModel = new SiteSettingModel();
        $this->notificationService = new ComplaintNotificationService();
    }

    public function index(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'แจ้งข้อร้องเรียน | ' . ($siteInfo['site_name_th'] ?? 'Complaint Form'),
            'meta_description' => 'แบบฟอร์มแจ้งข้อร้องเรียนถึงกรรมการบริหารคณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'complaints',
            'contact_email' => $siteInfo['contact_email'] ?? $siteInfo['email'] ?? '',
            'contact_phone' => $siteInfo['contact_phone'] ?? $siteInfo['phone'] ?? '',
        ]);

        return view('pages/complaints', $data);
    }

    public function submit()
    {
        $rules = [
            'complainant_name' => 'required|max_length[255]',
            'complainant_email' => 'required|valid_email|max_length[255]',
            'complainant_phone' => 'permit_empty|max_length[50]',
            'subject' => 'required|max_length[255]',
            'detail' => 'required|min_length[10]|max_length[5000]',
            'attachment' => 'permit_empty|max_size[attachment,5120]|ext_in[attachment,pdf,doc,docx,jpg,jpeg,png]|mime_in[attachment,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('error', 'กรุณาตรวจสอบข้อมูลให้ครบถ้วน');
        }

        if ($this->hasReachedRateLimit()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'มีการส่งเรื่องร้องเรียนจากเครือข่ายนี้หลายครั้ง กรุณารอสักครู่แล้วลองใหม่');
        }

        $attachmentPath = $this->storeAttachment($this->request->getFile('attachment'));
        if ($attachmentPath === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'ไม่สามารถอัปโหลดไฟล์แนบได้');
        }

        $complaintId = $this->complaintModel->createComplaint([
            'complainant_name' => trim((string) $this->request->getPost('complainant_name')),
            'complainant_email' => strtolower(trim((string) $this->request->getPost('complainant_email'))),
            'complainant_phone' => trim((string) $this->request->getPost('complainant_phone')),
            'subject' => trim((string) $this->request->getPost('subject')),
            'detail' => trim((string) $this->request->getPost('detail')),
            'attachment_path' => $attachmentPath ?: null,
            'status' => ComplaintModel::STATUS_NEW,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 65535),
        ]);

        $complaint = $this->complaintModel->find($complaintId);
        if ($complaint !== null) {
            $this->notificationService->sendNewComplaintNotification($complaint);
        }

        $this->incrementRateLimit();

        return redirect()->to(base_url('complaints'))
            ->with('success', 'ส่งเรื่องร้องเรียนเรียบร้อยแล้ว เจ้าหน้าที่จะรับทราบและดำเนินการต่อไป');
    }

    protected function getCommonData(): array
    {
        $settings = $this->siteSettingModel->getAll();
        $layout = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        return [
            'settings' => $settings,
            'site_info' => $settings,
            'layout' => $layout,
        ];
    }

    private function storeAttachment(?UploadedFile $file)
    {
        if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadPath = WRITEPATH . 'uploads/complaints';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            return false;
        }

        $newName = $file->getRandomName();
        if (! $file->move($uploadPath, $newName)) {
            return false;
        }

        return 'complaints/' . $newName;
    }

    private function hasReachedRateLimit(): bool
    {
        $cache = service('cache');
        $attempts = (int) ($cache->get($this->getRateLimitKey()) ?? 0);

        return $attempts >= self::RATE_LIMIT_MAX_ATTEMPTS;
    }

    private function incrementRateLimit(): void
    {
        $cache = service('cache');
        $key = $this->getRateLimitKey();
        $attempts = (int) ($cache->get($key) ?? 0);
        $cache->save($key, $attempts + 1, self::RATE_LIMIT_WINDOW);
    }

    private function getRateLimitKey(): string
    {
        return 'complaint_submit_' . md5((string) $this->request->getIPAddress());
    }
}
