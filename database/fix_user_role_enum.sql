-- เพิ่ม 'admin' และ 'editor' ใน role ENUM ของตาราง user (หลัง clone จาก researchrecord)
-- Run: php scripts/run_fix_user_role_enum.php

ALTER TABLE `user` MODIFY COLUMN `role` ENUM('user','admin','editor','faculty_admin','super_admin') DEFAULT 'user';
