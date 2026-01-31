-- =====================================================
-- NewScience Full Migration SQL
-- Run this file on production server
-- Generated: 2026-01-31
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- =====================================================
-- 1. USER TABLE
-- =====================================================
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

-- =====================================================
-- 2. SITE SETTINGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'json', 'boolean') DEFAULT 'text',
    `category` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. DEPARTMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) DEFAULT NULL,
    `code` VARCHAR(20) DEFAULT NULL,
    `description` TEXT,
    `description_en` TEXT,
    `image` VARCHAR(255) DEFAULT NULL,
    `website` VARCHAR(500) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `head_personnel_id` INT UNSIGNED DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. PERSONNEL TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `personnel` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(50) DEFAULT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `first_name_en` VARCHAR(100) DEFAULT NULL,
    `last_name_en` VARCHAR(100) DEFAULT NULL,
    `position` VARCHAR(255) DEFAULT NULL,
    `position_en` VARCHAR(255) DEFAULT NULL,
    `department_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT,
    `bio_en` TEXT,
    `education` TEXT,
    `expertise` TEXT,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `department_id` (`department_id`),
    KEY `status` (`status`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. PROGRAMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `programs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) DEFAULT NULL,
    `degree_th` VARCHAR(100) DEFAULT NULL,
    `degree_en` VARCHAR(100) DEFAULT NULL,
    `level` ENUM('bachelor', 'master', 'doctorate') NOT NULL DEFAULT 'bachelor',
    `department_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT,
    `description_en` TEXT,
    `credits` INT DEFAULT NULL,
    `duration` VARCHAR(50) DEFAULT NULL,
    `website` VARCHAR(500) DEFAULT NULL,
    `curriculum_file` VARCHAR(255) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `coordinator_id` INT UNSIGNED DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `department_id` (`department_id`),
    KEY `level` (`level`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. NEWS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `news` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `content` TEXT,
    `excerpt` VARCHAR(1000),
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `category` ENUM('general', 'student_activity', 'research_grant') DEFAULT 'general',
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `author_id` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
    `view_count` INT DEFAULT 0,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `status` (`status`),
    KEY `category` (`category`),
    KEY `published_at` (`published_at`),
    KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. NEWS IMAGES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `news_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `news_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `news_id` (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. ACTIVITIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `activities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `description` TEXT,
    `activity_date` DATE DEFAULT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `status` (`status`),
    KEY `activity_date` (`activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. ACTIVITY IMAGES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `activity_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `activity_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. LINKS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `links` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `category` VARCHAR(100) DEFAULT 'general',
    `icon` VARCHAR(100) DEFAULT NULL,
    `target` ENUM('_self', '_blank') DEFAULT '_blank',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category` (`category`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. HERO SLIDES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NULL DEFAULT NULL,
    `subtitle` VARCHAR(255) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `image` VARCHAR(500) NOT NULL,
    `link` VARCHAR(500) NULL DEFAULT NULL,
    `link_text` VARCHAR(100) NULL DEFAULT NULL,
    `show_buttons` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `start_date` DATETIME NULL DEFAULT NULL,
    `end_date` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Default Admin User (password: admin123)
INSERT IGNORE INTO `user` (`email`, `password`, `gf_name`, `gl_name`, `tf_name`, `tl_name`, `role`, `status`) VALUES
('admin@sci.uru.ac.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'ผู้ดูแล', 'ระบบ', 'admin', 'active');

-- Site Settings
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('site_name_th', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'text', 'general'),
('site_name_en', 'Faculty of Science and Technology', 'text', 'general'),
('university_name_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text', 'general'),
('university_name_en', 'Uttaradit Rajabhat University', 'text', 'general'),
('phone', '055-411096', 'text', 'contact'),
('email', 'sci@uru.ac.th', 'text', 'contact'),
('address_th', 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000', 'textarea', 'contact'),
('facebook', 'https://www.facebook.com/scienceuru', 'text', 'social');

-- Departments
INSERT IGNORE INTO `departments` (`id`, `name_th`, `name_en`, `code`, `sort_order`, `status`) VALUES
(1, 'สำนักงานคณบดี', 'Dean Office', 'DEAN', 1, 'active'),
(2, 'สาขาวิชาคณิตศาสตร์ประยุกต์', 'Applied Mathematics', 'MATH', 2, 'active'),
(3, 'สาขาวิชาชีววิทยา', 'Biology', 'BIO', 3, 'active'),
(4, 'สาขาวิชาเคมี', 'Chemistry', 'CHEM', 4, 'active'),
(5, 'สาขาวิชาเทคโนโลยีสารสนเทศ', 'Information Technology', 'IT', 5, 'active'),
(6, 'สาขาวิชาวิทยาการคอมพิวเตอร์', 'Computer Science', 'CS', 6, 'active'),
(7, 'สาขาวิชาวิทยาการข้อมูล', 'Data Science', 'DS', 7, 'active'),
(8, 'สาขาวิชาวิทยาศาสตร์การกีฬา', 'Sports Science', 'SPORT', 8, 'active'),
(9, 'สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', 'Environmental Science', 'ENV', 9, 'active'),
(10, 'สาขาวิชาสาธารณสุขศาสตร์', 'Public Health', 'PH', 10, 'active'),
(11, 'สาขาวิชาอาหารและโภชนาการ', 'Food and Nutrition', 'FOOD', 11, 'active');

-- Hero Slides
INSERT IGNORE INTO `hero_slides` (`id`, `title`, `subtitle`, `description`, `image`, `show_buttons`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี', 'Uttaradit Rajabhat University', 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น', 'assets/images/hero_background.png', 1, 1, 1, NOW()),
(2, 'งานวิจัยและนวัตกรรม', 'Research & Innovation', 'พัฒนางานวิจัยเพื่อตอบโจทย์ชุมชนและท้องถิ่น ส่งเสริมการสร้างนวัตกรรมที่มีคุณค่า', 'assets/images/research_laboratory.png', 0, 2, 1, NOW()),
(3, 'กิจกรรมนักศึกษา', 'Student Activities', 'ร่วมสร้างประสบการณ์การเรียนรู้นอกห้องเรียน พัฒนาทักษะและความสามารถรอบด้าน', 'assets/images/student_activities.png', 0, 3, 1, NOW());

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- MIGRATION COMPLETE
-- =====================================================
SELECT 'Migration completed successfully!' AS status;
