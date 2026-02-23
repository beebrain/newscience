-- =====================================================================
-- E-Certificate System Schema
-- =====================================================================
-- Run these statements after existing migrations. Each table uses utf8mb4.

-- ---------------------------------------------------------------------
-- 1. cert_templates
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cert_templates (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_th         VARCHAR(255) NOT NULL,
    name_en         VARCHAR(255) NULL,
    level           ENUM('program','faculty') NOT NULL,
    template_file   VARCHAR(500) NULL,
    field_mapping   JSON NULL,
    signature_x     DECIMAL(6,2) DEFAULT 300.00,
    signature_y     DECIMAL(6,2) DEFAULT 600.00,
    qr_x            DECIMAL(6,2) DEFAULT 450.00,
    qr_y            DECIMAL(6,2) DEFAULT 700.00,
    qr_size         DECIMAL(6,2) DEFAULT 60.00,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_by      INT(3) UNSIGNED ZEROFILL NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    CONSTRAINT fk_cert_templates_created_by FOREIGN KEY (created_by) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. cert_requests
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cert_requests (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_number  VARCHAR(20) NOT NULL UNIQUE,
    student_id      INT UNSIGNED NOT NULL,
    template_id     INT UNSIGNED NOT NULL,
    program_id      INT UNSIGNED NULL,
    level           ENUM('program','faculty') NOT NULL,
    purpose         VARCHAR(500) NULL,
    copies          TINYINT UNSIGNED DEFAULT 1,
    note            TEXT NULL,
    status          ENUM('pending','verified','approved','generating','completed','rejected') DEFAULT 'pending',
    rejected_reason VARCHAR(500) NULL,
    verified_by     INT(3) UNSIGNED ZEROFILL NULL,
    verified_at     DATETIME NULL,
    approved_by     INT(3) UNSIGNED ZEROFILL NULL,
    approved_at     DATETIME NULL,
    completed_at    DATETIME NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cert_requests_student (student_id),
    INDEX idx_cert_requests_status (status),
    INDEX idx_cert_requests_program (program_id),
    CONSTRAINT fk_cert_requests_student FOREIGN KEY (student_id) REFERENCES student_user(id),
    CONSTRAINT fk_cert_requests_template FOREIGN KEY (template_id) REFERENCES cert_templates(id),
    CONSTRAINT fk_cert_requests_program FOREIGN KEY (program_id) REFERENCES programs(id),
    CONSTRAINT fk_cert_requests_verified_by FOREIGN KEY (verified_by) REFERENCES user(uid),
    CONSTRAINT fk_cert_requests_approved_by FOREIGN KEY (approved_by) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. cert_approvals (audit trail)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cert_approvals (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      INT UNSIGNED NOT NULL,
    action          ENUM('submit','verify','approve','reject','return') NOT NULL,
    actor_id        INT(3) UNSIGNED ZEROFILL NOT NULL,
    actor_role      VARCHAR(50) NULL,
    comment         TEXT NULL,
    ip_address      VARCHAR(45) NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cert_approvals_request (request_id),
    CONSTRAINT fk_cert_approvals_request FOREIGN KEY (request_id) REFERENCES cert_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_cert_approvals_actor FOREIGN KEY (actor_id) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. certificates (issued documents)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS certificates (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id          INT UNSIGNED NOT NULL,
    certificate_no      VARCHAR(30) NOT NULL UNIQUE,
    pdf_path            VARCHAR(500) NOT NULL,
    pdf_hash            VARCHAR(64) NOT NULL,
    verification_token  VARCHAR(64) NOT NULL UNIQUE,
    student_snapshot    JSON NULL,
    signed_by           INT(3) UNSIGNED ZEROFILL NULL,
    signed_at           DATETIME NULL,
    download_count      INT UNSIGNED DEFAULT 0,
    last_downloaded_at  DATETIME NULL,
    issued_date         DATE NOT NULL,
    expiry_date         DATE NULL,
    is_revoked          TINYINT(1) DEFAULT 0,
    revoked_reason      VARCHAR(500) NULL,
    revoked_at          DATETIME NULL,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_certificates_request (request_id),
    CONSTRAINT fk_certificates_request FOREIGN KEY (request_id) REFERENCES cert_requests(id),
    CONSTRAINT fk_certificates_signed_by FOREIGN KEY (signed_by) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. cert_signers (authorized signers + certificates)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cert_signers (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_uid            INT(3) UNSIGNED ZEROFILL NOT NULL,
    signer_role         ENUM('program_chair','dean') NOT NULL,
    program_id          INT UNSIGNED NULL,
    signature_image     VARCHAR(500) NULL,
    pfx_path            VARCHAR(500) NULL,
    pfx_password_enc    VARCHAR(255) NULL,
    is_active           TINYINT(1) DEFAULT 1,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cert_signers_user_role_program (user_uid, signer_role, program_id),
    CONSTRAINT fk_cert_signers_user FOREIGN KEY (user_uid) REFERENCES user(uid),
    CONSTRAINT fk_cert_signers_program FOREIGN KEY (program_id) REFERENCES programs(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
