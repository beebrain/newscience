-- =====================================================
-- Calendar feature: Executive scheduling / ตารางนัดหมายกิจกรรมผู้บริหาร
-- Run this migration to add calendar_events and calendar_participants.
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- calendar_events
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `all_day` TINYINT(1) DEFAULT 0,
    `location` VARCHAR(255) DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#3b82f6',
    `status` ENUM('active', 'cancelled') DEFAULT 'active',
    `created_by` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `start_datetime` (`start_datetime`),
    KEY `end_datetime` (`end_datetime`),
    KEY `status` (`status`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `fk_calendar_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- calendar_participants (ใช้ user_email แทน user_id)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_participants` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `added_by` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_calendar_participants_event_email` (`event_id`, `user_email`),
    KEY `user_email` (`user_email`),
    KEY `event_id` (`event_id`),
    CONSTRAINT `fk_calendar_participants_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_calendar_participants_added_by` FOREIGN KEY (`added_by`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- systems: add calendar permission (if systems table exists)
-- -----------------------------------------------------
INSERT INTO `systems` (`slug`, `name_th`, `name_en`, `description`, `icon`, `is_active`, `sort_order`, `created_at`, `updated_at`)
SELECT 'calendar', 'ปฏิทินนัดหมาย', 'Calendar', 'ตารางนัดหมายกิจกรรมผู้บริหาร', NULL, 1, 13, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `systems` WHERE `slug` = 'calendar' LIMIT 1);
