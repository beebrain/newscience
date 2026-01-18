-- NewScience Database Schema
-- Run this file to create all necessary tables

-- Create user table (following researchrecord.user structure)
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

-- Create news table
CREATE TABLE IF NOT EXISTS `news` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `content` TEXT,
    `excerpt` VARCHAR(1000),
    `status` ENUM('draft', 'published') DEFAULT 'draft',
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

-- Create news_images table for multiple images per news
CREATE TABLE IF NOT EXISTS `news_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `news_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `news_id` (`news_id`),
    CONSTRAINT `fk_news_images_news` FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `user` (`email`, `password`, `gf_name`, `gl_name`, `role`, `status`) VALUES
('admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active');
