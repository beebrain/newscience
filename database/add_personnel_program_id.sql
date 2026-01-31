-- Add program_id (หลักสูตร) to personnel table
-- Run this if your personnel table does not have program_id yet.

ALTER TABLE `personnel`
ADD COLUMN `program_id` INT UNSIGNED DEFAULT NULL AFTER `department_id`,
ADD KEY `program_id` (`program_id`);

-- Optional: add foreign key (uncomment if your DB allows)
-- ALTER TABLE `personnel`
-- ADD CONSTRAINT `fk_personnel_program` FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE SET NULL;
