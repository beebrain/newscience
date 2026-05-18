-- Minimal schema for local stack / empty DB (homepage news API smoke)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `user` (
    `uid` INT(3) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `login_uid` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `gf_name` VARCHAR(255) DEFAULT NULL,
    `gl_name` VARCHAR(255) DEFAULT NULL,
    `tf_name` VARCHAR(255) DEFAULT NULL,
    `tl_name` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin', 'editor', 'user') DEFAULT 'user',
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`uid`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `news` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `content` TEXT,
    `excerpt` VARCHAR(1000),
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `display_as_event` TINYINT(1) NOT NULL DEFAULT 0,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `author_id` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
    `view_count` INT DEFAULT 0,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `status` (`status`),
    KEY `author_id` (`author_id`),
    CONSTRAINT `fk_news_author` FOREIGN KEY (`author_id`) REFERENCES `user`(`uid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `news_tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `news_news_tags` (
    `news_id` INT UNSIGNED NOT NULL,
    `news_tag_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`news_id`, `news_tag_id`),
    KEY `news_tag_id` (`news_tag_id`),
    CONSTRAINT `fk_nnt_news` FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nnt_tag` FOREIGN KEY (`news_tag_id`) REFERENCES `news_tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `user` (`uid`, `email`, `password`, `gf_name`, `gl_name`, `role`, `status`) VALUES
(1, 'admin@example.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Docker', 'admin', 'active');

INSERT IGNORE INTO `news_tags` (`id`, `name`, `slug`, `sort_order`) VALUES
(1, 'ข่าวทั่วไป', 'general', 1),
(2, 'กิจกรรมนักศึกษา', 'student_activity', 2),
(3, 'วิจัย/ทุนวิจัย', 'research_grant', 3),
(4, 'งานวิจัย', 'research', 4);

SET FOREIGN_KEY_CHECKS = 1;
