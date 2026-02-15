-- คำนำหน้าชื่อภาษาอังกฤษ (สำหรับแสดงนำหน้าชื่ออังกฤษ)
-- Run: mysql -u root -p newscience < database/add_personnel_academic_title_en.sql

ALTER TABLE `personnel`
ADD COLUMN `academic_title_en` VARCHAR(255) DEFAULT NULL
COMMENT 'Name prefix in English (e.g. Dr., Asst. Prof.)'
AFTER `academic_title`;
