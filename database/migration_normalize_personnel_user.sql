-- =============================================================================
-- Migration: Normalize personnel and user tables
-- Date: 2026-02-02
-- Description: 
--   - Use email as the key link between personnel and user
--   - Remove duplicate fields from personnel (name, name_en, email, image)
--   - Personnel will reference user data for personal information
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Step 1: Ensure user_uid is populated for all personnel with matching email
-- This links personnel to user table using email
UPDATE `personnel` p
INNER JOIN `user` u ON LOWER(TRIM(p.email)) = LOWER(TRIM(u.email))
SET p.user_uid = u.uid
WHERE p.user_uid IS NULL AND p.email IS NOT NULL AND p.email != '';

-- Step 2: For personnel without user records, create user records from personnel data
-- This ensures no data is lost
INSERT INTO `user` (`email`, `title`, `tf_name`, `tl_name`, `gf_name`, `gl_name`, `profile_image`, `role`, `status`)
SELECT 
    p.email,
    -- Extract title from name (common Thai titles)
    CASE 
        WHEN p.name LIKE 'ศ.ดร.%' THEN 'ศ.ดร.'
        WHEN p.name LIKE 'รศ.ดร.%' THEN 'รศ.ดร.'
        WHEN p.name LIKE 'ผศ.ดร.%' THEN 'ผศ.ดร.'
        WHEN p.name LIKE 'อ.ดร.%' THEN 'อ.ดร.'
        WHEN p.name LIKE 'ดร.%' THEN 'ดร.'
        WHEN p.name LIKE 'ศ.%' THEN 'ศ.'
        WHEN p.name LIKE 'รศ.%' THEN 'รศ.'
        WHEN p.name LIKE 'ผศ.%' THEN 'ผศ.'
        WHEN p.name LIKE 'อ.%' THEN 'อ.'
        WHEN p.name LIKE 'นาย%' THEN 'นาย'
        WHEN p.name LIKE 'นาง%' AND p.name NOT LIKE 'นางสาว%' THEN 'นาง'
        WHEN p.name LIKE 'นางสาว%' THEN 'นางสาว'
        ELSE NULL
    END as title,
    -- Thai first name (name without title, split by space - first part)
    TRIM(SUBSTRING_INDEX(
        TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
            p.name, 
            'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
            'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
            'นางสาว', ''), 'นาง', ''), 'นาย', '')),
        ' ', 1)) as tf_name,
    -- Thai last name (remaining after first name)
    TRIM(SUBSTRING(
        TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
            p.name, 
            'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
            'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
            'นางสาว', ''), 'นาง', ''), 'นาย', '')),
        LOCATE(' ', TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
            p.name, 
            'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
            'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
            'นางสาว', ''), 'นาง', ''), 'นาย', ''))) + 1
    )) as tl_name,
    -- English first name
    CASE 
        WHEN p.name_en IS NOT NULL AND p.name_en != '' 
        THEN TRIM(SUBSTRING_INDEX(p.name_en, ' ', 1))
        ELSE NULL
    END as gf_name,
    -- English last name
    CASE 
        WHEN p.name_en IS NOT NULL AND p.name_en != '' AND LOCATE(' ', p.name_en) > 0
        THEN TRIM(SUBSTRING(p.name_en, LOCATE(' ', p.name_en) + 1))
        ELSE NULL
    END as gl_name,
    p.image as profile_image,
    'user' as role,
    p.status
FROM `personnel` p
LEFT JOIN `user` u ON LOWER(TRIM(p.email)) = LOWER(TRIM(u.email))
WHERE u.uid IS NULL 
  AND p.email IS NOT NULL 
  AND p.email != ''
  AND p.email NOT IN (SELECT email FROM `user`);

-- Step 3: Update user_uid for newly created users
UPDATE `personnel` p
INNER JOIN `user` u ON LOWER(TRIM(p.email)) = LOWER(TRIM(u.email))
SET p.user_uid = u.uid
WHERE p.user_uid IS NULL;

-- Step 4: For users that have personnel records, update profile_image if not set
UPDATE `user` u
INNER JOIN `personnel` p ON u.uid = p.user_uid
SET u.profile_image = p.image
WHERE (u.profile_image IS NULL OR u.profile_image = '') 
  AND p.image IS NOT NULL 
  AND p.image != '';

-- Step 5: Update user names from personnel if user names are empty
UPDATE `user` u
INNER JOIN `personnel` p ON u.uid = p.user_uid
SET 
    u.tf_name = COALESCE(NULLIF(u.tf_name, ''), 
        TRIM(SUBSTRING_INDEX(
            TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                p.name, 
                'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
                'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
                'นางสาว', ''), 'นาง', ''), 'นาย', '')),
            ' ', 1))),
    u.tl_name = COALESCE(NULLIF(u.tl_name, ''),
        TRIM(SUBSTRING(
            TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                p.name, 
                'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
                'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
                'นางสาว', ''), 'นาง', ''), 'นาย', '')),
            LOCATE(' ', TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                p.name, 
                'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
                'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
                'นางสาว', ''), 'นาง', ''), 'นาย', ''))) + 1
        )))
WHERE (u.tf_name IS NULL OR u.tf_name = '') AND p.name IS NOT NULL AND p.name != '';

-- =============================================================================
-- IMPORTANT: Run migration steps above first, verify data, then run steps below
-- =============================================================================

-- Step 6: Drop redundant columns from personnel table
-- CAUTION: Only run after verifying data migration is successful!
-- Uncomment these lines when ready:

-- ALTER TABLE `personnel` DROP COLUMN `name`;
-- ALTER TABLE `personnel` DROP COLUMN `name_en`;
-- ALTER TABLE `personnel` DROP COLUMN `email`;
-- ALTER TABLE `personnel` DROP COLUMN `image`;

-- Step 7: Add foreign key constraint for user_uid
-- ALTER TABLE `personnel` 
--     ADD CONSTRAINT `fk_personnel_user` 
--     FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) 
--     ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Verification Queries (Run these to verify data before dropping columns)
-- =============================================================================

-- Check personnel with user links
-- SELECT p.id, p.name, p.email, p.user_uid, u.uid, u.email as user_email, 
--        CONCAT(u.title, ' ', u.tf_name, ' ', u.tl_name) as user_full_name
-- FROM personnel p
-- LEFT JOIN user u ON p.user_uid = u.uid
-- ORDER BY p.id;

-- Check for personnel without user links
-- SELECT p.id, p.name, p.email, p.user_uid
-- FROM personnel p
-- WHERE p.user_uid IS NULL;
