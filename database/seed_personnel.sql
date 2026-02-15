-- =====================================================
-- Clear personnel table and insert sample data
-- Run: mysql -u root -p newscience < database/seed_personnel.sql
-- Or: php scripts/seed_personnel.php
-- =====================================================

SET NAMES utf8mb4;

-- Clear personnel (do not truncate if other tables reference personnel)
DELETE FROM `personnel`;

-- Reset auto increment (optional)
ALTER TABLE `personnel` AUTO_INCREMENT = 1;

-- =====================================================
-- PERSONNEL (โครงสร้างองค์กร คณะวิทยาศาสตร์และเทคโนโลยี)
-- department_id 1 = สำนักงานคณบดี
-- =====================================================

-- คณบดี (Tier 1)
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ปริญญา ไกรวุฒินันท์', 'Parinya Kraiwoodinan', 'คณบดี', 'Dean', 1, 1, 'active');

-- รองคณบดี (Tier 2)
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ จุฬาลักษณ์ มหาวัน', 'Julalak Mahawan', 'รองคณบดี', 'Associate Dean', 1, 2, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. วารุณี จอมกิติชัย', 'Warunee Chomkitiyachai', 'รองคณบดี', 'Associate Dean', 1, 3, 'active'),
('อาจารย์ ดร. วีระศักดิ์ แก้วทรัพย์', 'Weerasak Kaewsup', 'รองคณบดี', 'Associate Dean', 1, 4, 'active');

-- ผู้ช่วยคณบดี (Tier 3)
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. พิศิษฐ์ นาคใจ', 'Phisit Nakchai', 'ผู้ช่วยคณบดี', 'Assistant Dean', 1, 5, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ธนากร ธนวัฒน์', 'Tanakorn Thanawat', 'ผู้ช่วยคณบดี', 'Assistant Dean', 1, 6, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. สัมพวัน วิริยะรัตนกุล', 'Sampawan Wiriyaratankul', 'ผู้ช่วยคณบดี', 'Assistant Dean', 1, 7, 'active');

-- หัวหน้าหน่วยจัดการงานวิจัย (Tier 4)
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. สุทธิดา วิทนาลัย', 'Sutthida Witnalai', 'หัวหน้าหน่วยจัดการงานวิจัย', 'Head of Research Management Unit', 1, 8, 'active');

-- =====================================================
-- คณิตศาสตร์ (สาขาวิชาคณิตศาสตร์ประยุกต์, department_id 2)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('อาจารย์ วิไลวรรณ รัตนกูล', 'Wilaiwan Rattanakul', 'ประธานหลักสูตร', 'Head of Curriculum', 2, 10, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ธัชชัย อยู่ยิ่ง', 'Thatchai Yooyung', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 2, 11, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ดิเรก บัวหลวง', 'Direk Bualuang', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 2, 12, 'active'),
('ผู้ช่วยศาสตราจารย์ ยุทธชัย มิ่งขวัญ', 'Yutthachai Mingkwan', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 2, 13, 'active'),
('อาจารย์ ดร. นราวดี นวลสอาด', 'Narawadee Nuansard', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 2, 14, 'active');

-- อาจารย์ประจำหลักสูตร คณิตศาสตร์
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('รองศาสตราจารย์ ดร. สุภาวิณี สัตยาภรณ์', 'Supawinee Sathyaporn', 'อาจารย์', 'Lecturer', 2, 15, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. พัชรี มณีรัตน์', 'Patcharee Maneerat', 'อาจารย์', 'Lecturer', 2, 16, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. นภาภรณ์ จันทร์สี', 'Napaporn Chansri', 'อาจารย์', 'Lecturer', 2, 17, 'active'),
('รองศาสตราจารย์ ดร. อิสระ อินจันทร์', 'Issara Inchan', 'อาจารย์', 'Lecturer', 2, 18, 'active'),
('ผู้ช่วยศาสตราจารย์ วรินสินี จันทะคุณ', 'Worrinsini Chanthakoon', 'อาจารย์', 'Lecturer', 2, 19, 'active');

-- =====================================================
-- เคมี (สาขาวิชาเคมี, department_id 4)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. อัมพวัน วิริยะรัตนกุล', 'Ampawan Wiriyaratankul', 'ประธานหลักสูตร', 'Head of Curriculum', 4, 20, 'active'),
('รองศาสตราจารย์ ดร. พงศ์เทพ จันทร์สันเทียะ', 'Pongthep Chansantia', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 4, 21, 'active'),
('อาจารย์ ดร. วชิราภรณ์ เขียวมั่ง', 'Wachiraporn Kheawmung', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 4, 22, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ณัฐกฤตา บุณณ์ประกอบ', 'Nattakrita Bunnaprakob', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 4, 23, 'active'),
('อาจารย์ ดร. ธนิต เมธีนุกูล', 'Thanit Methinukul', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 4, 24, 'active');

-- อาจารย์ประจำหลักสูตร เคมี
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('อาจารย์ ดร. พันธุ์ทิพย์ ถือเงิน', 'Puntip Thuean', 'อาจารย์', 'Lecturer', 4, 25, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. จิราพร เกตุวราภรณ์', 'Jiraporn Ketwaraporn', 'อาจารย์', 'Lecturer', 4, 26, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. วีรศักดิ์ จอมกิติชัย', 'Weerasak Chomkitiyachai', 'อาจารย์', 'Lecturer', 4, 27, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. พรทิพพา พิญญาพงษ์', 'Porntippa Pinyapong', 'อาจารย์', 'Lecturer', 4, 28, 'active');

-- =====================================================
-- ชีววิทยา (สาขาวิชาชีววิทยา, department_id 3)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. จิราภรณ์ นิคมทัศน์', 'Jiraporn Nikhomthat', 'ประธานหลักสูตร', 'Head of Curriculum', 3, 29, 'active'),
('อาจารย์ พัทธชัย ปิ่นนาค', 'Pattachai Pinnak', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 3, 30, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. วารุณี จอมกิติชัย', 'Warunee Chomkitiyachai', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 3, 31, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. วันวิสาข์ พิระภาค', 'Wanwisak Pirak', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 3, 32, 'active'),
('รองศาสตราจารย์ ดร. สิริวดี พรหมน้อย', 'Siriwadee Phromnoi', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 3, 33, 'active');

-- อาจารย์ประจำหลักสูตร ชีววิทยา
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. กชกร ลาภมาก', 'Kotchakorn Lapmak', 'อาจารย์', 'Lecturer', 3, 34, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. กิตติ เมืองตุ้ม', 'Kitti Mueangtum', 'อาจารย์', 'Lecturer', 3, 35, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. สุทธิดา วิทนาลัย', 'Sutthida Witnalai', 'อาจารย์', 'Lecturer', 3, 36, 'active');

-- =====================================================
-- เทคโนโลยีสารสนเทศ (สาขาวิชาเทคโนโลยีสารสนเทศ, department_id 5)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('อาจารย์ พิชิต พวงภาคีศิริ', 'Phichit Puangphakisi', 'ประธานหลักสูตร', 'Head of Curriculum', 5, 37, 'active'),
('ผู้ช่วยศาสตราจารย์ จุฬาลักษณ์ มหาวัน', 'Julalak Mahawan', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 5, 38, 'active'),
('อาจารย์ ดร. กนกวรรณ กันยะมี', 'Kanokwan Kanyami', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 5, 39, 'active'),
('ผู้ช่วยศาสตราจารย์ ชนิดา เรืองศิริวัฒนกุล', 'Chanida Ruangsiriwatthana', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 5, 40, 'active'),
('อาจารย์ นารีวรรณ พวงภาคีศิริ', 'Nareewan Puangphakisi', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 5, 41, 'active');

-- อาจารย์ประจำหลักสูตร เทคโนโลยีสารสนเทศ
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('อาจารย์ จำรูญ จันทร์กุญชร', 'Jamroon Chankunchon', 'อาจารย์', 'Lecturer', 5, 42, 'active'),
('อาจารย์ มานิตย์ พ่วงบางโพ', 'Manit Puungbangpho', 'อาจารย์', 'Lecturer', 5, 43, 'active');

-- =====================================================
-- ฟิสิกส์ (สาขาวิชาฟิสิกส์, department_id 12)
-- =====================================================
INSERT IGNORE INTO `departments` (`id`, `name_th`, `name_en`, `code`, `sort_order`, `status`) VALUES
(12, 'สาขาวิชาฟิสิกส์', 'Physics', 'PHYS', 12, 'active');

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ธันยบูรณ์ ถาวรวรรณ์', 'Thanyaboon Thaworan', 'ประธานหลักสูตร', 'Head of Curriculum', 12, 44, 'active'),
('รองศาสตราจารย์ ดร. สิงหเดช แตงจวง', 'Singhadech Taengchuang', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 12, 45, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. เอมอร วันเอก', 'Aem-on Wanek', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 12, 46, 'active'),
('อาจารย์ กนกวรรณ มารักษ์', 'Kanokwan Marak', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 12, 47, 'active'),
('อาจารย์ วิศิษฎ์ มหานิล', 'Wisit Mahanil', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 12, 48, 'active');

-- =====================================================
-- วิทยาการข้อมูล (สาขาวิชาวิทยาการข้อมูล, department_id 7)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. พีระพล ขุนอาสา', 'Peerapol Khunasa', 'ประธานหลักสูตร', 'Head of Curriculum', 7, 49, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. คเชนทร์ ซ่อนกลิ่น', 'Kachen Sornklin', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 7, 50, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ชาณิภา ซ่อนกลิ่น', 'Chanipa Sornklin', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 7, 51, 'active');

-- อาจารย์ประจำหลักสูตร วิทยาการข้อมูล
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('รองศาสตราจารย์ ดร. อิสระ อินจันทร์', 'Issara Inchan', 'อาจารย์', 'Lecturer', 7, 52, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. พัชรี มณีรัตน์', 'Patcharee Maneerat', 'อาจารย์', 'Lecturer', 7, 53, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. พิศิษฐ์ นาคใจ', 'Phisit Nakchai', 'อาจารย์', 'Lecturer', 7, 54, 'active');

-- =====================================================
-- วิทยาการคอมพิวเตอร์ (สาขาวิชาวิทยาการคอมพิวเตอร์, department_id 6)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. โสภณ วิริยะรัตนกุล', 'Sopon Wiriyaratankul', 'ประธานหลักสูตร', 'Head of Curriculum', 6, 55, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ราตรี คำโมง', 'Ratree Khamong', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 6, 56, 'active'),
('ผู้ช่วยศาสตราจารย์ สมคิด ทุ่นใจ', 'Somkit Thunchai', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 6, 57, 'active'),
('อาจารย์ กฤษณ์ ชัยวัณณคุปต์', 'Krit Chaiwannakup', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 6, 58, 'active'),
('อาจารย์ อนุชา เรืองศิริวัฒนกุล', 'Anucha Ruangsiriwatthana', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 6, 59, 'active');

-- อาจารย์ประจำหลักสูตร วิทยาการคอมพิวเตอร์
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('อาจารย์ พรเทพ จันทร์เพ็ง', 'Pornthep Chanpheng', 'อาจารย์', 'Lecturer', 6, 60, 'active'),
('อาจารย์ ชุมพล แพร่น่าน', 'Chumphon Phaenan', 'อาจารย์', 'Lecturer', 6, 61, 'active'),
('อาจารย์ สุรพล ชุ่มกลิ่น', 'Surapol Chumklin', 'อาจารย์', 'Lecturer', 6, 62, 'active');

-- =====================================================
-- วิทยาศาสตร์การกีฬาและการออกกำลังกาย (department_id 8)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. ภาคภูมิ โชคทวีพาณิชย์', 'Pakpoom Chokthaweepanich', 'ประธานหลักสูตร', 'Head of Curriculum', 8, 63, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. กิตติ์ คุณกิตติ', 'Krit Khunkitti', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 8, 64, 'active'),
('อาจารย์ ดร. วีระศักดิ์ แก้วทรัพย์', 'Weerasak Kaewsup', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 8, 65, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. วรวุฒิ ธุวะคำ', 'Worawut Thuawakham', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 8, 66, 'active'),
('อาจารย์ ชุติมา เมืองด่าน', 'Chutima Mueangdan', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 8, 67, 'active');

-- อาจารย์ประจำหลักสูตร วิทยาศาสตร์การกีฬา
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. เสรี แสงอุทัย', 'Seree Saeng-uthai', 'อาจารย์', 'Lecturer', 8, 68, 'active');

-- =====================================================
-- วิทยาศาสตร์สิ่งแวดล้อม (สาขาวิชาวิทยาศาสตร์สิ่งแวดล้อม, department_id 9)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ ดร. จันทร์เพ็ญ ชุมแสง', 'Chanphen Chumsaeng', 'ประธานหลักสูตร', 'Head of Curriculum', 9, 69, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. กฤษณะ คำฟอง', 'Krisana Khamfong', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 9, 70, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. สุภาพร พงศ์ธรพฤกษ์', 'Supaporn Pongthornpueak', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 9, 71, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ชาติทนง โพธิ์ดง', 'Chatthanong Phodong', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 9, 72, 'active'),
('ผู้ช่วยศาสตราจารย์ ปริญญา ไกรวุฒินันท์', 'Parinya Kraiwoodinan', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 9, 73, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. พุทธดี อุบลศุข', 'Phutthadee Ubonsuk', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 9, 74, 'active');

-- =====================================================
-- สาธารณสุขศาสตร์ (สาขาวิชาสาธารณสุขศาสตร์, department_id 10)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('ผู้ช่วยศาสตราจารย์ จงรัก ดวงทอง', 'Jongrak Duangthong', 'ประธานหลักสูตร', 'Head of Curriculum', 10, 75, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. พงษ์ศักดิ์ อ้นมอย', 'Pongsak Onmoi', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 10, 76, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. สุนีย์ กันแจ่ม', 'Sunee Kanjaem', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 10, 77, 'active'),
('รองศาสตราจารย์ ดร. ณิชารีย์ ใจคำวัง', 'Nicharee Jaikhamwang', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 10, 78, 'active'),
('ผู้ช่วยศาสตราจารย์ ศศิธร สุขจิตต์', 'Sasithorn Sukjit', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 10, 79, 'active');

-- อาจารย์ประจำหลักสูตร สาธารณสุขศาสตร์
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('รองศาสตราจารย์ ดร. จักรกฤษณ์ พิญญาพงษ์', 'Jakkrit Pinyapong', 'อาจารย์', 'Lecturer', 10, 80, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ศรัณยู เรือนจันทร์', 'Saranu Rueanchan', 'อาจารย์', 'Lecturer', 10, 81, 'active'),
('ผู้ช่วยศาสตราจารย์ เผด็จการ กันแจ่ม', 'Phetchakarn Kanjaem', 'อาจารย์', 'Lecturer', 10, 82, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ธนากร ธนวัฒน์', 'Tanakorn Thanawat', 'อาจารย์', 'Lecturer', 10, 83, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. กิตติวรรณ จันทร์ฤทธิ์', 'Kittiwan Chanrit', 'อาจารย์', 'Lecturer', 10, 84, 'active');

-- =====================================================
-- อาหารและโภชนาการ (สาขาวิชาอาหารและโภชนาการ, department_id 11)
-- =====================================================

-- อาจารย์ผู้รับผิดชอบหลักสูตร
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('อาจารย์ กานต์ธิดา ไชยมา', 'Kantida Chaima', 'ประธานหลักสูตร', 'Head of Curriculum', 11, 85, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. รสสุคนธ์ วงษ์ดอกไม้', 'Rasakorn Wongdokmai', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 11, 86, 'active'),
('ผู้ช่วยศาสตราจารย์ ฐิติพร เทียรฆนิธิกูล', 'Thitiporn Tiannithikun', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 11, 87, 'active'),
('ผู้ช่วยศาสตราจารย์ ดร. ชื่นกมล ปัญญายง', 'Chuenkamol Panyayong', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 11, 88, 'active'),
('ผู้ช่วยศาสตราจารย์ สุทธิพันธุ์ แดงใจ', 'Sutthiphan Daengjai', 'กรรมการหลักสูตร', 'Curriculum Committee Member', 11, 89, 'active');

-- =====================================================
-- สายสนับสนุน - สำนักงานคณบดี (department_id 1)
-- =====================================================

INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('นาง พจนีย์ อินจันทร์', 'Pojanee Inchan', 'เจ้าหน้าที่บริหารงานทั่วไป ชำนาญการ', 'General Administration Officer (Expert)', 1, 90, 'active'),
('นางสาว จินตนา แตงฤทธิ์', 'Jintana Taengrit', 'นักวิชาการศึกษา', 'Education Officer', 1, 91, 'active'),
('นางสาว โสภิณภา นันตา', 'Sophipha Nanta', 'นักวิชาการศึกษา', 'Education Officer', 1, 92, 'active'),
('นาง อิศรา ทั่งรอด', 'Itsara Thangrod', 'นักวิชาการศึกษา', 'Education Officer', 1, 93, 'active'),
('นาง ปิยะดา เทพสาธร', 'Piyada Thepsathon', 'บรรณารักษ์', 'Librarian', 1, 94, 'active'),
('นางสาว อาภาวรรณ พรหมมา', 'Aphawan Phromma', 'นักจัดการงานทั่วไป', 'General Management Officer', 1, 95, 'active'),
('นางสาว ชนันทกร วงค์ตะวัน', 'Chananatkon Wongtawan', 'นักจัดการงานทั่วไป', 'General Management Officer', 1, 96, 'active'),
('นาง ปัทมา จันทร์แย้ม', 'Patma Chanyaem', 'ผู้ปฏิบัติงานบริหาร', 'Administrative Staff', 1, 97, 'active'),
('นางสาว ญาณภา บุญอยู่', 'Yanapa Boonyu', 'ผู้ปฏิบัติงานบริหาร', 'Administrative Staff', 1, 98, 'active');

-- สายสนับสนุน - หลักสูตร/สาขาวิชา (ตามหน่วยงานที่สังกัด)
INSERT INTO `personnel` (`name`, `name_en`, `position`, `position_en`, `department_id`, `sort_order`, `status`) VALUES
('นาย ไพทูล รวดพวง', 'Phaitoon Ruadpuang', 'นักวิชาการโสตทัศนศึกษา หลักสูตรเทคโนโลยีสารสนเทศ', 'Audiovisual Officer - IT Program', 5, 99, 'active'),
('นางสาว อุทัยวรรณ ชั่งอ่อง', 'Uthaiwan Changong', 'นักจัดการงานทั่วไป หลักสูตรสาธารณสุขศาสตร์', 'General Management Officer - Public Health', 10, 100, 'active'),
('นาย อุเทน แสนบัณฑิต', 'Uthen Saenbandit', 'ช่างเครื่องคอมพิวเตอร์ หลักสูตรวิทยาการคอมพิวเตอร์', 'Computer Technician - Computer Science', 6, 101, 'active'),
('นาย นพดล บุณยรัตพันธุ์', 'Noppadon Bunyaratphan', 'ผู้ปฏิบัติงานวิทยาศาสตร์ หลักสูตรเคมี', 'Science Staff - Chemistry', 4, 102, 'active'),
('นาย เชาวฤทธิ์ วันเสาร์', 'Chaowarit Wansao', 'ผู้ปฏิบัติงานวิทยาศาสตร์ สาขาฟิสิกส์', 'Science Staff - Physics', 12, 103, 'active'),
('นางสาว สุกัญญา หมอนอิง', 'Sukanya Monning', 'ผู้ปฏิบัติงานวิทยาศาสตร์ หลักสูตรชีววิทยา', 'Science Staff - Biology', 3, 104, 'active'),
('นางสาว พรพรรณ ขจิตรัตน์', 'Pornpan Khajirat', 'ผู้ปฏิบัติงานบริหาร หลักสูตรสิ่งแวดล้อม', 'Administrative Staff - Environmental Science', 9, 105, 'active'),
('นาง จันทร์แรม มั่นคง', 'Chanraem Mankong', 'ผู้ปฏิบัติงานบริหาร หลักสูตรอาหารและโภชนาการ', 'Administrative Staff - Food and Nutrition', 11, 106, 'active'),
('นาย คณาธิป สอดจันทร์', 'Kanathip Sodchan', 'ผู้ปฏิบัติงานบริหาร หลักสูตรวิทยาศาสตร์การกีฬา', 'Administrative Staff - Sports Science', 8, 107, 'active'),
('นาย เหลือ น้อยพา', 'Luea Noipa', 'พนักงานทั่วไป หลักสูตรวิทยาศาสตร์การกีฬา', 'General Staff - Sports Science', 8, 108, 'active');

-- =====================================================
-- DONE
-- =====================================================
SELECT CONCAT('Personnel: ', COUNT(*), ' rows') AS status FROM `personnel`;
