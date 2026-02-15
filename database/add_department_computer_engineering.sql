-- Add department: สาขาวิชาวิศวกรรมคอมพิวเตอร์ (Computer Engineering)
-- Run: mysql -u root -p newscience < database/add_department_computer_engineering.sql
-- Or: php scripts/run_add_department_computer_engineering.php

SET NAMES utf8mb4;

INSERT INTO `departments` (`name_th`, `name_en`, `code`, `sort_order`, `status`)
SELECT 'สาขาวิชาวิศวกรรมคอมพิวเตอร์', 'Computer Engineering', 'CE', 13, 'active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `departments` WHERE `name_th` = 'สาขาวิชาวิศวกรรมคอมพิวเตอร์' LIMIT 1);
