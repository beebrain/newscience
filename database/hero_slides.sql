-- Hero Slides Table
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

-- Insert sample data
INSERT INTO `hero_slides` (`title`, `subtitle`, `description`, `image`, `show_buttons`, `sort_order`, `is_active`, `created_at`) VALUES
('ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี', 'Uttaradit Rajabhat University', 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น', 'assets/images/hero_background.png', 1, 1, 1, NOW()),
('งานวิจัยและนวัตกรรม', 'Research & Innovation', 'พัฒนางานวิจัยเพื่อตอบโจทย์ชุมชนและท้องถิ่น ส่งเสริมการสร้างนวัตกรรมที่มีคุณค่า', 'assets/images/research_laboratory.png', 0, 2, 1, NOW()),
('กิจกรรมนักศึกษา', 'Student Activities', 'ร่วมสร้างประสบการณ์การเรียนรู้นอกห้องเรียน พัฒนาทักษะและความสามารถรอบด้าน', 'assets/images/student_activities.png', 0, 3, 1, NOW());
