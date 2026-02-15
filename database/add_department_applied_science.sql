-- Add department: สาขาวิทยาศาสตร์ประยุกต์ (Applied Science)
-- Run: mysql -u root -p newscience < database/add_department_applied_science.sql
-- Or: php scripts/run_add_department_applied_science.php

SET NAMES utf8mb4;

INSERT INTO `departments` (`name_th`, `name_en`, `code`, `sort_order`, `status`)
SELECT 'สาขาวิทยาศาสตร์ประยุกต์', 'Applied Science', 'AS', 12, 'active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `departments` WHERE `name_th` = 'สาขาวิทยาศาสตร์ประยุกต์' LIMIT 1);
