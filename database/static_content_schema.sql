-- Static Content Database Schema
-- For editable content from sci.uru.ac.th
-- Generated: 2026-01-18

-- =============================================
-- Site Settings (key-value pairs for general settings)
-- =============================================
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'json', 'boolean') DEFAULT 'text',
    `category` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Personnel/Staff (คณบดี, รองคณบดี, อาจารย์)
-- =============================================
CREATE TABLE IF NOT EXISTS `personnel` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(50) COMMENT 'ตำแหน่งทางวิชาการ เช่น ผู้ช่วยศาสตราจารย์',
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `first_name_en` VARCHAR(100),
    `last_name_en` VARCHAR(100),
    `position` VARCHAR(255) COMMENT 'ตำแหน่งบริหาร เช่น คณบดี',
    `position_en` VARCHAR(255),
    `department_id` INT UNSIGNED,
    `email` VARCHAR(255),
    `phone` VARCHAR(50),
    `image` VARCHAR(255),
    `bio` TEXT,
    `bio_en` TEXT,
    `education` TEXT COMMENT 'JSON array of education history',
    `expertise` TEXT COMMENT 'JSON array of expertise areas',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_department` (`department_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Departments (สาขาวิชา)
-- =============================================
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255),
    `code` VARCHAR(20) UNIQUE,
    `description` TEXT,
    `description_en` TEXT,
    `image` VARCHAR(255),
    `website` VARCHAR(500),
    `phone` VARCHAR(50),
    `email` VARCHAR(255),
    `head_personnel_id` INT UNSIGNED COMMENT 'หัวหน้าสาขา',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Programs/Curriculum (หลักสูตร)
-- =============================================
CREATE TABLE IF NOT EXISTS `programs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255),
    `degree_th` VARCHAR(100) COMMENT 'วท.บ., วท.ม., ปร.ด.',
    `degree_en` VARCHAR(100) COMMENT 'B.Sc., M.Sc., Ph.D.',
    `level` ENUM('bachelor', 'master', 'doctorate') NOT NULL,
    `department_id` INT UNSIGNED,
    `description` TEXT,
    `description_en` TEXT,
    `credits` INT COMMENT 'Total credits',
    `duration` VARCHAR(50) COMMENT 'Program duration e.g., 4 years',
    `website` VARCHAR(500),
    `curriculum_file` VARCHAR(255) COMMENT 'PDF of curriculum',
    `image` VARCHAR(255),
    `coordinator_id` INT UNSIGNED COMMENT 'Program coordinator',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_department` (`department_id`),
    INDEX `idx_level` (`level`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Research Centers (หน่วยวิจัย/ศูนย์)
-- =============================================
CREATE TABLE IF NOT EXISTS `research_centers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255),
    `description` TEXT,
    `description_en` TEXT,
    `website` VARCHAR(500),
    `image` VARCHAR(255),
    `director_id` INT UNSIGNED,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- About/Organization Info (ข้อมูลองค์กร)
-- =============================================
CREATE TABLE IF NOT EXISTS `about_sections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `section_key` VARCHAR(50) NOT NULL UNIQUE COMMENT 'vision, mission, identity, history',
    `title_th` VARCHAR(255),
    `title_en` VARCHAR(255),
    `content_th` TEXT,
    `content_en` TEXT,
    `image` VARCHAR(255),
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Contact Information
-- =============================================
CREATE TABLE IF NOT EXISTS `contact_info` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) COMMENT 'address, phone, email, social',
    `label_th` VARCHAR(100),
    `label_en` VARCHAR(100),
    `value` VARCHAR(500),
    `icon` VARCHAR(50),
    `url` VARCHAR(500),
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Insert Initial Data
-- =============================================

-- Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('faculty_name_th', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'text', 'general', 'Faculty name in Thai'),
('faculty_name_en', 'Faculty of Science and Technology', 'text', 'general', 'Faculty name in English'),
('university_name_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text', 'general', 'University name in Thai'),
('university_name_en', 'Uttaradit Rajabhat University', 'text', 'general', 'University name in English'),
('dean_message', 'ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี', 'textarea', 'general', 'Dean welcome message'),
('facebook_url', 'https://www.facebook.com/scienceuru', 'text', 'social', 'Facebook page URL'),
('logo', NULL, 'image', 'general', 'Faculty logo'),
('phone', '055-411096 ต่อ 1600', 'text', 'contact', 'Main phone number'),
('email', 'science@uru.ac.th', 'text', 'contact', 'Main email address'),
('address', '27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000', 'textarea', 'contact', 'Faculty address');

-- Dean (คณบดี)
INSERT INTO `personnel` (`title`, `first_name`, `last_name`, `position`, `position_en`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์', 'ปริญญา', 'ไกรวุฒินันท์', 'คณบดีคณะวิทยาศาสตร์และเทคโนโลยี', 'Dean of Faculty of Science and Technology', 1, 'active');

-- Departments (สาขาวิชา)
INSERT INTO `departments` (`name_th`, `name_en`, `code`, `sort_order`) VALUES
('สาขาวิชาคณิตศาสตร์', 'Department of Mathematics', 'MATH', 1),
('สาขาวิชาชีววิทยา', 'Department of Biology', 'BIO', 2),
('สาขาวิชาเคมี', 'Department of Chemistry', 'CHEM', 3),
('สาขาวิชาเทคโนโลยีสารสนเทศ', 'Department of Information Technology', 'IT', 4),
('สาขาวิชาวิทยาการคอมพิวเตอร์', 'Department of Computer Science', 'CS', 5),
('สาขาวิชาวิทยาการข้อมูล', 'Department of Data Science', 'DS', 6),
('สาขาวิชาวิทยาศาสตร์การกีฬา', 'Department of Sports Science', 'SS', 7),
('สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', 'Department of Environmental Science', 'ENV', 8),
('สาขาวิชาสาธารณสุขศาสตร์', 'Department of Public Health', 'PH', 9),
('สาขาวิชาอาหารและโภชนาการ', 'Department of Food and Nutrition', 'FN', 10);

-- Programs (หลักสูตร)
INSERT INTO `programs` (`name_th`, `name_en`, `degree_th`, `degree_en`, `level`, `department_id`, `sort_order`) VALUES
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาคณิตศาสตร์', 'Bachelor of Science in Mathematics', 'วท.บ.', 'B.Sc.', 'bachelor', 1, 1),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาชีววิทยา', 'Bachelor of Science in Biology', 'วท.บ.', 'B.Sc.', 'bachelor', 2, 2),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาเคมี', 'Bachelor of Science in Chemistry', 'วท.บ.', 'B.Sc.', 'bachelor', 3, 3),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาเทคโนโลยีสารสนเทศ', 'Bachelor of Science in Information Technology', 'วท.บ.', 'B.Sc.', 'bachelor', 4, 4),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการคอมพิวเตอร์', 'Bachelor of Science in Computer Science', 'วท.บ.', 'B.Sc.', 'bachelor', 5, 5),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาวิทยาการข้อมูล', 'Bachelor of Science in Data Science', 'วท.บ.', 'B.Sc.', 'bachelor', 6, 6),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาวิทยาศาสตร์การกีฬา', 'Bachelor of Science in Sports Science', 'วท.บ.', 'B.Sc.', 'bachelor', 7, 7),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', 'Bachelor of Science in Environmental Science', 'วท.บ.', 'B.Sc.', 'bachelor', 8, 8),
('หลักสูตรสาธารณสุขศาสตรบัณฑิต', 'Bachelor of Public Health', 'ส.บ.', 'B.P.H.', 'bachelor', 9, 9),
('หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาอาหารและโภชนาการ', 'Bachelor of Science in Food and Nutrition', 'วท.บ.', 'B.Sc.', 'bachelor', 10, 10),
('หลักสูตรวิทยาศาสตรมหาบัณฑิต สาขาวิชาวิทยาศาสตร์ประยุกต์', 'Master of Science in Applied Science', 'วท.ม.', 'M.Sc.', 'master', NULL, 11),
('หลักสูตรปรัชญาดุษฎีบัณฑิต สาขาวิชาวิทยาศาสตร์ประยุกต์', 'Doctor of Philosophy in Applied Science', 'ปร.ด.', 'Ph.D.', 'doctorate', NULL, 12);

-- Research Centers
INSERT INTO `research_centers` (`name_th`, `name_en`, `website`, `sort_order`) VALUES
('ศูนย์ดิจิทัลเพื่อพัฒนาท้องถิ่น', 'Digital Center for Local Development', 'http://202.29.52.60/~dicenter', 1),
('หน่วยจัดการงานวิจัยและพันธกิจสัมพันธ์', 'Research Management and Social Engagement Unit', 'https://www.facebook.com/ScienceRMUURU', 2),
('ศูนย์พลังงานและสิ่งแวดล้อม', 'Energy and Environment Center', 'http://scirmu.sci.uru.ac.th/', 3);

-- About Sections
INSERT INTO `about_sections` (`section_key`, `title_th`, `title_en`, `content_th`) VALUES
('vision', 'วิสัยทัศน์', 'Vision', 'เป็นองค์กรชั้นนำด้านวิทยาศาสตร์และเทคโนโลยีเพื่อพัฒนาท้องถิ่น'),
('mission', 'พันธกิจ', 'Mission', 'ผลิตบัณฑิตที่มีคุณภาพ สร้างงานวิจัยและนวัตกรรม บริการวิชาการ และทำนุบำรุงศิลปวัฒนธรรม'),
('identity', 'อัตลักษณ์', 'Identity', 'บัณฑิตนักปฏิบัติที่มีความรู้คู่คุณธรรม'),
('philosophy', 'ปรัชญา', 'Philosophy', 'วิทยาศาสตร์และเทคโนโลยีเพื่อการพัฒนาท้องถิ่นอย่างยั่งยืน');

-- Contact Info
INSERT INTO `contact_info` (`type`, `label_th`, `label_en`, `value`, `icon`, `sort_order`) VALUES
('phone', 'โทรศัพท์', 'Phone', '055-411096 ต่อ 1600', 'phone', 1),
('email', 'อีเมล', 'Email', 'science@uru.ac.th', 'email', 2),
('address', 'ที่อยู่', 'Address', '27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000', 'location', 3),
('social', 'Facebook', 'Facebook', 'https://www.facebook.com/scienceuru', 'facebook', 4);
