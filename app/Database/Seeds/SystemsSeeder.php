<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SystemsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'slug'        => 'admin_core',
                'name_th'     => 'ระบบจัดการหลัก',
                'name_en'     => 'Admin Core',
                'description' => 'ข่าวสาร, องค์กร, หลักสูตร, สไลด์, กิจกรรม',
                'sort_order'  => 1,
            ],
            [
                'slug'        => 'admin_news',
                'name_th'     => 'ประกาศข่าว',
                'name_en'     => 'News Management',
                'description' => 'จัดการข่าวสารและประกาศบนเว็บไซต์',
                'sort_order'  => 2,
            ],
            [
                'slug'        => 'user_management',
                'name_th'     => 'จัดการผู้ใช้',
                'name_en'     => 'User Management',
                'description' => 'จัดการผู้ใช้งานระบบทั้งหมด',
                'sort_order'  => 3,
            ],
            [
                'slug'        => 'site_settings',
                'name_th'     => 'ตั้งค่าเว็บไซต์',
                'name_en'     => 'Site Settings',
                'description' => 'การตั้งค่าเว็บไซต์ทั่วไป',
                'sort_order'  => 4,
            ],
            [
                'slug'        => 'program_admin',
                'name_th'     => 'จัดการเว็บหลักสูตร',
                'name_en'     => 'Program Admin',
                'description' => 'Content Builder สำหรับแก้ไขเว็บหลักสูตร',
                'sort_order'  => 5,
            ],
            [
                'slug'        => 'ecert',
                'name_th'     => 'ระบบ E-Certificate',
                'name_en'     => 'E-Certificate System',
                'description' => 'จัดการกิจกรรม, เทมเพลต, และใบรับรอง',
                'sort_order'  => 6,
            ],
            [
                'slug'        => 'cert_approve',
                'name_th'     => 'อนุมัติใบรับรอง',
                'name_en'     => 'Certificate Approval',
                'description' => 'ระบบอนุมัติใบรับรอง (Program Chair & Dean)',
                'sort_order'  => 7,
            ],
            [
                'slug'        => 'student_admin',
                'name_th'     => 'จัดการบาร์โค้ด/กิจกรรม',
                'name_en'     => 'Student Admin',
                'description' => 'จัดการบาร์โค้ดและกิจกรรมนักศึกษา',
                'sort_order'  => 8,
            ],
            [
                'slug'        => 'edoc',
                'name_th'     => 'E-Document (ดูเอกสาร)',
                'name_en'     => 'E-Document View',
                'description' => 'ดูเอกสารในระบบสารบรรณ',
                'sort_order'  => 9,
            ],
            [
                'slug'        => 'edoc_admin',
                'name_th'     => 'E-Document (จัดการ)',
                'name_en'     => 'E-Document Admin',
                'description' => 'จัดการเอกสารในระบบสารบรรณ',
                'sort_order'  => 10,
            ],
            [
                'slug'        => 'research_record',
                'name_th'     => 'จัดการงานวิจัย',
                'name_en'     => 'Research (กบศ)',
                'description' => 'ลิงก์ไปยังระบบ กบศ',
                'sort_order'  => 11,
            ],
            [
                'slug'        => 'utility',
                'name_th'     => 'เครื่องมือผู้ดูแล',
                'name_en'     => 'Utility Tools',
                'description' => 'Upload, Import, Categorize News',
                'sort_order'  => 12,
            ],
            [
                'slug'        => 'exam',
                'name_th'     => 'ตารางคุมสอบ (ดู)',
                'name_en'     => 'Exam Schedule View',
                'description' => 'ดูตารางคุมสอบส่วนตัว',
                'sort_order'  => 13,
            ],
            [
                'slug'        => 'exam_admin',
                'name_th'     => 'ตารางคุมสอบ (จัดการ)',
                'name_en'     => 'Exam Schedule Admin',
                'description' => 'จัดการตารางคุมสอบ นำเข้า Excel',
                'sort_order'  => 14,
            ],
        ];

        $this->db->table('systems')->insertBatch($data);
    }
}
