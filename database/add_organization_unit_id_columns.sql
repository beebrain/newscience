-- ใช้ organization_units แทน departments: เพิ่ม organization_unit_id ใน programs และ personnel
-- Run หลัง add_organization_units.sql: mysql -u root -p newscience < database/add_organization_unit_id_columns.sql

-- programs: หลักสูตรสังกัดหน่วยงาน หลักสูตรป.ตรี (4) หรือ บัณฑิต (5)
ALTER TABLE `programs`
ADD COLUMN `organization_unit_id` TINYINT UNSIGNED DEFAULT NULL
COMMENT 'FK organization_units: 4=หลักสูตรป.ตรี, 5=หลักสูตรบัณฑิต'
AFTER `department_id`;

UPDATE `programs` SET `organization_unit_id` = 4 WHERE `level` = 'bachelor' AND `organization_unit_id` IS NULL;
UPDATE `programs` SET `organization_unit_id` = 5 WHERE `level` IN ('master', 'doctorate') AND `organization_unit_id` IS NULL;

ALTER TABLE `programs`
ADD KEY `organization_unit_id` (`organization_unit_id`),
ADD CONSTRAINT `fk_programs_organization_unit` FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units`(`id`) ON DELETE SET NULL;

-- personnel: บุคลากรสังกัดหน่วยงาน (ถ้ามี)
ALTER TABLE `personnel`
ADD COLUMN `organization_unit_id` TINYINT UNSIGNED DEFAULT NULL
COMMENT 'FK organization_units: 1=ผู้บริหาร, 2=สำนักงาน, 3=หน่วยวิจัย, 4/5=หลักสูตร'
AFTER `department_id`;

ALTER TABLE `personnel`
ADD KEY `organization_unit_id` (`organization_unit_id`),
ADD CONSTRAINT `fk_personnel_organization_unit` FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units`(`id`) ON DELETE SET NULL;
