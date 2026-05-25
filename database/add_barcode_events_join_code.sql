-- รหัสเข้าร่วมกิจกรรม (นักศึกษากรอกเพื่อขอสิทธิ์)
ALTER TABLE `barcode_events`
  ADD COLUMN `join_code` VARCHAR(32) NULL DEFAULT NULL AFTER `status`,
  ADD UNIQUE KEY `uq_barcode_events_join_code` (`join_code`);
