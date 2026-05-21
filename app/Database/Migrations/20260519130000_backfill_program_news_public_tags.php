<?php

namespace App\Database\Migrations;

use App\Models\NewsTagModel;
use CodeIgniter\Database\Migration;

/**
 * ข่าวหลักสูตรที่มีแต่ tag program_* ให้เพิ่ม general เพื่อแสดงในข่าวประชาสัมพันธ์
 */
class BackfillProgramNewsPublicTags extends Migration
{
    public function up()
    {
        $model = new NewsTagModel();
        $added = $model->backfillGeneralTagForProgramOnlyNews();
        if ($added > 0) {
            log_message('info', 'BackfillProgramNewsPublicTags: added general tag to {n} news items', ['n' => $added]);
        }
    }

    public function down()
    {
        // ไม่ลบ tag general ที่เพิ่มย้อนหลัง (ข้อมูลอาจถูกแก้มือหลัง migrate)
    }
}
