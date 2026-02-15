-- ตำแหน่งทางวิชาการ (ผู้ช่วยศาสตราจารย์, รองศาสตราจารย์ ฯลฯ) สำหรับหน้าโครงสร้างองค์กร
-- Run: mysql -u root -p newscience < database/add_personnel_academic_title.sql

ALTER TABLE `personnel`
ADD COLUMN `academic_title` VARCHAR(255) DEFAULT NULL
COMMENT 'ตำแหน่งทางวิชาการ เช่น ผู้ช่วยศาสตราจารย์ รองศาสตราจารย์ ศาสตราจารย์'
AFTER `position_detail`;
