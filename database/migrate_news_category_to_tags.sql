-- รันหลังจาก add_news_tags.sql เมื่อตาราง news มีคอลัมน์ category อยู่แล้ว
-- เพื่อคัดลอก category เดิมเข้า news_news_tags (1 ข่าว = 1 tag จาก category เดิม)
-- Run: mysql -u root -p newscience < database/migrate_news_category_to_tags.sql

INSERT IGNORE INTO news_news_tags (news_id, news_tag_id)
SELECT n.id, t.id
FROM news n
JOIN news_tags t ON t.slug = COALESCE(NULLIF(TRIM(n.category), ''), 'general');
