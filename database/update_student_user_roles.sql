-- Update Student User Table Roles Migration
-- Run: mysql -u user -p database_name < database/update_student_user_roles.sql

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Update student_user table role field
-- ============================================

-- Add program_id field if not exists
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'student_user' 
     AND COLUMN_NAME = 'program_id') > 0,
    'SELECT "program_id already exists in student_user table";',
    'ALTER TABLE `student_user` ADD COLUMN `program_id` INT DEFAULT NULL COMMENT "Program ID for access control";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modify role enum to include admin_student
ALTER TABLE `student_user` 
MODIFY COLUMN `role` ENUM('student', 'club', 'admin_student') DEFAULT 'student' 
COMMENT 'student=basic student, club=student club member, admin_student=student admin';

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS `idx_student_user_role` ON `student_user` (`role`);
CREATE INDEX IF NOT EXISTS `idx_student_user_program_id` ON `student_user` (`program_id`);
CREATE INDEX IF NOT EXISTS `idx_student_user_status` ON `student_user` (`status`);

-- Add foreign key for program_id if programs table exists
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'programs') > 0,
    'ALTER TABLE `student_user` ADD CONSTRAINT `fk_student_user_program` 
     FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) 
     ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "programs table not found, skipping foreign key";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify the changes
SELECT 'Student user table updated successfully' as status;
SELECT COUNT(*) as total_students FROM `student_user`;
SELECT role, COUNT(*) as count FROM `student_user` GROUP BY role;
