-- Add program: วิศวกรรมคอมพิวเตอร์ (Computer Engineering) สังกัดสาขาวิชาวิศวกรรมคอมพิวเตอร์
-- Run after add_department_computer_engineering so department id 13 exists.
-- Run: mysql -u root -p newscience < database/add_program_computer_engineering.sql

SET NAMES utf8mb4;

-- Use department_id from departments where name_th = สาขาวิชาวิศวกรรมคอมพิวเตอร์ (typically id 13)
INSERT INTO `programs` (`name_th`, `name_en`, `degree_th`, `degree_en`, `level`, `department_id`, `duration`, `sort_order`, `status`)
SELECT 'วิศวกรรมคอมพิวเตอร์', 'Computer Engineering', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', d.id, '4 ปี', 14, 'active'
FROM (SELECT id FROM `departments` WHERE `name_th` = 'สาขาวิชาวิศวกรรมคอมพิวเตอร์' LIMIT 1) d
WHERE NOT EXISTS (SELECT 1 FROM `programs` WHERE `name_th` = 'วิศวกรรมคอมพิวเตอร์' AND `department_id` = d.id LIMIT 1);
