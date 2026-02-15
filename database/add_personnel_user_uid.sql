-- Add user_uid to personnel: ลิงก์กับตาราง user ผ่านอีเมล (personnel.email = user.email)
-- Run: php scripts/run_add_personnel_user_uid.php

ALTER TABLE `personnel`
ADD COLUMN `user_uid` INT UNSIGNED DEFAULT NULL COMMENT 'ลิงก์ user (อ้างอิงโดย email)' AFTER `email`,
ADD KEY `user_uid` (`user_uid`);

-- FK ไป user.uid (ตาราง user ใช้ uid เป็น PK)
-- ALTER TABLE `personnel` ADD CONSTRAINT `fk_personnel_user` FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) ON DELETE SET NULL;
