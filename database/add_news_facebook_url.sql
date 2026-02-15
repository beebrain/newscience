-- Add facebook_url to news: optional link to Facebook post/page for this news.
-- Run: mysql -u root -p newscience < database/add_news_facebook_url.sql

ALTER TABLE `news`
ADD COLUMN `facebook_url` VARCHAR(500) DEFAULT NULL AFTER `featured_image`;
