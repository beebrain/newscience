<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่มระบบสิทธิ์ "ประกาศข่าว" (admin_news) สำหรับการเข้าถึงหน้าจัดการข่าวใน Admin
 */
class AddAdminNewsSystem extends Migration
{
    public function up()
    {
        $exists = $this->db->table('systems')
            ->where('slug', 'admin_news')
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table('systems')->insert([
            'slug'        => 'admin_news',
            'name_th'     => 'ประกาศข่าว',
            'name_en'     => 'News Management',
            'description' => 'จัดการข่าวสารและประกาศบนเว็บไซต์',
            'icon'        => null,
            'is_active'   => 1,
            'sort_order'  => 12,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->db->table('systems')->where('slug', 'admin_news')->delete();
    }
}
