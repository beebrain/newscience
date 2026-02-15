-- เพิ่มคอลัมน์ชื่อใน user ถ้าไม่มี (ตาราง clone จาก researchrecord อาจไม่มี gf_name, gl_name, tf_name, tl_name)
-- Run: php scripts/run_add_user_name_columns.php

ALTER TABLE `user`
ADD COLUMN IF NOT EXISTS `gf_name` VARCHAR(255) DEFAULT NULL AFTER `password`,
ADD COLUMN IF NOT EXISTS `gl_name` VARCHAR(255) DEFAULT NULL AFTER `gf_name`,
ADD COLUMN IF NOT EXISTS `tf_name` VARCHAR(255) DEFAULT NULL AFTER `gl_name`,
ADD COLUMN IF NOT EXISTS `tl_name` VARCHAR(255) DEFAULT NULL AFTER `tf_name`;
