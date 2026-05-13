-- E-document Gmail-style layout: per-user labels, flags, and forward log.
-- Run: mysql -u root -p newscience < database/edoc_gmail_layout.sql
--
-- หลักการ:
--   * ไม่แตะ edoctitle / fileaddress  -> ไฟล์จริงยังเก็บที่เดียว (no duplication)
--   * Labels เป็น per-user (เหมือน Gmail) — แต่ละคนเห็น label ของตัวเอง
--   * Forward = เพิ่ม recipient ผ่าน edoc_document_tags (มีอยู่แล้ว) + log ที่ edoc_forwards
--   * ไม่มี hard delete สำหรับ user  -> ใช้ is_archived แทน

SET NAMES utf8mb4;

-- 1) Labels ที่ผู้ใช้ตั้งชื่อเอง (Gmail-style custom labels)
CREATE TABLE IF NOT EXISTS `edoc_user_labels` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_email`  VARCHAR(255) NOT NULL,
    `name`        VARCHAR(100) NOT NULL COMMENT 'ชื่อ label ที่ user ตั้ง',
    `color`       VARCHAR(20)  NOT NULL DEFAULT '#6b7280' COMMENT 'hex color เช่น #d4af37',
    `sort_order`  INT NOT NULL DEFAULT 0,
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_label_name` (`user_email`, `name`),
    KEY `idx_user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Mapping เอกสาร <-> label (per user, M:N)
CREATE TABLE IF NOT EXISTS `edoc_document_labels` (
    `document_id` INT UNSIGNED NOT NULL COMMENT 'edoctitle.iddoc',
    `user_email`  VARCHAR(255) NOT NULL,
    `label_id`    INT UNSIGNED NOT NULL,
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`document_id`, `user_email`, `label_id`),
    KEY `idx_user_label` (`user_email`, `label_id`),
    KEY `idx_label` (`label_id`),
    CONSTRAINT `fk_edl_label`
        FOREIGN KEY (`label_id`) REFERENCES `edoc_user_labels`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Per-user state: star / important / read / archive
--    1 row ต่อ (document, user) — สร้างเมื่อ user มี interaction ครั้งแรก
CREATE TABLE IF NOT EXISTS `edoc_user_flags` (
    `document_id`  INT UNSIGNED NOT NULL,
    `user_email`   VARCHAR(255) NOT NULL,
    `is_starred`   TINYINT(1) NOT NULL DEFAULT 0,
    `is_important` TINYINT(1) NOT NULL DEFAULT 0,
    `is_archived`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'ซ่อนจาก Inbox (ไม่ใช่ลบ)',
    `read_at`      DATETIME NULL DEFAULT NULL COMMENT 'NULL = ยังไม่อ่าน',
    `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`document_id`, `user_email`),
    KEY `idx_user_starred`  (`user_email`, `is_starred`),
    KEY `idx_user_archived` (`user_email`, `is_archived`),
    KEY `idx_user_unread`   (`user_email`, `read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Forward log — ไฟล์ไม่ถูก duplicate
--    การ "ส่งต่อ" = INSERT row นี้ + เพิ่ม recipient ใน edoc_document_tags
CREATE TABLE IF NOT EXISTS `edoc_forwards` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id`   INT UNSIGNED NOT NULL,
    `from_email`    VARCHAR(255) NOT NULL,
    `to_email`      VARCHAR(255) NOT NULL,
    `note`          TEXT NULL COMMENT 'ข้อความ/เหตุผลที่ส่งต่อ',
    `forwarded_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_doc_to`   (`document_id`, `to_email`),
    KEY `idx_doc_from` (`document_id`, `from_email`),
    KEY `idx_to_email` (`to_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
