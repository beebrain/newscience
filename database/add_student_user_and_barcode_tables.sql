-- Barcode Distribution System: student_user + barcode_events, barcodes, barcode_event_eligibles
-- Based on main schema (SCHEMA_TABLES.md). Run after organization_units migration.
-- Run: mysql -u user -p database_name < database/add_student_user_and_barcode_tables.sql

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- student_user (structure from user; email as UNIQUE key)
-- ============================================
CREATE TABLE IF NOT EXISTS `student_user` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `login_uid` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL COMMENT 'UNIQUE; key for linking',
    `password` VARCHAR(255) DEFAULT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `gf_name` VARCHAR(255) DEFAULT NULL,
    `gl_name` VARCHAR(255) DEFAULT NULL,
    `tf_name` VARCHAR(255) DEFAULT NULL,
    `tl_name` VARCHAR(255) DEFAULT NULL,
    `th_name` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อ (ไทย)',
    `thai_lastname` VARCHAR(255) DEFAULT NULL COMMENT 'นามสกุล (ไทย)',
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('student', 'club') DEFAULT 'student' COMMENT 'student=นักศึกษา, club=นักศึกษาสโมสร',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `role` (`role`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- barcode_events (events for barcode distribution; separate from public events)
-- ============================================
CREATE TABLE IF NOT EXISTS `barcode_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `status` ENUM('draft', 'active', 'closed') DEFAULT 'draft',
    `created_by_student_user_id` INT UNSIGNED DEFAULT NULL COMMENT 'set when created by club (student_user role=club)',
    `created_by_user_uid` INT UNSIGNED DEFAULT NULL COMMENT 'set when created by system admin (user.uid)',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `event_date` (`event_date`),
    KEY `created_by_student_user_id` (`created_by_student_user_id`),
    KEY `created_by_user_uid` (`created_by_user_uid`),
    CONSTRAINT `fk_barcode_events_student_user` FOREIGN KEY (`created_by_student_user_id`) REFERENCES `student_user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_barcode_events_user` FOREIGN KEY (`created_by_user_uid`) REFERENCES `user`(`uid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- barcodes (one row per barcode code; linked to event and optionally to student)
-- ============================================
CREATE TABLE IF NOT EXISTS `barcodes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `barcode_event_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(255) NOT NULL COMMENT 'barcode string',
    `student_user_id` INT UNSIGNED DEFAULT NULL COMMENT 'assigned student; NULL until assigned',
    `assigned_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `barcode_event_code` (`barcode_event_id`, `code`),
    KEY `student_user_id` (`student_user_id`),
    CONSTRAINT `fk_barcodes_event` FOREIGN KEY (`barcode_event_id`) REFERENCES `barcode_events`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_barcodes_student_user` FOREIGN KEY (`student_user_id`) REFERENCES `student_user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- barcode_event_eligibles (students eligible to receive a barcode for this event)
-- ============================================
CREATE TABLE IF NOT EXISTS `barcode_event_eligibles` (
    `barcode_event_id` INT UNSIGNED NOT NULL,
    `student_user_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`barcode_event_id`, `student_user_id`),
    KEY `student_user_id` (`student_user_id`),
    CONSTRAINT `fk_eligibles_event` FOREIGN KEY (`barcode_event_id`) REFERENCES `barcode_events`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_eligibles_student_user` FOREIGN KEY (`student_user_id`) REFERENCES `student_user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
