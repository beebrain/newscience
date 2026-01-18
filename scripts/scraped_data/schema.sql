-- Generated Database Schema from sci.uru.ac.th
-- Generated at: 2026-01-18 11:05:59

-- Table: news
-- Sample data count: 143
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(500) NOT NULL,
  `slug` VARCHAR(500) UNIQUE,
  `content` TEXT,
  `excerpt` VARCHAR(1000),
  `featured_image` VARCHAR(255),
  `status` ENUM('draft','published') DEFAULT draft,
  `author_id` INT,
  `published_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: news_images
CREATE TABLE IF NOT EXISTS `news_images` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `news_id` INT,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(500),
  `sort_order` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name_th` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `description` TEXT,
  `image` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: programs
-- Sample data count: 1
CREATE TABLE IF NOT EXISTS `programs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name_th` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `degree_level` ENUM('bachelor','master','doctorate'),
  `department_id` INT,
  `description` TEXT,
  `url` VARCHAR(500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

