-- Migration: Normalize Organization Structure
-- Date: 2026-02-01
-- Purpose:
--   1. Add is_primary flag to personnel_programs
--   2. Remove deprecated fields
--   3. Use personnel_programs as Single Source of Truth for coordinator

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Step 1: Add is_primary column to personnel_programs
-- ============================================
-- Check if column exists before adding
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'personnel_programs'
    AND COLUMN_NAME = 'is_primary'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE personnel_programs ADD COLUMN is_primary TINYINT(1) DEFAULT 0 AFTER role_in_curriculum',
    'SELECT "is_primary column already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Step 2: Migrate existing data
-- Set is_primary based on personnel.program_id
-- ============================================
UPDATE personnel_programs pp
INNER JOIN personnel p ON pp.personnel_id = p.id AND pp.program_id = p.program_id
SET pp.is_primary = 1
WHERE pp.is_primary = 0;

-- ============================================
-- Step 3: Add index for performance
-- ============================================
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'personnel_programs'
    AND INDEX_NAME = 'idx_pp_primary'
);

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_pp_primary ON personnel_programs(personnel_id, is_primary)',
    'SELECT "idx_pp_primary index already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Step 4: Drop deprecated column (position_detail)
-- ============================================
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'personnel'
    AND COLUMN_NAME = 'position_detail'
);

SET @sql = IF(@col_exists > 0,
    'ALTER TABLE personnel DROP COLUMN position_detail',
    'SELECT "position_detail column does not exist"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Step 5: Set coordinator_id to NULL (deprecate)
-- We will query from personnel_programs instead
-- ============================================
-- Note: Keep the column for now but clear the data
-- UPDATE programs SET coordinator_id = NULL;

-- ============================================
-- Step 6: Ensure at least one primary program per personnel
-- For personnel with programs but no is_primary set, set the first one
-- ============================================
UPDATE personnel_programs pp1
INNER JOIN (
    SELECT personnel_id, MIN(id) as first_id
    FROM personnel_programs
    GROUP BY personnel_id
    HAVING SUM(is_primary) = 0
) pp2 ON pp1.id = pp2.first_id
SET pp1.is_primary = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Verification queries (run manually)
-- ============================================
-- Check is_primary distribution:
-- SELECT
--     COUNT(*) as total,
--     SUM(is_primary) as primary_count
-- FROM personnel_programs;

-- Check personnel with multiple programs:
-- SELECT
--     p.id,
--     CONCAT(p.first_name, ' ', p.last_name) as name,
--     COUNT(pp.id) as program_count,
--     SUM(pp.is_primary) as primary_count
-- FROM personnel p
-- LEFT JOIN personnel_programs pp ON p.id = pp.personnel_id
-- GROUP BY p.id
-- HAVING program_count > 1;
