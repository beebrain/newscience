-- Add program_id column to user table
-- Run: php scripts/run_sql.php database/add_user_program_id.sql

ALTER TABLE `user`
ADD COLUMN IF NOT EXISTS `program_id` INT UNSIGNED DEFAULT NULL AFTER `role`,
ADD KEY `program_id` (`program_id`);
