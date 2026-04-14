<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Certificate System Configuration
 * 
 * Folder Structure:
 * - writable/uploads/cert_system/templates/YYYY/  : PDF templates
 * - writable/uploads/cert_system/certificates/YYYY/ : Generated certificates
 * - writable/uploads/cert_system/temp/templates/    : Template preview temp
 * - writable/uploads/cert_system/temp/import/       : CSV import temp
 * - public/uploads/cert_system/signatures/          : Signer images
 */
class Certificate extends BaseConfig
{
    /**
     * Base directory for template uploads (organized by year)
     */
    public string $templateUploadPath = ROOTPATH . 'writable/uploads/cert_system/templates/';

    /**
     * Base directory for generated certificate PDFs (organized by year)
     */
    public string $certificateOutputPath = ROOTPATH . 'writable/uploads/cert_system/certificates/';

    /**
     * Directory for private signer keys / PFX files (never web-accessible)
     */
    public string $privateKeyPath = ROOTPATH . 'writable/private/cert_keys/';

    /**
     * Directory for temp uploads (templates preview)
     */
    public string $tempTemplatePath = ROOTPATH . 'writable/uploads/cert_system/temp/templates/';

    /**
     * Directory for temp uploads (CSV import)
     */
    public string $tempImportPath = ROOTPATH . 'writable/uploads/cert_system/temp/import/';

    /**
     * Directory for signature images (public)
     */
    public string $signaturePath = FCPATH . 'uploads/cert_system/signatures/';

    /**
     * Prefix patterns for request / certificate numbers
     */
    public string $requestNumberPrefix = 'CR';
    public string $certificateNumberPrefix = 'CERT';

    /**
     * Length (numeric part) for running numbers
     */
    public int $runningDigits = 5;

    /**
     * Token length for verification (bytes before hex encode)
     */
    public int $verificationTokenBytes = 32;

    /**
     * Default PDF protection options
     */
    public array $pdfPermissions = ['print'];

    /**
     * Default certificate validity (days). null = no expiry.
     */
    public ?int $defaultValidityDays = null;

    /**
     * Email notifications toggles
     */
    public bool $notifyStaffOnRequest = true;
    public bool $notifyStudentOnStatusChange = true;
    public bool $notifyApproverOnPending = true;

    /**
     * Temp file cleanup settings
     */
    public int $tempFileMaxAgeHours = 24;

    /**
     * File upload limits (bytes)
     */
    public int $maxTemplateSize = 8 * 1024 * 1024;      // 8 MB
    public int $maxCertificateSize = 5 * 1024 * 1024;    // 5 MB
    public int $maxImportSize = 2 * 1024 * 1024;         // 2 MB
    public int $maxSignatureSize = 1 * 1024 * 1024;      // 1 MB

    /**
     * เคยใช้จำกัดหน่วยงานของผู้จัด — ปัจจุบันการเข้า Dashboard ใช้สิทธิ์จาก session แทน (เก็บไว้เพื่อความเข้ากันได้)
     *
     * @var list<int>
     */
    public array $organizerOrgUnitIds = [];

    /**
     * โฟลเดอร์เก็บพื้นหลังกิจกรรม (relative จาก ROOTPATH writable)
     */
    public string $eventBackgroundUploadPath = ROOTPATH . 'writable/uploads/cert_system/event_backgrounds/';

    /**
     * ค่าเริ่มต้น overlay เมื่อกิจกรรมไม่ใช้ cert_templates (ตำแหน่ง mm บนหน้า A4 แนวตั้ง)
     * แก้ได้ผ่าน env cert.eventDefaultLayoutJson (string JSON เต็ม)
     */
    public string $eventCertificateDefaultLayoutJson = '{"field_mapping":{"student_name":{"x":90,"y":145,"font_size":22},"purpose":{"x":90,"y":168,"font_size":14}},"signature_x":150,"signature_y":200,"qr_x":18,"qr_y":262,"qr_size":22}';

    /**
     * ถ้า user.faculty มีข้อความย่อยใดย่อยหนึ่งในรายการนี้ (ไม่สนตัวพิมพ์) ถือว่าเป็นสังกัดคณะวิทยาศาสตร์และเทคโนโลยี
     * กำหนดเพิ่มได้ที่ env cert.organizerFacultyKeywords (คั่นด้วยจุลภาค)
     *
     * @var list<string>
     */
    public array $certOrganizerFacultyKeywords = [
        'วิทยาศาสตร์และเทคโนโลยี',
        'science and technology',
    ];

    /**
     * โรลที่ไม่บังคับตรวจ user.faculty (เช่น super_admin)
     * env cert.organizerFacultyBypassRoles — คั่นด้วยจุลภาค; ถ้าไม่ตั้งใช้ค่าเริ่มต้นด้านล่าง
     *
     * @var list<string>
     */
    public array $certOrganizerFacultyBypassRoles = ['super_admin'];

    public function __construct()
    {
        parent::__construct();

        $this->templateUploadPath = rtrim(env('cert.templatePath', $this->templateUploadPath), '/') . '/';
        $this->certificateOutputPath = rtrim(env('cert.outputPath', $this->certificateOutputPath), '/') . '/';
        $this->privateKeyPath = rtrim(env('cert.keyPath', $this->privateKeyPath), '/') . '/';
        $this->signaturePath = rtrim(env('cert.signaturePath', $this->signaturePath), '/') . '/';
        $this->requestNumberPrefix = env('cert.requestPrefix', $this->requestNumberPrefix) ?: $this->requestNumberPrefix;
        $this->certificateNumberPrefix = env('cert.certificatePrefix', $this->certificateNumberPrefix) ?: $this->certificateNumberPrefix;
        $this->runningDigits = (int) (env('cert.runningDigits', $this->runningDigits));
        $this->verificationTokenBytes = (int) (env('cert.tokenBytes', $this->verificationTokenBytes));
        $this->defaultValidityDays = env('cert.validityDays', $this->defaultValidityDays);
        $this->notifyStaffOnRequest = (bool) env('cert.notifyStaff', $this->notifyStaffOnRequest);
        $this->notifyStudentOnStatusChange = (bool) env('cert.notifyStudent', $this->notifyStudentOnStatusChange);
        $this->notifyApproverOnPending = (bool) env('cert.notifyApprover', $this->notifyApproverOnPending);

        $orgIds = env('cert.organizerOrgUnitIds', '');
        if (is_string($orgIds) && trim($orgIds) !== '') {
            $this->organizerOrgUnitIds = array_values(array_filter(array_map(
                static fn ($v) => (int) trim((string) $v),
                explode(',', $orgIds)
            ), static fn (int $v) => $v > 0));
        }

        $this->eventBackgroundUploadPath = rtrim(env('cert.eventBackgroundPath', $this->eventBackgroundUploadPath), '/\\') . DIRECTORY_SEPARATOR;

        $layoutEnv = env('cert.eventDefaultLayoutJson', '');
        if (is_string($layoutEnv) && trim($layoutEnv) !== '') {
            $this->eventCertificateDefaultLayoutJson = $layoutEnv;
        }

        $kwEnv = env('cert.organizerFacultyKeywords', null);
        if (is_string($kwEnv) && trim($kwEnv) !== '') {
            $this->certOrganizerFacultyKeywords = array_values(array_filter(array_map(
                static fn (string $s): string => trim($s),
                explode(',', $kwEnv)
            ), static fn (string $s): bool => $s !== ''));
        }

        $bypassEnv = env('cert.organizerFacultyBypassRoles', null);
        if (is_string($bypassEnv)) {
            $this->certOrganizerFacultyBypassRoles = array_values(array_filter(array_map(
                static fn (string $s): string => trim($s),
                explode(',', $bypassEnv)
            ), static fn (string $s): bool => $s !== ''));
        }
    }
}
