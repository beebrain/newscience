-- ลดฟิลด์ชื่อ personnel จาก title, first_name, last_name, first_name_en, last_name_en
-- เป็น name (ไทย) และ name_en (อังกฤษ) เท่านั้น
-- Run: php scripts/run_remove_personnel_name_fields.php

-- Step 1: Add new columns
ALTER TABLE `personnel`
ADD COLUMN `name` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล (ไทย)' AFTER `id`,
ADD COLUMN `name_en` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล (อังกฤษ)' AFTER `name`;

-- Step 2: Migrate data
UPDATE `personnel`
SET
  `name` = TRIM(CONCAT(IFNULL(`title`,''), ' ', IFNULL(`first_name`,''), ' ', IFNULL(`last_name`,''))),
  `name_en` = NULLIF(TRIM(CONCAT(IFNULL(`first_name_en`,''), ' ', IFNULL(`last_name_en`,''))), '');

-- Step 3: Set NOT NULL default for name where empty
UPDATE `personnel` SET `name` = '' WHERE `name` IS NULL;
ALTER TABLE `personnel` MODIFY COLUMN `name` VARCHAR(255) NOT NULL DEFAULT '';

-- Step 4: Drop old columns
ALTER TABLE `personnel`
DROP COLUMN `title`,
DROP COLUMN `first_name`,
DROP COLUMN `last_name`,
DROP COLUMN `first_name_en`,
DROP COLUMN `last_name_en`;
