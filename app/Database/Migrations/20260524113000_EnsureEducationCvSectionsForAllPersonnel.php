<?php

namespace App\Database\Migrations;

use App\Libraries\ResearchRecordCvSyncMerge;
use CodeIgniter\Database\Migration;

/**
 * สร้างหัวข้อ CV "การศึกษา" ให้บุคลากรทุกคน และย้ายข้อความสรุป personnel.education เป็นรายการ
 */
class EnsureEducationCvSectionsForAllPersonnel extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cv_sections') || ! $this->db->tableExists('personnel')) {
            return;
        }

        ResearchRecordCvSyncMerge::ensureEducationSectionForAllPersonnel();
    }

    public function down(): void
    {
        // ไม่ลบหัวข้อการศึกษาที่สร้าง — ข้อมูลผู้ใช้อาจกรอกแล้ว
    }
}
