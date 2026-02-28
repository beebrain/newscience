-- เพิ่มคอลัมน์ alumni_messages_json ใน program_pages (ศิษย์เก่าถึงรุ่นน้อง)
-- รันบน server: mysql -u user -p database_name < database/AddProgramPages_alumni_messages.sql

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'program_pages' AND COLUMN_NAME = 'alumni_messages_json'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE program_pages ADD COLUMN alumni_messages_json TEXT NULL AFTER curriculum_json',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
