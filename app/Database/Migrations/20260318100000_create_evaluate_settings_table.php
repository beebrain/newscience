<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ตารางตั้งค่าระบบการประเมินการสอน
 * - เปิด/ปิดระบบ (start_date, end_date)
 * - Email ผู้รับแจ้งเตือน
 * - ข้อความอีเมลสำหรับผู้ทรงคุณวุฒิ
 * - ข้อความอีเมลสำหรับผู้ขอรับการประเมิน
 */
class CreateEvaluateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => 'เปิด/ปิดระบบการรับคำร้อง (1=เปิด, 0=ปิด)',
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'วันที่เริ่มเปิดรับคำร้อง',
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'วันที่สิ้นสุดการรับคำร้อง',
            ],
            'notification_emails' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Email ผู้รับแจ้งเตือน (คั่นด้วย comma)',
            ],
            'referee_email_subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'หัวข้ออีเมลสำหรับผู้ทรงคุณวุฒิ',
            ],
            'referee_email_template' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'เทมเพลตอีเมลสำหรับผู้ทรงคุณวุฒิ',
            ],
            'applicant_email_subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'หัวข้ออีเมลสำหรับผู้ขอรับการประเมิน',
            ],
            'applicant_email_template' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'เทมเพลตอีเมลสำหรับผู้ขอรับการประเมิน',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('evaluate_settings');

        // Insert default settings
        $this->db->table('evaluate_settings')->insert([
            'is_active' => 1,
            'start_date' => null,
            'end_date' => null,
            'notification_emails' => null,
            'referee_email_subject' => 'ขอความอนุเคราะห์ประเมินการสอน - {position}',
            'referee_email_template' => "เรียน {referee_name}\n\nขอความอนุเคราะห์ประเมินการสอนของ {applicant_name}\nตำแหน่ง: {position}\nวิชา: {subject_name}\n\nกรุณาเข้าสู่ระบบเพื่อทำการประเมิน\n\nขอแสดงความนับถือ\nระบบการประเมินการสอน",
            'applicant_email_subject' => 'ยืนยันการส่งคำร้องขอรับการประเมิน - {position}',
            'applicant_email_template' => "เรียน {applicant_name}\n\nระบบได้รับคำร้องขอรับการประเมินการสอนของท่านเรียบร้อยแล้ว\n\nรายละเอียด:\n- ตำแหน่ง: {position}\n- วิชา: {subject_name}\n- วันที่ส่ง: {submit_date}\n\nทางผู้เกี่ยวข้องจะดำเนินการในลำดับต่อไป\n\nขอแสดงความนับถือ\nระบบการประเมินการสอน",
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('evaluate_settings', true);
    }
}
