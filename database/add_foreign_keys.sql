-- เพิ่ม Foreign Keys ตามที่ review ไว้
-- นโยบาย: ไม่ลบ User (ถอดถอนสิทธิ์เท่านั้น)
-- Run: php scripts/run_add_foreign_keys.php

-- 1) personnel_programs.personnel_id → personnel.id (ลบ personnel แล้วลบแถว pivot)
ALTER TABLE `personnel_programs`
ADD CONSTRAINT `fk_pp_personnel`
FOREIGN KEY (`personnel_id`) REFERENCES `personnel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 2) personnel_programs.program_id → programs.id (ลบหลักสูตรแล้วลบแถว pivot)
ALTER TABLE `personnel_programs`
ADD CONSTRAINT `fk_pp_program`
FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 3) personnel.user_uid → user.uid (ไม่ลบ user; RESTRICT ป้องกันการลบถ้ามี personnel ผูกอยู่)
ALTER TABLE `personnel`
ADD CONSTRAINT `fk_personnel_user`
FOREIGN KEY (`user_uid`) REFERENCES `user`(`uid`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 4) news.author_id → user.uid (ถ้าลบ user ข่าวยังอยู่ แค่ author_id เป็น NULL)
ALTER TABLE `news`
ADD CONSTRAINT `fk_news_author`
FOREIGN KEY (`author_id`) REFERENCES `user`(`uid`) ON DELETE SET NULL ON UPDATE CASCADE;
