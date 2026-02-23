-- =====================================================================
-- E-Certificate Events Schema (Redesigned Flow)
-- =====================================================================
-- เปลี่ยน flow จาก "นักศึกษาขอ" เป็น "Admin สร้างกิจกรรมแล้วออก Certificate ให้"
-- Run: mysql -u root -p newScience < database/cert_events_schema.sql

-- ---------------------------------------------------------------------
-- 1. cert_events (กิจกรรม/หัวข้ออบรม ที่จะออก Certificate)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cert_events (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(500) NOT NULL COMMENT 'ชื่อกิจกรรม/หัวข้ออบรม',
    description     TEXT NULL COMMENT 'รายละเอียดกิจกรรม',
    event_date      DATE NULL COMMENT 'วันที่จัดกิจกรรม',
    template_id     INT UNSIGNED NOT NULL COMMENT 'เทมเพลตที่ใช้',
    signer_id       INT(3) UNSIGNED ZEROFILL NULL COMMENT 'ผู้ลงนาม (FK user.uid)',
    status          ENUM('draft','open','issued','closed') DEFAULT 'draft' COMMENT 'สถานะ',
    created_by      INT(3) UNSIGNED ZEROFILL NULL COMMENT 'ผู้สร้าง (FK user.uid)',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_event_date (event_date),
    CONSTRAINT fk_cert_events_template FOREIGN KEY (template_id) REFERENCES cert_templates(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cert_events_signer FOREIGN KEY (signer_id) REFERENCES user(uid) ON DELETE SET NULL,
    CONSTRAINT fk_cert_events_created_by FOREIGN KEY (created_by) REFERENCES user(uid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. cert_event_recipients (รายชื่อผู้ได้รับ Certificate ในแต่ละกิจกรรม)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cert_event_recipients (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id        INT UNSIGNED NOT NULL COMMENT 'FK cert_events',
    student_id      INT UNSIGNED NULL COMMENT 'FK student_user (ถ้ามีในระบบ)',
    recipient_name  VARCHAR(255) NOT NULL COMMENT 'ชื่อผู้รับ',
    recipient_email VARCHAR(255) NULL COMMENT 'อีเมลผู้รับ',
    recipient_id_no VARCHAR(50) NULL COMMENT 'รหัสนักศึกษา/รหัสประจำตัว',
    extra_data      JSON NULL COMMENT 'ข้อมูลเพิ่มเติม (สาขา, คณะ, เกรด, etc.)',
    certificate_id  INT UNSIGNED NULL COMMENT 'FK certificates (หลังออก cert แล้ว)',
    status          ENUM('pending','issued','failed') DEFAULT 'pending' COMMENT 'สถานะ',
    error_message   VARCHAR(500) NULL COMMENT 'ข้อความ error ถ้าออก Cert ไม่สำเร็จ',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_id (event_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_certificate_id (certificate_id),
    CONSTRAINT fk_cert_event_recipients_event FOREIGN KEY (event_id) REFERENCES cert_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_cert_event_recipients_student FOREIGN KEY (student_id) REFERENCES student_user(id) ON DELETE SET NULL,
    CONSTRAINT fk_cert_event_recipients_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- View สำหรับดึงข้อมูล Certificate ที่ออกจากกิจกรรม
-- =====================================================================
CREATE OR REPLACE VIEW view_cert_event_summary AS
SELECT 
    ce.id AS event_id,
    ce.title AS event_title,
    ce.event_date,
    ce.status AS event_status,
    ct.name_th AS template_name,
    COUNT(cer.id) AS total_recipients,
    SUM(CASE WHEN cer.status = 'issued' THEN 1 ELSE 0 END) AS issued_count,
    SUM(CASE WHEN cer.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
    SUM(CASE WHEN cer.status = 'failed' THEN 1 ELSE 0 END) AS failed_count
FROM cert_events ce
LEFT JOIN cert_templates ct ON ct.id = ce.template_id
LEFT JOIN cert_event_recipients cer ON cer.event_id = ce.id
GROUP BY ce.id, ce.title, ce.event_date, ce.status, ct.name_th;
