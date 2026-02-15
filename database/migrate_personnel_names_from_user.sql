-- Migration: อัปเดต personnel.name และ personnel.name_en จากตาราง user
-- ใช้เฉพาะแถวที่ personnel.user_uid ถูกตั้งแล้ว
-- ชื่อไทยจาก user: ใช้ th_name + thai_lastname เป็นหลัก; fallback TRIM(CONCAT(title, tf_name, tl_name))
-- ชื่ออังกฤษจาก user: TRIM(CONCAT(COALESCE(gf_name,''), ' ', COALESCE(gl_name,'')))
--
-- แนะนำ: รันผ่าน PHP เพื่อรองรับ user table ที่อาจไม่มีคอลัมน์ครบ
--   php scripts/check_personnel_user_names.php [--diff]
--   php scripts/migrate_personnel_names_from_user.php [--dry-run]
--   php scripts/migrate_personnel_names_from_user.php

-- ตัวอย่าง SQL (ใช้เมื่อ user มีคอลัมน์ title, tf_name, tl_name, gf_name, gl_name ครบ):
/*
UPDATE personnel p
INNER JOIN user u ON u.uid = p.user_uid
SET
  p.name = NULLIF(TRIM(CONCAT(COALESCE(u.title,''), ' ', COALESCE(u.tf_name,''), ' ', COALESCE(u.tl_name,''))), ''),
  p.name_en = NULLIF(TRIM(CONCAT(COALESCE(u.gf_name,''), ' ', COALESCE(u.gl_name,''))), '')
WHERE p.user_uid IS NOT NULL
  AND (
    p.name IS NULL OR p.name = '' OR p.name != TRIM(CONCAT(COALESCE(u.title,''), ' ', COALESCE(u.tf_name,''), ' ', COALESCE(u.tl_name,'')))
    OR p.name_en IS NULL OR p.name_en != TRIM(CONCAT(COALESCE(u.gf_name,''), ' ', COALESCE(u.gl_name,'')))
  );
*/
