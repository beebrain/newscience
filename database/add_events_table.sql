-- Events table for "Events coming up" / กิจกรรมที่จะมาถึง
-- Run this migration to add the events feature.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `content` TEXT DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME DEFAULT NULL,
    `event_end_date` DATE DEFAULT NULL,
    `event_end_time` TIME DEFAULT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `sort_order` INT DEFAULT 0,
    `author_id` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `status` (`status`),
    KEY `event_date` (`event_date`),
    KEY `author_id` (`author_id`),
    CONSTRAINT `fk_events_author` FOREIGN KEY (`author_id`) REFERENCES `user`(`uid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
