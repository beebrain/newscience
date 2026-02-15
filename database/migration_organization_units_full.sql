-- Migration เดี่ยว: สร้าง organization_units → เพิ่ม organization_unit_id ใน programs/personnel → ลบ department_id และตาราง departments
-- Run: mysql -u root -p newscience < database/migration_organization_units_full.sql
-- รันได้ทั้งกรณีที่ยังมีตาราง departments และกรณีที่รันไปบางส่วนแล้ว (ตรวจสอบก่อน ADD/DROP)

-- =============================================================================
-- 1. สร้างตาราง organization_units และใส่ข้อมูล 5 หน่วยงาน
-- =============================================================================
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

INSERT IGNORE INTO `organization_units` (`id`, `name_th`, `name_en`, `code`, `sort_order`) VALUES
(1, 'ผู้บริหาร', 'Executives', 'executives', 1),
(2, 'สำนักงานคณบดี', 'Dean''s Office', 'office', 2),
(3, 'หัวหน้าหน่วยการจัดการงานวิจัย', 'Research Management Unit', 'research', 3),
(4, 'หลักสูตรระดับปริญญาตรี', 'Bachelor''s Degree Programs', 'bachelor', 4),
(5, 'หลักสูตรระดับบัณฑิตศึกษา', 'Graduate Programs', 'graduate', 5);

-- =============================================================================
-- 2. เพิ่ม organization_unit_id ใน programs (ถ้ายังไม่มี)
-- =============================================================================
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'programs' AND COLUMN_NAME = 'organization_unit_id');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `programs` ADD COLUMN `organization_unit_id` TINYINT UNSIGNED DEFAULT NULL COMMENT ''FK organization_units: 4=หลักสูตรป.ตรี, 5=หลักสูตรบัณฑิต'' AFTER `level`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `programs` SET `organization_unit_id` = 4 WHERE `level` = 'bachelor' AND (`organization_unit_id` IS NULL OR `organization_unit_id` = 0);
UPDATE `programs` SET `organization_unit_id` = 5 WHERE `level` IN ('master', 'doctorate') AND (`organization_unit_id` IS NULL OR `organization_unit_id` = 0);

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'programs' AND CONSTRAINT_NAME = 'fk_programs_organization_unit' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE `programs` ADD KEY `organization_unit_id` (`organization_unit_id`), ADD CONSTRAINT `fk_programs_organization_unit` FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units`(`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =============================================================================
-- 3. เพิ่ม organization_unit_id ใน personnel (ถ้ายังไม่มี)
-- =============================================================================
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel' AND COLUMN_NAME = 'organization_unit_id');

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `personnel` ADD COLUMN `organization_unit_id` TINYINT UNSIGNED DEFAULT NULL COMMENT ''FK organization_units'' AFTER `position_en`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel' AND CONSTRAINT_NAME = 'fk_personnel_organization_unit' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE `personnel` ADD KEY `organization_unit_id` (`organization_unit_id`), ADD CONSTRAINT `fk_personnel_organization_unit` FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units`(`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =============================================================================
-- 4. ลบ department_id จาก personnel และ programs แล้วลบตาราง departments
-- =============================================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS drop_department_refs$$

CREATE PROCEDURE drop_department_refs()
BEGIN
    DECLARE fk_name VARCHAR(64);
    DECLARE col_exists INT DEFAULT 0;

    -- personnel: ลบ FK ที่อ้างอิง departments (ถ้ามี)
    SELECT CONSTRAINT_NAME INTO fk_name
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'personnel'
      AND COLUMN_NAME = 'department_id'
      AND REFERENCED_TABLE_NAME = 'departments'
    LIMIT 1;
    IF fk_name IS NOT NULL AND fk_name != '' THEN
        SET @sql = CONCAT('ALTER TABLE `personnel` DROP FOREIGN KEY `', fk_name, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel' AND COLUMN_NAME = 'department_id';
    IF col_exists > 0 THEN
        ALTER TABLE `personnel` DROP COLUMN `department_id`;
    END IF;

    -- programs: ลบ FK ที่อ้างอิง departments (ถ้ามี)
    SET fk_name = NULL;
    SELECT CONSTRAINT_NAME INTO fk_name
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'programs'
      AND COLUMN_NAME = 'department_id'
      AND REFERENCED_TABLE_NAME = 'departments'
    LIMIT 1;
    IF fk_name IS NOT NULL AND fk_name != '' THEN
        SET @sql = CONCAT('ALTER TABLE `programs` DROP FOREIGN KEY `', fk_name, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
    SET col_exists = 0;
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'programs' AND COLUMN_NAME = 'department_id';
    IF col_exists > 0 THEN
        ALTER TABLE `programs` DROP COLUMN `department_id`;
    END IF;
END$$

DELIMITER ;

CALL drop_department_refs();
DROP PROCEDURE IF EXISTS drop_department_refs;

DROP TABLE IF EXISTS `departments`;
