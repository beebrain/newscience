-- Sample published news for homepage sections
INSERT IGNORE INTO `news` (`id`, `title`, `slug`, `content`, `excerpt`, `status`, `author_id`, `published_at`) VALUES
(1, 'เปิดรับสมัครนักศึกษาใหม่ 2569', 'open-admission-2569', '<p>เนื้อหาข่าวทดสอบ</p>', 'คณะวิทยาศาสตร์และเทคโนโลยี เปิดรับสมัครนักศึกษาใหม่', 'published', 1, NOW()),
(2, 'ประกาศปฏิทินกิจกรรมคณะ', 'faculty-calendar', '<p>เนื้อหาข่าวทดสอบ</p>', 'ติดตามกิจกรรมสำคัญของคณะได้ที่นี่', 'published', 1, NOW()),
(3, 'นิสิตคว้ารางวัลนวัตกรรม', 'student-innovation-award', '<p>เนื้อหาข่าวทดสอบ</p>', 'ขอแสดงความยินดีกับนิสิตที่ได้รับรางวัล', 'published', 1, NOW()),
(4, 'ทุนวิจัยสำหรับอาจารย์และนิสิต', 'research-grant-2569', '<p>เนื้อหาข่าวทดสอบ</p>', 'เปิดรับข้อเสนอโครงการวิจัย', 'published', 1, NOW()),
(5, 'ผลงานวิจัยตีพิมพ์ระดับนานาชาติ', 'research-publication', '<p>เนื้อหาข่าวทดสอบ</p>', 'คณาจารย์ตีพิมพ์ผลงานในวารสารนานาชาติ', 'published', 1, NOW()),
(6, 'กิจกรรมชมรมนักศึกษาเปิดภาคเรียน', 'student-club-opening', '<p>เนื้อหาข่าวทดสอบ</p>', 'ชมรมต่างๆ เปิดรับสมาชิกใหม่', 'published', 1, NOW());

INSERT IGNORE INTO `news_news_tags` (`news_id`, `news_tag_id`) VALUES
(1, 1), (2, 1),
(3, 2),
(4, 3), (5, 4),
(6, 2);
