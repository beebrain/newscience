-- เพิ่มคอลัมน์ elos_json และ curriculum_json ใน program_pages (กรณี server ยังไม่มี)
-- รันบน server: mysql -u user -p database_name < database/AddProgramPages_elos_curriculum.sql
-- หรือรันใน phpMyAdmin / MySQL client

-- เพิ่ม elos_json (ถ้ายังไม่มี)
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'program_pages' AND COLUMN_NAME = 'elos_json'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE program_pages ADD COLUMN elos_json TEXT NULL AFTER graduate_profile',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- เพิ่ม curriculum_json (ถ้ายังไม่มี)
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'program_pages' AND COLUMN_NAME = 'curriculum_json'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE program_pages ADD COLUMN curriculum_json TEXT NULL AFTER elos_json',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
