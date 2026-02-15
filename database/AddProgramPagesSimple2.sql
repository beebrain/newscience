-- Simple migration for curriculum website system
-- Run: mysql -u root newscience < database/AddProgramPagesSimple2.sql

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
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_program_id (program_id),
    INDEX idx_slug (slug),
    INDEX idx_is_published (is_published)
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
    INDEX idx_program_id (program_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
