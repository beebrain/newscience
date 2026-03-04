<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add system slug admin_downloads for Faculty Download Management (manage download categories and documents).
 */
class AddAdminDownloadsSystem extends Migration
{
    public function up()
    {
        $exists = $this->db->table('systems')
            ->where('slug', 'admin_downloads')
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table('systems')->insert([
            'slug'        => 'admin_downloads',
            'name_th'     => 'จัดการดาวน์โหลดคณะ',
            'name_en'     => 'Faculty Downloads',
            'description' => 'จัดการหมวดและเอกสารดาวน์โหลด (แบบฟอร์ม, คำสั่ง/ประกาศ/ระเบียบ)',
            'icon'        => null,
            'is_active'   => 1,
            'sort_order'  => 13,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->db->table('systems')->where('slug', 'admin_downloads')->delete();
    }
}
