<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompensationAmountToAcademicServices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('academic_services', [
            'compensation_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'has_compensation',
                'comment'    => 'จำนวนเงินค่าตอบแทน (บาท) เมื่อ has_compensation=yes',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('academic_services', 'compensation_amount');
    }
}
