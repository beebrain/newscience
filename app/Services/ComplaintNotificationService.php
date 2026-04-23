<?php

namespace App\Services;

use App\Models\SiteSettingModel;
use App\Models\UserModel;
use Config\Services;

class ComplaintNotificationService
{
    private SiteSettingModel $siteSettingModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->siteSettingModel = new SiteSettingModel();
        $this->userModel = new UserModel();
    }

    /**
     * @return string[]
     */
    public function getRecipientEmails(): array
    {
        $raw = (string) $this->siteSettingModel->getValue('complaint_notification_emails', '');
        $emails = preg_split('/[\s,;]+/', $raw) ?: [];
        $emails = array_values(array_filter(array_map(static function ($email): string {
            return strtolower(trim((string) $email));
        }, $emails), static function ($email): bool {
            return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }));

        if ($emails !== []) {
            return array_values(array_unique($emails));
        }

        $superAdmins = $this->userModel->getSuperAdmins();
        $fallback = [];
        foreach ($superAdmins as $admin) {
            $email = strtolower(trim((string) ($admin['email'] ?? '')));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fallback[] = $email;
            }
        }

        return array_values(array_unique($fallback));
    }

    public function sendNewComplaintNotification(array $complaint): void
    {
        $recipients = $this->getRecipientEmails();
        if ($recipients === []) {
            return;
        }

        $subject = 'แจ้งเตือน: มีเรื่องร้องเรียนใหม่เข้าระบบ';
        $baseUrl = rtrim((string) config('App')->baseURL, '/');
        $detailUrl = $baseUrl . '/admin/complaints?selected=' . (int) ($complaint['id'] ?? 0);
        $message = "เรียน คณะกรรมการบริหาร / ผู้ดูแลระบบ\n\n";
        $message .= "มีผู้ส่งเรื่องร้องเรียนใหม่เข้ามาในระบบ\n\n";
        $message .= "- รหัสเรื่อง: #" . (int) ($complaint['id'] ?? 0) . "\n";
        $message .= "- ผู้ร้องเรียน: " . ($complaint['complainant_name'] ?? '-') . "\n";
        $message .= "- อีเมล: " . ($complaint['complainant_email'] ?? '-') . "\n";
        if (! empty($complaint['complainant_phone'])) {
            $message .= "- โทรศัพท์: " . $complaint['complainant_phone'] . "\n";
        }
        $message .= "- หัวข้อ: " . ($complaint['subject'] ?? '-') . "\n";
        $message .= "- วันที่ส่ง: " . date('d/m/Y H:i:s', strtotime((string) ($complaint['created_at'] ?? 'now'))) . "\n\n";
        $message .= "รายละเอียดเบื้องต้น:\n";
        $message .= trim((string) ($complaint['detail'] ?? '')) . "\n\n";
        $message .= "ตรวจสอบรายละเอียดเพิ่มเติมได้ที่:\n" . $detailUrl . "\n";

        $email = Services::email();

        foreach ($recipients as $recipient) {
            try {
                $email->clear(true);
                $email->setTo($recipient);
                $email->setSubject($subject);
                $email->setMailType('text');
                $email->setMessage($message);
                $email->send();
            } catch (\Throwable $e) {
                log_message('error', 'ComplaintNotificationService: ' . $e->getMessage());
            }
        }
    }
}
