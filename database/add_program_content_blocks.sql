-- Migration: Add program_content_blocks table for Program Website Builder
-- Date: 2026-02-16

CREATE TABLE IF NOT EXISTS program_content_blocks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    block_key VARCHAR(100) NOT NULL,
    block_type ENUM('html', 'css', 'js', 'wysiwyg', 'markdown') DEFAULT 'wysiwyg',
    title VARCHAR(255),
    content LONGTEXT,
    custom_css TEXT,
    custom_js TEXT,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block_key (program_id, block_key),
    INDEX idx_program_id (program_id),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment explaining the table
ALTER TABLE program_content_blocks 
COMMENT = 'Stores custom content blocks for each program website';
