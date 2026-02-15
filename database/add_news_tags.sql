-- News Tags: 1 ข่าวมีได้หลายชนิดข่าว (หลาย tag)
-- Run: mysql -u root -p newscience < database/add_news_tags.sql

-- ตาราง tag (ชนิดข่าว) เช่น general, student_activity, research_grant
CREATE TABLE IF NOT EXISTS `news_tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'ชื่อแสดง เช่น ข่าวทั่วไป',
    `slug` VARCHAR(100) NOT NULL COMMENT 'ใช้ใน URL/API เช่น general',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง pivot: ข่าว <-> tag (many-to-many)
CREATE TABLE IF NOT EXISTS `news_news_tags` (
    `news_id` INT UNSIGNED NOT NULL,
    `news_tag_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`news_id`, `news_tag_id`),
    KEY `news_tag_id` (`news_tag_id`),
    CONSTRAINT `fk_nnt_news` FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nnt_tag` FOREIGN KEY (`news_tag_id`) REFERENCES `news_tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ใส่ tag เริ่มต้น (ตรงกับ category เดิม)
INSERT IGNORE INTO `news_tags` (`id`, `name`, `slug`, `sort_order`) VALUES
(1, 'ข่าวทั่วไป', 'general', 1),
(2, 'กิจกรรมนักศึกษา', 'student_activity', 2),
(3, 'วิจัย/ทุนวิจัย', 'research_grant', 3);

-- ถ้า news มีคอลัมน์ category อยู่แล้ว: คัดลอก category เดิมเข้า pivot
-- (รันส่วนนี้หลัง ALTER ถ้ามีคอลัมน์ category)
-- INSERT IGNORE INTO news_news_tags (news_id, news_tag_id)
-- SELECT n.id, t.id FROM news n
-- JOIN news_tags t ON t.slug = IFNULL(n.category, 'general')
-- WHERE n.category IS NOT NULL OR 1=1;
