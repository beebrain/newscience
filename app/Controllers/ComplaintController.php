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

    /** ชื่อช่อง honeypot (ซ่อนจากคน — บอตมักกรอกค่าให้) */
    private const HONEYPOT_FIELD = 'website';

    /** เวลาขั้นต่ำ (วินาที) ที่คนจริงใช้กรอกฟอร์ม — ต่ำกว่านี้ถือว่าเป็นบอต */
    private const MIN_FORM_FILL_SECONDS = 3;

    /** คีย์เก็บเวลาเปิดฟอร์มไว้ใน session สำหรับตรวจ timing */
    private const FORM_TS_SESSION_KEY = 'complaint_form_ts';

    /** ลายเซ็นบอตสแปมฟอร์มที่รู้จัก (ตัวพิมพ์เล็กทั้งหมด) */
    private const SPAM_TOKENS = ['lxbfyeaa'];

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

        // จดเวลาเปิดฟอร์มไว้ ใช้ตรวจว่าผู้ส่งกรอกเร็วผิดมนุษย์ (บอต) หรือไม่
        session()->set(self::FORM_TS_SESSION_KEY, time());

        return view('pages/complaints', $data);
    }

    public function submit()
    {
        // ตรวจจับบอตสแปม (เช่น lxbfYeaa) ก่อนทุกอย่าง แล้ว "แกล้งทำเป็นสำเร็จ"
        // เพื่อไม่ให้บอตรู้ตัวและปรับวิธี — ไม่มีการบันทึกลงฐานข้อมูลหรือส่งอีเมล
        if ($this->isLikelyBot()) {
            log_message('warning', 'Complaint spam blocked. IP: {ip} | UA: {ua}', [
                'ip' => $this->request->getIPAddress(),
                'ua' => substr((string) $this->request->getUserAgent(), 0, 255),
            ]);

            return redirect()->to(base_url('complaints'))
                ->with('success', 'ส่งเรื่องร้องเรียนเรียบร้อยแล้ว เจ้าหน้าที่จะรับทราบและดำเนินการต่อไป');
        }

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

    /**
     * ตรวจว่าการส่งครั้งนี้น่าจะมาจากบอตสแปมหรือไม่ โดยใช้ 3 ชั้น:
     *  1) honeypot — ช่องซ่อนที่คนมองไม่เห็น ถ้ามีค่ากรอกมาแปลว่าเป็นบอต
     *  2) timing — กรอกฟอร์มเสร็จเร็วเกินกว่ามนุษย์จะทำได้
     *  3) blocklist — เจอลายเซ็นบอตที่รู้จัก (เช่น lxbfYeaa) ในข้อมูลที่ส่งมา
     */
    private function isLikelyBot(): bool
    {
        // 1) honeypot ต้องว่างเสมอสำหรับคนจริง
        if (trim((string) $this->request->getPost(self::HONEYPOT_FIELD)) !== '') {
            return true;
        }

        // 2) ส่งเร็วผิดปกติ (มี timestamp ใน session เท่านั้นจึงตรวจ — กันfalse positive)
        $loadedAt = (int) session()->get(self::FORM_TS_SESSION_KEY);
        if ($loadedAt > 0 && (time() - $loadedAt) < self::MIN_FORM_FILL_SECONDS) {
            return true;
        }

        // 3) ลายเซ็นบอตที่รู้จักในทุกช่องข้อความ
        $haystack = strtolower(implode(' ', [
            (string) $this->request->getPost('complainant_name'),
            (string) $this->request->getPost('complainant_email'),
            (string) $this->request->getPost('complainant_phone'),
            (string) $this->request->getPost('subject'),
            (string) $this->request->getPost('detail'),
        ]));

        foreach (self::SPAM_TOKENS as $token) {
            if ($token !== '' && str_contains($haystack, $token)) {
                return true;
            }
        }

        return false;
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
