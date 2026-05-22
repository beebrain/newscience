<?php

namespace App\Libraries;

use App\Models\StudentUserModel;

/**
 * ผูก cert_event_recipients กับ student_user ผ่าน student_id / login_uid / email
 */
class CertRecipientStudentResolver
{
    protected StudentUserModel $studentModel;

    public function __construct(?StudentUserModel $studentModel = null)
    {
        $this->studentModel = $studentModel ?? new StudentUserModel();
    }

    /**
     * คืน student_user.id ถ้าหาได้ — null ถ้าไม่ match
     */
    public function resolve(?int $studentId, ?string $email, ?string $loginUid): ?int
    {
        if ($studentId !== null && $studentId > 0) {
            $row = $this->studentModel->find($studentId);

            return $row ? (int) $row['id'] : null;
        }

        $loginUid = trim((string) $loginUid);
        if ($loginUid !== '') {
            $row = $this->studentModel->where('login_uid', $loginUid)->first();
            if ($row) {
                return (int) $row['id'];
            }
        }

        $email = CertOrganizerAccess::normalizeEmail((string) $email);
        if ($email !== '') {
            $row = $this->studentModel->findByEmail($email);
            if ($row) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    /**
     * สร้าง payload สำหรับ insert recipient จากแถว student_user
     *
     * @return array<string, mixed>|null null ถ้า student ไม่ active
     */
    public function buildRecipientPayloadFromStudent(int $eventId, array $student): ?array
    {
        if (($student['status'] ?? '') !== 'active') {
            return null;
        }

        $studentId = (int) ($student['id'] ?? 0);
        if ($studentId <= 0) {
            return null;
        }

        return [
            'event_id'        => $eventId,
            'student_id'      => $studentId,
            'recipient_name'  => $this->studentModel->getFullName($student),
            'recipient_email' => CertOrganizerAccess::normalizeEmail((string) ($student['email'] ?? '')),
            'recipient_id_no' => $student['login_uid'] ?? null,
            'extra_data'      => json_encode([
                'program' => $student['program_id'] ?? null,
            ], JSON_UNESCAPED_UNICODE),
            'status'          => 'pending',
        ];
    }
}
