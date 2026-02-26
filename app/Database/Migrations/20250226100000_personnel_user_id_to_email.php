<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เปลี่ยน personnel จาก user_id/user_uid เป็นใช้ email (user_email)
 * 1. ให้ user.email เป็น UNIQUE ถ้ายังไม่มี (สำหรับ FK)
 * 2. เพิ่มคอลัมน์ personnel.user_email
 * 3. แมปข้อมูลจาก user: UPDATE personnel จาก user ตาม user_uid -> email
 * 4. ลบคอลัมน์เก่า (user_uid หรือ user_id) และเพิ่ม FK user_email -> user(email)
 */
class PersonnelUserIdToEmail extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1) ให้ user.email เป็น UNIQUE เพื่อใช้เป็น FK (ข้ามถ้ามีแล้ว)
        if ($db->tableExists('user')) {
            $indexData = $db->getIndexData('user');
            $hasUniqueEmail = false;
            foreach ($indexData as $name => $idx) {
                $idx = (array) $idx;
                $fields = $idx['fields'] ?? [];
                $unique = $idx['unique'] ?? false;
                if ($name === 'user_email_unique' || (is_array($fields) && in_array('email', $fields, true) && $unique)) {
                    $hasUniqueEmail = true;
                    break;
                }
            }
            if (!$hasUniqueEmail) {
                try {
                    $this->db->query('ALTER TABLE `user` ADD UNIQUE KEY `user_email_unique` (`email`)');
                } catch (\Throwable $e) {
                    // ข้ามถ้ามี key อยู่แล้ว (Duplicate key name)
                }
            }
        }

        if (!$db->tableExists('personnel')) {
            return;
        }

        // 2) เพิ่มคอลัมน์ user_email (ถ้ายังไม่มี)
        if (!$db->fieldExists('user_email', 'personnel')) {
            $this->forge->addColumn('personnel', [
                'user_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'       => $db->fieldExists('user_uid', 'personnel') ? 'user_uid' : 'organization_unit_id',
                ],
            ]);
        }

        // 3) แมป user_id/user_uid -> email จากตาราง user
        $uidCol = null;
        if ($db->fieldExists('user_uid', 'personnel')) {
            $uidCol = 'user_uid';
        } elseif ($db->fieldExists('user_id', 'personnel')) {
            $uidCol = 'user_id';
        }
        if ($uidCol !== null && $db->tableExists('user')) {
            $this->db->query("UPDATE personnel p
                INNER JOIN user u ON u.uid = p.{$uidCol}
                SET p.user_email = u.email
                WHERE p.{$uidCol} IS NOT NULL AND u.email IS NOT NULL AND u.email != ''");
        }

        // 4) ลบ FK ที่อ้างถึง user_uid ก่อน แล้วค่อยลบคอลัมน์
        if ($uidCol !== null) {
            $fkRow = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel' AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL", [$uidCol])->getRow();
            if ($fkRow && !empty($fkRow->CONSTRAINT_NAME)) {
                try {
                    $this->db->query("ALTER TABLE `personnel` DROP FOREIGN KEY `{$fkRow->CONSTRAINT_NAME}`");
                } catch (\Throwable $e) {
                }
            }
            $this->forge->dropColumn('personnel', $uidCol);
        }

        if ($db->fieldExists('user_email', 'personnel')) {
            try {
                $this->db->query('ALTER TABLE `personnel` ADD CONSTRAINT `personnel_user_email_foreign` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`) ON DELETE SET NULL ON UPDATE CASCADE');
            } catch (\Throwable $e) {
                // ข้ามถ้ามี constraint อยู่แล้ว
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('personnel')) {
            return;
        }

        // ลบ FK
        try {
            $this->db->query('ALTER TABLE `personnel` DROP FOREIGN KEY `personnel_user_email_foreign`');
        } catch (\Throwable $e) {
        }

        // ลบคอลัมน์ user_email
        if ($db->fieldExists('user_email', 'personnel')) {
            $this->forge->dropColumn('personnel', 'user_email');
        }

        // คืนคอลัมน์ user_uid (ต้องแมปจาก user อีกครั้งถ้าต้องการ rollback เต็ม)
        if (!$db->fieldExists('user_uid', 'personnel')) {
            $this->forge->addColumn('personnel', [
                'user_uid' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }
    }
}
