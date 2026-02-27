-- =============================================================================
-- สคริปต์ให้อาจารย์ในหลักสูตร (personnel_programs) มีสิทธิ์เข้าจัดการหลักสูตร
-- ใช้ตาราง user_system_access กำหนดสิทธิ์ (system slug = program_admin)
-- รายการหลักสูตรที่จัดการได้ = ระบบดึงจาก personnel_programs ตาม email อยู่แล้ว
-- =============================================================================
-- รัน: mysql -u user -p database_name < database/grant_program_admin_to_personnel.sql
--
-- หมายเหตุ: ถ้า personnel ไม่มีคอลัมน์ user_email ให้ใช้เฉพาะ p.email ในทุก query
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. แสดง Email ที่มีชื่ออยู่ในหลักสูตรทั้งหมด
--    (บุคลากรที่อยู่ใน personnel_programs ไม่ว่าจะบทบาทใด: ประธาน, กรรมการ, อาจารย์ประจำ)
-- -----------------------------------------------------------------------------
SELECT
    COALESCE(NULLIF(TRIM(p.user_email), ''), NULLIF(TRIM(p.email), '')) AS email,
    p.id AS personnel_id,
    p.name AS name_th,
    p.name_en,
    GROUP_CONCAT(DISTINCT pr.name_th ORDER BY pr.name_th SEPARATOR ' | ') AS programs,
    COUNT(DISTINCT pp.program_id) AS program_count
FROM personnel p
INNER JOIN personnel_programs pp ON pp.personnel_id = p.id
INNER JOIN programs pr ON pr.id = pp.program_id
WHERE (
    (p.user_email IS NOT NULL AND TRIM(IFNULL(p.user_email, '')) != '')
    OR (p.email IS NOT NULL AND TRIM(IFNULL(p.email, '')) != '')
)
GROUP BY p.id, 1, p.name, p.name_en
ORDER BY p.name, email;

-- (ตัวเลือก) แสดงเฉพาะรายการ email ไม่ซ้ำ
-- SELECT DISTINCT COALESCE(NULLIF(TRIM(p.user_email), ''), NULLIF(TRIM(p.email), '')) AS email
-- FROM personnel p
-- INNER JOIN personnel_programs pp ON pp.personnel_id = p.id
-- WHERE (p.user_email IS NOT NULL AND TRIM(IFNULL(p.user_email, '')) != '')
--    OR (p.email IS NOT NULL AND TRIM(IFNULL(p.email, '')) != '')
-- ORDER BY email;


-- -----------------------------------------------------------------------------
-- 2. กำหนดสิทธิ์เข้าจัดการหลักสูตร (program_admin) ผ่าน user_system_access
--    เฉพาะ user ที่มี account และ email ตรงกับบุคลากรใน personnel_programs
-- -----------------------------------------------------------------------------
-- 2.1 เพิ่มแถวใน user_system_access (system_id = program_admin, access_level = 'manage')
--     INSERT IGNORE = ถ้ามีแถวเดิม (user_email + system_id ซ้ำ) จะไม่ error
--     ถ้าตารางยังไม่มี UNIQUE(user_email, system_id) แนะนำให้เพิ่ม:
--     ALTER TABLE user_system_access ADD UNIQUE KEY uk_user_system (user_email, system_id);
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO user_system_access (user_email, user_uid, system_id, access_level, granted_by, granted_at)
SELECT DISTINCT
    u.email,
    u.uid,
    (SELECT id FROM systems WHERE slug = 'program_admin' LIMIT 1),
    'manage',
    NULL,
    NOW()
FROM user u
INNER JOIN personnel p ON (
    (p.user_email IS NOT NULL AND TRIM(IFNULL(p.user_email, '')) != '' AND p.user_email = u.email)
    OR (p.email IS NOT NULL AND TRIM(IFNULL(p.email, '')) != '' AND p.email = u.email)
)
INNER JOIN personnel_programs pp ON pp.personnel_id = p.id
WHERE u.email IS NOT NULL AND TRIM(u.email) != '';

-- -----------------------------------------------------------------------------
-- ตรวจสอบผล (optional)
-- -----------------------------------------------------------------------------
-- SELECT usa.user_email, usa.user_uid, s.slug, usa.access_level, usa.granted_at
-- FROM user_system_access usa
-- JOIN systems s ON s.id = usa.system_id
-- WHERE s.slug = 'program_admin'
-- ORDER BY usa.user_email;
