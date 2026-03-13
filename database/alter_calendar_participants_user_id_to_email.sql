-- =====================================================
-- เปลี่ยน calendar_participants จาก user_id เป็น user_email
-- รันเฉพาะเมื่อมีตาราง calendar_participants แบบเก่า (มีคอลัมน์ user_id) อยู่แล้ว
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- เพิ่มคอลัมน์ user_email
ALTER TABLE `calendar_participants`
    ADD COLUMN `user_email` VARCHAR(255) NULL AFTER `event_id`;

-- คัดลอก email จาก user ตาม user_id
UPDATE `calendar_participants` cp
INNER JOIN `user` u ON u.uid = cp.user_id
SET cp.user_email = u.email;

-- บังคับ NOT NULL หลัง backfill
ALTER TABLE `calendar_participants`
    MODIFY COLUMN `user_email` VARCHAR(255) NOT NULL;

-- ลบ FK และคอลัมน์ user_id
ALTER TABLE `calendar_participants`
    DROP FOREIGN KEY `fk_calendar_participants_user`;
ALTER TABLE `calendar_participants`
    DROP INDEX `uq_calendar_participants_event_user`,
    DROP INDEX `user_id`;
ALTER TABLE `calendar_participants`
    DROP COLUMN `user_id`;

-- สร้าง unique และ index ใหม่
ALTER TABLE `calendar_participants`
    ADD UNIQUE KEY `uq_calendar_participants_event_email` (`event_id`, `user_email`),
    ADD KEY `user_email` (`user_email`);

SET FOREIGN_KEY_CHECKS = 1;
