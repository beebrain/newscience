<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เปลี่ยน edoctitle.`order` จาก int เป็น varchar ตามที่ใช้ในต้นทาง
 */
class ChangeEdoctitleOrderToVarchar extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('order', 'edoctitle')) {
            return;
        }
        $this->db->query('ALTER TABLE edoctitle MODIFY COLUMN `order` VARCHAR(100) NULL DEFAULT NULL');
    }

    public function down()
    {
        if (!$this->db->fieldExists('order', 'edoctitle')) {
            return;
        }
        $this->db->query('ALTER TABLE edoctitle MODIFY COLUMN `order` INT NOT NULL DEFAULT 0');
    }
}
