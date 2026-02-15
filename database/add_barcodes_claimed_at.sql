-- Add claimed_at to barcodes: set when student clicks "รับบาร์โค้ด" (confirm receipt)
-- Run: mysql -u user -p database_name < database/add_barcodes_claimed_at.sql

ALTER TABLE `barcodes`
    ADD COLUMN `claimed_at` DATETIME DEFAULT NULL
    COMMENT 'เมื่อนักศึกษากดรับบาร์โค้ด (ยืนยันการรับ)'
    AFTER `assigned_at`;
