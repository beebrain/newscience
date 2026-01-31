-- =====================================================
-- Insert Data with UTF-8 Encoding
-- Run: mysql -u root -p --default-character-set=utf8mb4 newscience < insert_data.sql
-- =====================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =====================================================
-- Clear existing data
-- =====================================================
TRUNCATE TABLE hero_slides;
DELETE FROM site_settings;
DELETE FROM departments;
DELETE FROM programs;
DELETE FROM personnel;

-- =====================================================
-- HERO SLIDES
-- =====================================================
INSERT INTO `hero_slides` (`title`, `subtitle`, `description`, `image`, `show_buttons`, `sort_order`, `is_active`, `created_at`) VALUES
('ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี', 'Uttaradit Rajabhat University', 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น', 'assets/images/hero_background.png', 1, 1, 1, NOW()),
('งานวิจัยและนวัตกรรม', 'Research & Innovation', 'พัฒนางานวิจัยเพื่อตอบโจทย์ชุมชนและท้องถิ่น ส่งเสริมการสร้างนวัตกรรมที่มีคุณค่า', 'assets/images/research_laboratory.png', 0, 2, 1, NOW()),
('กิจกรรมนักศึกษา', 'Student Activities', 'ร่วมสร้างประสบการณ์การเรียนรู้นอกห้องเรียน พัฒนาทักษะและความสามารถรอบด้าน', 'assets/images/student_activities.png', 0, 3, 1, NOW());

-- =====================================================
-- SITE SETTINGS
-- =====================================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('site_name_th', 'คณะวิทยาศาสตร์และเทคโนโลยี', 'text', 'general'),
('site_name_en', 'Faculty of Science and Technology', 'text', 'general'),
('university_name_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text', 'general'),
('university_name_en', 'Uttaradit Rajabhat University', 'text', 'general'),
('phone', '055-411096 ต่อ 1600', 'text', 'contact'),
('fax', '055-411096 ต่อ 1700', 'text', 'contact'),
('email', 'sci@uru.ac.th', 'text', 'contact'),
('address_th', 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ 27 ถ.อินใจมี ต.ท่าอิฐ อ.เมือง จ.อุตรดิตถ์ 53000', 'textarea', 'contact'),
('address_en', 'Faculty of Science and Technology, Uttaradit Rajabhat University, 27 Injaimee Rd., Tha-It, Muang, Uttaradit 53000, Thailand', 'textarea', 'contact'),
('facebook', 'https://www.facebook.com/scienceuru', 'text', 'social'),
('website', 'https://sci.uru.ac.th', 'text', 'general'),
('hero_title_th', 'ยินดีต้อนรับสู่คณะวิทยาศาสตร์และเทคโนโลยี', 'text', 'hero'),
('hero_title_en', 'Welcome to Faculty of Science and Technology', 'text', 'hero'),
('hero_subtitle_th', 'มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'text', 'hero'),
('hero_subtitle_en', 'Uttaradit Rajabhat University', 'text', 'hero'),
('hero_description_th', 'สร้างบัณฑิตที่มีความรู้ความสามารถ พัฒนางานวิจัยและนวัตกรรม เพื่อรับใช้ชุมชนและท้องถิ่น', 'textarea', 'hero'),
('hero_description_en', 'Producing knowledgeable graduates, developing research and innovation to serve communities.', 'textarea', 'hero'),
('vision_th', 'เป็นองค์กรชั้นนำด้านวิทยาศาสตร์และเทคโนโลยีเพื่อพัฒนาท้องถิ่น', 'textarea', 'about'),
('vision_en', 'To be a leading organization in science and technology for local development', 'textarea', 'about'),
('mission_th', 'ผลิตบัณฑิตที่มีคุณภาพ สร้างงานวิจัยและนวัตกรรม บริการวิชาการ และทำนุบำรุงศิลปวัฒนธรรม', 'textarea', 'about'),
('mission_en', 'Produce quality graduates, create research and innovation, provide academic services, and preserve arts and culture', 'textarea', 'about');

-- =====================================================
-- DEPARTMENTS
-- =====================================================
INSERT INTO `departments` (`id`, `name_th`, `name_en`, `code`, `sort_order`, `status`) VALUES
(1, 'สำนักงานคณบดี', 'Dean Office', 'DEAN', 1, 'active'),
(2, 'สาขาวิชาคณิตศาสตร์ประยุกต์', 'Applied Mathematics', 'MATH', 2, 'active'),
(3, 'สาขาวิชาชีววิทยา', 'Biology', 'BIO', 3, 'active'),
(4, 'สาขาวิชาเคมี', 'Chemistry', 'CHEM', 4, 'active'),
(5, 'สาขาวิชาเทคโนโลยีสารสนเทศ', 'Information Technology', 'IT', 5, 'active'),
(6, 'สาขาวิชาวิทยาการคอมพิวเตอร์', 'Computer Science', 'CS', 6, 'active'),
(7, 'สาขาวิชาวิทยาการข้อมูล', 'Data Science', 'DS', 7, 'active'),
(8, 'สาขาวิชาวิทยาศาสตร์การกีฬาและการออกกำลังกาย', 'Sports and Exercise Science', 'SPORT', 8, 'active'),
(9, 'สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม', 'Environmental Science', 'ENV', 9, 'active'),
(10, 'สาขาวิชาสาธารณสุขศาสตร์', 'Public Health', 'PH', 10, 'active'),
(11, 'สาขาวิชาอาหารและโภชนาการ', 'Food and Nutrition', 'FOOD', 11, 'active');

-- =====================================================
-- PROGRAMS
-- =====================================================
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
('วิทยาศาสตร์ประยุกต์', 'Applied Science', 'ปรัชญาดุษฎีบัณฑิต', 'Doctor of Philosophy', 'doctorate', NULL, '3 ปี', 12, 'active');

-- =====================================================
-- DEFAULT ADMIN USER
-- =====================================================
INSERT IGNORE INTO `user` (`email`, `password`, `gf_name`, `gl_name`, `tf_name`, `tl_name`, `role`, `status`) VALUES
('admin@sci.uru.ac.th', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'ผู้ดูแล', 'ระบบ', 'admin', 'active');

-- =====================================================
-- DONE
-- =====================================================
SELECT 'Data inserted successfully!' AS status;
