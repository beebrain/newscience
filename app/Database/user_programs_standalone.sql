-- ตาราง pivot User–Programs: User อาจอยู่ได้หลายหลักสูตร (ใช้ email ของ user เป็น key)
-- ต้องมี UNIQUE บน user.email ก่อน (ALTER TABLE user ADD UNIQUE KEY user_email_unique (email);)
-- รันครั้งเดียว (หรือใช้ Migration ของ CodeIgniter แทน)

CREATE TABLE IF NOT EXISTS `user_programs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `program_id` int(11) unsigned NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email_program_id` (`user_email`,`program_id`),
  KEY `user_email` (`user_email`),
  KEY `program_id` (`program_id`),
  CONSTRAINT `user_programs_user_email_foreign` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_programs_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (ตัวเลือก) ย้ายข้อมูลจาก user.program_id ไป user_programs
-- INSERT INTO user_programs (user_email, program_id, is_primary, sort_order)
-- SELECT email, program_id, 1, 0 FROM user WHERE program_id IS NOT NULL AND program_id != '' AND email IS NOT NULL AND email != '';
