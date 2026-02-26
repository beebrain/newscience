<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ย้ายข้อมูลจาก user.program_id ไปยัง user_programs (ใช้ email เป็น key)
 * รันหลังจาก migration สร้างตาราง user_programs
 */
class UserProgramsFromUserProgramId extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('user_programs')) {
            return;
        }
        $builder = $db->table('user');
        $rows = $builder->select('email, program_id')
            ->where('program_id IS NOT NULL')
            ->where('program_id !=', '')
            ->where('email IS NOT NULL')
            ->where('email !=', '')
            ->get()
            ->getResultArray();
        if (empty($rows)) {
            return;
        }
        $insert = [];
        foreach ($rows as $row) {
            $email = trim((string) $row['email']);
            $pid = (int) $row['program_id'];
            if ($email !== '' && $pid > 0) {
                $insert[] = [
                    'user_email' => $email,
                    'program_id' => $pid,
                    'is_primary' => 1,
                    'sort_order' => 0,
                ];
            }
        }
        if (!empty($insert)) {
            $db->table('user_programs')->insertBatch($insert);
        }
    }
}
