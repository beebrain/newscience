-- Tables required for homepage (beyond news API)

CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    `category` VARCHAR(100) DEFAULT 'general',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('site_name_th', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'text', 'general'),
('site_name_en', 'Faculty of Science and Technology', 'text', 'general'),
('university_name_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text', 'general'),
('about_th', 'ทดสอบ local stack', 'textarea', 'about'),
('logo', '', 'image', 'general');

CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personnel` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `position` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `programs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) DEFAULT NULL,
    `level` ENUM('bachelor', 'master', 'doctorate') NOT NULL DEFAULT 'bachelor',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `program_pages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `program_id` INT UNSIGNED NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `hero_image` VARCHAR(255) DEFAULT NULL,
    `is_published` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `program_id` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NULL,
    `subtitle` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `image` VARCHAR(500) NOT NULL,
    `link` VARCHAR(500) NULL,
    `link_text` VARCHAR(100) NULL,
    `show_buttons` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `start_date` DATETIME NULL,
    `end_date` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `hero_slides` (`id`, `title`, `subtitle`, `description`, `image`, `show_buttons`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'ยินดีต้อนรับ (Docker)', 'URU Science', 'ทดสอบหน้าแรกใน Docker', 'assets/images/hero_background.png', 1, 1, 1, NOW());

CREATE TABLE IF NOT EXISTS `events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `content` TEXT DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `author_id` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `urgent_popups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NULL,
    `link_url` VARCHAR(500) NULL,
    `link_text` VARCHAR(100) NULL,
    `image` VARCHAR(500) NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `start_date` DATETIME NULL,
    `end_date` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
