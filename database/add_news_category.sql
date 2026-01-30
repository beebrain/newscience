-- Add category column to news table
-- Run this SQL to add news categorization

ALTER TABLE `news` ADD COLUMN `category` ENUM('general', 'student_activity', 'research_grant') DEFAULT 'general' AFTER `status`;

-- Update existing news to 'general' category (if any exist without category)
UPDATE `news` SET `category` = 'general' WHERE `category` IS NULL;
