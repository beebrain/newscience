<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * ตั้งค่าระบบการประเมินการสอน
 */
class EvaluateSettingsModel extends Model
{
    protected $table            = 'evaluate_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'is_active',
        'start_date',
        'end_date',
        'notification_emails',
        'referee_email_subject',
        'referee_email_template',
        'applicant_email_subject',
        'applicant_email_template',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    /**
     * ดึงการตั้งค่า (สร้าง default ถ้ายังไม่มี)
     */
    public function getSettings(): array
    {
        $row = $this->first();
        if ($row === null) {
            $default = [
                'is_active' => 1,
                'start_date' => null,
                'end_date' => null,
                'notification_emails' => null,
                'referee_email_subject' => 'ขอความอนุเคราะห์ประเมินการสอน - {position}',
                'referee_email_template' => "เรียน {referee_name}\n\nขอความอนุเคราะห์ประเมินการสอนของ {applicant_name}\nตำแหน่ง: {position}\nวิชา: {subject_name}\n\nกรุณาเข้าสู่ระบบเพื่อทำการประเมิน\n\nขอแสดงความนับถือ\nระบบการประเมินการสอน",
                'applicant_email_subject' => 'ยืนยันการส่งคำร้องขอรับการประเมิน - {position}',
                'applicant_email_template' => "เรียน {applicant_name}\n\nระบบได้รับคำร้องขอรับการประเมินการสอนของท่านเรียบร้อยแล้ว\n\nรายละเอียด:\n- ตำแหน่ง: {position}\n- วิชา: {subject_name}\n- วันที่ส่ง: {submit_date}\n\nทางผู้เกี่ยวข้องจะดำเนินการในลำดับต่อไป\n\nขอแสดงความนับถือ\nระบบการประเมินการสอน",
            ];
            $this->insert($default);
            return $this->first();
        }
        return $row;
    }

    /**
     * ตรวจสอบว่าระบบเปิดรับคำร้องอยู่หรือไม่
     */
    public function isAcceptingSubmissions(): bool
    {
        $settings = $this->getSettings();

        // ถ้าปิดระบบ
        if (empty($settings['is_active'])) {
            return false;
        }

        $today = date('Y-m-d');

        // ถ้ามีกำหนดช่วงเวลา
        if (!empty($settings['start_date']) && $today < $settings['start_date']) {
            return false;
        }
        if (!empty($settings['end_date']) && $today > $settings['end_date']) {
            return false;
        }

        return true;
    }

    /**
     * ดึงรายการ email ผู้รับแจ้งเตือนเป็น array
     */
    public function getNotificationEmails(): array
    {
        $settings = $this->getSettings();
        if (empty($settings['notification_emails'])) {
            return [];
        }
        return array_map('trim', explode(',', $settings['notification_emails']));
    }

    /**
     * แทนที่ placeholder ในเทมเพลต
     */
    public function parseTemplate(string $template, array $data): string
    {
        $replacements = [
            '{applicant_name}' => $data['applicant_name'] ?? '',
            '{referee_name}'   => $data['referee_name'] ?? '',
            '{position}'       => $data['position'] ?? '',
            '{subject_name}'   => $data['subject_name'] ?? '',
            '{subject_id}'     => $data['subject_id'] ?? '',
            '{submit_date}'    => $data['submit_date'] ?? date('d/m/Y'),
            '{approval_date}'  => $data['approval_date'] ?? '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
