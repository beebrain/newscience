-- ตาราง oauth_login_log: บันทึกการ login/logout ผ่าน URU Portal OAuth
-- ใช้ตรวจสอบปัญหาการ login ครั้งแรก และ audit trail

CREATE TABLE IF NOT EXISTS `oauth_login_log` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event`       VARCHAR(50)  NOT NULL COMMENT 'login_student, login_personnel, logout, error, first_login',
    `email`       VARCHAR(255) NOT NULL DEFAULT '',
    `login_uid`   VARCHAR(255) NOT NULL DEFAULT '',
    `user_type`   ENUM('student','personnel','unknown') NOT NULL DEFAULT 'unknown',
    `user_id`     INT UNSIGNED NULL     COMMENT 'uid (user) หรือ id (student_user)',
    `is_new_user` TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = สร้าง record ใหม่ในฐานข้อมูล',
    `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
    `message`     VARCHAR(500) NOT NULL DEFAULT '',
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_email`      (`email`),
    INDEX `idx_login_uid`  (`login_uid`),
    INDEX `idx_event`      (`event`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='บันทึกการ login/logout ผ่าน URU Portal OAuth 2.0';
