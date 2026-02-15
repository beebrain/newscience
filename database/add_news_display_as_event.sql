-- Add display_as_event to news: 0 = ข่าวประชาสัมพันธ์/กิจกรรมทั่วไป, 1 = ข่าวเกี่ยวกับ Event ที่จะเกิดขึ้น
-- Run this migration so Admin can choose whether news appears in "กิจกรรมที่จะมาถึง" section.

ALTER TABLE `news`
ADD COLUMN `display_as_event` TINYINT(1) NOT NULL DEFAULT 0
COMMENT '1 = แสดงใน section กิจกรรมที่จะมาถึง' AFTER `status`;
