-- เพิ่ม tag ข่าว: งานวิจัย (ใช้ร่วมกับ วิจัย/ทุนวิจัย ได้)
-- Run: mysql -u root -p newscience < database/add_news_tag_research.sql

INSERT IGNORE INTO `news_tags` (`name`, `slug`, `sort_order`) VALUES
('งานวิจัย', 'research', 4);
