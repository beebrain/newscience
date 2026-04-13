<?php

namespace App\Services;

use App\Libraries\CertOrganizerAccess;
use App\Models\CertEventRecipientModel;
use Config\Certificate as CertificateConfig;
use Config\Email as EmailConfig;
use Config\Services;

class CertificateEmailService
{
    /**
     * ส่งอีเมลใบรับรอง PDF ให้ผู้รับ (แนบไฟล์)
     *
     * @return array{ok: bool, error?: string}
     */
    public function sendIssuedCertificate(
        array $recipient,
        array $event,
        string $absolutePdfPath,
        ?string $verifyUrl = null
    ): array {
        $to = CertOrganizerAccess::normalizeEmail((string) ($recipient['recipient_email'] ?? ''));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'อีเมลผู้รับไม่ถูกต้อง'];
        }

        if (! is_file($absolutePdfPath)) {
            return ['ok' => false, 'error' => 'ไม่พบไฟล์ PDF'];
        }

        $emailConfig = config(EmailConfig::class);
        $from        = $emailConfig->fromEmail ?: (string) env('email.fromEmail', '');
        if ($from === '') {
            return ['ok' => false, 'error' => 'ยังไม่ได้ตั้งค่า email.fromEmail / Config\\Email::$fromEmail'];
        }

        $eventTitle = (string) ($event['title'] ?? 'กิจกรรม');
        $name       = (string) ($recipient['recipient_name'] ?? '');

        $body = "เรียน {$name}\n\n";
        $body .= "แนบใบรับรองการเข้าร่วมกิจกรรม: {$eventTitle}\n\n";
        if ($verifyUrl) {
            $body .= "ตรวจสอบความถูกต้องของใบรับรองได้ที่:\n{$verifyUrl}\n\n";
        }
        $body .= "จัดทำโดยระบบ E-Certificate คณะวิทยาศาสตร์และเทคโนโลยี\n";

        $mail = Services::email();
        $mail->clear(true);
        $mail->setFrom($from, $emailConfig->fromName ?: 'Science Faculty');
        $mail->setTo($to);
        $mail->setSubject('ใบรับรองการเข้าร่วม: ' . $eventTitle);
        $mail->setMailType('text');
        $mail->setMessage($body);
        $mail->attach($absolutePdfPath, 'attachment', basename($absolutePdfPath), 'application/pdf');

        try {
            if (! $mail->send()) {
                $dbg = $mail->printDebugger(['headers']);

                return ['ok' => false, 'error' => 'ส่งอีเมลไม่สำเร็จ: ' . mb_substr(strip_tags($dbg), 0, 400)];
            }
        } catch (\Throwable $e) {
            log_message('error', 'CertificateEmailService: ' . $e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        return ['ok' => true];
    }

    /**
     * บันทึกผลการส่งลงผู้รับ
     */
    public function persistSendResult(int $recipientId, bool $ok, ?string $error = null): void
    {
        $data = [
            'email_sent_at' => $ok ? date('Y-m-d H:i:s') : null,
            'email_error'   => $ok ? null : mb_substr((string) $error, 0, 500),
        ];
        (new CertEventRecipientModel())->update($recipientId, $data);
    }

    public function resolvePdfAbsolutePath(string $relative, ?CertificateConfig $cfg = null): ?string
    {
        $cfg ??= config(CertificateConfig::class);
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relative));
        if ($relative === '') {
            return null;
        }

        $norm = str_replace('\\', '/', $relative);
        if (str_starts_with($norm, 'uploads/cert_system/certificates/')) {
            $sub = substr($norm, strlen('uploads/cert_system/certificates/'));
            $full = rtrim($cfg->certificateOutputPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $sub);

            return is_file($full) ? $full : null;
        }

        if (str_starts_with($norm, 'uploads/')) {
            $full = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $norm);

            return is_file($full) ? $full : null;
        }

        return null;
    }
}
