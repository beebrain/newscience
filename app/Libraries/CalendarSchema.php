<?php

namespace App\Libraries;

use Config\Database;

class CalendarSchema
{
    public static function ensure(): void
    {
        $db = Database::connect();

        $db->query("CREATE TABLE IF NOT EXISTS `calendar_events` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `start_datetime` DATETIME NOT NULL,
            `end_datetime` DATETIME NOT NULL,
            `all_day` TINYINT(1) NOT NULL DEFAULT 0,
            `location` VARCHAR(255) NULL,
            `color` VARCHAR(7) NULL DEFAULT '#3b82f6',
            `status` ENUM('active','cancelled') NOT NULL DEFAULT 'active',
            `created_by` INT(11) UNSIGNED NOT NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            KEY `created_by` (`created_by`),
            KEY `status` (`status`),
            KEY `start_datetime` (`start_datetime`),
            KEY `end_datetime` (`end_datetime`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $db->query("CREATE TABLE IF NOT EXISTS `calendar_participants` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `event_id` INT(11) UNSIGNED NOT NULL,
            `user_email` VARCHAR(255) NOT NULL,
            `added_by` INT(11) UNSIGNED NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `calendar_event_email_unique` (`event_id`, `user_email`),
            KEY `event_id` (`event_id`),
            KEY `user_email` (`user_email`),
            KEY `added_by` (`added_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $addedByColumn = $db->query("SHOW COLUMNS FROM `calendar_participants` LIKE 'added_by'")->getRowArray();
        if (! $addedByColumn) {
            $db->query("ALTER TABLE `calendar_participants` ADD COLUMN `added_by` INT(11) UNSIGNED NULL AFTER `user_email`");
        }

        $createdAtColumn = $db->query("SHOW COLUMNS FROM `calendar_participants` LIKE 'created_at'")->getRowArray();
        if (! $createdAtColumn) {
            $db->query("ALTER TABLE `calendar_participants` ADD COLUMN `created_at` DATETIME NULL AFTER `added_by`");
        }

        $updatedAtColumn = $db->query("SHOW COLUMNS FROM `calendar_participants` LIKE 'updated_at'")->getRowArray();
        if (! $updatedAtColumn) {
            $db->query("ALTER TABLE `calendar_participants` ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`");
        }
    }
}
