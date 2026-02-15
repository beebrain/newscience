-- Pivot: บุคลากร–หลักสูตร (อาจารย์ 1 คน สังกัดได้หลายหลักสูตร)
-- กฎธุรกิจ: อาจารย์ 1 คน เป็นประธานได้ 1 หลักสูตร | เป็นอาจารย์ประจำได้หลายหลักสูตร | หลักสูตร 1 หลักสูตร มีประธานได้ 1 คน
-- Run: mysql -u root -p newscience < database/add_personnel_programs_table.sql
-- Or: php scripts/run_add_personnel_programs_table.php

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `personnel_programs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `personnel_id` INT UNSIGNED NOT NULL,
    `program_id` INT UNSIGNED NOT NULL,
    `role_in_curriculum` VARCHAR(100) DEFAULT NULL COMMENT 'ประธานหลักสูตร, กรรมการหลักสูตร, อาจารย์ประจำหลักสูตร',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personnel_program` (`personnel_id`, `program_id`),
    KEY `program_id` (`program_id`),
    KEY `personnel_id` (`personnel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
