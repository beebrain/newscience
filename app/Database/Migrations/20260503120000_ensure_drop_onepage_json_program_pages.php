<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ทวนยืนยันการลบ onepage_json บน production/server
 *
 * Idempotent: ถ้าไม่มีคอลัมน์อยู่แล้วจะไม่ทำอะไร (สอดคล้องกับ 2026-04-22-160000)
 * ใช้ให้ pipeline deploy รัน `php spark migrate` แล้วมั่นใจว่า schema ไม่เหลือ onepage_json
 */
class EnsureDropOnepageJsonProgramPages extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('program_pages') && $this->db->fieldExists('onepage_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'onepage_json');
        }
    }

    public function down(): void
    {
        // ไม่คืนคอลัมน์ — rollback จำกัดเพื่อไม่เปิดฟีเจอร์ Onepage กลับโดยไม่ตั้งใจ
    }
}
