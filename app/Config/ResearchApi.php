<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration for the external Research Record API
 * (คณะวิทยาศาสตร์และเทคโนโลยี – faculty personnel, publications, etc.)
 *
 * Set in .env:
 *   RESEARCH_API_BASE_URL = base URL of the research record app (no trailing slash)
 *   RESEARCH_API_KEY      = API key (default on their side: URU_RESEARCH)
 *   RESEARCH_API_FACULTY_ID   = faculty ID for คณะวิทยาศาสตร์และเทคโนโลยี (optional if code set)
 *   RESEARCH_API_FACULTY_CODE = faculty code e.g. FSC (optional if id set)
 */
class ResearchApi extends BaseConfig
{
    /** @var string Base URL of the research record public API (e.g. http://localhost/researchRecord/public/index.php) */
    public string $baseUrl = '';

    /** @var string API key for X-API-KEY header (default URU_RESEARCH as per their doc) */
    public string $apiKey = 'URU_RESEARCH';

    /** @var int|null Faculty ID for faculty-personnel endpoint */
    public ?int $facultyId = null;

    /** @var string|null Faculty code for faculty-personnel endpoint (e.g. FSC) */
    public ?string $facultyCode = null;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl     = rtrim(env('RESEARCH_API_BASE_URL', ''), '/');
        $this->apiKey      = env('RESEARCH_API_KEY', 'URU_RESEARCH');
        $id                = env('RESEARCH_API_FACULTY_ID');
        $this->facultyId   = $id !== null && $id !== '' ? (int) $id : null;
        $code              = env('RESEARCH_API_FACULTY_CODE');
        $this->facultyCode = $code !== null && $code !== '' ? $code : null;
    }

    /**
     * Whether the API is configured enough to call faculty-personnel
     */
    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && ($this->facultyId !== null || $this->facultyCode !== '');
    }
}
