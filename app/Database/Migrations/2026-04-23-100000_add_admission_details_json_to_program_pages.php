<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdmissionDetailsJsonToProgramPages extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('admission_details_json', 'program_pages')) {
            $this->forge->addColumn('program_pages', [
                'admission_details_json' => [
                    'type'    => 'LONGTEXT',
                    'null'    => true,
                    'comment' => 'JSON: {plan_seats, requirements:{study_plan,mor_kor_2_url,english_grade,selection_criteria,tuition_per_term,duration,credits_note,program_type}, supports:{scholarship,first_term_loan,ksl_loan,study_scholarship,entrepreneur_fund,dormitory} — supports default=true ทั้งหมด',
                    'after'   => 'tuition_fees_json',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('admission_details_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'admission_details_json');
        }
    }
}
