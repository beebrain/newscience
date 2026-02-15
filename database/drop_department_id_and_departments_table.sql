-- ยกเลิก column department_id จาก personnel และ programs แล้วลบตาราง departments
-- รันหลัง add_organization_unit_id_columns.sql (ต้องมี organization_unit_id แล้ว)
-- Run: mysql -u root -p newscience < database/drop_department_id_and_departments_table.sql
-- ใช้ stored procedure เพื่อตรวจสอบก่อน DROP (กัน error 1091)

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
