<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่มระบบสิทธิ์ "จัดการป๊อปอัปประกาศด่วน" (admin_urgent_popup)
 */
class AddAdminUrgentPopupSystem extends Migration
{
    public function up()
    {
        $exists = $this->db->table('systems')
            ->where('slug', 'admin_urgent_popup')
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table('systems')->insert([
            'slug'        => 'admin_urgent_popup',
            'name_th'     => 'จัดการป๊อปอัปประกาศด่วน',
            'name_en'     => 'Urgent Popup Management',
            'description' => 'จัดการประกาศด่วน (ป๊อปอัปหน้าแรก สูงสุด 3 รายการ)',
            'icon'        => null,
            'is_active'   => 1,
            'sort_order'  => 14,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->db->table('systems')->where('slug', 'admin_urgent_popup')->delete();
    }
}
