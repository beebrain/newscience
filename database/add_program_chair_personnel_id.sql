-- Add chair_personnel_id (ประธานหลักสูตร) to programs table
-- ใช้ ID ของบุคลากร (personnel.id) ระบุประธานหลักสูตรของแต่ละหลักสูตร
-- Run: mysql -u root -p newscience < database/add_program_chair_personnel_id.sql
-- Or: php scripts/run_add_program_chair_personnel_id.php

ALTER TABLE `programs`
ADD COLUMN `chair_personnel_id` INT UNSIGNED DEFAULT NULL COMMENT 'ประธานหลักสูตร (personnel.id)' AFTER `coordinator_id`,
ADD KEY `chair_personnel_id` (`chair_personnel_id`),
ADD CONSTRAINT `fk_programs_chair_personnel` FOREIGN KEY (`chair_personnel_id`) REFERENCES `personnel`(`id`) ON DELETE SET NULL;
