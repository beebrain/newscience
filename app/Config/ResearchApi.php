<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration for the external Research Record API
 * (คณะวิทยาศาสตร์และเทคโนโลยี – faculty personnel, publications, etc.)
 *
 * Set in .env:
 *   RESEARCH_API_BASE_URL = base URL of the research record app (no trailing slash)
 *   RESEARCH_API_KEY      = API key (required; must match RR .env RESEARCH_API_KEY or API_KEY)
 *   RESEARCH_API_FACULTY_ID   = faculty ID for คณะวิทยาศาสตร์และเทคโนโลยี (optional if code set)
 *   RESEARCH_API_FACULTY_CODE = faculty code e.g. FSC (optional if id set)
 *
 * ถ้าไม่ได้ตั้ง RESEARCH_API_FACULTY_ID / RESEARCH_API_FACULTY_CODE แต่มี base URL + API key
 * ระบบจะใช้รหัสคณะเริ่มต้น (faculty_id) ของ **คณะวิทยาศาสตร์และเทคโนโลยี** เพื่อไม่ให้ API ขอ faculty_id — โปรด override ใน .env ถ้ารหัสในกบศไม่ตรง
 */
class ResearchApi extends BaseConfig
{
    /** ชื่อคณะฝั่งเว็บ newScience (ใช้ในข้อความแจ้งผู้ใช้) */
    public const FACULTY_NAME_TH = 'คณะวิทยาศาสตร์และเทคโนโลยี';

    /** รหัสคณะเริ่มต้นในระบบกบศเมื่อไม่ได้ตั้งค่าใน .env (ตามตัวอย่าง doc_api.rd) */
    private const DEFAULT_FACULTY_ID = 1;

    /** @var string Base URL of the research record public API (e.g. http://localhost/researchRecord/public/index.php) */
    public string $baseUrl = '';

    /** @var string API key for X-API-KEY header (from .env only) */
    public string $apiKey = '';

    /** @var int|null Faculty ID for faculty-personnel endpoint */
    public ?int $facultyId = null;

    /** @var string|null Faculty code for faculty-personnel endpoint (e.g. FSC) */
    public ?string $facultyCode = null;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl     = rtrim((string) env('RESEARCH_API_BASE_URL', ''), '/');
        $this->apiKey      = trim((string) env('RESEARCH_API_KEY', ''));
        $id                = env('RESEARCH_API_FACULTY_ID');
        $this->facultyId   = $id !== null && $id !== '' ? (int) $id : null;
        $code              = env('RESEARCH_API_FACULTY_CODE');
        $this->facultyCode = $code !== null && $code !== '' ? $code : null;

        if ($this->baseUrl !== '' && $this->apiKey !== '') {
            if ($this->facultyId === null && ($this->facultyCode === null || $this->facultyCode === '')) {
                $this->facultyId = self::DEFAULT_FACULTY_ID;
            }
        }
    }

    /**
     * Base URL + API key (สำหรับซิงค์ CV / publications กับ RR)
     */
    public function syncConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /**
     * Whether the API is configured enough to call faculty-personnel
     */
    public function isConfigured(): bool
    {
        return $this->syncConfigured() && ($this->facultyId !== null || $this->facultyCode !== '');
    }
}
