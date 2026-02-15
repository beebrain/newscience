-- หน่วยงานในโครงสร้าง (Department) 5 กลุ่มคงที่ สำหรับหน้าบุคลากร
-- Run: mysql -u root -p newscience < database/add_organization_units.sql

CREATE TABLE IF NOT EXISTS `organization_units` (
  `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_th` VARCHAR(255) NOT NULL COMMENT 'ชื่อหน่วยงาน (ไทย)',
  `name_en` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อหน่วยงาน (อังกฤษ)',
  `code` VARCHAR(32) NOT NULL COMMENT 'รหัสใช้ในระบบ: executives, office, research, bachelor, graduate',
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='หน่วยงาน 5 กลุ่ม: ผู้บริหาร, สำนักงานคณบดี, หัวหน้าหน่วยวิจัย, หลักสูตรป.ตรี, หลักสูตรบัณฑิต';

INSERT INTO `organization_units` (`name_th`, `name_en`, `code`, `sort_order`) VALUES
('ผู้บริหาร', 'Executives', 'executives', 1),
('สำนักงานคณบดี', 'Dean''s Office', 'office', 2),
('หัวหน้าหน่วยการจัดการงานวิจัย', 'Research Management Unit', 'research', 3),
('หลักสูตรระดับปริญญาตรี', 'Bachelor''s Degree Programs', 'bachelor', 4),
('หลักสูตรระดับบัณฑิตศึกษา', 'Graduate Programs', 'graduate', 5);
