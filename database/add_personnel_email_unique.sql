-- ใช้ email เป็น Unique key สำหรับ personnel เพื่อเชื่อมกับ App ภายนอก (และ user)
-- หนึ่งอีเมลต่อหนึ่งบุคลากร; NULL ได้หลายแถว (MySQL UNIQUE อนุญาตหลาย NULL)
-- Run: php scripts/run_add_personnel_email_unique.php

-- ตรวจสอบ duplicate ก่อนรัน: SELECT email, COUNT(*) FROM personnel WHERE email IS NOT NULL AND email != '' GROUP BY email HAVING COUNT(*) > 1;

ALTER TABLE `personnel`
ADD UNIQUE KEY `email` (`email`);
