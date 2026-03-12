<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add academic_year column to academic_services for year-based filtering
 * and insert system slug academic_service for admin access control.
 */
class AddAcademicYearAndSystem extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('academic_services') && ! $this->db->fieldExists('academic_year', 'academic_services')) {
            $this->forge->addColumn('academic_services', [
                'academic_year' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'id',
                    'comment'    => 'ปีการศึกษา พ.ศ. เช่น 2568',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_academic_services_academic_year ON academic_services (academic_year)');
        }

        $exists = $this->db->table('systems')
            ->where('slug', 'academic_service')
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('systems')->insert([
                'slug'        => 'academic_service',
                'name_th'     => 'ข้อมูลบริการวิชาการ',
                'name_en'     => 'Academic Service',
                'description' => 'บันทึกและจัดการข้อมูลการบริการวิชาการของบุคลากร',
                'icon'        => null,
                'is_active'   => 1,
                'sort_order'  => 14,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('academic_services') && $this->db->fieldExists('academic_year', 'academic_services')) {
            $this->db->query('ALTER TABLE academic_services DROP INDEX idx_academic_services_academic_year');
            $this->forge->dropColumn('academic_services', 'academic_year');
        }
        $this->db->table('systems')->where('slug', 'academic_service')->delete();
    }
}
