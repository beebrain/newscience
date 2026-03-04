<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Consolidate Thai name columns: keep tf_name + tl_name, drop th_name / thai_name / thai_lastname.
 * Data from the dropped columns is copied into tf_name / tl_name where they are empty.
 */
class ConsolidateThaiNameColumns extends Migration
{
    public function up()
    {
        // --- user table ---
        if ($this->db->tableExists('user')) {
            $hasTfName        = $this->db->fieldExists('tf_name', 'user');
            $hasTlName        = $this->db->fieldExists('tl_name', 'user');
            $hasThName        = $this->db->fieldExists('th_name', 'user');
            $hasThaiName      = $this->db->fieldExists('thai_name', 'user');
            $hasThaiLastname  = $this->db->fieldExists('thai_lastname', 'user');

            if ($hasTfName && ($hasThName || $hasThaiName)) {
                $src = $hasThaiName ? 'thai_name' : 'th_name';
                $this->db->query("UPDATE `user` SET `tf_name` = `{$src}` WHERE (`tf_name` IS NULL OR `tf_name` = '') AND `{$src}` IS NOT NULL AND `{$src}` != ''");
                if ($hasThName && $hasThaiName) {
                    $this->db->query("UPDATE `user` SET `tf_name` = `th_name` WHERE (`tf_name` IS NULL OR `tf_name` = '') AND `th_name` IS NOT NULL AND `th_name` != ''");
                }
            }

            if ($hasTlName && $hasThaiLastname) {
                $this->db->query("UPDATE `user` SET `tl_name` = `thai_lastname` WHERE (`tl_name` IS NULL OR `tl_name` = '') AND `thai_lastname` IS NOT NULL AND `thai_lastname` != ''");
            }

            $dropCols = [];
            if ($hasThName)        $dropCols[] = 'th_name';
            if ($hasThaiName)      $dropCols[] = 'thai_name';
            if ($hasThaiLastname)  $dropCols[] = 'thai_lastname';
            foreach ($dropCols as $col) {
                $this->forge->dropColumn('user', $col);
            }
        }

        // --- student_user table ---
        if ($this->db->tableExists('student_user')) {
            $hasTfName       = $this->db->fieldExists('tf_name', 'student_user');
            $hasTlName       = $this->db->fieldExists('tl_name', 'student_user');
            $hasThName       = $this->db->fieldExists('th_name', 'student_user');
            $hasThaiLastname = $this->db->fieldExists('thai_lastname', 'student_user');

            if ($hasTfName && $hasThName) {
                $this->db->query("UPDATE `student_user` SET `tf_name` = `th_name` WHERE (`tf_name` IS NULL OR `tf_name` = '') AND `th_name` IS NOT NULL AND `th_name` != ''");
            }
            if ($hasTlName && $hasThaiLastname) {
                $this->db->query("UPDATE `student_user` SET `tl_name` = `thai_lastname` WHERE (`tl_name` IS NULL OR `tl_name` = '') AND `thai_lastname` IS NOT NULL AND `thai_lastname` != ''");
            }

            $dropCols = [];
            if ($hasThName)       $dropCols[] = 'th_name';
            if ($hasThaiLastname) $dropCols[] = 'thai_lastname';
            foreach ($dropCols as $col) {
                $this->forge->dropColumn('student_user', $col);
            }
        }
    }

    public function down()
    {
        // Re-add columns if needed (data cannot be restored)
        if ($this->db->tableExists('user')) {
            $add = [];
            if (! $this->db->fieldExists('th_name', 'user')) {
                $add['th_name'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null];
            }
            if (! $this->db->fieldExists('thai_name', 'user')) {
                $add['thai_name'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null];
            }
            if (! $this->db->fieldExists('thai_lastname', 'user')) {
                $add['thai_lastname'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null];
            }
            if ($add !== []) $this->forge->addColumn('user', $add);
        }

        if ($this->db->tableExists('student_user')) {
            $add = [];
            if (! $this->db->fieldExists('th_name', 'student_user')) {
                $add['th_name'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null];
            }
            if (! $this->db->fieldExists('thai_lastname', 'student_user')) {
                $add['thai_lastname'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null];
            }
            if ($add !== []) $this->forge->addColumn('student_user', $add);
        }
    }
}
