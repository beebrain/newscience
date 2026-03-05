-- =============================================================================
-- Script: อัปเดต tf_name และ tl_name ในตาราง user (และ student_user) โดยใช้ email
-- ใช้ข้อมูลในฐานข้อมูล local เป็นหลัก (personnel หรือตารางต้นทางที่กำหนด)
--
-- วิธีรัน:
--   mysql -u root -p newscience < database/update_user_tf_tl_by_email.sql
-- หรือเปิดใน MySQL client แล้วรันทีละส่วน (ตรวจสอบผลก่อนรันส่วนถัดไป)
-- =============================================================================

SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- วิธีที่ 1: อัปเดต user จากตาราง personnel (จับคู่ด้วย email)
-- ใช้ personnel.name (ชื่อ-นามสกุลไทยเต็ม) แยกเป็น tf_name = คำแรก, tl_name = ส่วนที่เหลือ
-- ลบคำนำหน้าชื่อ (ศ.ดร., รศ., ผศ., อ., ดร., นาย, นาง, นางสาว) ออกจาก name ก่อนแยก
-- -----------------------------------------------------------------------------

-- 1.1 อัปเดต user.tf_name, user.tl_name จาก personnel โดยจับคู่ email
UPDATE `user` u
INNER JOIN `personnel` p ON LOWER(TRIM(u.email)) = LOWER(TRIM(p.email))
SET
  u.tf_name = TRIM(SUBSTRING_INDEX(
    TRIM(
      REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
      REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        COALESCE(p.name, ''),
        'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
        'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
        'นางสาว', ''), 'นาง', ''), 'นาย', '')
    ),
    ' ', 1
  )),
  u.tl_name = NULLIF(TRIM(SUBSTRING(
    TRIM(
      REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
      REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        COALESCE(p.name, ''),
        'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
        'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
        'นางสาว', ''), 'นาง', ''), 'นาย', '')
    ),
    LOCATE(' ', TRIM(
      REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
      REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        COALESCE(p.name, ''),
        'ศ.ดร.', ''), 'รศ.ดร.', ''), 'ผศ.ดร.', ''), 'อ.ดร.', ''), 'ดร.', ''),
        'ศ.', ''), 'รศ.', ''), 'ผศ.', ''), 'อ.', ''),
        'นางสาว', ''), 'นาง', ''), 'นาย', '')
    )) + 1
  )), '')
WHERE p.name IS NOT NULL AND p.name != '';

-- -----------------------------------------------------------------------------
-- วิธีที่ 2 (ทางเลือก): ใช้ตารางต้นทางที่มีคอลัมน์ email, tf_name, tl_name โดยตรง
-- สร้างตารางชั่วคราวหรือใช้ตารางที่มีอยู่ แล้วรัน UPDATE ด้านล่าง
-- -----------------------------------------------------------------------------

/*
-- ตัวอย่าง: สร้างตารางชั่วคราวและใส่ข้อมูลจากที่มาใน local (เช่น export, ไฟล์อื่น)
CREATE TEMPORARY TABLE IF NOT EXISTS `_user_tf_tl_source` (
  `email` VARCHAR(255) NOT NULL,
  `tf_name` VARCHAR(255) DEFAULT NULL,
  `tl_name` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ใส่ข้อมูลต้นทาง (แก้ path/ค่าตามจริง)
-- LOAD DATA LOCAL INFILE '/path/to/names.csv'
--   INTO TABLE _user_tf_tl_source FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
--   IGNORE 1 ROWS (email, tf_name, tl_name);

-- หรือ INSERT ด้วยมือ เช่น
-- INSERT INTO _user_tf_tl_source (email, tf_name, tl_name) VALUES
--   ('user1@uru.ac.th', 'ชื่อ', 'นามสกุล'),
--   ('user2@uru.ac.th', 'ชื่อสอง', 'นามสกุลสอง');

-- อัปเดต user จากตารางต้นทาง (จับคู่ด้วย email)
UPDATE `user` u
INNER JOIN `_user_tf_tl_source` s ON LOWER(TRIM(u.email)) = LOWER(TRIM(s.email))
SET
  u.tf_name = NULLIF(TRIM(s.tf_name), ''),
  u.tl_name = NULLIF(TRIM(s.tl_name), '')
WHERE s.tf_name IS NOT NULL OR s.tl_name IS NOT NULL;

-- ลบตารางชั่วคราวเมื่อใช้เสร็จ
-- DROP TEMPORARY TABLE IF EXISTS _user_tf_tl_source;
*/

-- -----------------------------------------------------------------------------
-- (ถ้ามี) อัปเดต student_user จากตารางต้นทางใน local
-- ถ้าในฐานข้อมูลมีตารางที่เก็บชื่อนักศึกษาแบบมี email + ชื่อไทย (เช่น student_user
-- อัปเดตจาก personnel ไม่ได้เพราะ personnel เป็นบุคลากร) สามารถใช้วิธีที่ 2
-- โดยสร้าง _student_tf_tl_source (email, tf_name, tl_name) แล้วรันด้านล่าง
-- -----------------------------------------------------------------------------

/*
CREATE TEMPORARY TABLE IF NOT EXISTS `_student_tf_tl_source` (
  `email` VARCHAR(255) NOT NULL,
  `tf_name` VARCHAR(255) DEFAULT NULL,
  `tl_name` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ใส่ข้อมูลต้นทางแล้วรัน:
UPDATE `student_user` su
INNER JOIN `_student_tf_tl_source` s ON LOWER(TRIM(su.email)) = LOWER(TRIM(s.email))
SET
  su.tf_name = NULLIF(TRIM(s.tf_name), ''),
  su.tl_name = NULLIF(TRIM(s.tl_name), '')
WHERE s.tf_name IS NOT NULL OR s.tl_name IS NOT NULL;

DROP TEMPORARY TABLE IF EXISTS _student_tf_tl_source;
*/

-- -----------------------------------------------------------------------------
-- ตรวจสอบผล (รันหลัง UPDATE แล้ว)
-- -----------------------------------------------------------------------------
-- SELECT uid, email, tf_name, tl_name, gf_name, gl_name FROM `user` ORDER BY email LIMIT 50;
-- SELECT id, email, tf_name, tl_name FROM `student_user` ORDER BY email LIMIT 50;
