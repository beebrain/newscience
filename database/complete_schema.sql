-- Complete Database Schema for NewScience Website
-- Run this file to create all necessary tables
-- Based on sci.uru.ac.th structure

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- User Table (already exists, included for reference)
-- ============================================
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

-- ============================================
-- Site Settings Table
-- ============================================
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name_th', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'text'),
('site_name_en', 'Faculty of Science and Technology', 'text'),
('university_name_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text'),
('university_name_en', 'Uttaradit Rajabhat University', 'text'),
('phone', '055-411096', 'text'),
('fax', '055-411096 ต่อ 1700', 'text'),
('email', 'sci@uru.ac.th', 'text'),
('address_th', 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000', 'textarea'),
('address_en', 'Faculty of Science and Technology, Uttaradit Rajabhat University, 27 Injaimee Rd., Tha-It, Muang, Uttaradit 53000, Thailand', 'textarea'),
('facebook', 'https://www.facebook.com/scienceuru', 'text'),
('website', 'https://sci.uru.ac.th', 'text'),
('logo', '', 'image'),
('vision_th', '', 'textarea'),
('vision_en', '', 'textarea'),
('mission_th', '', 'textarea'),
('mission_en', '', 'textarea'),
('about_th', '', 'textarea'),
('about_en', '', 'textarea'),
('hero_title_th', 'ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี', 'text'),
('hero_title_en', 'Welcome to Faculty of Science and Technology', 'text'),
('hero_subtitle_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text'),
('hero_subtitle_en', 'Uttaradit Rajabhat University', 'text'),
('hero_description_th', 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น', 'textarea'),
('hero_description_en', 'Producing knowledgeable and capable graduates, developing research and innovation to serve communities and local areas.', 'textarea'),
('hero_image', '', 'image')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ============================================
-- Departments Table
-- ============================================
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

-- Insert default departments
INSERT INTO `departments` (`name_th`, `name_en`, `code`, `sort_order`, `status`) VALUES
('สำนักงานคณบดี', 'Dean Office', 'DEAN', 1, 'active'),
('สาขาวิชาคณิตศาสตร์ประยุกต์', 'Applied Mathematics', 'MATH', 2, 'active'),
('สาขาวิชาชีววิทยา', 'Biology', 'BIO', 3, 'active'),
('สาขาวิชาเคมี', 'Chemistry', 'CHEM', 4, 'active'),
('สาขาวิชาเทคโนโลยีสารสนเทศ', 'Information Technology', 'IT', 5, 'active'),
('สาขาวิชาวิทยาการคอมพิวเตอร์', 'Computer Science', 'CS', 6, 'active'),
('สาขาวิชาวิทยาการข้อมูล', 'Data Science', 'DS', 7, 'active'),
('สาขาวิชาวิทยาศาสตร์การกีฬาและการออกกำลังกาย', 'Sports and Exercise Science', 'SPORT', 8, 'active'),
('สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', 'Environmental Science', 'ENV', 9, 'active'),
('สาขาวิชาสาธารณสุขศาสตร์', 'Public Health', 'PH', 10, 'active'),
('สาขาวิชาอาหารและโภชนาการ', 'Food and Nutrition', 'FOOD', 11, 'active')
ON DUPLICATE KEY UPDATE `name_th` = VALUES(`name_th`);

-- ============================================
-- Personnel Table
-- ============================================
CREATE TABLE IF NOT EXISTS `personnel` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(50) DEFAULT NULL COMMENT 'Academic title (ศ.ดร., รศ.ดร., etc.)',
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `first_name_en` VARCHAR(100) DEFAULT NULL,
    `last_name_en` VARCHAR(100) DEFAULT NULL,
    `position` VARCHAR(255) DEFAULT NULL COMMENT 'Administrative position',
    `position_en` VARCHAR(255) DEFAULT NULL,
    `department_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT,
    `bio_en` TEXT,
    `education` TEXT COMMENT 'JSON array of education',
    `expertise` TEXT COMMENT 'JSON array of expertise areas',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `department_id` (`department_id`),
    KEY `status` (`status`),
    KEY `sort_order` (`sort_order`),
    CONSTRAINT `fk_personnel_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Programs Table
-- ============================================
CREATE TABLE IF NOT EXISTS `programs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) DEFAULT NULL,
    `degree_th` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., วิทยาศาสตรบัณฑิต',
    `degree_en` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., Bachelor of Science',
    `level` ENUM('bachelor', 'master', 'doctorate') NOT NULL DEFAULT 'bachelor',
    `department_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT,
    `description_en` TEXT,
    `credits` INT DEFAULT NULL COMMENT 'Total credits required',
    `duration` VARCHAR(50) DEFAULT NULL COMMENT 'e.g., 4 years',
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
    KEY `status` (`status`),
    CONSTRAINT `fk_programs_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default programs
INSERT INTO `programs` (`name_th`, `name_en`, `degree_th`, `degree_en`, `level`, `department_id`, `duration`, `sort_order`, `status`) VALUES
('คณิตศาสตร์ประยุกต์', 'Applied Mathematics', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 2, '4 ปี', 1, 'active'),
('ชีววิทยา', 'Biology', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 3, '4 ปี', 2, 'active'),
('เคมี', 'Chemistry', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 4, '4 ปี', 3, 'active'),
('เทคโนโลยีสารสนเทศ', 'Information Technology', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 5, '4 ปี', 4, 'active'),
('วิทยาการคอมพิวเตอร์', 'Computer Science', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 6, '4 ปี', 5, 'active'),
('วิทยาการข้อมูล', 'Data Science', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 7, '4 ปี', 6, 'active'),
('วิทยาศาสตร์การกีฬาและการออกกำลังกาย', 'Sports and Exercise Science', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 8, '4 ปี', 7, 'active'),
('วิทยาศาสตร์สิ่งแวดล้อม', 'Environmental Science', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 9, '4 ปี', 8, 'active'),
('สาธารณสุขศาสตร์', 'Public Health', 'สาธารณสุขศาสตรบัณฑิต', 'Bachelor of Public Health', 'bachelor', 10, '4 ปี', 9, 'active'),
('อาหารและโภชนาการ', 'Food and Nutrition', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', 11, '4 ปี', 10, 'active'),
('วิทยาศาสตร์ประยุกต์', 'Applied Science', 'วิทยาศาสตรมหาบัณฑิต', 'Master of Science', 'master', NULL, '2 ปี', 11, 'active'),
('วิทยาศาสตร์ประยุกต์', 'Applied Science', 'ปรัชญาดุษฎีบัณฑิต', 'Doctor of Philosophy', 'doctorate', NULL, '3 ปี', 12, 'active'),
('วิศวกรรมคอมพิวเตอร์และปัญญาประดิษฐ์', 'Computer Engineering and AI', 'วิศวกรรมศาสตรมหาบัณฑิต', 'Master of Engineering', 'master', 6, '2 ปี', 13, 'active')
ON DUPLICATE KEY UPDATE `name_th` = VALUES(`name_th`);

-- ============================================
-- News Table (already exists, included for reference)
-- ============================================
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
    KEY `published_at` (`published_at`),
    KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- News Images Table
-- ============================================
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

-- ============================================
-- Activities/Gallery Table
-- ============================================
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

-- ============================================
-- Activity Images Table
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `activity_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `activity_id` (`activity_id`),
    CONSTRAINT `fk_activity_images` FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Links Table (for external/internal links)
-- ============================================
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

-- Insert default admin user if not exists
INSERT INTO `user` (`email`, `password`, `gf_name`, `gl_name`, `tf_name`, `tl_name`, `role`, `status`) VALUES
('admin@sci.uru.ac.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'ผู้ดูแล', 'ระบบ', 'admin', 'active')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

SET FOREIGN_KEY_CHECKS = 1;
