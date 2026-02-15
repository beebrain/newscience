-- แก้ FK ที่ชี้ไปตาราง user backup หลังรัน clone user (RENAME TABLE user → user_backup_*)
-- สาเหตุ: เมื่อ RENAME TABLE แล้ว MySQL จะอัปเดต FK ให้ชี้ไปชื่อตารางใหม่ (backup)
-- วิธีแก้: ลบ FK แล้วเพิ่มใหม่ให้ชี้ไปที่ตาราง user
-- Run: php scripts/run_fix_personnel_user_fk.php
-- Or: mysql -u user -p newscience < database/fix_personnel_user_fk_references.sql

-- 1) personnel.user_uid → ต้องชี้ไปที่ user(uid)
ALTER TABLE `personnel` DROP FOREIGN KEY `fk_personnel_user`;
ALTER TABLE `personnel`
  ADD CONSTRAINT `fk_personnel_user`
  FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 2) news.author_id → ต้องชี้ไปที่ user(uid) (ถ้ามี constraint นี้และชี้ไป backup)
-- ถ้าไม่มี fk_news_author ให้ comment บรรทัด DROP ออกแล้วรันเฉพาะ ADD
-- ALTER TABLE `news` DROP FOREIGN KEY `fk_news_author`;
-- ALTER TABLE `news`
--   ADD CONSTRAINT `fk_news_author`
--   FOREIGN KEY (`author_id`) REFERENCES `user`(`uid`) ON DELETE SET NULL ON UPDATE CASCADE;
