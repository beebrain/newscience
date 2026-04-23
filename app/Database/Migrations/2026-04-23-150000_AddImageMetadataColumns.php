<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่ม metadata columns สำหรับรูปภาพใน 3 ตาราง (popup / news / event)
 *
 * - image_width / image_height: มิติจริงของรูป (px) — ใช้คำนวณ aspect-ratio ฝั่ง CSS ลด CLS
 * - image_focal_x / image_focal_y: จุดโฟกัสสำหรับ crop อัตโนมัติใน thumbnail (0–100)
 *
 * ทุกคอลัมน์ nullable — รูปเก่าจะมีค่า NULL (CSS fallback ไม่ใส่ blur backdrop)
 */
class AddImageMetadataColumns extends Migration
{
    public function up()
    {
        // urgent_popups — column ชื่อ image
        if ($this->db->tableExists('urgent_popups')) {
            $fields = [];
            if (!$this->db->fieldExists('image_width', 'urgent_popups')) {
                $fields['image_width']  = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'image'];
            }
            if (!$this->db->fieldExists('image_height', 'urgent_popups')) {
                $fields['image_height'] = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'image'];
            }
            if (!$this->db->fieldExists('image_focal_x', 'urgent_popups')) {
                $fields['image_focal_x'] = ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'image'];
            }
            if (!$this->db->fieldExists('image_focal_y', 'urgent_popups')) {
                $fields['image_focal_y'] = ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'image'];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('urgent_popups', $fields);
            }
        }

        // news — column ชื่อ featured_image
        if ($this->db->tableExists('news')) {
            $fields = [];
            if (!$this->db->fieldExists('featured_image_width', 'news')) {
                $fields['featured_image_width']  = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!$this->db->fieldExists('featured_image_height', 'news')) {
                $fields['featured_image_height'] = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!$this->db->fieldExists('featured_image_focal_x', 'news')) {
                $fields['featured_image_focal_x'] = ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!$this->db->fieldExists('featured_image_focal_y', 'news')) {
                $fields['featured_image_focal_y'] = ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('news', $fields);
            }
        }

        // events — column ชื่อ featured_image
        if ($this->db->tableExists('events')) {
            $fields = [];
            if (!$this->db->fieldExists('featured_image_width', 'events')) {
                $fields['featured_image_width']  = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!$this->db->fieldExists('featured_image_height', 'events')) {
                $fields['featured_image_height'] = ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!$this->db->fieldExists('featured_image_focal_x', 'events')) {
                $fields['featured_image_focal_x'] = ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!$this->db->fieldExists('featured_image_focal_y', 'events')) {
                $fields['featured_image_focal_y'] = ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'featured_image'];
            }
            if (!empty($fields)) {
                $this->forge->addColumn('events', $fields);
            }
        }
    }

    public function down()
    {
        $columns = ['image_width', 'image_height', 'image_focal_x', 'image_focal_y'];
        foreach ($columns as $col) {
            if ($this->db->tableExists('urgent_popups') && $this->db->fieldExists($col, 'urgent_popups')) {
                $this->forge->dropColumn('urgent_popups', $col);
            }
        }

        $featuredColumns = ['featured_image_width', 'featured_image_height', 'featured_image_focal_x', 'featured_image_focal_y'];
        foreach (['news', 'events'] as $table) {
            if (!$this->db->tableExists($table)) continue;
            foreach ($featuredColumns as $col) {
                if ($this->db->fieldExists($col, $table)) {
                    $this->forge->dropColumn($table, $col);
                }
            }
        }
    }
}
