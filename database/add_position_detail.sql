-- Add position_detail for รองอะไร, ผู้ช่วยด้านไหน, คณะ, ประธานหลักสูตรของหลักสูตร (program_id)
-- Run: mysql -u root -p newscience < database/add_position_detail.sql

ALTER TABLE `personnel`
ADD COLUMN `position_detail` VARCHAR(255) DEFAULT NULL COMMENT 'รายละเอียดตำแหน่ง: คณะ/รองอะไร/ผู้ช่วยด้านไหน/program_id สำหรับประธานหลักสูตร';
