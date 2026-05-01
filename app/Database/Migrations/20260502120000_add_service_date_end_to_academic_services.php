<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ช่วงวันที่บริการวิชาการ: วันสิ้นสุด (nullable = กิจกรรมวันเดียว)
 */
class AddServiceDateEndToAcademicServices extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('academic_services')) {
            return;
        }
        if ($this->db->fieldExists('service_date_end', 'academic_services')) {
            return;
        }
        $this->forge->addColumn('academic_services', [
            'service_date_end' => [
                'type'    => 'DATE',
                'null'    => true,
                'after'   => 'service_date',
                'comment' => 'วันสิ้นสุดช่วงบริการ (ว่าง = วันเดียวกับ service_date)',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('academic_services') && $this->db->fieldExists('service_date_end', 'academic_services')) {
            $this->forge->dropColumn('academic_services', 'service_date_end');
        }
    }
}
