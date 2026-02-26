<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CertEventRecipientModel - จัดการรายชื่อผู้ได้รับ Certificate ในแต่ละกิจกรรม
 */
class CertEventRecipientModel extends Model
{
    protected $table = 'cert_event_recipients';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'event_id',
        'student_id',
        'recipient_name',
        'recipient_email',
        'recipient_id_no',
        'extra_data',
        'certificate_id',
        'status',
        'error_message',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * ดึงรายชื่อผู้รับทั้งหมดในกิจกรรม พร้อมข้อมูล student และ certificate
     */
    public function getByEvent(int $eventId): array
    {
        return $this->select('cert_event_recipients.*, 
            student_user.th_name as student_firstname, 
            student_user.thai_lastname as student_lastname,
            student_user.email as student_email,
            student_user.login_uid as student_id_no,
            certificates.certificate_no,
            certificates.pdf_path,
            certificates.verification_token,
            certificates.issued_date')
            ->join('student_user', 'student_user.id = cert_event_recipients.student_id', 'left')
            ->join('certificates', 'certificates.id = cert_event_recipients.certificate_id', 'left')
            ->where('cert_event_recipients.event_id', $eventId)
            ->orderBy('cert_event_recipients.id', 'ASC')
            ->findAll();
    }

    /**
     * ดึงรายชื่อที่ยังไม่ได้ออก Certificate (pending)
     */
    public function getPendingByEvent(int $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->where('status', 'pending')
            ->findAll();
    }

    /**
     * ดึงรายชื่อที่ออก Certificate แล้ว (issued)
     */
    public function getIssuedByEvent(int $eventId): array
    {
        return $this->select('cert_event_recipients.*, certificates.certificate_no, certificates.pdf_path, certificates.verification_token, certificates.issued_date, certificates.download_count')
            ->join('certificates', 'certificates.id = cert_event_recipients.certificate_id')
            ->where('cert_event_recipients.event_id', $eventId)
            ->where('cert_event_recipients.status', 'issued')
            ->findAll();
    }

    /**
     * ดึงรายชื่อผู้รับโดย student_id (สำหรับหน้า student)
     */
    public function getByStudent(int $studentId): array
    {
        return $this->select('cert_event_recipients.*, 
            cert_events.title as event_title,
            cert_events.event_date,
            cert_templates.name_th as template_name,
            certificates.certificate_no,
            certificates.pdf_path,
            certificates.verification_token,
            certificates.issued_date,
            certificates.download_count')
            ->join('cert_events', 'cert_events.id = cert_event_recipients.event_id')
            ->join('cert_templates', 'cert_templates.id = cert_events.template_id', 'left')
            ->join('certificates', 'certificates.id = cert_event_recipients.certificate_id', 'left')
            ->where('cert_event_recipients.student_id', $studentId)
            ->orderBy('cert_event_recipients.created_at', 'DESC')
            ->findAll();
    }

    /**
     * อัปเดตสถานะและ certificate_id หลังออก Certificate สำเร็จ
     */
    public function markAsIssued(int $recipientId, int $certificateId): bool
    {
        return $this->update($recipientId, [
            'status' => 'issued',
            'certificate_id' => $certificateId,
            'error_message' => null,
        ]);
    }

    /**
     * บันทึก error ถ้าออก Certificate ไม่สำเร็จ
     */
    public function markAsFailed(int $recipientId, string $errorMessage): bool
    {
        return $this->update($recipientId, [
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * ลบรายชื่อที่ยังไม่ได้ออก Certificate (pending) ทั้งหมดในกิจกรรม
     */
    public function clearPending(int $eventId): bool
    {
        return $this->where('event_id', $eventId)
            ->where('status', 'pending')
            ->delete();
    }

    /**
     * นับจำนวนผู้รับตามสถานะ
     */
    public function countByStatus(int $eventId, string $status): int
    {
        return $this->where('event_id', $eventId)
            ->where('status', $status)
            ->countAllResults();
    }
}
