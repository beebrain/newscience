-- Update User Table Roles Migration
-- Run: mysql -u user -p database_name < database/update_user_roles.sql

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Update user table role field
-- ============================================

-- First, update existing users to new role system
UPDATE `user` SET `role` = 'super_admin' WHERE `role` = 'admin';
UPDATE `user` SET `role` = 'user' WHERE `role` = 'editor' OR `role` = 'user';

-- Add program_id field if not exists
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'user' 
     AND COLUMN_NAME = 'program_id') > 0,
    'SELECT "program_id already exists in user table";',
    'ALTER TABLE `user` ADD COLUMN `program_id` INT DEFAULT NULL COMMENT "Program ID for access control";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modify role enum to new values
ALTER TABLE `user` 
MODIFY COLUMN `role` ENUM('super_admin', 'faculty_admin', 'user') DEFAULT 'user' 
COMMENT 'super_admin=full access, faculty_admin=faculty access, user=basic access';

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS `idx_user_role` ON `user` (`role`);
CREATE INDEX IF NOT EXISTS `idx_user_program_id` ON `user` (`program_id`);
CREATE INDEX IF NOT EXISTS `idx_user_status` ON `user` (`status`);

-- Add foreign key for program_id if programs table exists
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'programs') > 0,
    'ALTER TABLE `user` ADD CONSTRAINT `fk_user_program` 
     FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) 
     ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "programs table not found, skipping foreign key";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify the changes
SELECT 'User table updated successfully' as status;
SELECT COUNT(*) as total_users FROM `user`;
SELECT role, COUNT(*) as count FROM `user` GROUP BY role;
