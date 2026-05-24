<?php

namespace App\Database\Migrations;

use App\Libraries\ResearchRecordCvSyncMerge;
use CodeIgniter\Database\Migration;

/**
 * สร้างหัวข้อ CV "งานวิจัยที่ตีพิมพ์" ให้บุคลากรทุกคน (ว่างได้จนกว่าจะมีรายการ)
 */
class EnsurePublicationCvSectionsForAllPersonnel extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cv_sections') || ! $this->db->tableExists('personnel')) {
            return;
        }

        ResearchRecordCvSyncMerge::ensurePublicationSectionForAllPersonnel();
    }

    public function down(): void
    {
        // ไม่ลบหัวข้อผลงานที่สร้าง — ข้อมูลผู้ใช้อาจกรอกแล้ว
    }
}
