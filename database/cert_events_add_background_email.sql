-- Prefer: php spark migrate (see app/Database/Migrations/20260413120000_cert_events_background_and_email.php)
-- MySQL 8+ example (adjust/remove columns manually if they already exist):

-- ALTER TABLE cert_events ADD COLUMN background_file VARCHAR(500) NULL;
-- ALTER TABLE cert_events ADD COLUMN background_kind VARCHAR(16) NULL;
-- ALTER TABLE cert_events ADD COLUMN layout_json TEXT NULL;
-- ALTER TABLE cert_event_recipients ADD COLUMN email_sent_at DATETIME NULL;
-- ALTER TABLE cert_event_recipients ADD COLUMN email_error VARCHAR(500) NULL;
