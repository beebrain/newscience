-- Migration: Add program pages and downloads tables for curriculum website system
-- Run: mysql -u root newscience < database/AddProgramPages.sql

-- Add slug column to programs table (for URL generation)
ALTER TABLE programs 
ADD COLUMN slug VARCHAR(100) UNIQUE AFTER name_en,
ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER status,
ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Create program_pages table for curriculum content
CREATE TABLE IF NOT EXISTS program_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    philosophy TEXT,
    objectives TEXT,
    graduate_profile TEXT,
    curriculum_structure TEXT,
    study_plan TEXT,
    career_prospects TEXT,
    tuition_fees TEXT,
    admission_info TEXT,
    contact_info TEXT,
    intro_video_url VARCHAR(500),
    gallery_images JSON,
    social_links JSON,
    hero_image VARCHAR(255),
    theme_color VARCHAR(7) DEFAULT '#1e40af',
    meta_description TEXT,
    is_published TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME UPDATE CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create program_downloads table for curriculum files
CREATE TABLE IF NOT EXISTS program_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
ALTER TABLE program_pages ADD INDEX idx_program_id (program_id);
ALTER TABLE program_pages ADD INDEX idx_slug (slug);
ALTER TABLE program_pages ADD INDEX idx_is_published (is_published);
ALTER TABLE program_downloads ADD INDEX idx_program_id (program_id);
ALTER TABLE program_downloads ADD INDEX idx_sort_order (sort_order);

-- Generate slugs for existing programs
UPDATE programs SET slug = CONCAT('program-', id, '-', LOWER(REPLACE(REPLACE(REPLACE(name_th, ' ', '-'), '(', ''), ') ', '')) WHERE slug IS NULL OR slug = '';

-- Create program pages for existing programs
INSERT INTO program_pages (program_id, slug, is_published, created_at, updated_at)
SELECT 
    id, 
    CONCAT('program-', id, '-', LOWER(REPLACE(REPLACE(name_th, ' ', '-'), '(', ''), ') ', '')) as slug,
    0,
    NOW(),
    NOW()
FROM programs 
WHERE id IS NOT NULL;

-- Add created_at and updated_at to programs table if they don't exist
SET @sql = '';
SELECT COUNT(*) INTO @has_created_at FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'programs' AND column_name = 'created_at';
SET @sql = CONCAT('ALTER TABLE programs ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
SET @sql = CONCAT('ALTER TABLE programs ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
PREPARE stmt FROM @sql;
IF @has_created_at = 0 THEN EXECUTE stmt; END IF;

SET @sql = '';
SELECT COUNT(*) INTO @has_updated_at FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'programs' AND column_name = 'updated_at';
SET @sql = CONCAT('ALTER TABLE programs ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
PREPARE stmt FROM @sql;
IF @has_updated_at = 0 THEN EXECUTE stmt; END IF;
