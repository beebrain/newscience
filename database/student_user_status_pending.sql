-- Add pending status for student_user (pre-registered by email before first Portal login)
-- Run: mysql -u user -p database_name < database/student_user_status_pending.sql

ALTER TABLE `student_user`
MODIFY COLUMN `status` ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active'
COMMENT 'active=ใช้งาน, inactive=ปิด, pending=รอเปิดจาก Portal ครั้งแรก';
