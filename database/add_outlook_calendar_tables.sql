-- =====================================================
-- Outlook Calendar Integration: ตารางเก็บ token และ mapping event → Outlook
-- รัน migration นี้ก่อนใช้ฟีเจอร์ "เชื่อมต่อ Outlook"
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- user_outlook_tokens (หนึ่ง user ต่อหนึ่งชุด token)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_outlook_tokens` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(3) UNSIGNED ZEROFILL NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `access_token` TEXT DEFAULT NULL,
    `refresh_token` TEXT NOT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_outlook_tokens_user_id` (`user_id`),
    KEY `user_email` (`user_email`),
    CONSTRAINT `fk_user_outlook_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- calendar_event_outlook_sync ( mapping event_id + user_email → outlook_event_id )
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_outlook_sync` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `user_email` VARCHAR(255) NOT NULL,
    `outlook_event_id` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_calendar_event_outlook_sync_event_email` (`event_id`, `user_email`),
    KEY `event_id` (`event_id`),
    KEY `user_email` (`user_email`),
    CONSTRAINT `fk_calendar_event_outlook_sync_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
