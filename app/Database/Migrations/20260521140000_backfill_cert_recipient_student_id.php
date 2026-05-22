<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ผูก cert_event_recipients.student_id จาก login_uid หรือ email ที่ match student_user
 */
class BackfillCertRecipientStudentId extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cert_event_recipients') || ! $this->db->tableExists('student_user')) {
            return;
        }

        $this->db->query(
            'UPDATE cert_event_recipients cer
             INNER JOIN student_user su ON su.login_uid = cer.recipient_id_no
             SET cer.student_id = su.id
             WHERE cer.student_id IS NULL
               AND cer.recipient_id_no IS NOT NULL
               AND TRIM(cer.recipient_id_no) != \'\''
        );

        $this->db->query(
            'UPDATE cert_event_recipients cer
             INNER JOIN student_user su ON LOWER(TRIM(su.email)) = LOWER(TRIM(cer.recipient_email))
             SET cer.student_id = su.id
             WHERE cer.student_id IS NULL
               AND cer.recipient_email IS NOT NULL
               AND TRIM(cer.recipient_email) != \'\''
        );
    }

    public function down(): void
    {
        // ไม่ revert — การผูก student_id จากข้อมูลที่มีอยู่แล้วไม่ทำลายความถูกต้อง
    }
}
