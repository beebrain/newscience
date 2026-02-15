<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserProgramIdSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->query("ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `program_id` INT UNSIGNED DEFAULT NULL AFTER `role`, ADD KEY `program_id` (`program_id`);");
        echo "✅ Successfully added program_id column to user table\n";
    }
}
