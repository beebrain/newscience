-- Add file_type to news_images to support both images and documents per news.
-- Run only if your news_images table does not have file_type yet:
--   mysql -u root -p newscience < database/add_news_images_file_type.sql

ALTER TABLE `news_images`
ADD COLUMN `file_type` VARCHAR(20) NOT NULL DEFAULT 'image'
COMMENT 'image or document'
AFTER `sort_order`;
