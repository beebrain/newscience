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
    }
}
