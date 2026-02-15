-- เพิ่มคอลัมน์ชื่อไทยใน user: th_name, thai_lastname (ใช้แสดงชื่อภาษาไทยเป็นหลัก)
-- Run: php scripts/run_add_user_th_name_columns.php

ALTER TABLE `user`
ADD COLUMN IF NOT EXISTS `th_name` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อ (ไทย)',
ADD COLUMN IF NOT EXISTS `thai_lastname` VARCHAR(255) DEFAULT NULL COMMENT 'นามสกุล (ไทย)';
