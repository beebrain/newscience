-- =============================================================================
-- ดึงข้อมูลตาราง user จากฐานข้อมูล Local
-- ใช้สำหรับดูหรือ export ข้อมูล user (email, ชื่อไทย/อังกฤษ, role, status ฯลฯ)
--
-- วิธีรัน:
--   mysql -u root -p newscience < database/fetch_user_table_local.sql
--   mysql -u root -p newscience -e "source database/fetch_user_table_local.sql"
-- Export เป็น CSV (ใน shell):
--   mysql -u root -p newscience -e "SELECT uid, login_uid, email, title, tf_name, tl_name, gf_name, gl_name, role, status, created_at FROM user ORDER BY email;" --batch | sed 's/\t/,/g' > user_table_local.csv
-- =============================================================================

SET NAMES utf8mb4;

-- ดึงทุกคอลัมน์หลักจาก user (ไม่รวม password เพื่อความปลอดภัย)
SELECT
  uid,
  login_uid,
  email,
  title,
  tf_name,
  tl_name,
  gf_name,
  gl_name,
  role,
  program_id,
  status,
  active,
  created_at,
  updated_at
FROM `user`
ORDER BY email;
